@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consultant/treatment-show.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endpush

@section('content')

<div class="treatment-history-page">

    <div class="history-header">
        <div>
            <h1>Treatment History Details</h1>
            <p>Complete clinical record for this patient appointment.</p>
        </div>

        <a href="{{ route('clinical.treatments') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Treatments
        </a>
    </div>

    <div class="history-card">

        <div class="card-title">
            <i class="fa-solid fa-user-injured"></i>
            Patient Appointment Information
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <label>Patient Number</label>
                <p>{{ $treatment->patient_number }}</p>
            </div>

            <div class="detail-item">
                <label>Patient Name</label>
                <p>{{ $treatment->patient_name }}</p>
            </div>

            <div class="detail-item">
                <label>Attending Doctor</label>
                <p>{{ $treatment->doctor_name }}</p>
            </div>

            <div class="detail-item">
                <label>Assigned Nurse</label>
                <p>{{ $treatment->nurse_name }}</p>
            </div>

            <div class="detail-item">
                <label>Appointment Date</label>
                <p>{{ $treatment->appointment_date }}</p>
            </div>

            <div class="detail-item">
                <label>Appointment Time</label>
                <p>{{ $treatment->appointment_time }}</p>
            </div>

            <div class="detail-item">
                <label>Room</label>
                <p>{{ $treatment->examination_room }}</p>
            </div>

            <div class="detail-item">
                <label>Status</label>

                @php
                    $statusClass = strtolower(str_replace(' ', '-', $treatment->status));
                @endphp

                <span class="status-badge {{ $statusClass }}">
                    {{ $treatment->status }}
                </span>
            </div>
        </div>

    </div>

    <div class="history-card">

        <div class="card-title">
            <i class="fa-solid fa-notes-medical"></i>
            Clinical Treatment Record
        </div>

        <div class="clinical-section">
            <div class="clinical-box">
                <label>Diagnosis</label>
                <p>{{ $treatment->diagnosis }}</p>
            </div>

            <div class="clinical-box">
                <label>Procedure</label>
                <p>{{ $treatment->procedure }}</p>
            </div>

            <div class="clinical-box">
                <label>Medication / Prescription</label>
                <p>{{ $treatment->medication ?? 'N/A' }}</p>
            </div>

            <div class="clinical-box">
                <label>Additional Notes</label>
                <p>{{ $treatment->additional_notes ?? 'No additional notes.' }}</p>
            </div>
        </div>

    </div>

    <div class="history-actions">
        <a 
            href="{{ route('clinical.treatments.create', $treatment->appointment_id) }}" 
            class="btn-edit-history"
        >
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Record
        </a>
    </div>

</div>

<style>
    .treatment-history-page {
    width: 100%;
    font-family: 'Poppins', sans-serif;
    color: #0f2f22;
}

.history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 26px;
}

.history-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: #0f2f22;
}

.history-header p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 14px;
}

.btn-back {
    background: #ffffff;
    color: #245c3d;
    border: 1px solid #d9e2ec;
    border-radius: 9px;
    padding: 12px 18px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
}

.history-card {
    background: #ffffff;
    border: 1px solid #dbe3ee;
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 14px 35px rgba(0, 0, 0, 0.05);
}

.card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #164333;
    font-size: 20px;
    font-weight: 800;
    padding-bottom: 14px;
    border-bottom: 1px solid #edf1f5;
    margin-bottom: 20px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
}

.detail-item,
.clinical-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
}

.detail-item label,
.clinical-box label {
    display: block;
    font-size: 12px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    margin-bottom: 8px;
}

.detail-item p,
.clinical-box p {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #17324d;
    line-height: 1.5;
}

.clinical-section {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.status-badge {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 800;
}

.status-badge.pending {
    background: #fff8e1;
    color: #b7791f;
}

.status-badge.in-progress {
    background: #e3f2fd;
    color: #1565c0;
}

.status-badge.completed {
    background: #e8f5e9;
    color: #1b7a36;
}

.history-actions {
    display: flex;
    justify-content: flex-end;
}

.btn-edit-history {
    background: #245c3d;
    color: #ffffff;
    border-radius: 9px;
    padding: 13px 20px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 800;
}

@media (max-width: 1100px) {
    .details-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .clinical-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 700px) {
    .history-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection