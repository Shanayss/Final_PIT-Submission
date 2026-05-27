@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Record Medication</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-record-layout">
        <section class="nurse-table-card">
            <div class="nurse-card-heading">
                <h2>Medications Due</h2>
            </div>
            <table class="nurse-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Medication ID</th>
                        <th>Medication</th>
                        <th>Dosage</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medications as $medication)
                        @php
                            $isAdministered = ! is_null($medication->administration_id);
                            $statusClass = $isAdministered ? 'is-administered' : 'is-due';
                            $timeLabel = ((int) $medication->unit_per_day >= 3) ? '08:00, 14:00, 20:00' : '09:00';
                        @endphp
                        <tr>
                            <td><strong>{{ $medication->first_name }} {{ $medication->last_name }}</strong><span>{{ $medication->patient_number }}</span></td>
                            <td>MED-{{ $medication->medication_id }}</td>
                            <td><strong>{{ $medication->drug_name }}</strong><span>{{ $medication->drug_number }}</span></td>
                            <td>{{ $medication->dosage ?? (($medication->unit_per_day ?? 1) . ' dose') }}</td>
                            <td>{{ $timeLabel }}</td>
                            <td><span class="nurse-status-pill {{ $statusClass }}">{{ $isAdministered ? 'Administered' : 'Due' }}</span></td>
                            <td>
                                <button type="button" class="nurse-small-button" data-medication-fill="{{ $medication->medication_id }}" data-dosage="{{ $medication->dosage }}">
                                    Record
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No active medication records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="nurse-panel">
            <h2>Record Administration</h2>
            <form class="nurse-form" method="POST" action="{{ route('nurse.medication.record.store') }}">
                @csrf
                <label>Medication
                    <select name="medication_id" id="record-medication-select" required>
                        <option value="">Select medication...</option>
                        @foreach($medications as $medication)
                            <option value="{{ $medication->medication_id }}" data-dosage="{{ $medication->dosage }}" @selected((string) old('medication_id') === (string) $medication->medication_id)>
                                MED-{{ $medication->medication_id }} - {{ $medication->patient_number }} / {{ $medication->drug_name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>Dosage
                    <input name="dosage_administered" id="record-dosage-input" value="{{ old('dosage_administered') }}" placeholder="e.g., 2 tablets">
                </label>

                <label>Time Administered
                    <input name="administered_at" type="time" value="{{ old('administered_at') }}">
                </label>

                <label>Notes
                    <textarea name="notes" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                </label>

                <button type="submit">Submit Record</button>
            </form>
        </section>
    </div>
</div>
@endsection
