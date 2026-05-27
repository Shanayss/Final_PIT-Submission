@extends('layouts.app')

@section('content')
<!-- Include Custom Outpatient Reports Layout Styling -->
<link rel="stylesheet" href="{{ asset('css/meddirector-outpatient-reports.css') }}">

<div class="meddirector-system-wrapper">
    
    <!-- Component Header Section Box Container -->
    <div class="meddirector-header-card">
        <h2 class="meddirector-header-title">Clinic Detail — Referrals & Follow-Ups</h2>
        <p class="meddirector-header-subtitle">Real-time monitoring of outpatient appointment completion tracking lists, pending referral issues, and scheduling logs.</p>
    </div>

    <!-- Master Layout Stack matching the design pattern exactly -->
    <div class="md-outpatient-master-stack">
        @forelse($clinicReportsData ?? [] as $clinic)
            <div class="md-clinic-block-card">
                
                <!-- Section 1: Clinic Metadata Header Banner Row -->
                <div class="md-clinic-top-section">
                    <div class="md-clinic-meta-left">
                        <div class="md-clinic-icon-box bg-theme-{{ $clinic['theme'] }}">
                            {!! $clinic['icon_svg'] !!}
                        </div>
                        <div class="md-clinic-text-details">
                            <div class="md-clinic-title-row">
                                <h3 class="md-clinic-name">{{ $clinic['clinic_name'] }}</h3>
                                <!-- Status Badges Inline Next to Title -->
                                <div class="md-clinic-badges-cluster">
                                    <span class="md-cluster-badge badge-seen">{{ $clinic['seen'] }} seen</span>
                                    <span class="md-cluster-badge badge-pending">{{ $clinic['pending'] }} pending</span>
                                    <span class="md-cluster-badge badge-dna">{{ $clinic['dna'] }} DNA</span>
                                    <span class="md-cluster-badge badge-new">{{ $clinic['new'] }} new</span>
                                </div>
                            </div>
                            <p class="md-clinic-subline">{{ $clinic['doctor_name'] }} · Room {{ $clinic['room_number'] }} · {{ $clinic['hours'] }}</p>
                            <!-- Secondary Row Badges directly below subtitle -->
                            <div class="md-clinic-sub-badges-row">
                                <span class="md-sub-badge badge-referrals">{{ $clinic['referrals_count'] }} referrals</span>
                                <span class="md-sub-badge badge-followups">{{ $clinic['followups_count'] }} follow-ups</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Two-Column Parallel Split Workspace Layer Rows -->
                <div class="md-clinic-split-workspace">
                    
                    <!-- Left Column Row List: Referrals Issued -->
                    <div class="md-workspace-column">
                        <h4 class="md-column-title-heading">Referrals Issued</h4>
                        <div class="md-items-list-deck">
                            @forelse($clinic['referrals'] as $ref)
                                <div class="md-list-item-row">
                                    <div class="md-avatar-initials bg-theme-{{ $clinic['theme'] }}">{{ $ref['initials'] }}</div>
                                    <div class="md-item-body-details">
                                        <div class="md-item-row-top">
                                            <span class="md-patient-full-name">{{ $ref['patient_name'] }}</span>
                                            <span class="md-status-pill pill-priority-{{ strtolower($ref['priority']) }}">{{ $ref['priority'] }}</span>
                                        </div>
                                        <p class="md-item-description-text">Referred to {{ $ref['destination'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="md-column-empty-fallback">No referrals issued for this clinic section.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Right Column Row List: Follow-Ups Scheduled -->
                    <div class="md-workspace-column">
                        <h4 class="md-column-title-heading">Follow-Ups Scheduled</h4>
                        <div class="md-items-list-deck">
                            @forelse($clinic['followups'] as $fw)
                                <div class="md-list-item-row">
                                    <div class="md-avatar-initials bg-theme-{{ $clinic['theme'] }}">{{ $fw['initials'] }}</div>
                                    <div class="md-item-body-details">
                                        <div class="md-item-row-top">
                                            <span class="md-patient-full-name">{{ $fw['patient_name'] }}</span>
                                            <span class="md-status-pill pill-schedule-state">{{ $fw['status'] }}</span>
                                        </div>
                                        <p class="md-item-description-text">{{ $fw['reason'] }} · {{ $fw['target_date'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="md-column-empty-fallback">No upcoming follow-ups scheduled.</div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        @empty
            <div class="md-outpatient-empty-canvas">
                No active outpatient clinical consultation rooms found inside the database config.
            </div>
        @endforelse
    </div>

</div>
@endsection
