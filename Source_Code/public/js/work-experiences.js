function openAddExperienceModal() {
    document.getElementById('experienceForm').reset();

    document.getElementById('experienceForm').action = window.experienceStoreUrl;
    document.getElementById('experienceMethod').value = 'POST';

    document.getElementById('experienceModalTitle').innerText = 'Add Work Experience';
    document.getElementById('experienceModalSub').innerText = 'Fill in the work experience details';
    document.getElementById('experienceSubmitBtn').innerHTML =
        '<i class="fas fa-save"></i> Add Experience';

    document.getElementById('experienceModalOverlay').classList.add('open');
}

function openEditExperienceModal(experience) {
    document.getElementById('experienceForm').reset();

    let updateUrl = window.experienceUpdateUrlTemplate.replace(
        'EXPERIENCE_ID_PLACEHOLDER',
        experience.experience_id
    );

    document.getElementById('experienceForm').action = updateUrl;
    document.getElementById('experienceMethod').value = 'PUT';

    document.getElementById('staff_number').value = experience.staff_number ?? '';
    document.getElementById('name_of_organization').value = experience.name_of_organization ?? '';
    document.getElementById('position_held').value = experience.position_held ?? '';
    document.getElementById('start_date').value = experience.start_date ?? '';
    document.getElementById('finish_date').value = experience.finish_date ?? '';

    document.getElementById('experienceModalTitle').innerText = 'Edit Work Experience';
    document.getElementById('experienceModalSub').innerText = 'Update work experience details';
    document.getElementById('experienceSubmitBtn').innerHTML =
        '<i class="fas fa-save"></i> Update Experience';

    document.getElementById('experienceModalOverlay').classList.add('open');
}

function closeExperienceModal() {
    document.getElementById('experienceModalOverlay').classList.remove('open');
}

function filterWorkExperiences() {
    let search = document.getElementById('searchInput').value.toLowerCase();
    let selectedPosition = document.getElementById('positionFilter').value.toLowerCase();

    let rows = document.querySelectorAll('#workExperienceTable tbody tr.experience-row');
    let emptyRow = document.getElementById('experienceEmptyRow');

    let visibleCount = 0;

    rows.forEach(function(row) {
        let text = row.innerText.toLowerCase();
        let positionCell = row.children[1].innerText.toLowerCase();

        let matchSearch = text.includes(search);
        let matchPosition = selectedPosition === '' || positionCell.includes(selectedPosition);

        row.style.display = matchSearch && matchPosition ? '' : 'none';

        if (matchSearch && matchPosition) {
            visibleCount++;
        }
    });

    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
}

let experienceModalOverlay = document.getElementById('experienceModalOverlay');

if (experienceModalOverlay) {
    experienceModalOverlay.addEventListener('click', function(e) {
        if (e.target === this) {
            closeExperienceModal();
        }
    });
}
