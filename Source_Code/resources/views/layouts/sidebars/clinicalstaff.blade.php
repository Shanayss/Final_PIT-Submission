{{-- Clinical Staff Sidebar Fonts and Icons --}}
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<ul class="menu">

    <li class="{{ request()->routeIs('clinical.dashboard') ? 'active' : '' }}">
        <a href="{{ route('clinical.dashboard') }}">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('clinical.appointments*') ? 'active' : '' }}">
        <a href="{{ route('clinical.appointments') }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Appointments</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('clinical.treatments*') ? 'active' : '' }}">
        <a href="{{ route('clinical.treatments') }}">
            <i class="fa-solid fa-notes-medical"></i>
            <span>Treatments</span>
        </a>
    </li>

</ul>

<style>
    /* ================================
       CLINICAL STAFF SIDEBAR MENU
    ================================= */

    .sidebar {
        font-family: 'Poppins', sans-serif !important;
    }

    .sidebar .menu {
        list-style: none;
        padding: 0;
        margin: 35px 0 0 0;
    }

    .sidebar .menu li {
        margin-bottom: 16px;
    }

    .sidebar .menu li a {
        display: flex;
        align-items: center;
        gap: 12px;

        width: 100%;
        padding: 14px 18px;
        border-radius: 10px;

        font-family: 'Poppins', sans-serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;

        color: #ffffff !important;
        text-decoration: none !important;

        transition: 0.2s ease;
    }

    .sidebar .menu li a i {
        width: 18px;
        min-width: 18px;
        text-align: center;
        font-size: 15px;
        color: #ffffff !important;
    }

    .sidebar .menu li a span {
        font-family: 'Poppins', sans-serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
    }

    .sidebar .menu li a:hover {
        background: rgba(255, 255, 255, 0.12);
    }

    .sidebar .menu li.active a {
        background: #63A978;
        color: #ffffff !important;
    }
</style>