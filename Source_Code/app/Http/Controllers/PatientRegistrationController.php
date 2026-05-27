<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientRegistrationController extends Controller
{
    public function index()
    {
        $localDoctors = DB::table('local_doctors')->orderBy('full_name')->get();

        return view('components.patient-registration-modal', compact('localDoctors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'address' => ['nullable', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:20'],
            'kin_name' => ['nullable', 'string', 'max:100'],
            'kin_relationship' => ['nullable', 'string', 'max:50'],
            'kin_address' => ['nullable', 'string', 'max:100'],
            'kin_telephone' => ['nullable', 'string', 'max:20'],
            'clinic_number' => ['nullable', 'exists:local_doctors,clinic_number'],
        ]);

        try {
            $patientNumber = null;

            DB::transaction(function () use ($data, &$patientNumber) {
                $patientNumber = $this->nextPatientNumber();

                DB::table('patients')->insert([
                    'patient_number' => $patientNumber,
                    'clinic_number' => $data['clinic_number'] ?? null,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'address' => $data['address'] ?? null,
                    'telephone' => $data['telephone'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                    'date_registered' => today(),
                ]);

                $this->syncPostgresPatientSequence();

                if (! empty($data['kin_name'])) {
                    DB::table('next_of_kins')->insert([
                        'patient_number' => $patientNumber,
                        'full_name' => $data['kin_name'],
                        'relationship' => $data['kin_relationship'] ?? 'Emergency Contact',
                        'address' => $data['kin_address'] ?? null,
                        'telephone' => $data['kin_telephone'] ?? null,
                    ]);
                }

                $this->scheduleInitialConsultantAppointment($patientNumber, $data['clinic_number'] ?? null);
            });

            $patient = DB::table('patients')->where('patient_number', $patientNumber)->first();
            $nextOfKin = DB::table('next_of_kins')->where('patient_number', $patientNumber)->first();

            return response()->json([
                'success' => true,
                'patient_number' => $patientNumber,
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient' => $patient,
                'next_of_kin' => $nextOfKin,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering patient: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getRecentPatients()
    {
        $patients = DB::table('patients')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('in_patients')
                    ->whereRaw('in_patients.patient_number = patients.patient_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->orderByDesc('date_registered')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'patients' => $patients,
        ]);
    }

    public function getPatientDetails(string $patientNumber)
    {
        $patient = DB::table('patients')
            ->where('patient_number', $patientNumber)
            ->first();

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.',
            ], 404);
        }

        $nextOfKin = DB::table('next_of_kins')
            ->where('patient_number', $patientNumber)
            ->first();

        $localDoctor = $patient->clinic_number
            ? DB::table('local_doctors')->where('clinic_number', $patient->clinic_number)->first()
            : null;

        return response()->json([
            'success' => true,
            'patient' => $patient,
            'next_of_kin' => $nextOfKin,
            'local_doctor' => $localDoctor,
        ]);
    }

    private function nextPatientNumber(): string
    {
        $latest = DB::table('patients')->max('patient_number');
        $number = $latest ? ((int) substr($latest, 1)) + 1 : 1;

        return 'P' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    private function usesPostgreSql(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    private function syncPostgresPatientSequence(): void
    {
        if (! $this->usesPostgreSql()) {
            return;
        }

        DB::statement('CREATE SEQUENCE IF NOT EXISTS patient_seq START 1');
        DB::statement("
            SELECT setval(
                'patient_seq',
                GREATEST(
                    COALESCE((SELECT MAX(CAST(SUBSTRING(patient_number FROM 2) AS INTEGER)) FROM patients WHERE patient_number LIKE 'P%'), 0),
                    1
                ),
                true
            )
        ");
    }

    private function scheduleInitialConsultantAppointment(string $patientNumber, ?int $clinicNumber): void
    {
        $consultant = DB::table('staff')
            ->whereIn('position', ['Consultant', 'Doctor'])
            ->orderByRaw("CASE WHEN position = 'Consultant' THEN 0 ELSE 1 END")
            ->orderBy('staff_number')
            ->first();

        if (! $consultant) {
            return;
        }

        $appointmentDate = today()->toDateString();
        $rooms = collect(range(1, 20))->map(fn ($i) => 'E' . str_pad((string) $i, 3, '0', STR_PAD_LEFT));
        $times = collect(['09:00', '09:30', '10:00', '10:30', '11:00', '13:00', '13:30', '14:00', '14:30', '15:00']);

        foreach ($times as $time) {
            foreach ($rooms as $room) {
                $roomBooked = DB::table('appointments')
                    ->whereDate('appointment_date', $appointmentDate)
                    ->where('appointment_time', $time)
                    ->where('examination_room', $room)
                    ->exists();

                $consultantBooked = DB::table('appointments')
                    ->whereDate('appointment_date', $appointmentDate)
                    ->where('appointment_time', $time)
                    ->where('staff_number', $consultant->staff_number)
                    ->exists();

                if (! $roomBooked && ! $consultantBooked) {
                    DB::table('appointments')->insert([
                        'patient_number' => $patientNumber,
                        'clinic_number' => $clinicNumber,
                        'staff_number' => $consultant->staff_number,
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $time,
                        'examination_room' => $room,
                        'status' => 'Scheduled',
                    ]);

                    return;
                }
            }
        }
    }
}
