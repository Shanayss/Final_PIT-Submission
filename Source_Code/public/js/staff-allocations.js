function openAddAllocationModal() {

    document.getElementById('allocationForm').reset();

    document.getElementById('allocationForm').action =
        window.allocationStoreUrl;

    document.getElementById('allocationMethod').value = 'POST';

    document.getElementById('allocationModalTitle').innerText =
        'Add Staff Allocation';

    document.getElementById('allocationModalSub').innerText =
        'Assign staff to ward schedules';

    document.getElementById('allocationSubmitBtn').innerHTML =
        '<i class="fas fa-save"></i> Add Allocation';

    document.getElementById('allocationModalOverlay')
        .classList.add('open');
}

function openEditAllocationModal(allocation) {

    document.getElementById('allocationForm').reset();

    let updateUrl =
        window.allocationUpdateUrlTemplate.replace(
            'ALLOCATION_ID_PLACEHOLDER',
            allocation.allocation_id
        );

    document.getElementById('allocationForm').action = updateUrl;

    document.getElementById('allocationMethod').value = 'PUT';

    document.getElementById('staff_number').value =
        allocation.staff_number ?? '';

    document.getElementById('ward_number').value =
        allocation.ward_number ?? '';

    document.getElementById('role_for_week').value =
        allocation.role_for_week ?? '';

    document.getElementById('shift').value =
        allocation.shift ?? '';

    document.getElementById('week_start_date').value =
        allocation.week_start_date ?? '';

    document.getElementById('allocationModalTitle').innerText =
        'Edit Staff Allocation';

    document.getElementById('allocationModalSub').innerText =
        'Update allocation details';

    document.getElementById('allocationSubmitBtn').innerHTML =
        '<i class="fas fa-save"></i> Update Allocation';

    document.getElementById('allocationModalOverlay')
        .classList.add('open');
}

function closeAllocationModal() {

    document.getElementById('allocationModalOverlay')
        .classList.remove('open');
}

function filterStaffAllocations() {

    let search =
        document.getElementById('searchInput')
        .value.toLowerCase();

    let selectedShift =
        document.getElementById('shiftFilter')
        .value.toLowerCase();

    let rows =
        document.querySelectorAll(
            '#allocationTable tbody tr.allocation-row'
        );

    let emptyRow =
        document.getElementById('allocationEmptyRow');

    let visibleCount = 0;

    rows.forEach(function(row) {

        let text = row.innerText.toLowerCase();

        let shiftCell =
            row.children[4].innerText.toLowerCase();

        let matchSearch =
            text.includes(search);

        let matchShift =
            selectedShift === '' ||
            shiftCell.includes(selectedShift);

        if (matchSearch && matchShift) {

            row.style.display = '';
            visibleCount++;

        } else {

            row.style.display = 'none';

        }

    });

    emptyRow.style.display =
        visibleCount === 0 ? '' : 'none';
}

let allocationModalOverlay =
    document.getElementById('allocationModalOverlay');

if (allocationModalOverlay) {

    allocationModalOverlay.addEventListener('click', function(e) {

        if (e.target === this) {

            closeAllocationModal();

        }

    });

}
