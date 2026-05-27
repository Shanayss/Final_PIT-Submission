@php
    $position = session('staff_position');
    $wardOpen = request()->routeIs('nurse.assign-beds', 'nurse.admit-patients', 'nurse.discharge-patients', 'nurse.wards', 'nurse.ward-occupancy');
    $medOpen = request()->routeIs('nurse.medication.*');
    $careOpen = request()->routeIs('nurse.patient-care*', 'nurse.patients');
    $suppliesOpen = request()->routeIs('nurse.supplies*');
    $schedulingOpen = request()->routeIs('nurse.scheduling');
    $reportsOpen = request()->routeIs('nurse.reports');
@endphp

<ul class="menu nurse-menu">
    <li class="{{ request()->routeIs('nurse.dashboard') ? 'active' : '' }}">
        <a href="{{ route('nurse.dashboard') }}"><i class="fa-solid fa-table-cells-large"></i>Dashboard</a>
    </li>

    {{-- JUNIOR NURSE ONLY: Patient Registration --}}
    @if(trim((string)$position) === 'Junior Nurse')

        <li class="{{ request()->routeIs('nurse.register-patient') ? 'active' : '' }}">
            <a href="{{ route('nurse.register-patient') }}"><i class="fa-solid fa-user-plus"></i>Register Patient</a>
        </li>
    @endif

    {{-- WARD MANAGEMENT: All nursing positions --}}
    @if(in_array($position, ['Charge Nurse', 'Senior Nurse', 'Junior Nurse', 'Auxiliary Staff', 'Nursing Staff']))
