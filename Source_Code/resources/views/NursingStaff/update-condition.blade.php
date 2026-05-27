@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Update Patient Condition</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-record-layout">
        <div class="nurse-card-list">
            @forelse($patients as $patient)
                <section class="nurse-panel">
                    <div class="nurse-card-title-row">
                        <div>
                            <h2>{{ $patient->first_name }} {{ $patient->last_name }}</h2>
                            <p>{{ $patient->patient_number }} • {{ $patient->age ?? 'N/A' }} years old</p>
<p>{{ $patient->ward_name ?? 'N/A' }} - {{ $patient->bed_number ? str_pad($patient->bed_number, 2, '0', STR_PAD_LEFT) : 'Awaiting bed' }}</p>
                        </div>
                        <span class="nurse-status-pill {{ strtolower($patient->condition_status) === 'critical' ? 'is-critical' : 'is-administered' }}">{{ $patient->condition_status }}</span>
                    </div>
                    <div class="nurse-info-grid">
                        <div><span>Diagnosis</span><strong>{{ $patient->diagnosis_details ?? 'N/A' }}</strong></div>
                        <div><span>Vital Signs</span><strong>BP: {{ $patient->blood_pressure ?? 'N/A' }}<br>Temp: {{ $patient->temperature ?? 'N/A' }}<br>HR: {{ $patient->heart_rate ?? 'N/A' }}</strong></div>
                    </div>
                    <button type="button" class="nurse-small-button" data-condition-patient="{{ $patient->patient_number }}">Update Condition</button>
                </section>
            @empty
                <section class="nurse-panel"><p class="nurse-muted">No admitted patients found.</p></section>
            @endforelse
        </div>

        <section class="nurse-panel">
            <h2>Update Form</h2>
            <form class="nurse-form" method="POST" action="{{ route('nurse.patient-care.update-condition.store') }}">
                @csrf
                <label>Patient ID
                    <select name="patient_number" id="condition-patient-select" required>
                        <option value="">Select patient...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->patient_number }}">{{ $patient->patient_number }} - {{ $patient->first_name }} {{ $patient->last_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Condition Status
                    <select name="condition_status" required>
                        <option>Stable</option>
                        <option>Recovering</option>
                        <option>Critical</option>
                    </select>
                </label>
                <label>Blood Pressure
                    <input name="blood_pressure" placeholder="e.g., 120/80">
                </label>
                <label>Temperature
                    <input name="temperature" placeholder="e.g., 98.6F">
                </label>
                <label>Heart Rate
                    <input name="heart_rate" placeholder="e.g., 72 bpm">
                </label>
                <label>Notes
                    <textarea name="notes" placeholder="Additional observations..."></textarea>
                </label>
                <button type="submit">Submit Update</button>
            </form>
        </section>
    </div>
</div>
@endsection
