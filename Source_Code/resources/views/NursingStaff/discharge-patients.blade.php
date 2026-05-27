@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Discharge Patients</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Ward</th>
                    <th>Bed</th>
                    <th>Admitted</th>
                    <th>Expected Leave</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td><strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong><span>{{ $patient->patient_number }}</span></td>
                        <td>{{ $patient->ward_name ?? 'N/A' }}</td>
<td>{{ $patient->bed_number ? str_pad($patient->bed_number, 2, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                        <td>{{ $patient->date_admitted ?? 'Waiting' }}</td>
                        <td>{{ $patient->date_expected_leave ?? 'N/A' }}</td>
                        <td>
                            <div class="nurse-table-actions">
                            <a class="nurse-link-button" href="{{ route('nurse.patient-care.assigned.show', $patient->patient_number) }}">View Details</a>
                            <form class="nurse-inline-form" method="POST" action="{{ route('nurse.discharge-patients.store') }}">
                                @csrf
                                <input type="hidden" name="in_patient_id" value="{{ $patient->in_patient_id }}">
                                <button class="nurse-small-button" type="submit">Discharge</button>
                            </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">No active in-patients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
