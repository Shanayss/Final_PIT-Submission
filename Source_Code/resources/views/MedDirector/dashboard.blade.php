@extends('layouts.app')

@section('content')
<div class="meddirector-card">
    <h3 style="margin-bottom:14px;">Dashboard Features</h3>

    <div class="meddirector-grid">
        <div class="meddirector-card meddirector-span-3">
            <h3>Total Patients</h3>
            <div class="meddirector-number">{{ $totalPatients ?? 0 }}</div>
        </div>

        <div class="meddirector-card meddirector-span-3">
            <h3>Total Staff</h3>
            <div class="meddirector-number">{{ $totalStaff ?? 0 }}</div>
        </div>

        <div class="meddirector-card meddirector-span-3">
            <h3>Occupied Beds</h3>
            <div class="meddirector-number">{{ $occupiedBeds ?? 0 }}</div>
        </div>

        <div class="meddirector-card meddirector-span-3">
            <h3>Available Beds</h3>
            <div class="meddirector-number">{{ $availableBeds ?? 0 }}</div>
        </div>

        <div class="meddirector-card meddirector-span-12">
            <h3>Ward Occupancy Summary</h3>
            <table class="meddirector-table">
                <thead>
                    <tr>
                        <th>Ward</th>
                        <th>Occupied</th>
                        <th>Available</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wardOccupancy ?? [] as $ward)
                        <tr>
                            <td>
                                <div style="font-weight:800;">{{ $ward['ward_name'] }} ({{ $ward['ward_number'] }})</div>
                            </td>
                            <td>
                                <span class="meddirector-pill meddirector-pill--bad">{{ $ward['occupied'] }}</span>
                            </td>
                            <td>
                                <span class="meddirector-pill meddirector-pill--ok">{{ $ward['available'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="meddirector-subtext">No occupancy data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

