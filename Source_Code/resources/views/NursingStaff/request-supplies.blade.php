@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Request Supplies</h1>
        <form class="nurse-search" method="GET" action="{{ route('nurse.supplies.request') }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input name="search" value="{{ $search }}" placeholder="Search inventory...">
        </form>
    </div>

    <section class="nurse-table-card">
        <div class="nurse-card-heading"><h2>Current Inventory Status</h2></div>
        <table class="nurse-table">
            <thead>
                <tr><th>Item ID</th><th>Item Name</th><th>Category</th><th>Current Stock</th><th>Min. Stock</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    @php
                        $critical = $item->quantity_of_stock <= max((int) floor($item->reorder_level / 2), 1);
                        $low = $item->quantity_of_stock <= $item->reorder_level;
                    @endphp
                    <tr>
                        <td>{{ $item->item_number }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->description ?? 'General Supply' }}</td>
                        <td>{{ $item->quantity_of_stock }}</td>
                        <td>{{ $item->reorder_level }}</td>
                        <td><span class="nurse-status-pill {{ $critical ? 'is-critical' : ($low ? 'is-due' : 'is-administered') }}">{{ $critical ? 'Critical' : ($low ? 'Low Stock' : 'In Stock') }}</span></td>
                        <td><a class="nurse-small-button" href="{{ route('nurse.supplies.create', ['item_number' => $item->item_number]) }}">Request</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7">No inventory items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
