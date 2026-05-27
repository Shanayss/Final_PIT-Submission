@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Patient Care</h1>
    </div>

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Ward</th>
                    <th>Bed</th>
                    <th>Contact</th>
                    <th>Ward Dates</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td><strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong><span>{{ $patient->patient_number }}</span></td>
                        <td>{{ $patient->ward_name ?? 'N/A' }}</td>
<td>{{ $patient->bed_number ? str_pad($patient->bed_number, 2, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                        <td>{{ $patient->telephone ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ $patient->bed_number ? ($patient->date_admitted ?? 'N/A') : 'Waiting list' }}</strong>
                            <span>Expected leave: {{ $patient->date_expected_leave ?? 'N/A' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No active patient care records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
