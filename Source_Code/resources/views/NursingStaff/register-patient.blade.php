@extends('layouts.app')

@section('content')
<div class="nurse-page">
    @include('NursingStaff.partials.flash')

    <section class="nurse-registration-card">
        <div class="nurse-registration-header">
            <div>
                <h1>Patient Registration</h1>
                <p>Manage and view patient records</p>
            </div>
            <button type="button" class="nurse-light-button" data-open-patient-form>
                <i class="fa-solid fa-plus"></i>
                Add Patient
            </button>
        </div>

        <form class="nurse-registration-search" method="GET" action="{{ route('nurse.register-patient') }}">
            <input name="search" value="{{ $search }}" placeholder="Search by name, patient number or ward...">
        </form>

        <div class="nurse-registration-panel-overlay" id="patient-registration-overlay" @if(! $errors->any()) hidden @endif></div>
        <aside class="nurse-registration-panel" id="patient-registration-form" aria-hidden="{{ $errors->any() ? 'false' : 'true' }}" @if(! $errors->any()) hidden @endif>
            <div class="nurse-registration-panel-header">
                <div>
                    <h2>Add Patient</h2>
                    <p>Register the patient and schedule the consultant examination.</p>
                </div>
                <button type="button" class="nurse-panel-close" data-close-patient-form aria-label="Close patient form">&times;</button>
            </div>
            <div class="nurse-registration-panel-body">
                <form class="nurse-form" method="POST" action="{{ route('nurse.register-patient.store') }}">
                    @csrf
                    @include('NursingStaff.partials.patient-registration-fields', ['patient' => null])
                    <h3>Consultant Appointment</h3>
                    <div class="nurse-form-grid">
                        <label>Hospital Consultant
                            <select name="appointment_staff_number" required>
                                <option value="">Select consultant...</option>
                                @foreach($consultants as $consultant)
                                    <option value="{{ $consultant->staff_number }}" @selected(old('appointment_staff_number') === $consultant->staff_number)>
                                        {{ $consultant->first_name }} {{ $consultant->last_name }} - {{ $consultant->position }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>Appointment Date
                            <input name="appointment_date" type="date" value="{{ old('appointment_date', now()->toDateString()) }}" required>
                        </label>
                        <label>Appointment Time
                            <input name="appointment_time" type="time" value="{{ old('appointment_time', '09:00') }}" required>
                        </label>
                        <label>Examination Room
                            <select name="examination_room" required>
                                @for($i = 1; $i <= 20; $i++)
                                    @php($room = 'E' . str_pad($i, 3, '0', STR_PAD_LEFT))
                                    <option value="{{ $room }}" @selected(old('examination_room') === $room)>{{ $room }}</option>
                                @endfor
                            </select>
                        </label>
                    </div>
                    <button type="submit">Register and Schedule Appointment</button>
                </form>
            </div>
        </aside>

        <div class="nurse-registration-table-wrap">
            <table class="nurse-registration-table">
                <thead>
                    <tr>
                        <th>Patient Number</th>
                        <th>Name</th>
                        <th>Date of Birth</th>
                        <th>Ward</th>
                        <th>Date Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>{{ $patient->patient_number }}</td>
                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                            <td>{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d-M-y') : 'N/A' }}</td>
                            <td>
                                <span class="nurse-ward-pill">{{ $patient->ward_name ?? 'Not Admitted' }}</span>
                            </td>
                            <td>{{ $patient->date_registered ? \Carbon\Carbon::parse($patient->date_registered)->format('d-M-y') : 'N/A' }}</td>
                            <td>
                                <details class="nurse-action-menu">
                                    <summary>View</summary>
                                    <div class="nurse-action-panel">
                                        <div class="nurse-patient-summary">
                                            <p><strong>Phone:</strong> {{ $patient->telephone ?? 'N/A' }}</p>
                                            <p><strong>Gender:</strong> {{ $patient->sex ?? 'N/A' }}</p>
                                            <p><strong>Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
                                            <p><strong>Next of Kin:</strong> {{ $patient->kin_name ?? 'N/A' }}</p>
                                        </div>

                                        <form class="nurse-form" method="POST" action="{{ route('nurse.register-patient.update', $patient->patient_number) }}">
                                            @csrf
                                            @method('PUT')
                                            @include('NursingStaff.partials.patient-registration-fields', ['patient' => $patient])
                                            <button type="submit">Update Patient</button>
                                        </form>

                                        <form method="POST" action="{{ route('nurse.register-patient.destroy', $patient->patient_number) }}" onsubmit="return confirm('Delete this patient registration record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="nurse-danger-button" type="submit" @disabled((bool) $patient->is_admitted)>Delete Patient</button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No patient records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="nurse-registration-footer">Showing {{ $patients->count() }} patients</div>
    </section>
</div>
@endsection
