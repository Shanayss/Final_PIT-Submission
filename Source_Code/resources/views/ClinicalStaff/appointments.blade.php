@extends('layouts.app')

@push('styles')
    {{-- Font Awesome icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Appointment dashboard CSS --}}
    <link rel="stylesheet" href="{{ asset('css/consultant/appointments.css') }}">
@endpush

@section('content')

<div class="appointment-container">

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Dashboard statistic cards --}}
    <div class="cards-container">

        {{-- Appointments Card --}}
        <div class="stat-card active-card">
            <div class="stat-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>

            <div>
                <h3>Appointments</h3>
                <h1>{{ $totalAppointments ?? 0 }}</h1>
                <p>Today’s scheduled visits</p>
            </div>
        </div>

        {{-- Pending Card --}}
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

            <div>
                <h3>Pending</h3>
                <h1>{{ $pendingCount ?? 0 }}</h1>
                <p>Waiting for completion</p>
            </div>
        </div>

        {{-- Completed Card --}}
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div>
                <h3>Completed</h3>
                <h1>{{ $completedCount ?? 0 }}</h1>
                <p>Finished procedures</p>
            </div>
        </div>

    </div>

    {{-- Recent appointments header and actions --}}
    <div class="appointments-header">
        <h2>Recent Appointments</h2>

        <div class="appointments-actions">

            {{-- Search and filter form --}}
            <form method="GET" action="{{ route('clinical.appointments') }}" class="filter-row">
                <input 
                    type="text"
                    name="search"
                    placeholder="Search patient, ID, or status..."
                    value="{{ request('search') }}"
                >

                <button type="submit">
                    Filter
                </button>
            </form>

        </div>
    </div>

    {{-- Appointments table --}}
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Patient</th>
                    <th>Staff/Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($appointments as $appointment)
                    @php
                        $statusClass = strtolower(str_replace(' ', '-', $appointment->status));
                    @endphp

                    <tr>
                        {{-- Appointment ID --}}
                        <td>{{ $appointment->appointment_id }}</td>

                        {{-- Patient name --}}
                        <td>{{ $appointment->patient_display_name }}</td>

                        {{-- Doctor / Consultant --}}
                        <td>{{ $appointment->doctor_display_name }}</td>

                        {{-- Appointment date --}}
                        <td>{{ $appointment->appointment_date }}</td>

                        {{-- Appointment time --}}
                        <td>{{ $appointment->appointment_time }}</td>

                        {{-- Room --}}
                        <td>{{ $appointment->examination_room }}</td>

                        {{-- Status --}}
                        <td>
                            <span class="status {{ $statusClass }}">
                                {{ $appointment->status }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="actions">

                                {{-- Edit button --}}
                                <a 
                                    href="{{ route('clinical.appointments.edit', $appointment->appointment_id) }}" 
                                    class="btn-edit"
                                >
                                    Edit
                                </a>

                                {{-- Delete button --}}
                                <form 
                                    action="{{ route('clinical.appointments.destroy', $appointment->appointment_id) }}" 
                                    method="POST"
                                    onsubmit="return confirm('Delete this appointment?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete">
                                        Delete
                                    </button>
                                </form>

                                {{-- Treatment button --}}
                                <a href="{{ route('clinical.treatments.create', $appointment->appointment_id) }}" class="btn-treatment">
                                    Treatment
                                </a>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding: 30px;">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            No appointments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
