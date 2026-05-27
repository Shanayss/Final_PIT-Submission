@extends('layouts.app')

@section('content')
<!-- Include Custom Patient Report Layout Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-patient-reports.css') }}">

<div class="meddirector-system-wrapper">
    
    <!-- Component Header Section Box Container -->
    <div class="meddirector-header-card">
        <h2 class="meddirector-header-title">Patient Report Per Ward</h2>
        <p class="meddirector-header-subtitle">Real-time occupancy tracking, daily discharges logging, and incoming ward admission metrics logs.</p>
    </div>

    <div class="meddirector-report-summary-grid">
        <div class="md-report-summary-card">
            <span class="md-summary-label">Wards</span>
            <strong class="md-summary-value">{{ $reportSummary['total_wards'] ?? 0 }}</strong>
        </div>
        <div class="md-report-summary-card">
            <span class="md-summary-label">Occupied</span>
            <strong class="md-summary-value">{{ $reportSummary['occupied'] ?? 0 }}</strong>
        </div>
        <div class="md-report-summary-card">
            <span class="md-summary-label">Available</span>
            <strong class="md-summary-value">{{ $reportSummary['available'] ?? 0 }}</strong>
        </div>
        <div class="md-report-summary-card">
            <span class="md-summary-label">Admissions</span>
            <strong class="md-summary-value">{{ $reportSummary['admissions'] ?? 0 }}</strong>
        </div>
        <div class="md-report-summary-card">
            <span class="md-summary-label">Discharges</span>
            <strong class="md-summary-value">{{ $reportSummary['discharges'] ?? 0 }}</strong>
        </div>
    </div>

    <!-- Responsive Multi-Column Split Masonry Grid Section Layout -->
    <div class="meddirector-report-masonry-grid">
        @forelse($wardReportsData ?? [] as $row)
            <div class="meddirector-report-card">
                <!-- Top Row: Ward Name Header and Styled Capacity Bed Count Badge -->
                <div class="md-report-card-top">
                    <div class="md-report-title-group">
                        <span class="md-report-icon-box color-theme-{{ $row['theme'] }}">
                            {!! $row['icon_svg'] !!}
                        </span>
                        <h3 class="md-ward-display-name">{{ $row['ward_name'] }}</h3>
                    </div>
                    <span class="md-capacity-badge badge-theme-{{ $row['theme'] }}">
                        {{ $row['total_beds'] }} beds
                    </span>
                </div>

                <!-- Lower Matrix Split Layer Row: Core Metric Columns Quantifiers Group -->
                <div class="md-report-metrics-splitter-row">
                    <!-- Column Node Segment 1: Current Occupants Tracking Metrics -->
                    <div class="md-metric-column-node">
                        <div class="md-metric-quantifier-number">{{ $row['occupied'] }}</div>
                        <div class="md-metric-label-text">Occupied</div>
                    </div>

                    <div class="md-metric-column-node">
                        <div class="md-metric-quantifier-number">{{ $row['available'] }}</div>
                        <div class="md-metric-label-text">Available</div>
                    </div>

                    <!-- Column Node Segment 2: Discharges Count Tracker Log Parameters -->
                    <div class="md-metric-column-node">
                        <div class="md-metric-quantifier-number">{{ $row['discharges'] }}</div>
                        <div class="md-metric-label-text">Discharges</div>
                    </div>

                    <!-- Column Node Segment 3: New Admissions Registries Metrics -->
                    <div class="md-metric-column-node">
                        <div class="md-metric-quantifier-number">{{ $row['admissions'] }}</div>
                        <div class="md-metric-label-text">Admissions</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="meddirector-report-empty-state-box">
                No active patient ward database logging records found on server storage layers.
            </div>
        @endforelse
    </div>

</div>
@endsection
