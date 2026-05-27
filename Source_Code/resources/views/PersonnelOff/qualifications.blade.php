@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/qualifications.css') }}">

<div class="staff-module-header">
    <h2>Staff Qualifications</h2>
    <p>Manage staff qualifications and certifications</p>
</div>

@if (session('success'))
    <div class="qualification-toast success" id="qualificationToast">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="qualification-toast error" id="qualificationToast">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<div class="table-card">

    <div class="table-tools qualification-tools">
        <div class="search-box qualification-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by staff, qualification, or institution..." onkeyup="filterQualifications()">
        </div>

        <select id="qualificationFilter" class="filter-select" onchange="filterQualifications()">
            <option value="">All Qualifications</option>
            @foreach($qualifications->pluck('qualification_type')->unique()->sort() as $type)
                <option value="{{ strtolower($type) }}">{{ $type }}</option>
            @endforeach
        </select>

        <button type="button" class="sm-btn-add" onclick="openAddQualificationModal()">
            <i class="fas fa-plus"></i>
            <span>Add Qualification</span>
        </button>
    </div>

    <div class="table-responsive">
        <table class="modern-table" id="qualificationTable">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Position</th>
                    <th>Qualification</th>
                    <th>Institution</th>
                    <th>Date Qualified</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($qualifications as $qualification)
                    <tr class="qualification-row">
                        <td>
                            <strong>{{ $qualification->first_name }} {{ $qualification->last_name }}</strong>
                        </td>

                        <td>
                            <span class="role-badge">{{ $qualification->position }}</span>
                        </td>

                        <td>{{ $qualification->qualification_type }}</td>
                        <td>{{ $qualification->institution_name }}</td>
                        <td>{{ $qualification->qualification_date ?? 'N/A' }}</td>

                        <td>
                            <div class="sm-actions">
                                <button
                                    type="button"
                                    class="btn-edit"
                                    onclick='openEditQualificationModal(@json($qualification))'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('qualifications.destroy', $qualification->qualification_id) }}"
                                    onsubmit="return confirm('Delete this qualification?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">No qualifications found</td>
                    </tr>
                @endforelse

                <tr id="qualificationEmptyRow" style="display:none;">
                    <td colspan="6" class="empty-state">
                        <div class="empty-box">
                            <i class="fas fa-search"></i>
                            <p>No matching qualification found.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="qualificationModalOverlay">
    <div class="qualification-modal">

        <div class="modal-header">
            <h3 id="qualificationModalTitle">Add Qualification</h3>
            <p id="qualificationModalSub">Fill in qualification details</p>
        </div>

        <form method="POST" id="qualificationForm" action="{{ route('qualifications.store') }}">
            @csrf
            <input type="hidden" name="_method" id="qualificationFormMethod" value="POST">

            <div class="form-grid">
                <div class="form-group">
                    <label>Select Staff</label>

                    <select name="staff_number" id="staff_number" required>
                        <option value="">Select Staff</option>

                        @foreach($staffs as $staff)
                            <option value="{{ $staff->staff_number }}">
                                {{ $staff->staff_number }} - {{ $staff->first_name }} {{ $staff->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Qualification Type</label>

                    <select name="qualification_type" id="qualification_type" required>
                        <option value="">Select Qualification</option>

                        <option value="Doctor of Medicine">Doctor of Medicine</option>
                        <option value="BS Nursing">BS Nursing</option>
                        <option value="Caregiving NC II">Caregiving NC II</option>
                        <option value="BS Business Administration">BS Business Administration</option>
                        <option value="BS Accountancy">BS Accountancy</option>
                        <option value="BS Human Resource Management">BS Human Resource Management</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Qualification Date</label>
                    <input type="date" name="qualification_date" id="qualification_date" required>
                </div>

                <div class="form-group">
                    <label>Institution Name</label>

                    <select name="institution_name" id="institution_name" required>
                        <option value="">Select Institution</option>

                        <option value="University of Leeds">University of Leeds</option>
                        <option value="University of Manchester">University of Manchester</option>
                        <option value="University of Glasgow">University of Glasgow</option>
                        <option value="University of Birmingham">University of Birmingham</option>
                        <option value="University of Edinburgh">University of Edinburgh</option>
                        <option value="King's College London">King's College London</option>
                        <option value="Leeds City College">Leeds City College</option>
                        <option value="The Manchester College">The Manchester College</option>
                        <option value="London South Bank University">London South Bank University</option>
                        <option value="Edinburgh Napier University">Edinburgh Napier University</option>
                    </select>
                </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeQualificationModal()">Cancel</button>
                <button type="submit" class="btn-save" id="qualificationSubmitBtn">
                    <i class="fas fa-save"></i>
                    Add Qualification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function filterQualifications() {
    let search = document.getElementById('searchInput').value.toLowerCase();
    let selectedQualification = document.getElementById('qualificationFilter').value.toLowerCase();
    let rows = document.querySelectorAll('#qualificationTable tbody tr.qualification-row');
    let emptyRow = document.getElementById('qualificationEmptyRow');
    let visibleCount = 0;

    rows.forEach(function(row) {
        let text = row.innerText.toLowerCase();
        let qualificationCell = row.children[2].innerText.toLowerCase();

        let matchSearch = text.includes(search);
        let matchQualification = selectedQualification === '' || qualificationCell.includes(selectedQualification);

        row.style.display = matchSearch && matchQualification ? '' : 'none';

        if (matchSearch && matchQualification) {
            visibleCount++;
        }
    });

    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function openAddQualificationModal() {
    document.getElementById('qualificationForm').reset();
    document.getElementById('qualificationForm').action = "{{ route('qualifications.store') }}";
    document.getElementById('qualificationFormMethod').value = "POST";

    document.getElementById('qualificationModalTitle').innerText = "Add Qualification";
    document.getElementById('qualificationModalSub').innerText = "Fill in qualification details";
    document.getElementById('qualificationSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Add Qualification';

    document.getElementById('qualificationModalOverlay').classList.add('open');
}

function openEditQualificationModal(qualification) {
    document.getElementById('qualificationForm').reset();

    let updateUrl = "{{ route('qualifications.update', 'QUALIFICATION_ID') }}";
    updateUrl = updateUrl.replace('QUALIFICATION_ID', qualification.qualification_id);

    document.getElementById('qualificationForm').action = updateUrl;
    document.getElementById('qualificationFormMethod').value = "PUT";

    document.getElementById('staff_number').value = qualification.staff_number ?? '';
    document.getElementById('qualification_type').value = qualification.qualification_type ?? '';
    document.getElementById('qualification_date').value = qualification.qualification_date ?? '';
    document.getElementById('institution_name').value = qualification.institution_name ?? '';

    document.getElementById('qualificationModalTitle').innerText = "Edit Qualification";
    document.getElementById('qualificationModalSub').innerText = "Update qualification details";
    document.getElementById('qualificationSubmitBtn').innerHTML = '<i class="fas fa-save"></i> Update Qualification';

    document.getElementById('qualificationModalOverlay').classList.add('open');
}

function closeQualificationModal() {
    document.getElementById('qualificationModalOverlay').classList.remove('open');
}

let qualificationModal = document.getElementById('qualificationModalOverlay');

if (qualificationModal) {
    qualificationModal.addEventListener('click', function(event) {
        if (event.target === this) {
            closeQualificationModal();
        }
    });
}

let initialToast = document.getElementById('qualificationToast');

if (initialToast) {
    setTimeout(function () {
        initialToast.style.opacity = '0';
        initialToast.style.transform = 'translateX(-50%) translateY(-10px)';

        setTimeout(function () {
            initialToast.remove();
        }, 300);
    }, 2500);
}
</script>

@endsection
