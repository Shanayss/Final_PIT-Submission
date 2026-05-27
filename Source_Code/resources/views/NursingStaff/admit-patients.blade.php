@extends('layouts.app')

@section('content')
<div class="nurse-page">
    <div class="nurse-page-header">
        <h1>Admit New Patient</h1>
    </div>

    @include('NursingStaff.partials.flash')

    <div class="nurse-narrow-page">
        <section class="nurse-panel">
            <h2>Patient Admission Form</h2>

            <form class="nurse-form" method="POST" action="{{ route('nurse.admit-patients.store') }}">
                @csrf

                <label>Existing Registered Patient
                    <select name="existing_patient_number" id="admit-existing-patient">
                        <option value="">-- Admits new patient / register here --</option>

                        @foreach($registeredPatients as $rp)
                            <option
                                value="{{ $rp->patient_number }}"
                                data-first-name="{{ $rp->first_name }}"
                                data-last-name="{{ $rp->last_name }}"
                                data-date-of-birth="{{ $rp->date_of_birth }}"
                                data-sex="{{ $rp->sex }}"
                                data-telephone="{{ $rp->telephone }}"
                                data-address="{{ $rp->address }}"
                                data-marital-status="{{ $rp->marital_status }}"
                                data-kin-name="{{ $rp->kin_name }}"
                                data-kin-telephone="{{ $rp->kin_telephone }}"
                                data-ward-number="{{ $rp->waiting_ward_number }}"
                                data-expected-stay-days="{{ $rp->waiting_expected_stay_days }}"
                                data-date-placed-on-waiting-list="{{ $rp->waiting_date_placed_on_waiting_list }}"
                                @selected(old('existing_patient_number') === $rp->patient_number)
                            >
                                {{ $rp->patient_number }} - {{ $rp->first_name }} {{ $rp->last_name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <div class="nurse-form-grid">
                    <label>First Name
                        <input
                            type="text"
                            name="first_name"
                            id="admit-first-name"
                            value="{{ old('first_name') }}"
                            placeholder="Enter first name"
                        >
                    </label>

                    <label>Last Name
                        <input
                            type="text"
                            name="last_name"
                            id="admit-last-name"
                            value="{{ old('last_name') }}"
                            placeholder="Enter last name"
                        >
                    </label>

                    <label>Date of Birth
                        <input
                            type="date"
                            name="date_of_birth"
                            id="admit-date-of-birth"
                            value="{{ old('date_of_birth') }}"
                        >
                    </label>

                    <label>Gender
                        <select name="sex" id="admit-sex">
                            <option value="">--</option>

                            <option value="Male" @selected(old('sex') === 'Male')>
                                Male
                            </option>

                            <option value="Female" @selected(old('sex') === 'Female')>
                                Female
                            </option>
                        </select>
                    </label>
                </div>

                <label>Contact Number
                    <input
                        type="text"
                        name="telephone"
                        id="admit-telephone"
                        value="{{ old('telephone') }}"
                        placeholder="Phone number"
                    >
                </label>

                <label>Emergency Contact
                    <input
                        type="text"
                        name="kin_name"
                        id="admit-kin-name"
                        value="{{ old('kin_name') }}"
                        placeholder="Emergency contact name and number"
                    >
                </label>

                <input
                    type="hidden"
                    name="kin_relationship"
                    value="{{ old('kin_relationship', 'Emergency Contact') }}"
                >

                <div class="nurse-form-grid">
                    <label>Required Ward
                        <select name="ward_number" id="admit-ward-number" required>
                            <option value="">Select ward...</option>

                            @foreach($wards as $ward)
                                <option
                                    value="{{ $ward->ward_number }}"
                                    @selected((string) old('ward_number') === (string) $ward->ward_number)
                                >
                                    {{ $ward->ward_name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>Expected Stay (days)
                        <input
                            type="number"
                            name="expected_stay_days"
                            id="admit-expected-stay-days"
                            min="1"
                            max="365"
                            value="{{ old('expected_stay_days') }}"
                            placeholder="Example: 5"
                            required
                        >
                    </label>

                    <label>Date Placed on Waiting List
                        <input
                            type="date"
                            name="date_placed_on_waiting_list"
                            id="admit-date-placed-on-waiting-list"
                            value="{{ old('date_placed_on_waiting_list', now()->toDateString()) }}"
                            required
                        >
                    </label>
                </div>

                <button type="submit">
                    Place on Waiting List
                </button>
            </form>
        </section>
    </div>
</div>
@endsection
