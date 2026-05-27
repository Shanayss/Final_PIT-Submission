@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/PersonnelOff/qualifications.css') }}">

<div class="staff-module-header">
    <h2>Work Experiences</h2>
    <p>Manage staff work experience records</p>
</div>

<div class="table-card">

    <div class="table-tools qualification-tools">

        <div class="search-box qualification-search">
            <i class="fas fa-search"></i>
            <input
                type="text"
                id="searchInput"
                placeholder="Search by staff, position, or organization..."
                onkeyup="filterWorkExperiences()"
            >
        </div>

        <select id="positionFilter" class="filter-select" onchange="filterWorkExperiences()">
            <option value="">All Positions</option>

            @foreach($workExperiences->pluck('position')->unique()->sort() as $position)
                <option value="{{ strtolower($position) }}">
                    {{ $position }}
                </option>
            @endforeach
        </select>

        <button type="button" class="sm-btn-add" onclick="openAddExperienceModal()">
            <i class="fas fa-plus"></i> Add Experience
        </button>

    </div>

    <div class="table-responsive">

        <table class="modern-table" id="workExperienceTable">

            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Position</th>
                    <th>Organization</th>
                    <th>Position Held</th>
                    <th>Start Date</th>
                    <th>Finish Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($workExperiences as $experience)

                    <tr class="experience-row">

                        <td>
                            <strong>
                                {{ $experience->first_name }} {{ $experience->last_name }}
                            </strong>
                        </td>

                        <td>
                            <span class="role-badge">
                                {{ $experience->position }}
                            </span>
                        </td>

                        <td>{{ $experience->name_of_organization ?? 'N/A' }}</td>

                        <td>{{ $experience->position_held ?? 'N/A' }}</td>

                        <td>{{ $experience->start_date ?? 'N/A' }}</td>

                        <td>{{ $experience->finish_date ?? 'Present' }}</td>

                        <td>
                            <div class="sm-actions">

                                <button
                                    type="button"
                                    class="btn-edit"
                                    title="Edit"
                                    onclick='openEditExperienceModal(@json($experience))'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route('work-experiences.destroy', $experience->experience_id) }}"
                                    onsubmit="return confirm('Delete this work experience?')">

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
                            No work experiences found
                        </td>
                    </tr>

                @endforelse

                <tr id="experienceEmptyRow" style="display:none;">
                    <td colspan="7" class="empty-state">
                        <div class="empty-box">
                            <i class="fas fa-search"></i>
                            <p>No matching work experience found.</p>
                        </div>
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>

<div class="modal-overlay" id="experienceModalOverlay">
    <div class="qualification-modal">

        <div class="modal-header">
            <h3 id="experienceModalTitle">Add Work Experience</h3>
            <p id="experienceModalSub">Fill in the work experience details</p>
        </div>

        <form method="POST" id="experienceForm" action="{{ route('work-experiences.store') }}">
            @csrf
            <input type="hidden" name="_method" id="experienceMethod" value="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label>Staff</label>
                    <select name="staff_number" id="staff_number" required>
                        <option value="">Select staff</option>

                        @foreach($staffs as $staff)
                            <option value="{{ $staff->staff_number }}">
                                {{ $staff->first_name }} {{ $staff->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Organization Name</label>
                    <input
                        type="text"
                        name="name_of_organization"
                        id="name_of_organization"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Position Held</label>
                    <input
                        type="text"
                        name="position_held"
                        id="position_held"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Finish Date</label>
                    <input
                        type="date"
                        name="finish_date"
                        id="finish_date"
                    >
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeExperienceModal()">
                    Cancel
                </button>

                <button type="submit" class="btn-save" id="experienceSubmitBtn">
                    <i class="fas fa-save"></i> Add Experience
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    window.experienceStoreUrl = "{{ route('work-experiences.store') }}";
    window.experienceUpdateUrlTemplate = "{{ route('work-experiences.update', 'EXPERIENCE_ID_PLACEHOLDER') }}";
</script>

<script src="{{ asset('js/work-experiences.js') }}"></script>

@endsection
