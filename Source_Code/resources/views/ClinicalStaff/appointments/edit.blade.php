@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/consultant/create-appointment.css') }}">
@endpush

@section('content')
<div class="appointment-container">
    <div class="form-card">
        <form action="{{ route('clinical.appointments.update', $appointment->appointment_id) }}" method="POST">
            @csrf
            @method('PUT')

            <h4>
                <i class="fa-solid fa-calendar-plus"></i>
                Edit Appointment
            </h4>

            <div class="section-title">
                <i class="fa-solid fa-user"></i>
                Patient Information
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Patient Name *</label>
                    <select name="patient_number" id="patient_number" class="custom-select" required>
                        <option value="">Select Patient</option>
                        @foreach($patients as $patient)
                            <option
                                value="{{ $patient->patient_number }}"
                                data-clinic="{{ $patient->clinic_number }}"
                                data-phone="{{ $patient->telephone }}"
                                {{ old('patient_number', $appointment->patient_number) == $patient->patient_number ? 'selected' : '' }}
                            >
                                {{ $patient->patient_number }} - {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $appointment->patient_phone) }}" readonly>
                </div>
            </div>

            <div class="section-title">
                <i class="fa-solid fa-stethoscope"></i>
                Appointment Details
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Select Consultant *</label>
                    <select name="staff_number" class="custom-select" required>
                        <option value="">Choose a consultant</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->staff_number }}" {{ old('staff_number', $appointment->staff_number) == $doctor->staff_number ? 'selected' : '' }}>
                                {{ $doctor->first_name }} {{ $doctor->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('staff_number')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Clinic Number *</label>
                    <input type="text" id="clinic_number" name="clinic_number" value="{{ old('clinic_number', $appointment->clinic_number) }}" readonly>
                </div>

                <div class="form-group">
                    <label>Examination Room *</label>
                    <select name="examination_room" class="custom-select" required>
                        <option value="">Select Room</option>
                        @for($i = 1; $i <= 20; $i++)
                            @php($room = 'E' . str_pad($i, 3, '0', STR_PAD_LEFT))
                            <option value="{{ $room }}" {{ old('examination_room', $appointment->examination_room) == $room ? 'selected' : '' }}>
                                {{ $room }}
                            </option>
                        @endfor
                    </select>
                    @error('examination_room')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Appointment Date *</label>
                    <input type="date" name="appointment_date" value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
                    @error('appointment_date')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Appointment Time *</label>
                    <input type="time" name="appointment_time" value="{{ old('appointment_time', substr($appointment->appointment_time, 0, 5)) }}" required>
                    @error('appointment_time')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="custom-select">
                        <option value="Scheduled" {{ old('status', $appointment->status) == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Completed" {{ old('status', $appointment->status) == 'Completed' ? 'selected' : '' }}>Completed</option>
                        <option value="Cancelled" {{ old('status', $appointment->status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <small class="error-text">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('clinical.appointments') }}" class="btn-clear">Cancel</a>
                <button type="submit" class="btn-submit">Save Appointment</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('patient_number').addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('clinic_number').value = selectedOption.getAttribute('data-clinic') || '';
        document.getElementById('phone_number').value = selectedOption.getAttribute('data-phone') || '';
    });
</script>
@endsection
