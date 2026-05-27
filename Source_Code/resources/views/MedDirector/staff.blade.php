@extends('layouts.app')

@section('content')
<!-- Include Custom Staff Layout Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-staff.css') }}">

<div class="meddirector-system-wrapper">
    
    <!-- Header Block Component Section -->
    <div class="meddirector-header-card">
        <h2 class="meddirector-header-title">Staff Overview</h2>
        <p class="meddirector-header-subtitle">Overview of real-time staff deployment rosters, live shift attendance records, and allocation managers.</p>
    </div>

    <!-- Live Telemetry Staff Status Cards Grid -->
    <div class="meddirector-staff-metrics-grid">
        <div class="meddirector-staff-card">
            <div class="md-staff-card-header">
                <svg class="md-staff-svg-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="md-staff-label">Allocated staff</span>
            </div>
            <div class="md-staff-number">{{ $totalStaffOnDuty ?? 0 }}</div>
            <div class="md-staff-subtext">{{ $totalRegisteredStaff ?? 0 }} staff records in database</div>
        </div>

        <div class="meddirector-staff-card">
            <div class="md-staff-card-header">
                <svg class="md-staff-svg-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="md-staff-label">Nurses on shift</span>
            </div>
            <div class="md-staff-number">{{ $nursesOnShift ?? 0 }}</div>
            <div class="md-staff-subtext">{{ $nursesPercentage ?? 0 }}% of total</div>
        </div>

        <div class="meddirector-staff-card">
            <div class="md-staff-card-header">
                <svg class="md-staff-svg-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="md-staff-label">Doctors on duty</span>
            </div>
            <div class="md-staff-number">{{ $doctorsOnDuty ?? 0 }}</div>
            <div class="md-staff-subtext">{{ $doctorsPercentage ?? 0 }}% of total</div>
        </div>

        <div class="meddirector-staff-card">
            <div class="md-staff-card-header">
                <svg class="md-staff-svg-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="md-staff-label">Support on duty</span>
            </div>
            <div class="md-staff-number">{{ $supportOnDuty ?? 0 }}</div>
            <div class="md-staff-subtext">{{ $supportPercentage ?? 0 }}% of total</div>
        </div>
    </div>

    <!-- Expanded Full-Width Staff Per Ward Workspace Container -->
    <div class="meddirector-roster-workspace-full">
        <div class="meddirector-panel-card">
            <div class="md-panel-header">
                <div class="md-panel-header-left">
                    <svg class="md-panel-title-icon" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="md-panel-title">Staff per ward</h3>
                </div>
            </div>

            <!-- Scrollable list window tracking all 17+ database wards safely -->
            <div class="md-panel-list-scrollable">
                @forelse($wardStaffRecords ?? [] as $record)
                    <div class="md-panel-row-item">
                        <div class="md-panel-icon-box color-theme-{{ $record['color_theme'] }}">
                            {!! $record['icon_svg'] !!}
                        </div>
                        <div class="md-panel-details">
                            <h4 class="md-ward-row-title">{{ $record['ward_name'] }}</h4>
                            <div class="md-badge-row">
                                <span class="md-roster-pill pill-total">{{ $record['total_allocated'] }} allocated</span>
                                <span class="md-roster-pill pill-nurses">{{ $record['nurses'] }} nurses</span>
                                <span class="md-roster-pill pill-doctors">{{ $record['doctors'] }} doctors</span>
                                <span class="md-roster-pill pill-support">{{ $record['support'] }} support</span>
                            </div>
                        </div>
                        <div class="md-roster-side-metrics">
                            <div class="md-metric-patients {{ $record['alert'] ? 'text-alert' : '' }}">
                                {{ $record['occupied'] }}
                                @if($record['alert'])
                                    <span class="md-alert-triangle">&#9888;</span>
                                @endif
                            </div>
                            <div class="md-metric-beds">{{ $record['total_beds'] }} beds</div>
                        </div>
                    </div>
                @empty
                    <div class="md-panel-empty">No active staffing distribution data found.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- Include Custom JS Behavioral Asset Logic -->
<script src="{{ asset('js/meddirector-staff.js') }}"></script>
@endsection
