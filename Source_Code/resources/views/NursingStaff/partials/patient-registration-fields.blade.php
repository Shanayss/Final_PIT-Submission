@php
    $field = fn (string $name, $default = '') => old($name, $patient?->{$name} ?? $default);
@endphp

<div class="nurse-form-grid">
    <label>First Name
        <input name="first_name" value="{{ $field('first_name') }}" required placeholder="Enter first name">
    </label>
    <label>Last Name
        <input name="last_name" value="{{ $field('last_name') }}" required placeholder="Enter last name">
    </label>
    <label>Date of Birth
        <input name="date_of_birth" value="{{ $field('date_of_birth') }}" type="date">
    </label>
    <label>Gender
        <select name="sex">
            <option value="">Select gender</option>
            <option value="Male" @selected($field('sex') === 'Male')>Male</option>
            <option value="Female" @selected($field('sex') === 'Female')>Female</option>
        </select>
    </label>
    <label>Contact Number
        <input name="telephone" value="{{ $field('telephone') }}" placeholder="Phone number">
    </label>
    <label>Marital Status
        <select name="marital_status">
            <option value="">Select status</option>
            @foreach(['Single', 'Married', 'Divorced', 'Widowed'] as $status)
                <option value="{{ $status }}" @selected($field('marital_status') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </label>
</div>

<label>Address
    <input name="address" value="{{ $field('address') }}" placeholder="Home address">
</label>

<div class="nurse-form-grid">
    <label>Local Doctor
        <select name="clinic_number">
            <option value="">No referring doctor</option>
            @foreach($localDoctors as $doctor)
                <option value="{{ $doctor->clinic_number }}" @selected((string) $field('clinic_number') === (string) $doctor->clinic_number)>
                    {{ $doctor->full_name }}
                </option>
            @endforeach
        </select>
    </label>
    <label>Next of Kin Name
        <input name="kin_name" value="{{ $field('kin_name') }}" placeholder="Full name">
    </label>
    <label>Relationship
        <input name="kin_relationship" value="{{ $field('kin_relationship') }}" placeholder="Relationship">
    </label>
    <label>Next of Kin Phone
        <input name="kin_telephone" value="{{ $field('kin_telephone') }}" placeholder="Phone number">
    </label>
</div>

<label>Next of Kin Address
    <input name="kin_address" value="{{ $field('kin_address') }}" placeholder="Address">
</label>