<li class="menu-dropdown {{ $wardOpen ? 'active open' : '' }}">
            <a href="javascript:void(0)"><i class="fa-solid fa-bed"></i>Ward Management<i class="fa-solid fa-chevron-right menu-chevron"></i></a>
            <ul class="submenu">                {{-- Charge Nurse & Senior Nurse can assign beds --}}
                @if(in_array($position, ['Charge Nurse', 'Senior Nurse']))
                    <li class="{{ request()->routeIs('nurse.assign-beds') ? 'active' : '' }}"><a href="{{ route('nurse.assign-beds') }}">Assign Beds</a></li>
                @endif

                {{-- Charge Nurse & Senior Nurse can admit patients --}}
                @if(in_array($position, ['Charge Nurse', 'Senior Nurse']))
                    <li class="{{ request()->routeIs('nurse.admit-patients') ? 'active' : '' }}"><a href="{{ route('nurse.admit-patients') }}">Admit Patients</a></li>
                @endif

                {{-- Charge Nurse & Senior Nurse can discharge patients --}}
                @if(in_array($position, ['Charge Nurse', 'Senior Nurse']))
                    <li class="{{ request()->routeIs('nurse.discharge-patients') ? 'active' : '' }}"><a href="{{ route('nurse.discharge-patients') }}">Discharge Patients</a></li>
                @endif

                {{-- Ward & Bed --}}
                <li class="{{ request()->routeIs('nurse.wards') ? 'active' : '' }}"><a href="{{ route('nurse.wards') }}">Ward & Bed</a></li>

                {{-- All positions can view ward occupancy --}}
                <li class="{{ request()->routeIs('nurse.ward-occupancy') ? 'active' : '' }}"><a href="{{ route('nurse.ward-occupancy') }}">Ward Occupancy</a></li>
            </ul>
        </li>
    @endif

    {{-- MEDICATION: Charge Nurse, Senior Nurse, Junior Nurse (all nursing roles show this) --}}
    @if(in_array($position, ['Charge Nurse', 'Senior Nurse', 'Junior Nurse', 'Nursing Staff']))
        <li class="menu-dropdown {{ $medOpen ? 'active' : '' }}">
            <a href="javascript:void(0)"><i class="fa-solid fa-capsules"></i>Medication<i class="fa-solid fa-chevron-right menu-chevron"></i></a>
            <ul class="submenu">                <li class="{{ request()->routeIs('nurse.medication.record') ? 'active' : '' }}"><a href="{{ route('nurse.medication.record') }}">Record Medication</a></li>
                <li class="{{ request()->routeIs('nurse.medication.prescriptions') ? 'active' : '' }}"><a href="{{ route('nurse.medication.prescriptions') }}">Medication Orders</a></li>
                <li class="{{ request()->routeIs('nurse.medication.schedules') ? 'active' : '' }}"><a href="{{ route('nurse.medication.schedules') }}">Monitor Schedules</a></li>
            </ul>
        </li>
    @endif

    {{-- PATIENT CARE: All nursing roles --}}
    @if(in_array($position, ['Charge Nurse', 'Senior Nurse', 'Junior Nurse', 'Nursing Staff']))
        <li class="menu-dropdown {{ $careOpen ? 'active' : '' }}">
            <a href="javascript:void(0)"><i class="fa-regular fa-heart"></i>Patient Care<i class="fa-solid fa-chevron-right menu-chevron"></i></a>
            <ul class="submenu">                <li class="{{ request()->routeIs('nurse.patient-care.update-condition') ? 'active' : '' }}"><a href="{{ route('nurse.patient-care.update-condition') }}">Update Condition</a></li>
                <li class="{{ request()->routeIs('nurse.patient-care.assigned', 'nurse.patient-care.assigned.show') ? 'active' : '' }}"><a href="{{ route('nurse.patient-care.assigned') }}">View Assigned Patients</a></li>
                <li class="{{ request()->routeIs('nurse.patient-care.care-notes') ? 'active' : '' }}"><a href="{{ route('nurse.patient-care.care-notes') }}">Record Care Notes</a></li>
            </ul>
        </li>
    @endif

    {{-- STAFF SHIFT MANAGEMENT: Charge Nurse only --}}
    @if(in_array($position, ['Charge Nurse']))
        <li class="{{ $schedulingOpen ? 'active' : '' }}">
            <a href="{{ route('nurse.scheduling') }}"><i class="fa-regular fa-calendar"></i>Staff Shift Management</a>
        </li>
    @endif

    {{-- SUPPLIES: Charge Nurse only --}}
    @if(in_array($position, ['Charge Nurse']))
        <li class="menu-dropdown {{ $suppliesOpen ? 'active' : '' }}">
            <a href="javascript:void(0)"><i class="fa-solid fa-cube"></i>Supplies<i class="fa-solid fa-chevron-right menu-chevron"></i></a>
            <ul class="submenu">                <li class="{{ request()->routeIs('nurse.supplies.create') ? 'active' : '' }}"><a href="{{ route('nurse.supplies.create') }}">Create Requisition</a></li>
                <li class="{{ request()->routeIs('nurse.supplies.request') ? 'active' : '' }}"><a href="{{ route('nurse.supplies.request') }}">Request Supplies</a></li>
                <li class="{{ request()->routeIs('nurse.supplies.confirm') ? 'active' : '' }}"><a href="{{ route('nurse.supplies.confirm') }}">Confirm Deliveries</a></li>
            </ul>
        </li>
    @endif

    {{-- REPORTS: Charge Nurse, Senior Nurse --}}
    @if(in_array($position, ['Charge Nurse', 'Senior Nurse']))
        <li class="{{ $reportsOpen ? 'active' : '' }}">
            <a href="{{ route('nurse.reports') }}"><i class="fa-regular fa-file-lines"></i>Reports</a>
        </li>
    @endif
</ul>

<script>
function openPatientModal(event) {
    event.preventDefault();
    if (typeof window.openPatientModal === 'function') {
        window.openPatientModal();
    }
}

// Nurse sidebar dropdown toggle (single source of truth)
(function initNurseDropdowns() {
    const triggers = document.querySelectorAll('.nurse-menu .menu-dropdown > a');
    triggers.forEach(trigger => {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();

            const parent = this.closest('.menu-dropdown');
            if (!parent) return;

            // close other dropdowns
            document.querySelectorAll('.nurse-menu .menu-dropdown.open').forEach(el => {
                if (el !== parent) el.classList.remove('open');
            });

            // toggle this one
            parent.classList.toggle('open');
        });
    });
})();
</script>
