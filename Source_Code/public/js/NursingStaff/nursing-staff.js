function filterBeds(wardSelectId, bedSelectId) {
    const wardSelect = document.getElementById(wardSelectId);
    const bedSelect = document.getElementById(bedSelectId);

    if (!wardSelect || !bedSelect) {
        return;
    }

    const options = Array.from(bedSelect.options);

    function applyFilter() {
        const ward = wardSelect.value;
        options.forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = ward && option.dataset.ward !== ward;
        });

        const selected = bedSelect.selectedOptions[0];
        if (selected && selected.hidden) {
            bedSelect.value = '';
        }
    }

    wardSelect.addEventListener('change', applyFilter);
    applyFilter();
}

filterBeds('assign-ward-select', 'assign-bed-select');
filterBeds('admit-ward-select', 'admit-bed-select');

const assignPatientSelect = document.getElementById('assign-patient-select');
if (assignPatientSelect) {
    const wardSelect = document.getElementById('assign-ward-select');
    const expectedDischargeInput = document.getElementById('assign-expected-discharge-date');

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return year + '-' + month + '-' + day;
    }

    function expectedDischargeFromStay(days) {
        const stayDays = Number.parseInt(days, 10);

        if (!Number.isFinite(stayDays) || stayDays < 1) {
            return '';
        }

        const date = new Date();
        date.setDate(date.getDate() + stayDays);

        return formatDate(date);
    }

    function applySelectedPatientWard() {
        const selected = assignPatientSelect.selectedOptions[0];

        if (!selected || !selected.value) {
            return;
        }

        if (wardSelect && selected.dataset.ward) {
            wardSelect.value = selected.dataset.ward;
            wardSelect.dispatchEvent(new Event('change'));
        }

        if (expectedDischargeInput && !expectedDischargeInput.value) {
            expectedDischargeInput.value = selected.dataset.expectedDischarge
                || expectedDischargeFromStay(selected.dataset.expectedStay);
        }
    }

    assignPatientSelect.addEventListener('change', applySelectedPatientWard);
    applySelectedPatientWard();
}

const openPatientFormButton = document.querySelector('[data-open-patient-form]');
const closePatientFormButton = document.querySelector('[data-close-patient-form]');
const patientRegistrationPanel = document.getElementById('patient-registration-form');
const patientRegistrationOverlay = document.getElementById('patient-registration-overlay');

function setPatientRegistrationPanel(isOpen) {
    if (!patientRegistrationPanel || !patientRegistrationOverlay) {
        return;
    }

    patientRegistrationPanel.hidden = !isOpen;
    patientRegistrationOverlay.hidden = !isOpen;
    patientRegistrationPanel.classList.toggle('is-open', isOpen);
    patientRegistrationOverlay.classList.toggle('is-open', isOpen);
    patientRegistrationPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    document.body.classList.toggle('nurse-panel-open', isOpen);
}

if (openPatientFormButton) {
    openPatientFormButton.addEventListener('click', function () {
        setPatientRegistrationPanel(true);
    });
}

if (closePatientFormButton) {
    closePatientFormButton.addEventListener('click', function () {
        setPatientRegistrationPanel(false);
    });
}

if (patientRegistrationOverlay) {
    patientRegistrationOverlay.addEventListener('click', function () {
        setPatientRegistrationPanel(false);
    });
}

if (patientRegistrationPanel && !patientRegistrationPanel.hidden) {
    setPatientRegistrationPanel(true);
}

document.querySelectorAll('[data-bed-choice]').forEach(function (button) {
    button.addEventListener('click', function () {
        const wardSelect = document.getElementById('assign-ward-select');
        const bedSelect = document.getElementById('assign-bed-select');

        if (!wardSelect || !bedSelect) {
            return;
        }

        wardSelect.value = button.dataset.ward;
        wardSelect.dispatchEvent(new Event('change'));
        bedSelect.value = button.dataset.bed;
    });
});

document.querySelectorAll('[data-prescription-detail]').forEach(function (button) {
    button.addEventListener('click', function () {
        const row = button.closest('tr');
        const detailRow = row ? row.nextElementSibling : null;

        if (row && detailRow && detailRow.classList.contains('nurse-prescription-details')) {
            const isOpening = detailRow.hidden;
            detailRow.hidden = !isOpening;
            row.classList.toggle('nurse-row-highlight', isOpening);
            button.textContent = isOpening ? 'Hide Details' : 'View Details';
        }
    });
});

