@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Medication Orders</h1>
        <form class="nurse-search" method="GET" action="{{ route('nurse.medication.prescriptions') }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="search" value="{{ $search }}" placeholder="Search medication orders...">
        </form>
    </div>

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Medication ID</th>
                    <th>Patient</th>
                    <th>Ordered By</th>
                    <th>Drug</th>
                    <th>Method</th>
                    <th>Duration</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescriptions as $prescription)
                    @php
                        $start = $prescription->start_date ? \Carbon\Carbon::parse($prescription->start_date) : null;
                        $end = $prescription->end_date ? \Carbon\Carbon::parse($prescription->end_date) : null;
                    @endphp
                    <tr>
                        <td>MED-{{ $prescription->medication_id }}</td>
                        <td>
                            <strong>{{ $prescription->first_name }} {{ $prescription->last_name }}</strong>
                            <span>{{ $prescription->patient_number }}</span>
                        </td>
                        <td>
                            <strong>{{ trim(($prescription->staff_first_name ?? '') . ' ' . ($prescription->staff_last_name ?? '')) ?: 'Unassigned' }}</strong>
                            <span>{{ $prescription->staff_number ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <strong>{{ $prescription->drug_name }}</strong>
                            <span>{{ $prescription->drug_number }}{{ $prescription->dosage ? ' / ' . $prescription->dosage : '' }}</span>
                        </td>
                        <td>{{ $prescription->method_of_admin ?? 'N/A' }}</td>
                        <td>{{ $start && $end ? $start->diffInDays($end) . ' days' : 'Ongoing' }}</td>
                        <td>{{ $prescription->start_date ?? 'N/A' }}</td>
                        <td>{{ $prescription->end_date ?? 'N/A' }}</td>
                        <td><button type="button" class="nurse-link-button" data-prescription-detail>View Details</button></td>
                    </tr>
                    <tr class="nurse-prescription-details" hidden>
                        <td colspan="9">
                            <div class="nurse-prescription-detail-grid">
                                <div>
                                    <span>Patient</span>
                                    <strong>{{ $prescription->patient_number }} - {{ $prescription->first_name }} {{ $prescription->last_name }}</strong>
                                </div>
                                <div>
                                    <span>Drug</span>
                                    <strong>{{ $prescription->drug_name }} {{ $prescription->dosage ? '(' . $prescription->dosage . ')' : '' }}</strong>
                                </div>
                                <div>
                                    <span>Administration</span>
                                    <strong>{{ $prescription->method_of_admin ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span>Units Per Day</span>
                                    <strong>{{ $prescription->unit_per_day ?? 'N/A' }}</strong>
                                </div>
                                <div>
                                    <span>Ordered By</span>
                                    <strong>{{ trim(($prescription->staff_first_name ?? '') . ' ' . ($prescription->staff_last_name ?? '')) ?: 'Unassigned' }}</strong>
                                </div>
                                <div>
                                    <span>Description</span>
                                    <strong>{{ $prescription->description ?? 'No description recorded.' }}</strong>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">No medication orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
