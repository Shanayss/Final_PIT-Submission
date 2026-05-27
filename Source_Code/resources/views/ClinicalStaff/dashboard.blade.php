@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/consultant/dashboard.css') }}">

{{-- Poppins font from previous system --}}
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

@endpush

@section('content')

<div class="cards-container">

    <div class="card active-card">

        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>

        <div>
            <h3>Today's Appointment</h3>

            <div class="number">
                0
            </div>

            <p>Scheduled visits today</p>
        </div>

    </div>

    <div class="card">

        <div class="stat-icon">
            <i class="fas fa-user-injured"></i>
        </div>

        <div>
            <h3>Total Patients</h3>

            <div class="number">
                0
            </div>

            <p>Total handled patients</p>
        </div>

    </div>

</div>


@endsection
