{{-- TREATMENT DASHBOARD --}}

@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consultant/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/consultant/treatments.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endpush

@section('content')

<div class="treatment-dashboard">

    {{-- Success message --}}
    @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Treatment statistic cards --}}
    <div class="treatment-stats">

        <div class="treatment-card treatment-card-primary">
            <div class="treatment-icon">
                <i class="fas fa-notes-medical"></i>
            </div>

            <div>
                <h3>Treatments</h3>
                <strong>{{ $total ?? 0 }}</strong>
                <p>Total treatment records</p>
            </div>
        </div>

        <div class="treatment-card">
            <div class="treatment-icon">
                <i class="fas fa-clock"></i>
            </div>

            <div>
                <h3>Pending</h3>
                <strong>{{ $pending ?? 0 }}</strong>
                <p>Waiting for treatment</p>
            </div>
        </div>

        <div class="treatment-card">
            <div class="treatment-icon">
                <i class="fas fa-spinner"></i>
            </div>

            <div>
                <h3>In Progress</h3>
                <strong>{{ $progress ?? 0 }}</strong>
                <p>Currently ongoing</p>
            </div>
        </div>

        <div class="treatment-card">
            <div class="treatment-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <div>
                <h3>Completed</h3>
                <strong>{{ $completed ?? 0 }}</strong>
                <p>Successfully completed</p>
            </div>
        </div>

    </div>

    {{-- Header and filter --}}
    <div class="treatment-header">
        <h2 class="section-title">Treatment Records</h2>

        <form class="treatment-filter" method="GET" action="{{ route('clinical.treatments') }}">
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Search patient, diagnosis, or status..."
            >

            <button type="submit">
                Filter
            </button>
        </form>
    </div>

    {{-- Treatment records table --}}
    <div class="treatment-table-card">
        <table class="treatment-table">
            <thead>
                <tr>
                    <th>Treatment ID</th>
                    <th>Patient</th>
                    <th>Diagnosis</th>
                    <th>Procedure</th>
                    <th>Medication</th>
                    <th>Nurse</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($treatments as $treatment)
                    <tr>
                        <td>{{ $treatment->id }}</td>

                        <td>{{ $treatment->patient_name ?? 'No patient' }}</td>

                        <td>{{ $treatment->diagnosis }}</td>

                        <td>{{ $treatment->procedure }}</td>

                        <td>{{ $treatment->medication ?? 'N/A' }}</td>

                        <td>{{ $treatment->nurse_name ?? 'Not assigned' }}</td>

                        <td>
                            @php
                                $statusClass = strtolower(str_replace(' ', '-', $treatment->status));
                            @endphp

                            <span class="treatment-status {{ $statusClass }}">
                                {{ $treatment->status }}
                            </span>
                        </td>

                        <td>
                            <div class="treatment-actions">

                                <a 
                                    href="{{ route('clinical.treatments.show', $treatment->id) }}" 
                                    class="mini-action view"
                                >
                                    View
                                </a>

                                <a 
                                    href="{{ route('clinical.treatments.create', $treatment->appointment_id) }}" 
                                    class="mini-action edit"
                                >
                                    Edit
                                </a>

                                <form 
                                    action="{{ route('clinical.treatments.destroy', $treatment->id) }}" 
                                    method="POST"
                                    onsubmit="return confirm('Delete this treatment record?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="mini-action delete">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-row">
                            No treatment records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
    /* ================================
   TREATMENT DASHBOARD PAGE
================================ */

.treatment-dashboard {
    width: 100%;
    font-family: 'Poppins', sans-serif;
    color: #0f2f22;
}

/* ================================
   STAT CARDS
================================ */

.treatment-stats {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.treatment-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 22px;
    display: flex;
    align-items: center;
    gap: 18px;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.06);
    transition: 0.3s ease;
}

.treatment-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 18px 38px rgba(0, 0, 0, 0.08);
}

.treatment-card-primary {
    background: linear-gradient(135deg, #2D533E, #42B883);
    color: #ffffff;
}

.treatment-icon {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: #E8F5E9;
    color: #2D533E;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
    flex-shrink: 0;
}

.treatment-card h3 {
    margin: 0 0 8px 0;
    font-size: 17px;
    font-weight: 700;
}

.treatment-card strong {
    display: block;
    font-size: 34px;
    font-weight: 800;
    line-height: 1;
}

.treatment-card p {
    margin: 8px 0 0 0;
    font-size: 13px;
    color: #53627a;
}

.treatment-card-primary p {
    color: rgba(255, 255, 255, 0.9);
}

/* ================================
   SECTION TITLE + FILTER
================================ */

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f2f22;
    margin: 22px 0 14px 0;
}

.table-title {
    margin-top: 34px;
}

.treatment-filter {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 26px;
}

.treatment-filter input {
    width: 310px;
    height: 46px;
    padding: 0 16px;
    border: 1px solid #d9e2ec;
    border-radius: 9px;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    outline: none;
    background: #ffffff;
    color: #0f2f22;
}

.treatment-filter input:focus {
    border-color: #2D533E;
    box-shadow: 0 0 0 3px rgba(45, 83, 62, 0.08);
}

.treatment-filter button {
    height: 46px;
    padding: 0 22px;
    border: none;
    border-radius: 9px;
    background: #245c3d;
    color: #ffffff;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.treatment-filter button:hover {
    background: #1d4930;
}

/* ================================
   TABLE CARD
================================ */

.treatment-table-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 24px 20px 14px;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.06);
    overflow-x: auto;
}

.treatment-table {
    width: 100%;
    border-collapse: collapse;
}

.treatment-table th {
    padding: 16px 14px;
    text-align: center;
    border-bottom: 2px solid #edf1f5;
    color: #4b5f5a;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    white-space: nowrap;
}

.treatment-table td {
    padding: 20px 14px;
    text-align: center;
    border-bottom: 1px solid #edf1f5;
    color: #17324d;
    font-size: 14px;
    font-weight: 500;
    vertical-align: middle;
}

.treatment-table tbody tr:last-child td {
    border-bottom: none;
}

.empty-row {
    text-align: center;
    padding: 35px !important;
    color: #64748b;
}

/* ================================
   STATUS BADGES
================================ */

.treatment-status {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
}

.treatment-status.pending {
    background: #fff8e1;
    color: #b7791f;
}

.treatment-status.in-progress {
    background: #e3f2fd;
    color: #1565c0;
}

.treatment-status.completed {
    background: #e8f5e9;
    color: #1b7a36;
}

/* ================================
   ACTION BUTTONS
================================ */

.treatment-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.mini-action {
    border: none;
    border-radius: 7px;
    padding: 8px 14px;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: 0.2s ease;
    display: inline-block;
}

.treatment-actions form {
    margin: 0;
}

.mini-action.edit {
    background: #e8f2ff;
    color: #1565c0;
}

.mini-action.delete {
    background: #fff0f0;
    color: #d32f2f;
}

.mini-action:hover {
    transform: translateY(-1px);
}

.mini-action.view {
    background: #eef7f1;
    color: #166534;
}

/* ================================
   RESPONSIVE
================================ */

@media (max-width: 1100px) {
    .treatment-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {
    .treatment-stats {
        grid-template-columns: 1fr;
    }

    .treatment-filter {
        flex-direction: column;
        align-items: stretch;
    }

    .treatment-filter input,
    .treatment-filter button {
        width: 100%;
    }
}

</style>
@endsection