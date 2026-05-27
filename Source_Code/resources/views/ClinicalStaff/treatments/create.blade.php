{{-- TREATMENT FORM --}}

@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consultant/create-treatment.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endpush

@section('content')

<div class="treatment-form-page">

    <form method="POST" action="{{ route('clinical.treatments.store', $appointment->appointment_id) }}">
        @csrf

        <div class="info-card">
            <h2>
                <i class="fa-solid fa-user-doctor"></i>
                Patient Appointment Information
            </h2>

            <div class="form-grid">
                <div class="form-group">
                    <label>Patient Number</label>
                    <input type="text" value="{{ $appointment->patient_number }}" readonly>
                </div>

                <div class="form-group">
                    <label>Patient Name</label>
                    <input type="text" value="{{ $appointment->patient_name }}" readonly>
                </div>

                <div class="form-group">
                    <label>Attending Doctor</label>
                    <input type="text" value="{{ $appointment->doctor_name }}" readonly>
                </div>

                <div class="form-group">
                    <label>Appointment Date</label>
                    <input type="text" value="{{ $appointment->appointment_date }}" readonly>
                </div>

                <div class="form-group">
                    <label>Appointment Time</label>
                    <input type="text" value="{{ $appointment->appointment_time }}" readonly>
                </div>

                <div class="form-group">
                    <label>Room</label>
                    <input type="text" value="{{ $appointment->examination_room }}" readonly>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>
                <i class="fa-solid fa-file-medical"></i>
                Clinical Records
            </h3>

            <div class="form-group full-width">
                <label>Diagnosis Description *</label>
                <textarea 
                    name="diagnosis" 
                    placeholder="Enter findings, clinical symptoms, and conditions..." 
                    required
                >{{ old('diagnosis', $existingTreatment->diagnosis ?? '') }}</textarea>

                @error('diagnosis')
                    <small class="error-text">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Prescribed Treatment / Procedure *</label>
                    <select name="procedure" required>
                        <option value="">Select a Procedure</option>

                        @foreach($procedures as $procedure)
                            <option 
                                value="{{ $procedure->procedure_name }}"
                                {{ old('procedure', $existingTreatment->procedure ?? '') == $procedure->procedure_name ? 'selected' : '' }}
                            >
                                {{ $procedure->procedure_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('procedure')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Medication / Prescription</label>
                    <select name="medication">
                        <option value="">Select a Medication</option>

                        @foreach($medications as $medication)
                            <option 
                                value="{{ $medication->drug_number }}"
                                {{ old('medication', $existingTreatment->medication ?? '') == $medication->drug_number ? 'selected' : '' }}
                            >
                                {{ $medication->drug_number }} - {{ $medication->drug_name }} / {{ $medication->dosage }} / {{ $medication->method_of_admin }}
                            </option>
                        @endforeach
                    </select>

                    @error('medication')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Units Per Day</label>
                    <input name="unit_per_day" type="number" min="1" max="24" value="{{ old('unit_per_day') }}" placeholder="Example: 3">
                    @error('unit_per_day')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Medication Start Date</label>
                    <input name="medication_start_date" type="date" value="{{ old('medication_start_date', now()->toDateString()) }}">
                    @error('medication_start_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Medication Finish Date</label>
                    <input name="medication_end_date" type="date" value="{{ old('medication_end_date') }}">
                    @error('medication_end_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>
                <i class="fa-solid fa-user-nurse"></i>
                Staff Assignment
            </h3>

            <div class="form-grid">
                <div class="form-group">
                    <label>Attending Doctor *</label>
                    <select name="doctor_display" disabled>
                        <option selected>
                            {{ $appointment->doctor_name }}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assigned Nurse</label>
                    <select name="assigned_nurse">
                        <option value="">Choose a nurse</option>

                        @foreach($nurses as $nurse)
                            <option 
                                value="{{ $nurse->staff_number }}"
                                {{ old('assigned_nurse', $existingTreatment->assigned_nurse ?? '') == $nurse->staff_number ? 'selected' : '' }}
                            >
                                {{ $nurse->first_name }} {{ $nurse->last_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('assigned_nurse')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group full-width">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="Pending" {{ old('status', $existingTreatment->status ?? '') == 'Pending' ? 'selected' : '' }}>
                            Pending
                        </option>

                        <option value="In Progress" {{ old('status', $existingTreatment->status ?? '') == 'In Progress' ? 'selected' : '' }}>
                            In Progress
                        </option>

                        <option value="Completed" {{ old('status', $existingTreatment->status ?? 'Completed') == 'Completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>

                    @error('status')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-group full-width">
                <label>Additional Notes</label>
                <textarea 
                    name="additional_notes" 
                    placeholder="Enter special instructions or patient concerns..."
                >{{ old('additional_notes', $existingTreatment->additional_notes ?? '') }}</textarea>

                @error('additional_notes')
                    <small class="error-text">{{ $message }}</small>
                @enderror
            </div>
        </div>

        <div class="form-section">
            <h3>
                <i class="fa-solid fa-bed-pulse"></i>
                In-Patient Waiting List
            </h3>

            @if($activeInPatient)
                <p class="nurse-muted">
                    This patient is already on the waiting list or admitted for Ward {{ $activeInPatient->ward_number }}.
                </p>
            @else
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>
                            <input type="checkbox" name="place_on_waiting_list" value="1" data-waiting-list-checkbox {{ old('place_on_waiting_list') ? 'checked' : '' }}>
                            Place this patient on the ward waiting list
                        </label>
                    </div>

                    <div class="form-group">
                        <label>Required Ward</label>
                        <select name="ward_number" data-waiting-list-field>
                            <option value="">Select Ward</option>
                            @foreach($wards as $ward)
                                <option value="{{ $ward->ward_number }}" {{ old('ward_number') == $ward->ward_number ? 'selected' : '' }}>
                                    {{ $ward->ward_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ward_number')
                            <small class="error-text">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Expected Stay (days)</label>
                        <input name="expected_stay_days" type="number" min="1" max="365" value="{{ old('expected_stay_days') }}" placeholder="Example: 7" data-waiting-list-field>
                        @error('expected_stay_days')
                            <small class="error-text">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Date Placed on Waiting List</label>
                        <input name="date_placed_on_waiting_list" type="date" value="{{ old('date_placed_on_waiting_list', now()->toDateString()) }}" data-waiting-list-field>
                        @error('date_placed_on_waiting_list')
                            <small class="error-text">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            @endif
        </div>

        <div class="form-actions">
            <a href="{{ route('clinical.appointments') }}" class="btn-cancel">
                Cancel
            </a>

            <button type="submit" class="btn-submit">
                Save Treatment Record
            </button>
        </div>

    </form>

</div>

<script>
    document.querySelectorAll('[data-waiting-list-field]').forEach(function (field) {
        field.addEventListener('change', function () {
            const checkbox = document.querySelector('[data-waiting-list-checkbox]');

            if (checkbox && field.value) {
                checkbox.checked = true;
            }
        });
    });
</script>

<style>
    .treatment-form-page {
    width: 100%;
    font-family: 'Poppins', sans-serif;
    color: #0f2f22;
}

.info-card,
.form-section {
    background: #ffffff;
    border: 1px solid #dbe3ee;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 26px;
}

.info-card h2,
.form-section h3 {
    margin: 0 0 22px;
    font-size: 21px;
    font-weight: 700;
    color: #164333;
}

.form-section h3 {
    padding-bottom: 12px;
    border-bottom: 1px solid #edf1f5;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 18px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    border: 1px solid #d9e2ec;
    border-radius: 9px;
    padding: 14px 16px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    color: #17324d;
    outline: none;
    background: #ffffff;
}

.form-group input[readonly],
.form-group select:disabled {
    background: #f5f8fb;
    color: #334155;
}

.form-group textarea {
    min-height: 90px;
    resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2d6045;
    box-shadow: 0 0 0 3px rgba(45, 96, 69, 0.08);
}

.error-text {
    color: #d32f2f;
    font-size: 12px;
    margin-top: 6px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 14px;
    margin-top: 24px;
}

.btn-cancel,
.btn-submit {
    border: none;
    border-radius: 9px;
    padding: 13px 22px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.btn-cancel {
    background: #ffffff;
    color: #245c3d;
    border: 1px solid #d9e2ec;
}

.btn-submit {
    background: #245c3d;
    color: #ffffff;
}

.btn-submit:hover {
    background: #1d4930;
}

@media (max-width: 900px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
