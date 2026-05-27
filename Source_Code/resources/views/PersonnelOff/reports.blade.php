@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/qualifications.css') }}">

<div class="staff-module-header">
    <h2>Reports</h2>
    <p>Generate staff allocation and personnel summary reports</p>
</div>

<div class="table-card">

    <div class="report-grid">

        <!-- Staff per Ward -->
        <div class="report-card">
            <div class="report-icon">
                <i class="fas fa-hospital"></i>
            </div>

            <h3>Staff per Ward Report</h3>

            <p>
                View staff assigned to each ward and monitor department staffing distribution.
            </p>

            <div class="report-actions">
                <a href="{{ route('reports.staffPerWard') }}" class="sm-btn-add">
                    <i class="fas fa-eye"></i>
                    View Report
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-cancel">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Allocation Summary -->
        <div class="report-card">
            <div class="report-icon">
                <i class="fas fa-calendar-check"></i>
            </div>

            <h3>Staff Allocation Summary</h3>

            <p>
                Summarize staff schedules, ward assignments, and shift allocations.
            </p>

            <div class="report-actions">
                <a href="{{ route('reports.allocationSummary') }}" class="sm-btn-add">
                    <i class="fas fa-eye"></i>
                    View Report
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-cancel">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Qualification Summary -->
        <div class="report-card">
            <div class="report-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>

            <h3>Staff Qualification Summary</h3>

            <p>
                Review recorded staff qualifications, certifications, and educational records.
            </p>

            <div class="report-actions">
                <a href="{{ route('reports.qualificationSummary') }}" class="sm-btn-add">
                    <i class="fas fa-eye"></i>
                    View Report
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-cancel">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Work Experience -->
        <div class="report-card">
            <div class="report-icon">
                <i class="fas fa-briefcase"></i>
            </div>

            <h3>Work Experience Summary</h3>

            <p>
                Review previous employment history and staff work experience records.
            </p>

            <div class="report-actions">
                <a href="{{ route('reports.workExperienceSummary') }}" class="sm-btn-add">
                    <i class="fas fa-eye"></i>
                    View Report
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-cancel">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

        <!-- Contract Summary -->
        <div class="report-card">
            <div class="report-icon">
                <i class="fas fa-file-contract"></i>
            </div>

            <h3>Contract Summary Report</h3>

            <p>
                View employment contracts, contract types, and overall staffing agreements.
            </p>

            <div class="report-actions">
                <a href="{{ route('reports.contractSummary') }}" class="sm-btn-add">
                    <i class="fas fa-eye"></i>
                    View Report
                </a>

                <button
                    type="button"
                    onclick="window.print()"
                    class="btn-cancel">
                    <i class="fas fa-print"></i>
                    Print
                </button>
            </div>
        </div>

    </div>

</div>

@endsection
