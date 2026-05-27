@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Record Care Notes</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-record-layout">
        <section>
            <h2 class="nurse-section-title">Recent Care Notes</h2>
            <div class="nurse-card-list">
                @forelse($notes as $note)
                    <article class="nurse-panel">
                        <div class="nurse-card-title-row">
                            <div>
                                <h2>{{ $note->first_name }} {{ $note->last_name }}</h2>
                                <p>{{ $note->patient_number }} • {{ $note->note_type }}</p>
                            </div>
                            <span>{{ $note->recorded_at }}</span>
                        </div>
                        <p>{{ $note->notes }}</p>
                        <p class="nurse-muted">Recorded by: {{ trim(($note->staff_first_name ?? '') . ' ' . ($note->staff_last_name ?? '')) ?: 'N/A' }}</p>
                    </article>
                @empty
                    <section class="nurse-panel"><p class="nurse-muted">No care notes recorded yet.</p></section>
                @endforelse
            </div>
        </section>

        <section class="nurse-panel">
            <h2>Add Care Note</h2>
            <form class="nurse-form" method="POST" action="{{ route('nurse.patient-care.care-notes.store') }}">
                @csrf
                <label>Patient
                    <select name="patient_number" required>
                        <option value="">Select patient...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->patient_number }}">{{ $patient->patient_number }} - {{ $patient->first_name }} {{ $patient->last_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Note Type
                    <select name="note_type" required>
                        <option>General Observation</option>
                        <option>Vital Signs</option>
                        <option>Medication Response</option>
                        <option>Patient Concern</option>
                    </select>
                </label>
                <label>Care Notes
                    <textarea name="notes" placeholder="Enter detailed care notes..." required></textarea>
                </label>
                <div class="nurse-info-note">All care notes are timestamped and permanently recorded in patient records.</div>
                <button type="submit">Save Note</button>
            </form>
        </section>
    </div>
</div>
@endsection
