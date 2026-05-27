// Inherit the dynamic database configuration structure passed from Laravel Blade
const wardsConfigurationMap = window.wardsConfigurationMap || {};

// Global State Trackers
let activeWardBedsCollection = [];
let currentlySelectedBedId = null;
let currentActiveWardId = null;

document.addEventListener("DOMContentLoaded", () => {
    console.log("Initializing Ward Grid Blueprint Map...");
    console.log("Available database configurations map:", wardsConfigurationMap);
    console.log("Default starting ID from blade view:", window.defaultWardId);

    const availableKeys = Object.keys(wardsConfigurationMap);
    
    if (availableKeys.length === 0) {
        console.error("CRITICAL ERROR: window.wardsConfigurationMap is completely empty. Ensure your database seeder has data!");
        document.getElementById('md-ward-display-title').textContent = "Error: No ward configurations found in database.";
        return;
    }

    // FIX 2: Normalize startingWardId to string to prevent key type mismatch between PHP integer keys and JS string lookup
    let startingWardId = String(window.defaultWardId);
    if (!startingWardId || !wardsConfigurationMap[startingWardId]) {
        startingWardId = availableKeys[0]; // Self-correction fallback to the first database entry row
    }

    console.log("Resolved starting execution Ward ID node:", startingWardId);
    switchWardContextLayout(startingWardId);
    initializeControlActions();
});

/**
 * Handles clearing state parameters and rendering the distinct bed array lists
 */
function switchWardContextLayout(wardId) {
    currentActiveWardId = wardId;
    const config = wardsConfigurationMap[wardId];
    
    if (!config) {
        console.error(`Lookup fault: Ward ID "${wardId}" could not be resolved inside configurations map.`);
        return;
    }

    console.log(`Successfully rendering data matrix for: ${config.name} (${config.bedsCount} beds)`);

    // Load beds list array values straight from database configuration payload
    activeWardBedsCollection = config.bedsList || [];

    // Reset highlights and profiles summary cards sheets
    currentlySelectedBedId = null;
    updatePatientDetailPanelDisplay(null);

    // Dynamic title string adjustment
    const titleElement = document.getElementById('md-ward-display-title');
    if (titleElement) {
        titleElement.textContent = `${config.name} — ${config.bedsCount} beds`;
    }

    generateBedGridMatrix(activeWardBedsCollection);
}

/**
 * Builds individual interactive bed card nodes into the grid layout block elements
 */
function generateBedGridMatrix(beds) {
    const contextContainer = document.getElementById('meddirector-bed-grid-container');
    if (!contextContainer) return; 

    contextContainer.innerHTML = ''; 

    beds.forEach(bed => {
        const structuralCard = document.createElement('div');
        structuralCard.classList.add('meddirector-bed-card', bed.status);
        structuralCard.setAttribute('data-bed-id', bed.id);

        if (currentlySelectedBedId === bed.id) {
            structuralCard.classList.add('is-active');
        }

        structuralCard.innerHTML = `
            <div class="bed-num">${bed.id}</div>
            <div class="bed-txt">${bed.label}</div>
        `;

        structuralCard.addEventListener('click', () => toggleBedSelectionState(bed.id));
        contextContainer.appendChild(structuralCard);
    });
}

function toggleBedSelectionState(id) {
    if (currentlySelectedBedId === id) {
        currentlySelectedBedId = null;
        updatePatientDetailPanelDisplay(null);
    } else {
        currentlySelectedBedId = id;
        const targetBedRecord = activeWardBedsCollection.find(b => b.id === id);
        updatePatientDetailPanelDisplay(targetBedRecord);
    }
    generateBedGridMatrix(activeWardBedsCollection);
}

function updatePatientDetailPanelDisplay(bedObj) {
    const infoCard = document.getElementById('md-patient-info-card');
    const placeholder = document.getElementById('md-patient-card-placeholder');

    if (!infoCard || !placeholder) return;

    if (!bedObj) {
        infoCard.classList.add('fallback-hidden');
        placeholder.classList.remove('fallback-hidden');
        return;
    }

    placeholder.classList.add('fallback-hidden');
    infoCard.classList.remove('fallback-hidden');

    document.getElementById('md-detail-bed-title').textContent = `Bed ${bedObj.id} — selected`;
    document.getElementById('md-detail-patient-name').textContent = bedObj.patient || "No patient assigned";
    document.getElementById('md-detail-admitted').textContent = bedObj.admitted || "—";
    document.getElementById('md-detail-leave').textContent = bedObj.leave || "—";

    const pill = document.getElementById('md-detail-pill-status');
    if (pill) {
        pill.className = 'md-status-badge'; 
        if (bedObj.status === 'occupied') {
            pill.textContent = 'In ward';
            pill.classList.add('badge-inward');
        } else {
            pill.textContent = 'Vacant';
            pill.style.backgroundColor = '#e2e8f0';
            pill.style.color = '#4a5568';
        }
    }
}

function initializeControlActions() {
    const wardSelector = document.getElementById('md-ward-selector');

    if (wardSelector) {
        wardSelector.addEventListener('change', (e) => {
            // FIX 2 (also here): Normalize to string so key lookup stays consistent
            switchWardContextLayout(String(e.target.value));
        });
    }
}
