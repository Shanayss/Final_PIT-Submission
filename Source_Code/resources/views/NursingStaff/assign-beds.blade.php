@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Assign Beds</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-two-column">
        <section class="nurse-panel">
            <h2>Ward Beds</h2>
            <div class="nurse-ward-list">
                @forelse($wards as $ward)
                    @php($beds = $bedsByWard->get($ward->ward_number, collect()))
                    <div class="nurse-ward-card">
                        <h3>{{ $ward->ward_name }}</h3>
                        <div class="nurse-bed-tags">
@forelse($beds as $bed)
                                <button
                                    type="button"
                                    class="nurse-bed-tag {{ $bed->live_status === 'Occupied' ? 'is-occupied' : '' }}"
                                    @if($bed->live_status === 'Available') data-bed-choice data-ward="{{ $bed->ward_number }}" data-bed="{{ $bed->bed_number }}" @else disabled @endif
                                >
                                    {{ str_pad($bed->bed_number, 2, '0', STR_PAD_LEFT) }}
                                </button>
                            @empty
                                <span class="nurse-muted">No beds found</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="nurse-muted">No wards found.</p>
                @endforelse
            </div>
        </section>

        <section class="nurse-panel">
            <h2>Assign Patient to Bed</h2>
            <form class="nurse-form" method="POST" action="{{ route('nurse.assign-beds.store') }}">
                @csrf
                <label>Patient ID
                    <select name="patient_number" id="assign-patient-select" required>
                        <option value="">Select Patient ID</option>
                        @foreach($patients as $patient)
                            <option
                                value="{{ $patient->patient_number }}"
                                data-ward="{{ $patient->ward_number }}"
                                data-expected-stay="{{ $patient->expected_stay_days }}"
                                data-expected-discharge="{{ $patient->date_expected_leave }}"
                                @selected(old('patient_number') === $patient->patient_number)
                            >
                                {{ $patient->patient_number }} - {{ $patient->first_name }} {{ $patient->last_name }}
                                (Ward {{ $patient->ward_number }}, {{ $patient->expected_stay_days ?? 'N/A' }} days)
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>Ward
                    <select name="ward_number" id="assign-ward-select" required>
                        <option value="">Select ward...</option>
                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_number }}" @selected((string) old('ward_number') === (string) $ward->ward_number)>{{ $ward->ward_name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Select Bed
                    <select name="bed_number" id="assign-bed-select" required>
                        <option value="">Select bed...</option>
                        @foreach($availableBedsByWard->flatten() as $bed)
<option value="{{ $bed->bed_number }}" data-ward="{{ $bed->ward_number }}" @selected((string) old('bed_number') === (string) $bed->bed_number)>{{ str_pad($bed->bed_number, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </select>
                </label>

                <label>Expected Discharge Date
                    <input name="expected_discharge_date" id="assign-expected-discharge-date" type="date" value="{{ old('expected_discharge_date') }}" required>
                </label>

                <button type="submit">Assign Bed</button>
            </form>
        </section>
    </div>
</div>
@endsection
