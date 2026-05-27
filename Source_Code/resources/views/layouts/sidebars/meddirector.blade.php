<ul class="menu">

    <li class="active">
        <a href="{{ route('meddirector.dashboard') }}">Dashboard</a>
    </li>

    <li>
        <a href="{{ route('meddirector.staff') }}">Staff Overview</a>
    </li>

    <li>
        <a href="{{ route('meddirector.wards') }}">Patient & Ward Monitoring</a>
    </li>


    <li>
        <a href="{{ route('meddirector.suppliers.index') }}">Supplier</a>
    </li>

    <!-- Interactive Reports Dropdown -->
    <li class="menu-dropdown">
        <a href="javascript:void(0)" class="dropdown-trigger">
            <span>Reports</span>
            <svg class="dropdown-chevron-icon" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </a>
        <ul class="submenu">
            <li><a href="{{ route('meddirector.reports.patients') }}">Patient Reports (Per Ward)</a></li>
            <li><a href="{{ route('meddirector.reports.outpatient') }}">Out-Patient Clinic Reports</a></li>
            <li><a href="{{ route('meddirector.reports.supply') }}">Supply Usage Reports</a></li>
        </ul>
    </li>
</ul>

<style>
    .menu-dropdown {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .dropdown-trigger {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .dropdown-chevron-icon {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0.7;
    }

    /* Hidden Default State for Submenu */
    .menu-dropdown .submenu {
        display: none;
        list-style: none;
        padding: 4px 0 6px 16px;
        margin: 0;
    }

    /* Open State Modifiers */
    .menu-dropdown.is-open .submenu {
        display: block;
    }

    .menu-dropdown.is-open .dropdown-chevron-icon {
        transform: rotate(180deg);
    }
</style>

<!-- Localized Interaction Script -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const dropdownTriggers = document.querySelectorAll('.menu-dropdown .dropdown-trigger');

        dropdownTriggers.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const parentLi = btn.closest('.menu-dropdown');
                
                // Toggle active menu open class cleanly
                parentLi.classList.toggle('is-open');
            });
        });

        // Smart Sticky Check: Keeps menu expanded automatically if user is on a sub-report view page
        const currentURLPath = window.location.pathname;
        document.querySelectorAll('.submenu li a').forEach(subLink => {
            const linkHref = subLink.getAttribute('href');
            if (linkHref && currentURLPath.includes(new URL(linkHref, window.location.origin).pathname)) {
                const boundaryParent = subLink.closest('.menu-dropdown');
                if (boundaryParent) boundaryParent.classList.add('is-open');
                subLink.style.fontWeight = "700"; 
            }
        });
    });
</script>