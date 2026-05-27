@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>View Assigned Patients</h1>
        <form class="nurse-filter-row" method="GET" action="{{ route('nurse.patient-care.assigned') }}">
            <select name="ward_number">
                <option value="">All Wards</option>
                @foreach($wards as $ward)
                    <option value="{{ $ward->ward_number }}" @selected((string) $selectedWard === (string) $ward->ward_number)>{{ $ward->ward_name }}</option>
                @endforeach
            </select>
            <input name="search" value="{{ $search }}" placeholder="Search patients...">
            <button type="submit">Filter</button>
        </form>
    </div>

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Ward/Bed</th>
                    <th>Diagnosis</th>
                    <th>Condition</th>
                    <th>Vitals</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>{{ $patient->patient_number }}</td>
                        <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                        <td>{{ $patient->age ?? 'N/A' }}</td>
<td>{{ $patient->ward_name ?? 'N/A' }} - {{ $patient->bed_number ? str_pad($patient->bed_number, 2, '0', STR_PAD_LEFT) : 'Awaiting bed' }}</td>
                        <td>{{ $patient->diagnosis_details ?? 'N/A' }}</td>
                        <td><span class="nurse-status-pill {{ strtolower($patient->condition_status) === 'critical' ? 'is-critical' : (strtolower($patient->condition_status) === 'recovering' ? 'is-due' : 'is-administered') }}">{{ $patient->condition_status }}</span></td>
                        <td>
                            <span>BP: {{ $patient->blood_pressure ?? 'N/A' }}</span>
                            <span>Temp: {{ $patient->temperature ?? 'N/A' }}</span>
                            <span>HR: {{ $patient->heart_rate ?? 'N/A' }}</span>
                        </td>
                        <td><a class="nurse-link-button" href="{{ route('nurse.patient-care.assigned.show', $patient->patient_number) }}">View Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8">No assigned patients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
