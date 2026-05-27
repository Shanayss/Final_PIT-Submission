@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>{{ $patient->first_name }} {{ $patient->last_name }}</h1>
        <a class="nurse-action-button" href="{{ route('nurse.patient-care.assigned') }}">Back</a>
    </div>

    <div class="nurse-detail-grid">
        <section class="nurse-panel">
            <h2>Patient Details</h2>
            <p><strong>ID:</strong> {{ $patient->patient_number }}</p>
            <p><strong>Age:</strong> {{ $patient->age ?? 'N/A' }}</p>
            <p><strong>Sex:</strong> {{ $patient->sex ?? 'N/A' }}</p>
            <p><strong>Marital Status:</strong> {{ $patient->marital_status ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $patient->telephone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
<p><strong>Ward/Bed:</strong> {{ $patient->ward_name ?? 'N/A' }} / {{ $patient->bed_number ? str_pad($patient->bed_number, 2, '0', STR_PAD_LEFT) : 'Awaiting bed' }}</p>
            <p><strong>Waiting List Date:</strong> {{ $patient->date_placed_on_waiting_list ?? 'N/A' }}</p>
            <p><strong>Expected Stay:</strong> {{ $patient->expected_stay_days ? $patient->expected_stay_days . ' days' : 'N/A' }}</p>
            <p><strong>Date Placed in Ward:</strong> {{ $patient->bed_number ? ($patient->date_admitted ?? 'N/A') : 'Not yet placed' }}</p>
            <p><strong>Expected Leave:</strong> {{ $patient->date_expected_leave ?? 'N/A' }}</p>
            <p><strong>Actual Leave:</strong> {{ $patient->date_actual_leave ?? 'Not recorded' }}</p>
        </section>

        <section class="nurse-panel">
            <h2>Next of Kin</h2>
            @if($nextOfKin)
                <p><strong>Name:</strong> {{ $nextOfKin->full_name }}</p>
                <p><strong>Relationship:</strong> {{ $nextOfKin->relationship }}</p>
                <p><strong>Phone:</strong> {{ $nextOfKin->telephone ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $nextOfKin->address ?? 'N/A' }}</p>
            @else
                <p class="nurse-muted">No next of kin record found.</p>
            @endif
        </section>

        @if($localDoctor)
            <section class="nurse-panel">
                <h2>Local Doctor</h2>
                <p><strong>Clinic:</strong> {{ $localDoctor->clinic_number }}</p>
                <p><strong>Name:</strong> {{ $localDoctor->full_name }}</p>
                <p><strong>Phone:</strong> {{ $localDoctor->telephone ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $localDoctor->address ?? 'N/A' }}</p>
            </section>
        @else
            <section class="nurse-panel">
                <h2>Local Doctor</h2>
                <p class="nurse-muted">No local doctor assigned.</p>
            </section>
        @endif
    </div>

    <section class="nurse-table-card nurse-section-gap">
        <div class="nurse-card-heading"><h2>Care Notes</h2></div>
        <table class="nurse-table">
            <tbody>
                @forelse($careNotes as $note)
                    <tr>
                        <td><strong>{{ $note->note_type }}</strong><span>{{ $note->recorded_at }}</span></td>
                        <td>{{ $note->notes }}</td>
                    </tr>
                @empty
                    <tr><td>No care notes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
