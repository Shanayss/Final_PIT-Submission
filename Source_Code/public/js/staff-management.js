function openAddModal() {
    document.getElementById('staffForm').reset();

    document.getElementById('staffForm').action =
        window.staffStoreUrl || document.getElementById('staffForm').action;

    document.getElementById('formMethod').value = "POST";

    document.getElementById('staff_number').readOnly = false;
    document.getElementById('staff_number').style.backgroundColor = '#ffffff';

    document.getElementById('modalTitle').innerText = "Add Staff Member";
    document.getElementById('modalSub').innerText = "Fill in the staff details";
    document.getElementById('submitBtn').innerHTML =
        '<i class="fas fa-save" style="margin-right:6px"></i> Add Staff';

    document.getElementById('modalOverlay').classList.add('open');
}

function openEditModal(staff) {
    document.getElementById('staffForm').reset();

    var updateUrl = (window.staffUpdateUrlTemplate || '').replace(
        'STAFF_NUMBER_PLACEHOLDER',
        staff.staff_number
    );

    if (updateUrl) {
        document.getElementById('staffForm').action = updateUrl;
    }

    document.getElementById('formMethod').value = "PUT";

    document.getElementById('staff_number').value = staff.staff_number ?? '';
    document.getElementById('staff_number').readOnly = true;
    document.getElementById('staff_number').style.backgroundColor = '#f3f4f6';

    document.getElementById('role_id').value = staff.role_id ?? '';
    document.getElementById('first_name').value = staff.first_name ?? '';
    document.getElementById('last_name').value = staff.last_name ?? '';
    document.getElementById('sex').value = staff.sex ?? '';
    document.getElementById('date_of_birth').value = staff.date_of_birth ?? '';
    document.getElementById('telephone').value = staff.telephone ?? '';
    document.getElementById('email').value = staff.email ?? '';
    document.getElementById('password').value = '';
    document.getElementById('nin').value = staff.nin ?? '';
    document.getElementById('address').value = staff.address ?? '';
    document.getElementById('position').value = staff.position ?? '';
    document.getElementById('current_salary').value = staff.current_salary ?? '';
    document.getElementById('salary_scale').value = staff.salary_scale ?? '';
    document.getElementById('hours_per_week').value = staff.hours_per_week ?? '';
    document.getElementById('contract_type').value = staff.contract_type ?? '';
    document.getElementById('payment_type').value = staff.payment_type ?? '';

    document.getElementById('modalTitle').innerText = "Edit Staff Member";
    document.getElementById('modalSub').innerText = "Update staff information";
    document.getElementById('submitBtn').innerHTML =
        '<i class="fas fa-save" style="margin-right:6px"></i> Update Staff';

    document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

/* VIEW DETAILS MODAL */

function openViewModal(member, roleName) {
    document.getElementById('view_staff_number').innerText = member.staff_number ?? '-';
    document.getElementById('view_full_name').innerText =
        `${member.first_name ?? ''} ${member.last_name ?? ''}`.trim() || '-';

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

function closeViewModal() {
    document.getElementById('viewModalOverlay').classList.remove('open');
}

/* CLOSE MODALS WHEN CLICKING OUTSIDE */

var modalOverlay = document.getElementById('modalOverlay');

if (modalOverlay) {
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal();
        }
    });
}

var viewModalOverlay = document.getElementById('viewModalOverlay');

if (viewModalOverlay) {
    viewModalOverlay.addEventListener('click', function (e) {
        if (e.target === this) {
            closeViewModal();
        }
    });
}

/* SEARCH + FILTER */

function filterTable() {
    var search = document.getElementById('searchInput').value.toLowerCase();
    var role = document.getElementById('roleFilter').value.toLowerCase();

    var rows = document.querySelectorAll('#staffTable tbody tr.staff-row');
    var searchEmptyRow = document.getElementById('searchEmptyRow');

    var visibleCount = 0;

    rows.forEach(function (row) {
        var text = row.innerText.toLowerCase();

        var roleBadge = row.querySelector('.badge');
        var roleTxt = roleBadge ? roleBadge.innerText.toLowerCase() : '';

        var matchSearch = text.includes(search);
        var matchRole = role === '' || roleTxt.includes(role);

        if (matchSearch && matchRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    if (searchEmptyRow) {
        searchEmptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}
