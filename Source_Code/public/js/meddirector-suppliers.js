/**
 * WellMeadows Supplier Management Framework Module
 * Handles dynamic rendering, responsive filtering, and modal display states.
 */
const dynamicSuppliersCollection = window.suppliersJsonConfig || [];

document.addEventListener("DOMContentLoaded", () => {
    renderSuppliersDataMatrix(dynamicSuppliersCollection);
    initializeControlHandshakers();
});

function renderSuppliersDataMatrix(dataset) {
    const tableBody = document.getElementById('md-supplier-table-rows');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    if (dataset.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#718096; padding: 40px;">No registered suppliers found inside the database yet. Click "+ Add supplier" to input records.</td></tr>`;
        return;
    }

    dataset.forEach(sup => {
        const rowNode = document.createElement('tr');
        
        const type = sup.type || 'pharmaceutical';
        let typeBadgeLabel = type.charAt(0).toUpperCase() + type.slice(1);
        let typeClassName = type;
        if (type === 'medical-hardware' || type === 'hardware') {
            typeBadgeLabel = "Medical Hardware";
            typeClassName = "hardware";
        }

        rowNode.innerHTML = `
            <td>
                <div class="md-sup-name-main">${sup.name}</div>
                <span class="md-sup-type-badge ${typeClassName}">${typeBadgeLabel}</span>
            </td>
            <td>
                <div>${sup.address}</div>
                <div class="md-txt-addr-sub">${sup.city || ''}</div>
            </td>
            <td style="font-weight: 500;">${sup.tel}</td>
            <td style="font-weight: 500; color: #475569;">${sup.fax || '—'}</td>
            <td style="text-align: center;">
                <button class="md-btn-action-update" onclick="handleSupplierUpdateAction('${sup.id}')">Update</button>
                <button class="md-btn-action-update" onclick="handleSupplierDeleteAction('${sup.id}')" style="margin-left:8px; background:#e53e3e; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;">Delete</button>

            </td>
        `;
        tableBody.appendChild(rowNode);
    });
}

function handleSupplierDeleteAction(supplierId) {
    const ok = confirm('Delete this supplier?');
    if (!ok) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/MedDirector/suppliers/${supplierId}`;

    const csrfToken = getSupplierCsrfToken();
    if (!csrfToken) {
        alert('CSRF token not found. Please reload the page.');
        return;
    }

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

function handleSupplierUpdateAction(supplierId) {

    const payload = {
        supplier_name: prompt('Supplier name:'),
        supplier_type: prompt('Supplier type (pharmaceutical|surgical|medical-hardware):'),
        telephone: prompt('Telephone:'),
        fax: prompt('Fax (optional):'),
        address: prompt('Address:'),
    };



    // Basic guard: cancel means no update.

    const values = Object.values(payload);
    if (values.some(v => v === null)) return;


    // Minimal redirect-based update using an auto-post form.
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/MedDirector/suppliers/${supplierId}`;

    // Laravel needs method spoofing for PUT.
    const csrfToken = getSupplierCsrfToken();
    if (!csrfToken) {
        alert('CSRF token not found. Please reload the page.');
        return;
    }

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'PUT';

    const addField = (name, val) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = val;
        form.appendChild(input);
    };

    addField('supplier_name', payload.supplier_name);
    addField('supplier_type', payload.supplier_type);
    addField('telephone', payload.telephone);
    addField('fax', payload.fax);
    addField('address', payload.address);

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

function getSupplierCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';
}


function initializeControlHandshakers() {
    const searchField = document.getElementById('md-supplier-search');
    const btnAdd = document.getElementById('md-btn-add-supplier');
    const modalOverlay = document.getElementById('md-supplier-modal-overlay');

    let currentSearchTermString = '';

    function evaluateGlobalCombinedDataFiltering() {
        const filteredList = dynamicSuppliersCollection.filter(sup => {
            return sup.name.toLowerCase().includes(currentSearchTermString) || 
                   sup.address.toLowerCase().includes(currentSearchTermString);
        });
        renderSuppliersDataMatrix(filteredList);
    }

    if (searchField) {
        searchField.addEventListener('input', (e) => {
            currentSearchTermString = e.target.value.toLowerCase().trim();
            evaluateGlobalCombinedDataFiltering();
        });
    }

    // Control triggers targeting form popup view layout overlay panels toggles
    if (btnAdd && modalOverlay) {
        const btnCloseX = document.getElementById('md-close-modal-btn');
        const btnCancel = document.getElementById('md-cancel-modal-btn');

        btnAdd.addEventListener('click', () => {
            modalOverlay.classList.remove('md-hidden');
        });

        const hideSupplierFormModal = () => {
            modalOverlay.classList.add('md-hidden');
            document.getElementById('md-add-supplier-form').reset();
        };

        if (btnCloseX) btnCloseX.addEventListener('click', hideSupplierFormModal);
        if (btnCancel) btnCancel.addEventListener('click', hideSupplierFormModal);

        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) hideSupplierFormModal();
        });
    }
}
