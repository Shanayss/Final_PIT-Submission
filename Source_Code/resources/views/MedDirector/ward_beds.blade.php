@extends('layouts.app')

@section('content')
<!-- CSRF Verification Security Token Header Node Alignment Holder -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include Custom CSS Asset Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-wards.css') }}">

<div class="meddirector-system-wrapper">
    <!-- Header Card Component -->
    <div class="meddirector-header-card">
        <h2 class="meddirector-header-title">Patient & Ward Monitoring</h2>
        <p class="meddirector-header-subtitle">Overview of real-time clinic ward map configurations, live occupancy tracks, and assignment wizards.</p>
    </div>

    <div class="meddirector-summary-grid">
        <div class="meddirector-summary-card">
            <span class="md-summary-label">Patients</span>
            <strong class="md-summary-value">{{ $totalPatients ?? 0 }}</strong>
        </div>
        <div class="meddirector-summary-card">
            <span class="md-summary-label">Staff</span>
            <strong class="md-summary-value">{{ $totalStaff ?? 0 }}</strong>
        </div>
        <div class="meddirector-summary-card">
            <span class="md-summary-label">Occupied beds</span>
            <strong class="md-summary-value">{{ $occupiedBeds ?? 0 }}</strong>
        </div>
        <div class="meddirector-summary-card">
            <span class="md-summary-label">Available beds</span>
            <strong class="md-summary-value">{{ $availableBeds ?? 0 }}</strong>
        </div>
    </div>

    <!-- Controls Toolbar Action Bar Row -->
    <div class="meddirector-control-card-bar">
        <div class="meddirector-bar-left"></div>
        <div class="meddirector-bar-right">
            <select id="md-ward-selector" class="meddirector-dropdown-select">
                @foreach($wardOccupancy as $index => $ward)
                    <option value="{{ $ward['ward_number'] }}" {{ $index === 0 ? 'selected' : '' }}>
                        Switch to {{ $ward['ward_name'] }} (Ward {{ $ward['ward_number'] }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Status Tracker Legends Placed Out to Keep Elements Even -->
    <div class="meddirector-legend-bar">
        <div class="meddirector-legend-item"><span class="meddirector-dot occupied"></span> Occupied</div>
        <div class="meddirector-legend-item"><span class="meddirector-dot vacant"></span> Vacant</div>
        <div class="meddirector-legend-item"><span class="meddirector-dot reserved"></span> Reserved</div>
    </div>

    <!-- Main Workspace Layout Flex Container Splitter Grid -->
    <div class="meddirector-workspace-layout">
        
        <!-- Left Side: Bed Matrix Grid Elements Section -->
        <div class="meddirector-workspace-left">
            <!-- Ward Grid Container Wrapper Box -->
            <div class="meddirector-ward-subcard">
                <h3 id="md-ward-display-title" class="meddirector-ward-subtitle">Loading ward data...</h3>
                <div id="meddirector-bed-grid-container" class="meddirector-bed-grid"></div>
            </div>
        </div>

        <!-- Right Side: Patient Detail Summary Monitor Profile Card -->
        <div class="meddirector-workspace-right">
            <div id="md-patient-info-card" class="meddirector-patient-details-card fallback-hidden">
                <div class="md-patient-card-header">
                    <span id="md-detail-bed-title" class="md-detail-header-text">Bed 0 — selected</span>
                </div>
                <div class="md-patient-card-body">
                    <div class="md-detail-row">
                        <span class="md-detail-label">Patient</span>
                        <span id="md-detail-patient-name" class="md-detail-val font-bold">—</span>
                    </div>
                    <div class="md-detail-row">
                        <span class="md-detail-label">Date admitted</span>
                        <span id="md-detail-admitted" class="md-detail-val font-bold">—</span>
                    </div>
                    <div class="md-detail-row">
                        <span class="md-detail-label">Expected leave</span>
                        <span id="md-detail-leave" class="meddirector-detail-val font-bold">—</span>
                    </div>
                    <div class="md-detail-row no-border">
                        <span class="md-detail-label">Status</span>
                        <span class="md-detail-val">
                            <span id="md-detail-pill-status" class="md-status-badge badge-inward">In ward</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Default Placeholder Helper Prompt Card Component View -->
            <div id="md-patient-card-placeholder" class="meddirector-placeholder-tip-card">
                <p>Click on any bed number in the grid system layout to view live patient records profiles summary tracker panels here.</p>
            </div>
        </div>

    </div>
</div>

<script>
    // Bridges data securely to JavaScript layer
    window.wardsConfigurationMap = {!! $wardsJsonConfig !!};
    window.defaultWardId = "{{ $wardOccupancy[0]['ward_number'] ?? '1' }}";
</script>
<!-- Include Custom JS Behavioral Asset Logic -->
<script src="{{ asset('js/meddirector-wards.js') }}"></script>
@endsection
