@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Create Supply Requisition</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-record-layout">
        <section class="nurse-panel">
            <h2>New Requisition Form</h2>
            <form class="nurse-form" method="POST" action="{{ route('nurse.supplies.store') }}">
                @csrf
                <div class="nurse-form-grid">
                    <label>Requisition Date
                        <input value="{{ now()->format('m/d/Y') }}" disabled>
                    </label>
                    <label>Priority
                        <select name="priority" required>
                            <option>Normal</option>
                            <option>Low</option>
                            <option>High</option>
                            <option>Urgent</option>
                        </select>
                    </label>
                </div>

                <label>Department/Ward
                    <select name="ward_number" required>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_number }}">{{ $ward->ward_name }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="nurse-requisition-row">
                    <label>Item
                        <select name="item_number[]" required>
                            <option value="">Select item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->item_number }}" @selected($prefillItem === $item->item_number)>{{ $item->item_number }} - {{ $item->item_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Quantity
                        <input name="quantity[]" type="number" min="1" placeholder="Qty" required>
                    </label>
                </div>

                <label>Justification/Notes
                    <textarea name="notes" placeholder="Explain the need for these supplies..."></textarea>
                </label>

                <button type="submit">Submit Requisition</button>
            </form>
        </section>

        <section class="nurse-panel">
            <h2>Low Stock Items</h2>
            <div class="nurse-card-list">
                @forelse($lowStockItems as $item)
                    <a class="nurse-low-stock-card" href="{{ route('nurse.supplies.create', ['item_number' => $item->item_number]) }}">
                        <strong>{{ $item->item_name }}</strong>
                        <span>Current: {{ $item->quantity_of_stock }} / Min: {{ $item->reorder_level }}</span>
                    </a>
                @empty
                    <p class="nurse-muted">No low stock items.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
