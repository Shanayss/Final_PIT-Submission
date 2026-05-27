@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/qualifications.css') }}">

<div class="staff-module-header">
    <h2>Staff Allocations</h2>
    <p>Manage department assignments and staff allocation records</p>
</div>

<div class="table-card">

    <div class="table-tools qualification-tools">

        <div class="search-box qualification-search">
            <i class="fas fa-search"></i>
            <input
                type="text"
                id="searchInput"
                placeholder="Search by staff, ward, role, or shift..."
                onkeyup="filterStaffAllocations()"
            >
        </div>

        <select id="shiftFilter" class="filter-select" onchange="filterStaffAllocations()">
            <option value="">All Shifts</option>

            @foreach($staffAllocations->pluck('shift')->unique()->sort() as $shift)
                <option value="{{ strtolower($shift) }}">
                    {{ $shift }}
                </option>
            @endforeach
        </select>

        <button type="button" class="sm-btn-add" onclick="openAddAllocationModal()">
            <i class="fas fa-plus"></i> Add Allocation
        </button>

    </div>

    <div class="table-responsive">

        <table class="modern-table" id="allocationTable">

            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Position</th>
                    <th>Ward</th>
                    <th>Role For Week</th>
                    <th>Shift</th>
                    <th>Week Start</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($staffAllocations as $allocation)

                    <tr class="allocation-row">

                        <td>
                            <strong>
                                {{ $allocation->first_name }} {{ $allocation->last_name }}
                            </strong>
                        </td>

                        <td>
                            <span class="role-badge">
                                {{ $allocation->position }}
                            </span>
                        </td>

                        <td>
                            {{ $allocation->ward_name ?? 'Ward ' . $allocation->ward_number }}
                        </td>

                        <td>{{ $allocation->role_for_week }}</td>

                        <td>{{ $allocation->shift }}</td>

                        <td>{{ $allocation->week_start_date }}</td>

                        <td>
                            <div class="sm-actions">

                                <button
                                    type="button"
                                    class="btn-edit"
                                    title="Edit"
                                    onclick='openEditAllocationModal(@json($allocation))'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('staffallocations.destroy', $allocation->allocation_id) }}"
                                    onsubmit="return confirm('Delete this staff allocation?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty-state">
                            No staff allocations found
                        </td>
                    </tr>

                @endforelse

                <tr id="allocationEmptyRow" style="display:none;">
                    <td colspan="7" class="empty-state">
                        <div class="empty-box">
                            <i class="fas fa-search"></i>
                            <p>No matching staff allocation found.</p>
                        </div>
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>

<div class="modal-overlay" id="allocationModalOverlay">
    <div class="qualification-modal">

        <div class="modal-header">
            <h3 id="allocationModalTitle">Add Staff Allocation</h3>
            <p id="allocationModalSub">Assign staff to ward schedules</p>
        </div>

        <form method="POST" id="allocationForm" action="{{ route('staffallocations.store') }}">
            @csrf
            <input type="hidden" name="_method" id="allocationMethod" value="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Staff</label>
                    <select name="staff_number" id="staff_number" required>
                        <option value="">Select staff</option>

                        @foreach($staffs as $staff)
                            <option value="{{ $staff->staff_number }}">
                                {{ $staff->staff_number }} - {{ $staff->first_name }} {{ $staff->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Ward</label>
                    <select name="ward_number" id="ward_number" required>
                        <option value="">Select ward</option>

                        @foreach($wards as $ward)
                            <option value="{{ $ward->ward_number }}">
                                {{ $ward->ward_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Role For Week</label>

                    <select name="role_for_week" id="role_for_week" required>
                        <option value="">Select role</option>

                        <option value="Doctor">Doctor</option>
                        <option value="Senior Nurse">Senior Nurse</option>
                        <option value="Charge Nurse">Charge Nurse</option>
                        <option value="Junior Nurse">Junior Nurse</option>
                        <option value="Auxiliary Staff">Auxiliary Staff</option>
                        <option value="Personnel Officer">Personnel Officer</option>
                        <option value="Cashier">Cashier</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Shift</label>
                    <select name="shift" id="shift" required>
                        <option value="">Select shift</option>
                        <option value="Early">Early</option>
                        <option value="Late">Late</option>
                        <option value="Night">Night</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Week Start Date</label>
                    <input
                        type="date"
                        name="week_start_date"
                        id="week_start_date"
                        required
                    >
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAllocationModal()">
                    Cancel
                </button>

                <button type="submit" class="btn-save" id="allocationSubmitBtn">
                    <i class="fas fa-save"></i> Add Allocation
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    window.allocationStoreUrl = "{{ route('staffallocations.store') }}";
    window.allocationUpdateUrlTemplate = "{{ route('staffallocations.update', 'ALLOCATION_ID_PLACEHOLDER') }}";
</script>

<script src="{{ asset('js/staff-allocations.js') }}"></script>

@endsection
