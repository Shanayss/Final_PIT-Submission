<?php

namespace App\Http\Controllers\ClinicalStaff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClinicalTreatmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $treatmentsQuery = DB::table('treatments')
            ->join('diagnoses', 'diagnoses.diagnosis_id', '=', 'treatments.diagnosis_id')
            ->leftJoin('appointments', 'diagnoses.appointment_id', '=', 'appointments.appointment_id')
            ->leftJoin('patients', 'treatments.patient_number', '=', 'patients.patient_number')
            ->leftJoin('staff as nurses', 'treatments.staff_number', '=', 'nurses.staff_number')
            ->select(
                'treatments.treatment_id as id',
                'diagnoses.appointment_id',
                'diagnoses.diagnosis_details as diagnosis',
                'treatments.procedure_name as procedure',
                DB::raw("'N/A' as medication"),
                DB::raw("'Completed' as status"),
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(patients.first_name, ' ', patients.last_name)), ''),
                        treatments.patient_number
                    ) as patient_name
                "),
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(nurses.first_name, ' ', nurses.last_name)), ''),
                        treatments.staff_number,
                        'Not assigned'
                    ) as nurse_name
                ")
            );

        if ($search) {
            $treatmentsQuery->where(function ($query) use ($search) {
                $query->where('patients.first_name', 'like', "%{$search}%")
                    ->orWhere('patients.last_name', 'like', "%{$search}%")
                    ->orWhere('diagnoses.diagnosis_details', 'like', "%{$search}%")
                    ->orWhere('treatments.procedure_name', 'like', "%{$search}%")
                    ->orWhere('treatments.results', 'like', "%{$search}%")
                    ->orWhere('treatments.patient_number', 'like', "%{$search}%");
            });
        }

        $treatments = $treatmentsQuery
            ->orderByDesc('treatments.treatment_id')
            ->get();

        $total = DB::table('treatments')->count();
        $pending = 0;
        $progress = 0;
        $completed = $total;

        return view('ClinicalStaff.treatments', compact('treatments', 'total', 'pending', 'progress', 'completed'));
    }

    public function create($appointment_id)
    {
        $appointment = DB::table('appointments')
            ->leftJoin('patients', 'appointments.patient_number', '=', 'patients.patient_number')
            ->leftJoin('staff', 'appointments.staff_number', '=', 'staff.staff_number')
            ->select(
                'appointments.appointment_id',
                'appointments.patient_number',
                'appointments.staff_number',
                'appointments.clinic_number',
                'appointments.appointment_date',
                'appointments.appointment_time',
                'appointments.examination_room',
                'appointments.status',
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(patients.first_name, ' ', patients.last_name)), ''),
                        appointments.patient_number
                    ) as patient_name
                "),
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(staff.first_name, ' ', staff.last_name)), ''),
                        appointments.staff_number
                    ) as doctor_name
                ")
            )
            ->where('appointments.appointment_id', $appointment_id)
            ->first();

        if (! $appointment) {
            abort(404);
        }

        $existingDiagnosis = DB::table('diagnoses')
            ->where('appointment_id', $appointment_id)
            ->orderByDesc('diagnosis_id')
            ->first();

        $existingTreatment = null;
        if ($existingDiagnosis) {
            $treatment = DB::table('treatments')->where('diagnosis_id', $existingDiagnosis->diagnosis_id)->first();
            if ($treatment) {
                $existingTreatment = (object) [
                    'diagnosis' => $existingDiagnosis->diagnosis_details,
                    'procedure' => $treatment->procedure_name,
                    'medication' => DB::table('patient_medications')
                        ->where('patient_number', $appointment->patient_number)
                        ->where('staff_number', $appointment->staff_number)
                        ->orderByDesc('medication_id')
                        ->value('drug_number'),
                    'assigned_nurse' => $treatment->staff_number,
                    'status' => 'Completed',
                    'additional_notes' => $treatment->results,
                ];
            }
        }

        $nurses = DB::table('staff')
            ->whereIn('position', ['Nurse', 'Charge Nurse', 'Senior Nurse', 'Junior Nurse', 'Nursing Staff'])
            ->orderBy('first_name')
            ->get();

        $medications = DB::table('drugs')->orderBy('drug_name')->get();
        $procedures = Schema::hasTable('procedures')
            ? DB::table('procedures')->orderBy('procedure_name')->get()
            : collect([
                (object) ['procedure_name' => 'Observation and Monitoring'],
                (object) ['procedure_name' => 'Medication Review'],
                (object) ['procedure_name' => 'Diagnostic Test'],
                (object) ['procedure_name' => 'Ward Admission Referral'],
            ]);
        $wards = DB::table('wards')->where('total_beds', '>', 0)->orderBy('ward_name')->get();

        $activeInPatient = DB::table('in_patients')
            ->where('patient_number', $appointment->patient_number)
            ->whereNull('date_actual_leave')
            ->first();

        return view('ClinicalStaff.treatments.create', compact(
            'appointment',
            'existingTreatment',
            'nurses',
            'medications',
            'procedures',
            'wards',
            'activeInPatient'
        ));
    }

    public function store(Request $request, $appointment_id)
    {
        $data = $request->validate([
            'diagnosis' => ['required', 'string', 'max:255'],
            'procedure' => ['required', 'string', 'max:100'],
            'medication' => ['nullable', 'exists:drugs,drug_number'],
            'unit_per_day' => ['required_with:medication', 'nullable', 'integer', 'min:1', 'max:24'],
            'medication_start_date' => ['required_with:medication', 'nullable', 'date'],
            'medication_end_date' => ['required_with:medication', 'nullable', 'date', 'after_or_equal:medication_start_date'],
            'assigned_nurse' => ['nullable', 'exists:staff,staff_number'],
            'status' => ['required', 'string', 'in:Pending,In Progress,Completed'],
            'additional_notes' => ['nullable', 'string'],
            'place_on_waiting_list' => ['nullable', 'boolean'],
            'ward_number' => ['required_if:place_on_waiting_list,1', 'nullable', 'exists:wards,ward_number'],
            'expected_stay_days' => ['required_if:place_on_waiting_list,1', 'nullable', 'integer', 'min:1', 'max:365'],
            'date_placed_on_waiting_list' => ['required_if:place_on_waiting_list,1', 'nullable', 'date'],
        ]);

        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointment_id)
            ->first();

        if (! $appointment) {
            abort(404);
        }

        DB::transaction(function () use ($appointment, $appointment_id, $data) {
            $existingDiagnosis = DB::table('diagnoses')
                ->where('appointment_id', $appointment_id)
                ->orderByDesc('diagnosis_id')
                ->first();

            $diagnosisPayload = [
                'appointment_id' => $appointment_id,
                'patient_number' => $appointment->patient_number,
                'staff_number' => $appointment->staff_number,
                'diagnosis_details' => $data['diagnosis'],
                'diagnosis_date' => today(),
                'notes' => $data['additional_notes'] ?? null,
            ];

            if ($existingDiagnosis) {
                DB::table('diagnoses')
                    ->where('diagnosis_id', $existingDiagnosis->diagnosis_id)
                    ->update($diagnosisPayload);
                $diagnosisId = $existingDiagnosis->diagnosis_id;
            } else {
                $diagnosisId = DB::table('diagnoses')->insertGetId($diagnosisPayload, 'diagnosis_id');
            }

            $treatmentPayload = [
                'patient_number' => $appointment->patient_number,
                'diagnosis_id' => $diagnosisId,
                'staff_number' => $data['assigned_nurse'] ?? $appointment->staff_number,
                'procedure_name' => $data['procedure'],
                'treatment_date' => today(),
                'treatment_time' => now()->format('H:i:s'),
                'results' => trim(implode("\n", array_filter([
                    $data['medication'] ? 'Medication: ' . $data['medication'] : null,
                    'Status: ' . $data['status'],
                    $data['additional_notes'] ?? null,
                ]))),
            ];

            $existingTreatment = DB::table('treatments')
                ->where('diagnosis_id', $diagnosisId)
                ->first();

            if ($existingTreatment) {
                DB::table('treatments')
                    ->where('treatment_id', $existingTreatment->treatment_id)
                    ->update($treatmentPayload);
            } else {
                DB::table('treatments')->insert($treatmentPayload);
            }

            if (! empty($data['medication'])) {
                DB::table('patient_medications')->insert([
                    'staff_number' => $appointment->staff_number,
                    'patient_number' => $appointment->patient_number,
                    'drug_number' => $data['medication'],
                    'unit_per_day' => $data['unit_per_day'],
                    'start_date' => $data['medication_start_date'],
                    'end_date' => $data['medication_end_date'],
                ]);
            }

            DB::table('appointments')
                ->where('appointment_id', $appointment_id)
                ->update(['status' => $data['status'] === 'Completed' ? 'Completed' : 'Scheduled']);

            $hasWaitingListDetails = ! empty($data['ward_number'])
                && ! empty($data['expected_stay_days'])
                && ! empty($data['date_placed_on_waiting_list']);

            if (! empty($data['place_on_waiting_list']) || $hasWaitingListDetails) {
                $alreadyWaitingOrAdmitted = DB::table('in_patients')
                    ->where('patient_number', $appointment->patient_number)
                    ->whereNull('date_actual_leave')
                    ->exists();

                if (! $alreadyWaitingOrAdmitted) {
                    DB::table('in_patients')->insert([
                        'patient_number' => $appointment->patient_number,
                        'ward_number' => $data['ward_number'],
                        'bed_number' => null,
                        'date_placed_on_waiting_list' => $data['date_placed_on_waiting_list'],
                        'expected_stay_days' => $data['expected_stay_days'],
                        'date_admitted' => today()->toDateString(),
                        'date_expected_leave' => null,
                        'date_actual_leave' => null,
                        'status' => 'Waiting',
                    ]);
                }
            }
        });

        return redirect()
            ->route('clinical.treatments')
            ->with('success', 'Treatment record saved successfully.');
    }

    public function show($treatment_id)
    {
        $treatment = DB::table('treatments')
            ->join('diagnoses', 'diagnoses.diagnosis_id', '=', 'treatments.diagnosis_id')
            ->leftJoin('appointments', 'diagnoses.appointment_id', '=', 'appointments.appointment_id')
            ->leftJoin('patients', 'treatments.patient_number', '=', 'patients.patient_number')
            ->leftJoin('staff as doctors', 'appointments.staff_number', '=', 'doctors.staff_number')
            ->leftJoin('staff as nurses', 'treatments.staff_number', '=', 'nurses.staff_number')
            ->select(
                'treatments.treatment_id as id',
                'diagnoses.appointment_id',
                'diagnoses.diagnosis_details as diagnosis',
                'treatments.procedure_name as procedure',
                DB::raw("'N/A' as medication"),
                DB::raw("'Completed' as status"),
                'treatments.results as additional_notes',
                'appointments.patient_number',
                'appointments.appointment_date',
                'appointments.appointment_time',
                'appointments.examination_room',
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(patients.first_name, ' ', patients.last_name)), ''),
                        appointments.patient_number
                    ) as patient_name
                "),
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(doctors.first_name, ' ', doctors.last_name)), ''),
                        appointments.staff_number
                    ) as doctor_name
                "),
                DB::raw("
                    COALESCE(
                        NULLIF(TRIM(CONCAT(nurses.first_name, ' ', nurses.last_name)), ''),
                        treatments.staff_number,
                        'Not assigned'
                    ) as nurse_name
                ")
            )
            ->where('treatments.treatment_id', $treatment_id)
            ->first();

        if (! $treatment) {
            abort(404);
        }

        return view('ClinicalStaff.treatments.show', compact('treatment'));
    }

    public function destroy($treatment_id)
    {
        DB::table('treatments')->where('treatment_id', $treatment_id)->delete();

        return redirect()
            ->route('clinical.treatments')
            ->with('success', 'Treatment record deleted successfully.');
    }

    private function waitingListAdmissionDate(string $waitingListDate): ?string
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $waitingListDate;
        }

        $isNullable = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'in_patients')
            ->where('column_name', 'date_admitted')
            ->value('is_nullable');

        return $isNullable === 'YES' ? null : $waitingListDate;
    }
}
