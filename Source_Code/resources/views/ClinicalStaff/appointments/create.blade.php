@extends('layouts.app')

@push('styles')
    {{-- Font Awesome icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Create appointment CSS --}}
    <link rel="stylesheet" href="{{ asset('css/consultant/create-appointment.css') }}">
@endpush

@section('content')

<div class="appointment-container">

    {{-- Form card container --}}
    <div class="form-card">

        {{-- Add Appointment Form --}}
        <form action="{{ route('clinical.appointments.store') }}" method="POST">
            @csrf

            <h4>
                <i class="fa-solid fa-calendar-plus"></i>
                Schedule New Appointment
            </h4>

            {{-- Patient information section --}}
            <div class="section-title">
                <i class="fa-solid fa-user"></i>
                Patient Information
            </div>

            <div class="form-grid">

                {{-- Patient dropdown --}}
                <div class="form-group">
                    <label>Patient Name *</label>

                    <select name="patient_number" id="patient_number" class="custom-select" required>
                        <option value="">Select Patient</option>

                        @foreach($patients as $patient)
                            <option
                                value="{{ $patient->patient_number }}"
                                data-clinic="{{ $patient->clinic_number }}"
                                data-phone="{{ $patient->telephone }}"
                                {{ old('patient_number') == $patient->patient_number ? 'selected' : '' }}
                            >
                                {{ $patient->patient_number }} - {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('patient_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Phone number --}}
                <div class="form-group">
                    <label>Phone Number *</label>

                    <input 
                        type="text"
                        id="phone_number"
                        name="phone_number"
                        placeholder="Phone number will appear after selecting patient"
                        readonly
                    >

                    @error('phone_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Appointment details section --}}
            <div class="section-title">
                <i class="fa-solid fa-stethoscope"></i>
                Appointment Details
            </div>

            <div class="form-grid">

                {{-- Consultant dropdown --}}
                <div class="form-group">
                    <label>Select Consultant *</label>

                    <select name="staff_number" class="custom-select" required>
                        <option value="">Choose a consultant</option>

                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->staff_number }}" {{ old('staff_number') == $doctor->staff_number ? 'selected' : '' }}>
                                {{ $doctor->first_name }} {{ $doctor->last_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('staff_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Clinic number --}}
                <div class="form-group">
                    <label>Clinic Number *</label>

                    <input 
                        type="text"
                        id="clinic_number"
                        name="clinic_number"
                        placeholder="Clinic number will appear after selecting patient"
                        readonly
                    >

                    @error('clinic_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Examination room --}}
                <div class="form-group">
                    <label>Examination Room *</label>

                    <select name="examination_room" class="custom-select" required>
                        <option value="">Select Room</option>

                        @for($i = 1; $i <= 20; $i++)
                            @php
                                $room = 'E' . str_pad($i, 3, '0', STR_PAD_LEFT);
                            @endphp

                            <option value="{{ $room }}" {{ old('examination_room') == $room ? 'selected' : '' }}>
                                {{ $room }}
                            </option>
                        @endfor
                    </select>

                    @error('examination_room')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Appointment date --}}
                <div class="form-group">
                    <label>Appointment Date *</label>

                    <input 
                        type="date"
                        name="appointment_date"
                        value="{{ old('appointment_date') }}"
                        required
                    >

                    @error('appointment_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Appointment time --}}
                <div class="form-group">
                    <label>Appointment Time *</label>

                    <input 
                        type="time"
                        name="appointment_time"
                        value="{{ old('appointment_time') }}"
                        required
                    >

                    @error('appointment_time')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label>Status</label>

                    <select name="status" class="custom-select">
                        <option value="Scheduled" {{ old('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Completed" {{ old('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ old('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>

                    @error('status')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Additional notes --}}
            <div class="form-group full-width">
                <label>Additional Notes</label>

                <textarea 
                    name="additional_notes"
                    placeholder="Enter special instructions or patient concerns..."
                >{{ old('additional_notes') }}</textarea>

                @error('additional_notes')
                    <small class="error-text">{{ $message }}</small>
                @enderror
            </div>

            {{-- Form buttons --}}
            <div class="form-actions">
                <button type="reset" class="btn-clear">
                    Clear Form
                </button>

                <button type="submit" class="btn-submit">
                    Schedule Appointment
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    document.getElementById('patient_number').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];

        const clinicNumber = selectedOption.getAttribute('data-clinic') || '';
        const phoneNumber = selectedOption.getAttribute('data-phone') || '';

        document.getElementById('clinic_number').value = clinicNumber;
        document.getElementById('phone_number').value = phoneNumber;
    });
</script>

@endsection
