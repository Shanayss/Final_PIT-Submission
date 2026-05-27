@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Ward Occupancy</h1>
    </div>

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Ward</th>
                    <th>Total Beds</th>
                    <th>Occupied</th>
                    <th>Available</th>
                    <th>Occupancy</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wards as $ward)
                    @php
                        $total = (int) $ward->total_beds;
                        $occupied = (int) $ward->occupied_beds;
                        $percent = $total > 0 ? round(($occupied / $total) * 100) : 0;
                    @endphp
                    <tr>
                        <td><strong>{{ $ward->ward_name }}</strong><span>Ward {{ $ward->ward_number }}</span></td>
                        <td>{{ $total }}</td>
                        <td>{{ $occupied }}</td>
                        <td>{{ max($total - $occupied, 0) }}</td>
                        <td>
                            <div class="nurse-progress"><span style="width: {{ $percent }}%"></span></div>
                            <span>{{ $percent }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No wards found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
