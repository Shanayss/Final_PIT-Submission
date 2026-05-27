@extends('layouts.app')

@section('content')

    <div class="nurse-stats">
        <a class="nurse-stat-card" href="{{ route('nurse.admit-patients') }}">
            <span>Today Admissions</span>
            <strong>{{ $todayAdmissions }}</strong>
        </a>
        <a class="nurse-stat-card" href="{{ route('nurse.patient-care') }}">
            <span>Active Patients</span>
            <strong>{{ $activePatients }}</strong>
        </a>
        <a class="nurse-stat-card" href="{{ route('nurse.assign-beds') }}">
            <span>Available Beds</span>
            <strong>{{ $availableBeds }}</strong>
        </a>
        <a class="nurse-stat-card" href="{{ route('nurse.medication.schedules') }}">
            <span>Medication Orders</span>
            <strong>{{ $medicationsDue }}</strong>
        </a>
    </div>
</div>
@endsection