const existingPatientSelect = document.getElementById('admit-existing-patient');
if (existingPatientSelect) {
    const summary = document.getElementById('admit-existing-summary');
    const fields = {
        firstName: document.getElementById('admit-first-name'),
        lastName: document.getElementById('admit-last-name'),
        dateOfBirth: document.getElementById('admit-date-of-birth'),
        sex: document.getElementById('admit-sex'),
        telephone: document.getElementById('admit-telephone'),
        kinName: document.getElementById('admit-kin-name'),
        wardNumber: document.getElementById('admit-ward-number'),
        expectedStayDays: document.getElementById('admit-expected-stay-days'),
        datePlacedOnWaitingList: document.getElementById('admit-date-placed-on-waiting-list'),
    };

    const originalValues = Object.fromEntries(
        Object.entries(fields).map(([key, field]) => [key, field ? field.value : ''])
    );
    const patientDetailFields = [
        fields.firstName,
        fields.lastName,
        fields.dateOfBirth,
        fields.sex,
        fields.telephone,
        fields.kinName,
    ];

    function setReadOnly(isReadOnly) {
        patientDetailFields.forEach(function (field) {
            if (!field) {
                return;
            }

            if (field.tagName === 'SELECT') {
                field.disabled = isReadOnly;
            } else {
                field.readOnly = isReadOnly;
            }
        });
    }

    function writeSummary(key, value) {
        const node = summary ? summary.querySelector('[data-existing-summary="' + key + '"]') : null;
        if (node) {
            node.textContent = value || 'N/A';
        }
    }

    function applyExistingPatient() {
        const selected = existingPatientSelect.selectedOptions[0];
        const hasPatient = Boolean(selected && selected.value);

        if (!hasPatient) {
            Object.entries(fields).forEach(function ([key, field]) {
                if (field) {
                    field.value = originalValues[key] || '';
                }
            });
            setReadOnly(false);
            if (summary) {
                summary.hidden = true;
            }
            return;
        }

        if (fields.firstName) fields.firstName.value = selected.dataset.firstName || '';
        if (fields.lastName) fields.lastName.value = selected.dataset.lastName || '';
        if (fields.dateOfBirth) fields.dateOfBirth.value = selected.dataset.dateOfBirth || '';
        if (fields.sex) fields.sex.value = selected.dataset.sex || '';
        if (fields.telephone) fields.telephone.value = selected.dataset.telephone || '';
        if (fields.kinName) {
            fields.kinName.value = [selected.dataset.kinName, selected.dataset.kinTelephone]
                .filter(Boolean)
                .join(' - ');
        }
        if (fields.wardNumber) {
            fields.wardNumber.value = selected.dataset.wardNumber || originalValues.wardNumber || '';
        }
        if (fields.expectedStayDays) {
            fields.expectedStayDays.value = selected.dataset.expectedStayDays || originalValues.expectedStayDays || '';
        }
        if (fields.datePlacedOnWaitingList) {
            fields.datePlacedOnWaitingList.value = selected.dataset.datePlacedOnWaitingList || originalValues.datePlacedOnWaitingList || '';
        }

        writeSummary('patientNumber', selected.value);
        writeSummary('dateOfBirth', selected.dataset.dateOfBirth);
        writeSummary('sex', selected.dataset.sex);
        writeSummary('telephone', selected.dataset.telephone);
        writeSummary('address', selected.dataset.address);
        writeSummary('maritalStatus', selected.dataset.maritalStatus);
        if (summary) {
            summary.hidden = false;
        }

        setReadOnly(true);
    }

    existingPatientSelect.addEventListener('change', applyExistingPatient);
    applyExistingPatient();
}

document.querySelectorAll('[data-medication-fill]').forEach(function (button) {
    button.addEventListener('click', function () {
        const medicationSelect = document.getElementById('record-medication-select');
        const dosageInput = document.getElementById('record-dosage-input');

        if (medicationSelect) {
            medicationSelect.value = button.dataset.medicationFill;
        }

        if (dosageInput && button.dataset.dosage) {
            dosageInput.value = button.dataset.dosage;
        }
    });
});

document.querySelectorAll('[data-condition-patient]').forEach(function (button) {
    button.addEventListener('click', function () {
        const select = document.getElementById('condition-patient-select');
        if (!select) return;

        // Fill patient id and ensure the UI shows the selected value.
        select.value = button.dataset.conditionPatient;
        select.dispatchEvent(new Event('change'));

        // Optional UX: scroll form into view so the user sees the filled dropdown.
        const form = select.closest('form');
        if (form) {
            form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
