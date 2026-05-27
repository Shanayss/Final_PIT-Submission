<?php

namespace App\Http\Controllers\NursingStaff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class NursingStaffController extends Controller
{
    public function dashboard()
    {
        return view('NursingStaff.dashboard', [
            'todayAdmissions' => DB::table('in_patients')->whereDate('date_admitted', today())->count(),
            'activePatients' => DB::table('in_patients')->whereNull('date_actual_leave')->count(),
            'availableBeds' => $this->availableBedsQuery()->count(),
            'medicationsDue' => $this->activeMedicationQuery()->count(),
        ]);
    }

    public function assignBeds()
    {
        $wards = $this->wardsWithBeds();
        $bedsByWard = DB::table('beds')
            ->join('wards', 'wards.ward_number', '=', 'beds.ward_number')
            ->leftJoin('in_patients', function ($join) {
                $join->on('in_patients.bed_number', '=', 'beds.bed_number')
                    ->on('in_patients.ward_number', '=', 'beds.ward_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->select(
                'beds.bed_number',
                'beds.ward_number',
                'wards.ward_name',
                DB::raw('CASE WHEN in_patients.in_patient_id IS NULL THEN \'Available\' ELSE \'Occupied\' END as live_status')
            )
            ->orderBy('wards.ward_name')
            ->orderBy('beds.bed_number')
            ->get()
            ->groupBy('ward_number');

        $availableBedsByWard = $bedsByWard
            ->map(fn ($beds) => $beds->where('live_status', 'Available')->values());

        $patients = DB::table('in_patients')
            ->join('patients', 'patients.patient_number', '=', 'in_patients.patient_number')
            ->whereNull('in_patients.date_actual_leave')
            ->whereNull('in_patients.bed_number')
            ->select(
                'patients.patient_number',
                'patients.first_name',
                'patients.last_name',
                'in_patients.ward_number',
                'in_patients.date_placed_on_waiting_list',
                'in_patients.expected_stay_days',
                'in_patients.date_expected_leave'
            )
            ->orderBy('patients.last_name')
            ->get();

        return view('NursingStaff.assign-beds', compact('wards', 'bedsByWard', 'availableBedsByWard', 'patients'));
    }

    public function storeBedAssignment(Request $request)
    {
        $data = $request->validate([
            'patient_number' => ['required', 'exists:patients,patient_number'],
            'ward_number' => ['required', 'exists:wards,ward_number'],
            'bed_number' => ['required', 'exists:beds,bed_number'],
            'expected_discharge_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $awaitingBed = DB::table('in_patients')
            ->where('patient_number', $data['patient_number'])
            ->whereNull('date_actual_leave')
            ->whereNull('bed_number')
            ->exists();

        if (! $awaitingBed) {
            return back()->withErrors(['patient_number' => 'Only patients on the waiting list can be assigned a bed here.'])->withInput();
        }

        $bed = DB::table('beds')
            ->where('bed_number', $data['bed_number'])
            ->where('ward_number', $data['ward_number'])
            ->first();

        if (! $bed) {
            return back()->withErrors(['bed_number' => 'The selected bed does not belong to the selected ward.'])->withInput();
        }

        $occupied = DB::table('in_patients')
            ->where('bed_number', $data['bed_number'])
            ->whereNull('date_actual_leave')
            ->where('patient_number', '!=', $data['patient_number'])
            ->exists();

        if ($occupied) {
            return back()->withErrors(['bed_number' => 'That bed is already assigned to an active patient.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $currentStay = DB::table('in_patients')
                ->where('patient_number', $data['patient_number'])
                ->whereNull('date_actual_leave')
                ->latest('in_patient_id')
                ->first();

            if (! $currentStay || $currentStay->bed_number) {
                throw new \RuntimeException('Patient is not currently awaiting bed assignment.');
            }

            if ((string) $currentStay->ward_number !== (string) $data['ward_number']) {
                throw new \RuntimeException('Selected bed must be in the patient required ward.');
            }

            DB::table('in_patients')
                ->where('in_patient_id', $currentStay->in_patient_id)
                ->update([
                    'ward_number' => $data['ward_number'],
                    'bed_number' => $data['bed_number'],
                    'date_admitted' => today()->toDateString(),
                    'date_expected_leave' => $data['expected_discharge_date'],
                    'status' => 'Admitted',
                ]);

            DB::table('beds')->where('bed_number', $data['bed_number'])->update(['status' => 'Occupied']);
        });

        return redirect()->route('nurse.assign-beds')->with('status', 'Bed assigned successfully.');
    }

    public function admitPatients()
    {
        return view('NursingStaff.admit-patients', [
            'wards' => $this->wardsWithBeds(),
            'availableBeds' => $this->availableBedsQuery()->orderBy('beds.bed_number')->get(),
            'localDoctors' => DB::table('local_doctors')->orderBy('full_name')->get(),
            'admittedPatients' => $this->admittedPatientDetailsQuery()->orderByDesc('in_patients.date_admitted')->get(),
            'registeredPatients' => DB::table('patients')
                ->leftJoin('next_of_kins', 'next_of_kins.patient_number', '=', 'patients.patient_number')
                ->leftJoin('in_patients', function ($join) {
                    $join->on('in_patients.patient_number', '=', 'patients.patient_number')
                        ->whereNull('in_patients.date_actual_leave');
                })
                ->where(function ($query) {
                    $query->whereNull('in_patients.in_patient_id')
                        ->orWhereNull('in_patients.bed_number');
                })
                ->select(
                    'patients.*',
                    'next_of_kins.full_name as kin_name',
                    'next_of_kins.relationship as kin_relationship',
                    'next_of_kins.address as kin_address',
                    'next_of_kins.telephone as kin_telephone',
                    'in_patients.ward_number as waiting_ward_number',
                    'in_patients.expected_stay_days as waiting_expected_stay_days',
                    'in_patients.date_placed_on_waiting_list as waiting_date_placed_on_waiting_list'
                )
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    public function storeAdmission(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required_without:existing_patient_number', 'string', 'max:50'],
            'last_name' => ['required_without:existing_patient_number', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'marital_status' => ['nullable', 'string', 'max:20'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'clinic_number' => ['nullable', 'exists:local_doctors,clinic_number'],
            'kin_name' => ['required_without:existing_patient_number', 'string', 'max:100'],
            'kin_relationship' => ['required_without:existing_patient_number', 'string', 'max:50'],
            'kin_address' => ['nullable', 'string', 'max:100'],
            'kin_telephone' => ['nullable', 'string', 'max:20'],
            'ward_number' => ['required', 'exists:wards,ward_number'],
            'date_placed_on_waiting_list' => ['required', 'date'],
            'expected_stay_days' => ['required', 'integer', 'min:1', 'max:365'],
            // optional: admit an existing registered patient by patient_number
            'existing_patient_number' => ['nullable', 'exists:patients,patient_number'],
        ]);

        // If an existing patient number is provided, admit that patient instead of registering a new
        if (! empty($data['existing_patient_number'])) {
            $activeStay = DB::table('in_patients')
                ->where('patient_number', $data['existing_patient_number'])
                ->whereNull('date_actual_leave')
                ->latest('in_patient_id')
                ->first();

            if ($activeStay && $activeStay->bed_number) {
                return back()->withErrors(['existing_patient_number' => 'This patient is already admitted.'])->withInput();
            }

            DB::transaction(function () use ($data) {
                $patientNumber = $data['existing_patient_number'];
                $activeStay = DB::table('in_patients')
                    ->where('patient_number', $patientNumber)
                    ->whereNull('date_actual_leave')
                    ->latest('in_patient_id')
                    ->first();

                if ($activeStay) {
                    DB::table('in_patients')
                        ->where('in_patient_id', $activeStay->in_patient_id)
                        ->update([
                            'ward_number' => $data['ward_number'],
                            'date_placed_on_waiting_list' => $data['date_placed_on_waiting_list'],
                            'expected_stay_days' => $data['expected_stay_days'],
                            'status' => 'Waiting',
                        ]);

                    return;
                }

                DB::table('in_patients')->insert([
                    'patient_number' => $patientNumber,
                    'ward_number' => $data['ward_number'],
                    'bed_number' => null,
                    'date_placed_on_waiting_list' => $data['date_placed_on_waiting_list'],
                    'expected_stay_days' => $data['expected_stay_days'],
                    'date_admitted' => today()->toDateString(),
                    'date_expected_leave' => null,
                    'status' => 'Waiting',
                ]);
            });

            return redirect()->route('nurse.admit-patients')->with('status', 'Existing patient waiting list details saved successfully.');
        }

        DB::transaction(function () use ($data) {
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

            DB::table('in_patients')->insert([
                'patient_number' => $patientNumber,
                'ward_number' => $data['ward_number'],
                'bed_number' => null,
                'date_placed_on_waiting_list' => $data['date_placed_on_waiting_list'],
                'expected_stay_days' => $data['expected_stay_days'],
                'date_admitted' => today()->toDateString(),
                'date_expected_leave' => null,
                'status' => 'Waiting',
            ]);

            DB::table('next_of_kins')->insert([
                'patient_number' => $patientNumber,
                'full_name' => $data['kin_name'],
                'relationship' => $data['kin_relationship'],
                'address' => $data['kin_address'] ?? null,
                'telephone' => $data['kin_telephone'] ?? null,
            ]);
        });

        return redirect()->route('nurse.admit-patients')->with('status', 'Patient added to the waiting list successfully.');
    }

    public function wardBeds()
    {
        return view('meddirector.ward_beds', $this->wardBedMonitoringData());
    }

    public function dischargePatients()
    {
        $patients = $this->activePatientsQuery()->orderBy('patients.last_name')->get();

        return view('NursingStaff.discharge-patients', compact('patients'));
    }

    public function storeDischarge(Request $request)
    {
        $data = $request->validate([
            'in_patient_id' => ['required', 'exists:in_patients,in_patient_id'],
            'date_actual_leave' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($data) {
            $stay = DB::table('in_patients')->where('in_patient_id', $data['in_patient_id'])->first();

            DB::table('in_patients')
                ->where('in_patient_id', $data['in_patient_id'])
                ->update([
                    'date_actual_leave' => $data['date_actual_leave'],
                    'status' => 'Discharged',
                ]);

            if ($stay?->bed_number) {
                DB::table('beds')
                    ->where('ward_number', $stay->ward_number)
                    ->where('bed_number', $stay->bed_number)
                    ->update(['status' => 'Available']);
            }
        });

        return redirect()->route('nurse.discharge-patients')->with('status', 'Patient discharged successfully.');
    }

    public function wardOccupancy()
    {
        $wards = DB::table('wards')
            ->leftJoin('beds', 'beds.ward_number', '=', 'wards.ward_number')
            ->leftJoin('in_patients', function ($join) {
                $join->on('in_patients.bed_number', '=', 'beds.bed_number')
                    ->on('in_patients.ward_number', '=', 'beds.ward_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->select(
                'wards.ward_number',
                'wards.ward_name',
                DB::raw('COUNT(DISTINCT beds.bed_number) as total_beds'),
                DB::raw('COUNT(DISTINCT in_patients.in_patient_id) as occupied_beds')
            )
            ->groupBy('wards.ward_number', 'wards.ward_name')
            ->orderBy('wards.ward_name')
            ->get();

        return view('NursingStaff.ward-occupancy', compact('wards'));
    }

    public function viewPrescriptions(Request $request)
    {
        $search = $request->string('search')->toString();
        $prescriptions = $this->medicationOrdersQuery()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('patients.first_name', 'like', "%{$search}%")
                        ->orWhere('patients.last_name', 'like', "%{$search}%")
                        ->orWhere('drugs.drug_name', 'like', "%{$search}%")
                        ->orWhere('drugs.drug_number', 'like', "%{$search}%")
                        ->orWhere('patient_medications.patient_number', 'like', "%{$search}%")
                        ->orWhere('patient_medications.medication_id', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('patient_medications.start_date')
            ->get();

        return view('NursingStaff.view-prescriptions', compact('prescriptions', 'search'));
    }

    public function medicationSchedules()
    {
        $schedules = $this->activeMedicationQuery()
            ->leftJoin('medication_administrations', function ($join) {
                $join->on('medication_administrations.medication_id', '=', 'patient_medications.medication_id')
                    ->whereDate('medication_administrations.administered_at', today());
            })
            ->addSelect('medication_administrations.administration_id')
            ->orderBy('patient_medications.unit_per_day')
            ->orderBy('patients.last_name')
            ->get()
            ->groupBy(fn ($row) => ((int) $row->unit_per_day >= 3 ? '06:00' : '08:00'));

        return view('NursingStaff.medication-schedules', compact('schedules'));
    }

    public function recordMedication()
    {
        $medications = $this->activeMedicationQuery()
            ->leftJoin('medication_administrations', function ($join) {
                $join->on('medication_administrations.medication_id', '=', 'patient_medications.medication_id')
                    ->whereDate('medication_administrations.administered_at', today());
            })
            ->addSelect(
                'medication_administrations.administration_id',
                'medication_administrations.administered_at',
                'medication_administrations.dosage_administered'
            )
            ->orderBy('patients.last_name')
            ->get();

        return view('NursingStaff.record-medication', [
            'medications' => $medications,
        ]);
    }

    public function storeMedicationRecord(Request $request)
    {
        $data = $request->validate([
            'medication_id' => ['required', 'exists:patient_medications,medication_id'],
            'dosage_administered' => ['nullable', 'string', 'max:50'],
            'administered_at' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $administeredAt = ! empty($data['administered_at'])
            ? now()->setTimeFromTimeString($data['administered_at'])
            : now();

        $medication = DB::table('patient_medications')->where('medication_id', $data['medication_id'])->first();

        DB::table('medication_administrations')->insert([
            'medication_id' => $data['medication_id'],
            'patient_number' => $medication->patient_number,
            'staff_number' => session('staff_id'),
            'dosage_administered' => $data['dosage_administered'] ?? null,
            'administered_at' => $administeredAt,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('nurse.medication.record')->with('status', 'Medication administration recorded.');
    }

    public function patients()
    {
        return redirect()->route('nurse.patient-care.assigned');
    }

    public function registerPatients(Request $request)
    {
        $search = $request->string('search')->toString();

        $patients = DB::table('patients')
            ->leftJoin('in_patients', function ($join) {
                $join->on('in_patients.patient_number', '=', 'patients.patient_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->leftJoin('wards', 'wards.ward_number', '=', 'in_patients.ward_number')
            ->leftJoin('next_of_kins', 'next_of_kins.patient_number', '=', 'patients.patient_number')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('patients.patient_number', 'like', "%{$search}%")
                        ->orWhere('patients.first_name', 'like', "%{$search}%")
                        ->orWhere('patients.last_name', 'like', "%{$search}%")
                        ->orWhere('wards.ward_name', 'like', "%{$search}%");
                });
            })
            ->select(
                'patients.patient_number',
                'patients.clinic_number',
                'patients.first_name',
                'patients.last_name',
                'patients.address',
                'patients.telephone',
                'patients.date_of_birth',
                'patients.sex',
                'patients.marital_status',
                'patients.date_registered',
                'next_of_kins.full_name as kin_name',
                'next_of_kins.relationship as kin_relationship',
                'next_of_kins.address as kin_address',
                'next_of_kins.telephone as kin_telephone',
                'wards.ward_name',
                DB::raw('CASE WHEN in_patients.in_patient_id IS NULL THEN 0 ELSE 1 END as is_admitted')
            )
            ->orderByDesc('patients.date_registered')
            ->orderBy('patients.last_name')
            ->get();

        return view('NursingStaff.register-patient', [
            'patients' => $patients,
            'localDoctors' => DB::table('local_doctors')->orderBy('full_name')->get(),
            'consultants' => DB::table('staff')
                ->where('position', 'Consultant')
                ->orderBy('first_name')
                ->get(),
            'search' => $search,
        ]);
    }

    public function storeRegisteredPatient(Request $request)
    {
        $data = $this->validatePatientRegistration($request);
        $appointmentData = $request->validate([
            'appointment_staff_number' => [
                'required',
                Rule::exists('staff', 'staff_number')->where('position', 'Consultant'),
            ],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'examination_room' => ['required', 'string', 'max:20'],
        ]);

        if ($this->appointmentSlotIsBooked($appointmentData)) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'The selected consultant or room is already booked at that date and time.']);
        }

        DB::transaction(function () use ($data, $appointmentData) {
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
            $this->syncNextOfKin($patientNumber, $data);

            DB::table('appointments')->insert([
                'patient_number' => $patientNumber,
                'clinic_number' => $data['clinic_number'] ?? null,
                'staff_number' => $appointmentData['appointment_staff_number'],
                'appointment_date' => $appointmentData['appointment_date'],
                'appointment_time' => $appointmentData['appointment_time'],
                'examination_room' => $appointmentData['examination_room'],
                'status' => 'Scheduled',
            ]);
        });

        return redirect()->route('nurse.register-patient')->with('status', 'Patient registered and consultant appointment scheduled successfully.');
    }

    public function updateRegisteredPatient(Request $request, string $patientNumber)
    {
        $data = $this->validatePatientRegistration($request);

        DB::transaction(function () use ($data, $patientNumber) {
            DB::table('patients')
                ->where('patient_number', $patientNumber)
                ->update([
                    'clinic_number' => $data['clinic_number'] ?? null,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'address' => $data['address'] ?? null,
                    'telephone' => $data['telephone'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'marital_status' => $data['marital_status'] ?? null,
                ]);

            $this->syncNextOfKin($patientNumber, $data);
        });

        return redirect()->route('nurse.register-patient')->with('status', 'Patient updated successfully.');
    }

    public function destroyRegisteredPatient(string $patientNumber)
    {
        $activeStay = DB::table('in_patients')
            ->where('patient_number', $patientNumber)
            ->whereNull('date_actual_leave')
            ->exists();

        if ($activeStay) {
            return back()->withErrors(['patient_number' => 'Discharge the patient before deleting the registration record.']);
        }

        DB::table('patients')->where('patient_number', $patientNumber)->delete();

        return redirect()->route('nurse.register-patient')->with('status', 'Patient deleted successfully.');
    }

    public function updateCondition(Request $request)
    {
        return view('NursingStaff.update-condition', [
            'patients' => $this->admittedPatientDetailsQuery($request)->get(),
        ]);
    }

    public function storeCondition(Request $request)
    {
        $data = $request->validate([
            'patient_number' => ['required', 'exists:patients,patient_number'],
            'condition_status' => ['required', Rule::in(['Stable', 'Recovering', 'Critical'])],
            'blood_pressure' => ['nullable', 'string', 'max:20'],
            'temperature' => ['nullable', 'string', 'max:20'],
            'heart_rate' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::table('patient_condition_updates')->insert([
            'patient_number' => $data['patient_number'],
            'staff_number' => session('staff_id'),
            'condition_status' => $data['condition_status'],
            'blood_pressure' => $data['blood_pressure'] ?? null,
            'temperature' => $data['temperature'] ?? null,
            'heart_rate' => $data['heart_rate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_at' => now(),
        ]);

        return redirect()->route('nurse.patient-care.update-condition')->with('status', 'Patient condition updated.');
    }

    public function assignedPatients(Request $request)
    {
        return view('NursingStaff.assigned-patients', [
            'patients' => $this->admittedPatientDetailsQuery($request)->get(),
            'wards' => $this->wardsWithBeds(),
            'selectedWard' => $request->string('ward_number')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function patientDetails(string $patientNumber)
    {
        $patient = $this->admittedPatientDetailsQuery()
            ->where('patients.patient_number', $patientNumber)
            ->firstOrFail();

        return view('NursingStaff.patient-details', [
            'patient' => $patient,
            'nextOfKin' => DB::table('next_of_kins')->where('patient_number', $patientNumber)->first(),
            'localDoctor' => $patient->clinic_number
                ? DB::table('local_doctors')->where('clinic_number', $patient->clinic_number)->first()
                : null,
            'careNotes' => $this->careNotesQuery()->where('care_notes.patient_number', $patientNumber)->get(),
        ]);
    }

    public function careNotes()
    {
        return view('NursingStaff.care-notes', [
            'notes' => $this->careNotesQuery()->get(),
            'patients' => $this->activePatientsQuery()->orderBy('patients.last_name')->get(),
        ]);
    }

    public function storeCareNote(Request $request)
    {
        $data = $request->validate([
            'patient_number' => ['required', 'exists:patients,patient_number'],
            'note_type' => ['required', 'string', 'max:50'],
            'notes' => ['required', 'string', 'max:1000'],
        ]);

        DB::table('care_notes')->insert([
            'patient_number' => $data['patient_number'],
            'staff_number' => session('staff_id'),
            'note_type' => $data['note_type'],
            'notes' => $data['notes'],
            'recorded_at' => now(),
        ]);

        return redirect()->route('nurse.patient-care.care-notes')->with('status', 'Care note saved.');
    }

    public function createRequisition(Request $request)
    {
        return view('NursingStaff.create-requisition', [
            'items' => DB::table('items')->orderBy('item_name')->get(),
            'lowStockItems' => DB::table('items')->whereColumn('quantity_of_stock', '<=', 'reorder_level')->orderBy('item_name')->get(),
            'wards' => $this->wardsWithBeds(),
            'prefillItem' => $request->string('item_number')->toString(),
        ]);
    }

    public function storeRequisition(Request $request)
    {
        $data = $request->validate([
            'ward_number' => ['required', 'exists:wards,ward_number'],
            'priority' => ['required', Rule::in(['Low', 'Normal', 'High', 'Urgent'])],
            'item_number' => ['required', 'array', 'min:1'],
            'item_number.*' => ['required', 'exists:items,item_number'],
            'quantity' => ['required', 'array', 'min:1'],
            'quantity.*' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data) {
            $requisitionNumber = ((int) DB::table('requisitions')->max('requisition_number')) + 1;

            DB::table('requisitions')->insert([
                'requisition_number' => $requisitionNumber,
                'staff_number' => session('staff_id'),
                'ward_number' => $data['ward_number'],
                'date_ordered' => today(),
                'status' => 'Pending',
            ]);

            foreach ($data['item_number'] as $index => $itemNumber) {
                DB::table('requisition_items')->insert([
                    'requisition_number' => $requisitionNumber,
                    'item_number' => $itemNumber,
                    'quantity_required' => $data['quantity'][$index],
                ]);
            }
        });

        return redirect()->route('nurse.supplies.create')->with('status', 'Supply requisition submitted.');
    }

    public function requestSupplies(Request $request)
    {
        $search = $request->string('search')->toString();

        return view('NursingStaff.request-supplies', [
            'items' => DB::table('items')
                ->when($search, fn ($query) => $query->where('item_name', 'like', "%{$search}%")->orWhere('item_number', 'like', "%{$search}%"))
                ->orderBy('item_number')
                ->get(),
            'search' => $search,
        ]);
    }

    public function confirmDeliveries()
    {
        return view('NursingStaff.confirm-deliveries', [
            'requisitions' => DB::table('requisitions')
                ->join('wards', 'wards.ward_number', '=', 'requisitions.ward_number')
                ->leftJoin('staff', 'staff.staff_number', '=', 'requisitions.staff_number')
                ->where('requisitions.status', '!=', 'Delivered')
                ->select('requisitions.*', 'wards.ward_name', 'staff.first_name', 'staff.last_name')
                ->orderByDesc('requisitions.date_ordered')
                ->get(),
        ]);
    }

    public function storeDeliveryConfirmation(Request $request)
    {
        $data = $request->validate([
            'requisition_number' => ['required', 'exists:requisitions,requisition_number'],
        ]);

        DB::transaction(function () use ($data) {
            DB::table('requisitions')
                ->where('requisition_number', $data['requisition_number'])
                ->where('status', '!=', 'Delivered')
                ->update([
                    'status' => 'Delivered',
                    'date_received' => today(),
                ]);

            $items = DB::table('requisition_items')
                ->where('requisition_number', $data['requisition_number'])
                ->get();

            foreach ($items as $item) {
                DB::table('items')
                    ->where('item_number', $item->item_number)
                    ->update([
                        'quantity_of_stock' => DB::raw('GREATEST(quantity_of_stock - ' . (int) $item->quantity_required . ', 0)'),
                    ]);
            }
        });

        return redirect()->route('nurse.supplies.confirm')->with('status', 'Delivery confirmed.');
    }

    public function supplies()
    {
        return redirect()->route('nurse.supplies.request');
    }

    public function reports()
    {
        return view('NursingStaff.reports');
    }

    public function scheduling()
    {
        $nursingRoles = ['Charge Nurse', 'Senior Nurse', 'Junior Nurse', 'Auxiliary Staff', 'Nursing Staff'];

        $weekStart = DB::table('staff_allocations')
            ->join('staff', 'staff.staff_number', '=', 'staff_allocations.staff_number')
            ->where(function ($query) use ($nursingRoles) {
                $query->where('staff.role_id', 4)
                    ->orWhereIn('staff.position', $nursingRoles)
                    ->orWhereIn('staff_allocations.role_for_week', $nursingRoles);
            })
            ->max('week_start_date') ?? today()->startOfWeek()->toDateString();

        $allocations = DB::table('staff_allocations')
            ->join('staff', 'staff.staff_number', '=', 'staff_allocations.staff_number')
            ->leftJoin('wards', 'wards.ward_number', '=', 'staff_allocations.ward_number')
            ->where('week_start_date', $weekStart)
            ->where(function ($query) use ($nursingRoles) {
                $query->where('staff.role_id', 4)
                    ->orWhereIn('staff.position', $nursingRoles)
                    ->orWhereIn('staff_allocations.role_for_week', $nursingRoles);
            })
            ->select('staff_allocations.*', 'staff.first_name', 'staff.last_name', 'staff.position', 'wards.ward_name')
            ->orderBy('shift')
            ->orderBy('staff.last_name')
            ->get()
            ->groupBy('shift');

        return view('NursingStaff.scheduling', compact('weekStart', 'allocations'));
    }

    private function wardsWithBeds()
    {
        return DB::table('wards')
            ->where('total_beds', '>', 0)
            ->orderBy('ward_name')
            ->get();
    }

    private function availableBedsQuery()
    {
        return DB::table('beds')
            ->join('wards', 'wards.ward_number', '=', 'beds.ward_number')
            ->leftJoin('in_patients', function ($join) {
                $join->on('in_patients.bed_number', '=', 'beds.bed_number')
                    ->on('in_patients.ward_number', '=', 'beds.ward_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->whereNull('in_patients.in_patient_id')
            ->select('beds.bed_number', 'beds.ward_number', 'wards.ward_name');
    }

    private function wardBedMonitoringData(): array
    {
        $wardsFromDb = DB::table('wards')->orderBy('ward_number')->get();
        $activePatients = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->whereNotNull('ward_number')
            ->whereNotNull('bed_number')
            ->get()
            ->groupBy('ward_number');
        $bedsByWard = DB::table('beds')
            ->orderBy('bed_number')
            ->get()
            ->groupBy('ward_number');

        $wardsJsonConfig = $wardsFromDb->mapWithKeys(function ($ward) use ($activePatients, $bedsByWard) {
            $wardAdmissions = $activePatients->get($ward->ward_number, collect());
            $wardBeds = $bedsByWard->get($ward->ward_number, collect())->values();
            $totalBeds = $wardBeds->count() ?: (int) $ward->total_beds;

            $bedsList = [];
            for ($i = 0; $i < $totalBeds; $i++) {
                $bedRecord = $wardBeds->get($i);
                $bedNumber = $bedRecord->bed_number ?? ($i + 1);
                $sessionPatient = $wardAdmissions->firstWhere('bed_number', $bedNumber);
                $isOccupied = (bool) $sessionPatient;

                $bedsList[] = [
                    'id' => $bedNumber,
                    'relative_bed_index' => $i + 1,
                    'status' => $isOccupied ? 'occupied' : 'vacant',
                    'label' => $isOccupied ? 'Occ' : 'Free',
                    'patient' => $sessionPatient ? 'Patient ' . $sessionPatient->patient_number : null,
                    'admitted' => $sessionPatient ? date('d-M-Y', strtotime($sessionPatient->date_admitted)) : null,
                    'leave' => $sessionPatient && $sessionPatient->date_expected_leave
                        ? date('d-M-Y', strtotime($sessionPatient->date_expected_leave))
                        : null,
                ];
            }

            return [
                $ward->ward_number => [
                    'name' => $ward->ward_name,
                    'bedsCount' => $totalBeds,
                    'bedsList' => $bedsList,
                ],
            ];
        })->toJson();

        $totalPatients = DB::table('patients')->count();
        $totalStaff = DB::table('staff')->count();
        $totalBeds = DB::table('beds')->count();
        $occupiedBeds = DB::table('in_patients')
            ->whereNull('date_actual_leave')
            ->whereNotNull('ward_number')
            ->whereNotNull('bed_number')
            ->select('ward_number', 'bed_number')
            ->distinct()
            ->get()
            ->count();
        $availableBeds = max(0, $totalBeds - $occupiedBeds);

        $wardOccupancy = $wardsFromDb->map(function ($ward) use ($bedsByWard, $activePatients) {
            $wardBeds = $bedsByWard->get($ward->ward_number, collect());
            $occupied = $activePatients->get($ward->ward_number, collect())
                ->pluck('bed_number')
                ->filter()
                ->unique()
                ->count();

            return [
                'ward_name' => $ward->ward_name,
                'ward_number' => $ward->ward_number,
                'occupied' => $occupied,
                'available' => max(0, ($wardBeds->count() ?: (int) $ward->total_beds) - $occupied),
            ];
        })->toArray();

        return compact(
            'totalPatients',
            'totalStaff',
            'occupiedBeds',
            'availableBeds',
            'wardOccupancy',
            'wardsJsonConfig'
        );
    }

    private function activePatientsQuery()
    {
        return DB::table('in_patients')
            ->join('patients', 'patients.patient_number', '=', 'in_patients.patient_number')
            ->leftJoin('wards', 'wards.ward_number', '=', 'in_patients.ward_number')
            ->whereNull('in_patients.date_actual_leave')
            ->select(
                'in_patients.in_patient_id',
                'in_patients.patient_number',
                'in_patients.ward_number',
                'in_patients.bed_number',
                'in_patients.date_placed_on_waiting_list',
                'in_patients.expected_stay_days',
                'in_patients.date_admitted',
                'in_patients.date_expected_leave',
                'in_patients.date_actual_leave',
                'patients.first_name',
                'patients.last_name',
                'patients.telephone',
                'patients.sex',
                'wards.ward_name'
            );
    }

    private function admittedPatientDetailsQuery(?Request $request = null)
    {
        $search = $request?->string('search')->toString();
        $wardNumber = $request?->string('ward_number')->toString();

        return DB::table('in_patients')
            ->join('patients', 'patients.patient_number', '=', 'in_patients.patient_number')
            ->leftJoin('wards', 'wards.ward_number', '=', 'in_patients.ward_number')
            ->leftJoin('diagnoses', function ($join) {
                $join->on('diagnoses.patient_number', '=', 'patients.patient_number')
                    ->whereRaw('diagnoses.diagnosis_id = (SELECT MAX(d2.diagnosis_id) FROM diagnoses d2 WHERE d2.patient_number = patients.patient_number)');
            })
            ->leftJoin('patient_condition_updates', function ($join) {
                $join->on('patient_condition_updates.patient_number', '=', 'patients.patient_number')
                    ->whereRaw('patient_condition_updates.condition_id = (SELECT MAX(pcu2.condition_id) FROM patient_condition_updates pcu2 WHERE pcu2.patient_number = patients.patient_number)');
            })
            ->whereNull('in_patients.date_actual_leave')
            ->when($wardNumber, fn ($query) => $query->where('in_patients.ward_number', $wardNumber))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('patients.patient_number', 'like', "%{$search}%")
                        ->orWhere('patients.first_name', 'like', "%{$search}%")
                        ->orWhere('patients.last_name', 'like', "%{$search}%");
                });
            })
            ->select(
                'in_patients.in_patient_id',
                'in_patients.patient_number',
                'in_patients.ward_number',
                'in_patients.bed_number',
                'in_patients.date_placed_on_waiting_list',
                'in_patients.expected_stay_days',
                'in_patients.date_admitted',
                'in_patients.date_expected_leave',
                'in_patients.date_actual_leave',
                'patients.clinic_number',
                'patients.first_name',
                'patients.last_name',
                'patients.address',
                'patients.telephone',
                'patients.date_of_birth',
                'patients.sex',
                'patients.marital_status',
                'wards.ward_name',
                'diagnoses.diagnosis_details',
                DB::raw("CASE WHEN patients.date_of_birth IS NULL THEN NULL ELSE DATE_PART('year', AGE(patients.date_of_birth))::int END as age"),
                DB::raw("COALESCE(patient_condition_updates.condition_status, 'Stable') as condition_status"),
                'patient_condition_updates.blood_pressure',
                'patient_condition_updates.temperature',
                'patient_condition_updates.heart_rate',
                'patient_condition_updates.notes as condition_notes'
            )
            ->orderBy('patients.last_name');
    }

    private function careNotesQuery()
    {
        return DB::table('care_notes')
            ->join('patients', 'patients.patient_number', '=', 'care_notes.patient_number')
            ->leftJoin('staff', 'staff.staff_number', '=', 'care_notes.staff_number')
            ->select(
                'care_notes.*',
                'patients.first_name',
                'patients.last_name',
                'staff.first_name as staff_first_name',
                'staff.last_name as staff_last_name'
            )
            ->orderByDesc('care_notes.recorded_at');
    }

    private function activeMedicationQuery()
    {
        return $this->medicationOrdersQuery()
            ->join('in_patients', function ($join) {
                $join->on('in_patients.patient_number', '=', 'patients.patient_number')
                    ->whereNull('in_patients.date_actual_leave');
            })
            ->addSelect('in_patients.bed_number');
    }

    private function medicationOrdersQuery()
    {
        return DB::table('patient_medications')
            ->join('patients', 'patients.patient_number', '=', 'patient_medications.patient_number')
            ->join('drugs', 'drugs.drug_number', '=', 'patient_medications.drug_number')
            ->leftJoin('staff', 'staff.staff_number', '=', 'patient_medications.staff_number')
            ->select(
                'patient_medications.medication_id',
                'patient_medications.drug_number',
                'patient_medications.staff_number',
                'patient_medications.unit_per_day',
                'patient_medications.start_date',
                'patient_medications.end_date',
                'patients.patient_number',
                'patients.first_name',
                'patients.last_name',
                'drugs.drug_name',
                'drugs.description',
                'drugs.dosage',
                'drugs.method_of_admin',
                'staff.first_name as staff_first_name',
                'staff.last_name as staff_last_name'
            );
    }

    private function nextPatientNumber(): string
    {
        $latest = DB::table('patients')->max('patient_number');
        $number = $latest ? ((int) substr($latest, 1)) + 1 : 1;

        return 'P' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }

    private function validatePatientRegistration(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'sex' => ['nullable', Rule::in(['Male', 'Female'])],
            'address' => ['nullable', 'string', 'max:100'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:20'],
            'kin_name' => ['nullable', 'string', 'max:100'],
            'kin_relationship' => ['nullable', 'required_with:kin_name', 'string', 'max:50'],
            'kin_address' => ['nullable', 'string', 'max:100'],
            'kin_telephone' => ['nullable', 'string', 'max:20'],
            'clinic_number' => ['nullable', 'exists:local_doctors,clinic_number'],
        ]);
    }

    private function appointmentSlotIsBooked(array $appointmentData): bool
    {
        return DB::table('appointments')
            ->whereDate('appointment_date', $appointmentData['appointment_date'])
            ->where('appointment_time', $appointmentData['appointment_time'])
            ->where(function ($query) use ($appointmentData) {
                $query->where('staff_number', $appointmentData['appointment_staff_number'])
                    ->orWhere('examination_room', $appointmentData['examination_room']);
            })
            ->exists();
    }

    private function recordInitialDiagnosis(string $patientNumber, ?string $diagnosis): void
    {
        if (! $diagnosis) {
            return;
        }

        $appointmentId = DB::table('appointments')->insertGetId([
            'patient_number' => $patientNumber,
            'staff_number' => session('staff_id'),
            'appointment_date' => today(),
            'appointment_time' => now()->format('H:i:s'),
            'examination_room' => 'Admission',
            'status' => 'Completed',
        ], 'appointment_id');

        DB::table('diagnoses')->insert([
            'appointment_id' => $appointmentId,
            'patient_number' => $patientNumber,
            'staff_number' => session('staff_id'),
            'diagnosis_details' => $diagnosis,
            'diagnosis_date' => today(),
            'notes' => 'Recorded during patient admission.',
        ]);
    }

    private function syncNextOfKin(string $patientNumber, array $data): void
    {
        if (empty($data['kin_name'])) {
            DB::table('next_of_kins')->where('patient_number', $patientNumber)->delete();
            return;
        }

        DB::table('next_of_kins')->updateOrInsert(
            ['patient_number' => $patientNumber],
            [
                'full_name' => $data['kin_name'],
                'relationship' => $data['kin_relationship'] ?? 'Emergency Contact',
                'address' => $data['kin_address'] ?? null,
                'telephone' => $data['kin_telephone'] ?? null,
            ]
        );
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

    private function usesPostgreSql(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    private function waitingListAdmissionDate(string $waitingListDate): ?string
    {
        if (! $this->usesPostgreSql()) {
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
