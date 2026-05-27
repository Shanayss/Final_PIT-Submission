@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Current Week Rota</h1>
        <div class="nurse-date"><i class="fa-regular fa-calendar"></i>{{ \Carbon\Carbon::parse($weekStart)->format('M d') }} - {{ \Carbon\Carbon::parse($weekStart)->addDays(6)->format('M d, Y') }}</div>
    </div>

    <section class="nurse-table-card">
        <table class="nurse-table">
            <thead>
                <tr>
                    <th>Shift</th>
                    <th>Staff</th>
                    <th>Role</th>
                    <th>Ward</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allocations as $shift => $items)
                    @foreach($items as $item)
                        <tr>
                            <td>{{ $shift ?? 'Unassigned' }}</td>
                            <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                            <td>{{ $item->role_for_week ?? $item->position ?? 'N/A' }}</td>
                            <td>{{ $item->ward_name ?? ('Ward ' . $item->ward_number) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="4">No rota records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
