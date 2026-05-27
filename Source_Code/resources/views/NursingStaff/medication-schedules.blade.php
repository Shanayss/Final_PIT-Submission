@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Medication Schedules</h1>
        <div class="nurse-date"><i class="fa-regular fa-clock"></i>Today: {{ now()->format('M d, Y') }}</div>
    </div>

    <div class="nurse-schedule-list">
        @forelse($schedules as $time => $items)
            <section class="nurse-schedule-card">
                <div class="nurse-schedule-heading">
                    <span class="nurse-time-pill">{{ $time }}</span>
                    <span>{{ $items->count() }} {{ \Illuminate\Support\Str::plural('patient', $items->count()) }}</span>
                </div>
                <div class="nurse-medication-rows">
                    @foreach($items as $item)
                        <div class="nurse-medication-row">
                            <div>
                                <strong>{{ $item->first_name }} {{ $item->last_name }}</strong>
                                <span>MED-{{ $item->medication_id }} / {{ $item->drug_name }} {{ $item->dosage }}</span>
                            </div>
                            <div class="nurse-row-actions">
<span>Bed: {{ $item->bed_number ? str_pad($item->bed_number, 2, '0', STR_PAD_LEFT) : 'N/A' }}</span>
                                @if($item->administration_id)
                                    <span class="nurse-status-pill is-administered">Administered</span>
                                @else
                                    <form method="POST" action="{{ route('nurse.medication.record.store') }}">
                                        @csrf
                                        <input type="hidden" name="medication_id" value="{{ $item->medication_id }}">
                                        <input type="hidden" name="dosage_administered" value="{{ $item->dosage }}">
                                        <button type="submit">Mark as Given</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <section class="nurse-panel"><p class="nurse-muted">No medication schedules due today.</p></section>
        @endforelse
    </div>
</div>
@endsection
