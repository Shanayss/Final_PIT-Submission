@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/staff-dashboard.css') }}">

<div class="sd-wrap">

    <div class="sd-hero">
        <div>
            <span class="sd-kicker">Personnel Officer Overview</span>
            <h2>Staff & Department Dashboard</h2>
            <p>Monitor staff records, staff allocation, qualifications, work experience, and contract activity.</p>
        </div>

        <div class="sd-hero-icon">
            <i class="fas fa-user-tie"></i>
        </div>
    </div>

    <div class="sd-stats">

        <div class="sd-stat-card">
            <div>
                <div class="sd-stat-label">Total Staff</div>
                <div class="sd-stat-val">{{ $totalStaff }}</div>
                <div class="sd-stat-note">All registered hospital staff</div>
            </div>
            <div class="sd-stat-icon"><i class="fas fa-users"></i></div>
        </div>

        <div class="sd-stat-card">
            <div>
                <div class="sd-stat-label">Assigned to Wards</div>
                <div class="sd-stat-val">{{ $assignedToWards }}</div>
                <div class="sd-stat-note">Staff currently allocated to wards</div>
            </div>
            <div class="sd-stat-icon"><i class="fas fa-hospital"></i></div>
        </div>

        <div class="sd-stat-card">
            <div>
                <div class="sd-stat-label">Qualifications Recorded</div>
                <div class="sd-stat-val">{{ $qualificationsRecorded }}</div>
                <div class="sd-stat-note">Staff education and training records</div>
            </div>
            <div class="sd-stat-icon"><i class="fas fa-certificate"></i></div>
        </div>

        <div class="sd-stat-card">
            <div>
                <div class="sd-stat-label">Work Experiences</div>
                <div class="sd-stat-val">{{ $workExperiences }}</div>
                <div class="sd-stat-note">Previous employment records</div>
            </div>
            <div class="sd-stat-icon"><i class="fas fa-briefcase"></i></div>
        </div>

        <div class="sd-stat-card">
            <div>
                <div class="sd-stat-label">Active Contracts</div>
                <div class="sd-stat-val">{{ $activeContracts }}</div>
                <div class="sd-stat-note">Staff with contract details</div>
            </div>
            <div class="sd-stat-icon"><i class="fas fa-file-contract"></i></div>
        </div>

    </div>

    <div class="sd-bottom">

        <div class="sd-card">
            <div class="sd-card-header">
                <div>
                    <h3><span class="sd-dot"></span> HR Records Overview</h3>
                    <p>Summary of staff assignments and personnel records</p>
                </div>
            </div>

            <table class="sd-table">
                <thead>
                    <tr>
                        <th>Record Type</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Assigned to Wards</td>
                        <td><span class="sd-badge">{{ $assignedToWards }}</span></td>
                    </tr>

                    <tr>
                        <td>Assigned to Clinics</td>
                        <td><span class="sd-badge">{{ $assignedToClinics }}</span></td>
                    </tr>

                    <tr>
                        <td>Qualifications Recorded</td>
                        <td><span class="sd-badge">{{ $qualificationsRecorded }}</span></td>
                    </tr>

                    <tr>
                        <td>Work Experiences</td>
                        <td><span class="sd-badge">{{ $workExperiences }}</span></td>
                    </tr>

                    <tr>
                        <td>Active Contracts</td>
                        <td><span class="sd-badge">{{ $activeContracts }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="sd-card">
            <div class="sd-card-header">
                <div>
                    <h3><span class="sd-dot"></span> Recent Staff Records</h3>
                    <p>Latest staff records available in the HR database</p>
                </div>
            </div>

            <div class="sd-activity">

                @forelse ($recentStaff as $member)
                    <div class="sd-act-item">
                        <div class="sd-act-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>

                        <div>
                            <div class="sd-act-title">
                                {{ $member->first_name }} {{ $member->last_name }}
                            </div>

                            <div class="sd-act-sub">
                                {{ $member->position ?? 'No position recorded' }}
                            </div>

                            <div class="sd-act-time">
                                Staff record available
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sd-empty">
                        <i class="fas fa-folder-open"></i>
                        <p>No recent staff records found.</p>
                    </div>
                @endforelse

            </div>
        </div>

    </div>

</div>

@endsection
