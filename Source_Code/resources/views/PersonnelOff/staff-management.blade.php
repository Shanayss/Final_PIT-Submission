@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/PersonnelOff/staff-management.css') }}">
@endpush

@section('content')

<div class="staff-module-header">
    <h2>Staff Management</h2>
    <p>Manage all hospital staff members</p>
</div>

@if (session('success'))
    <div class="alert-success centered-alert" id="successAlert">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
@endif

<div class="sm-toolbar">
    <div class="sm-search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" class="sm-search" placeholder="Search by name, email, or telephone..." id="searchInput" onkeyup="filterTable()">
    </div>

    <select class="sm-filter" id="roleFilter" onchange="filterTable()">
        <option value="">All Roles</option>
        <option value="Medical Director">Medical Director</option>
        <option value="Personnel Officer">Personnel Officer</option>
        <option value="Clinical Staff">Clinical Staff</option>
        <option value="Nursing Staff">Nursing Staff</option>
        <option value="Cashier">Cashier</option>
    </select>

    <button type="button" class="sm-btn-add" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add Staff
    </button>
</div>

<div class="sm-card">
    <table class="sm-table" id="staffTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Position</th>
                <th>Contact</th>
                <th>Contract</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="staffTableBody">
            @forelse ($staff as $member)
                @php
                    $roleName = match((int)$member->role_id) {
                        1 => 'Medical Director',
                        2 => 'Personnel Officer',
                        3 => 'Clinical Staff',
                        4 => 'Nursing Staff',
                        5 => 'Cashier',
                        default => 'Unknown'
                    };

                    $badgeClass = match((int)$member->role_id) {
                        1, 3 => 'badge-doctor',
                        4 => 'badge-nurse',
                        2 => 'badge-admin',
                        5 => 'badge-cashier',
                        default => 'badge-admin'
                    };
                @endphp

                <tr class="staff-row">
                    <td class="sm-name">
                        {{ $member->first_name }} {{ $member->last_name }}<br>
                        <span class="staff-subtitle">{{ $member->staff_number }}</span>
                    </td>

                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $roleName }}</span>
                    </td>

                    <td>{{ $member->position ?? 'N/A' }}</td>

                    <td>
                        {{ $member->telephone ?? 'N/A' }}<br>
                        <span class="staff-subtitle">{{ $member->email }}</span>
                    </td>

                    <td>
                        <span class="status">{{ $member->contract_type ?? 'Active' }}</span>
                    </td>

                    <td>
                        <div class="sm-actions">
                            <button type="button" class="btn-view" title="View Details" onclick='openViewModal(@json($member), "{{ $roleName }}")'>
                                <i class="fas fa-eye"></i>
                            </button>

                            <button type="button" class="btn-edit" title="Edit" onclick='openEditModal(@json($member))'>
                                <i class="fas fa-edit"></i>
                            </button>

                            <form method="POST" action="{{ route('staff.destroy', $member->staff_number) }}" onsubmit="return confirm('Delete this staff member?')">
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
                <tr id="emptyRow">
                    <td colspan="6" class="no-data-cell">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>No staff records found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse

            <tr id="searchEmptyRow" style="display:none;">
                <td colspan="6" class="no-data-cell">
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>No matching staff found.</p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ADD / EDIT STAFF MODAL --}}
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-title" id="modalTitle">Add Staff Member</div>
        <div class="modal-sub" id="modalSub">Fill in the staff details</div>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" id="staffForm" action="{{ route('staff.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div class="modal-section">Personal Information</div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Staff Number</label>
                    <input type="text" name="staff_number" id="staff_number" value="{{ old('staff_number') }}" placeholder="S074" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select name="role_id" id="role_id" required>
                        <option value="">Select role</option>
                        <option value="1" {{ old('role_id') == 1 ? 'selected' : '' }}>Medical Director</option>
                        <option value="2" {{ old('role_id') == 2 ? 'selected' : '' }}>Personnel Officer</option>
                        <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>Clinical Staff</option>
                        <option value="4" {{ old('role_id') == 4 ? 'selected' : '' }}>Nursing Staff</option>
                        <option value="5" {{ old('role_id') == 5 ? 'selected' : '' }}>Cashier</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required>
                </div>

                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required>
                </div>

                <div class="form-group">
                    <label>Sex</label>
                    <select name="sex" id="sex">
                        <option value="">Select sex</option>
                        <option value="Male" {{ old('sex') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('sex') == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}">
                </div>

                <div class="form-group">
                    <label>Telephone</label>
                    <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" id="password">
                </div>

                <div class="form-group">
                    <label>NIN</label>
                    <input type="text" name="nin" id="nin" value="{{ old('nin') }}">
                </div>

                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}">
                </div>

                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" id="position" value="{{ old('position') }}">
                </div>
            </div>

            <div class="modal-section">Employment Information</div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Current Salary</label>
                    <input type="number" name="current_salary" id="current_salary" value="{{ old('current_salary') }}">
                </div>

                <div class="form-group">
                    <label>Salary Scale</label>
                    <input type="text" name="salary_scale" id="salary_scale" value="{{ old('salary_scale') }}">
                </div>

                <div class="form-group">
                    <label>Hours Per Week</label>
                    <input type="number" name="hours_per_week" id="hours_per_week" value="{{ old('hours_per_week') }}">
                </div>

                <div class="form-group">
                    <label>Contract Type</label>
                    <select name="contract_type" id="contract_type">
                        <option value="">Select contract</option>
                        <option value="Permanent" {{ old('contract_type') == 'Permanent' ? 'selected' : '' }}>Permanent</option>
                        <option value="Temporary" {{ old('contract_type') == 'Temporary' ? 'selected' : '' }}>Temporary</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Payment Type</label>
                    <select name="payment_type" id="payment_type">
                        <option value="">Select payment</option>
                        <option value="Monthly" {{ old('payment_type') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="Weekly" {{ old('payment_type') == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save" id="submitBtn">
                    <i class="fas fa-save" style="margin-right:6px"></i> Add Staff
                </button>
            </div>
        </form>
    </div>
</div>

{{-- VIEW STAFF DETAILS MODAL --}}
<div class="modal-overlay" id="viewModalOverlay">
    <div class="modal">
        <div class="modal-title">Staff Details</div>
        <div class="modal-sub">Complete HR staff information</div>

        <div class="view-grid">
            <div class="view-item"><label>Staff Number</label><span id="view_staff_number"></span></div>
            <div class="view-item"><label>Full Name</label><span id="view_full_name"></span></div>
            <div class="view-item"><label>Role</label><span id="view_role"></span></div>
            <div class="view-item"><label>Position</label><span id="view_position"></span></div>
            <div class="view-item"><label>Email</label><span id="view_email"></span></div>
            <div class="view-item"><label>Telephone</label><span id="view_telephone"></span></div>
            <div class="view-item"><label>Date of Birth</label><span id="view_dob"></span></div>
            <div class="view-item"><label>Sex</label><span id="view_sex"></span></div>
            <div class="view-item"><label>NIN</label><span id="view_nin"></span></div>
            <div class="view-item"><label>Address</label><span id="view_address"></span></div>
            <div class="view-item"><label>Current Salary</label><span id="view_salary"></span></div>
            <div class="view-item"><label>Salary Scale</label><span id="view_salary_scale"></span></div>
            <div class="view-item"><label>Hours Per Week</label><span id="view_hours"></span></div>
            <div class="view-item"><label>Contract Type</label><span id="view_contract"></span></div>
            <div class="view-item"><label>Payment Type</label><span id="view_payment"></span></div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.staffStoreUrl = "{{ route('staff.store') }}";
        window.staffUpdateUrlTemplate = "{{ route('staff.update', 'STAFF_NUMBER_PLACEHOLDER') }}";

        function openViewModal(member, roleName)
        {
            document.getElementById('view_staff_number').innerText = member.staff_number ?? '-';
            document.getElementById('view_full_name').innerText = `${member.first_name ?? ''} ${member.last_name ?? ''}`.trim() || '-';
            document.getElementById('view_role').innerText = roleName ?? '-';
            document.getElementById('view_position').innerText = member.position ?? '-';
            document.getElementById('view_email').innerText = member.email ?? '-';
            document.getElementById('view_telephone').innerText = member.telephone ?? '-';
            document.getElementById('view_dob').innerText = member.date_of_birth ?? '-';
            document.getElementById('view_sex').innerText = member.sex ?? '-';
            document.getElementById('view_nin').innerText = member.nin ?? '-';
            document.getElementById('view_address').innerText = member.address ?? '-';
            document.getElementById('view_salary').innerText = member.current_salary ?? '-';
            document.getElementById('view_salary_scale').innerText = member.salary_scale ?? '-';
            document.getElementById('view_hours').innerText = member.hours_per_week ?? '-';
            document.getElementById('view_contract').innerText = member.contract_type ?? '-';
            document.getElementById('view_payment').innerText = member.payment_type ?? '-';

            document.getElementById('viewModalOverlay').classList.add('open');
        }

        function closeViewModal()
        {
            document.getElementById('viewModalOverlay').classList.remove('open');
        }
    </script>

    <script src="{{ asset('js/staff-management.js') }}"></script>

        @if (session('success'))
        <script>
            setTimeout(() => {

                const alertBox =
                    document.getElementById('successAlert');

                if(alertBox){

                    alertBox.style.transition =
                        'all 0.4s ease';

                    alertBox.style.opacity = '0';

                    alertBox.style.transform =
                        'translateY(-10px)';

                    setTimeout(() => {
                        alertBox.remove();
                    }, 400);
                }

            }, 2500);
        </script>
        @endif

    @if ($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                openAddModal();
            });
        </script>
    @endif
@endpush

@endsection
