<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
   
    <title>WellMeadows Hospital</title>

    @stack('styles')
    
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/meddirector-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/meddirector-wards.css') }}">
    @if(request()->routeIs('nurse.*'))
        <link rel="stylesheet" href="{{ asset('css/NursingStaff/nursing-staff.css') }}">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @if(request()->routeIs('clinical.appointments'))
        <link rel="stylesheet" href="{{ asset('css/consultant/appointments.css') }}?v={{ time() }}">
    @endif

    @stack('styles')
</head>
<body class="{{ request()->routeIs('nurse.*') ? 'is-nurse-page is-nursing-staff-page' : '' }}">

<div class="dashboard-container">

    {{-- SIDEBAR --}}
    @include('layouts.navigation')

    {{-- MAIN CONTENT --}}
    <div class="main-content">

        {{-- TOPBAR --}}
        <div class="topbar">
            <div class="topbar-header">
                <div class="notification-wrapper">
                    <button type="button" id="notification-button" class="notification-button" aria-label="Open notifications">
                        <span class="notification-icon">🔔</span>
                        <span class="notification-badge"></span>
                    </button>
                    <div class="notification-dropdown" id="notification-panel" aria-hidden="true">
                        <div class="notification-dropdown-header">
                            <h3>Notifications</h3>
                            <button type="button" id="close-notification-panel" aria-label="Close notifications">×</button>
                        </div>
                        <div class="notification-dropdown-body">
                            <h4>No notification</h4>
                            <p>There are no notifications yet. Once notification data is added, this dropdown will show your recent alerts.</p>
                        </div>
                    </div>
                </div>

                <details class="profile-menu">
                    <summary class="profile-button">
                        <span class="avatar-letter">{{ strtoupper(substr(session('staff_name', Auth::user()?->name ?? 'User'), 0, 1)) }}</span>
                        <span class="profile-name">{{ session('staff_name', Auth::user()?->name ?? 'User') }}</span>
                    </summary>
                    <ul>
                        <li><button type="button" id="open-profile-panel">Profile</button></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        </li>
                    </ul>
                </details>
            </div>

            @if(
                request()->routeIs('personnel.dashboard')||
                request()->routeIs('meddirector.dashboard')||
                request()->routeIs('clinical.dashboard')||
                request()->routeIs('nurse.dashboard')||
                request()->routeIs('cashier.dashboard')
            )
                <div class="welcome-card">
                    <div class="welcome-card-content">
                        <div>
                            <h1>Welcome, {{ session('staff_name', Auth::user()?->name ?? 'User') }}</h1>
                            <p>{{ session('staff_position') }}</p>
                        </div>
                        <div class="welcome-card-meta">
                            <div class="current-date">
                                {{ now()->format('l, j F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- PAGE CONTENT --}}
        <div class="content-area">
            @yield('content')
        </div>

    </div>

</div>

<div class="profile-panel-overlay" id="profile-panel-overlay"></div>
<aside class="profile-panel" id="profile-panel" aria-hidden="true">
    <div class="profile-panel-header">
        <div>
            <h2>My Profile</h2>
            <p class="profile-panel-subtitle">Account details and role information</p>
        </div>
        <button type="button" id="close-profile-panel" aria-label="Close profile panel">×</button>
    </div>
    <div class="profile-panel-avatar">
        <div class="profile-panel-avatar-circle">{{ strtoupper(substr(session('staff_name', Auth::user()?->name ?? 'U'), 0, 1)) }}</div>
        <div>
            <h3>{{ session('staff_name', Auth::user()?->name ?? 'Unknown') }}</h3>
            <span>{{ session('staff_position', 'N/A') }}</span>
        </div>
    </div>
    <div class="profile-panel-content">
        <div class="profile-panel-item">
            <span class="profile-item-label">Email</span>
            <span class="profile-item-value">{{ session('staff_email', Auth::user()?->email ?? 'N/A') }}</span>
        </div>
        <div class="profile-panel-item">
            <span class="profile-item-label">Position</span>
            <span class="profile-item-value">{{ session('staff_position', 'N/A') }}</span>
        </div>
    </div>
</aside>

{{-- PATIENT REGISTRATION MODAL --}}
@if(in_array(trim((string) session('staff_position')), ['Junior Nurse']))
    @include('components.patient-registration-modal')
@endif

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
<script src="{{ asset('js/meddirector-wards.js') }}"></script>
@if(request()->routeIs('nurse.*'))
    <script src="{{ asset('js/NursingStaff/nursing-staff.js') }}"></script>
    <script src="{{ asset('js/patient-registration-modal.js') }}"></script>
@endif
@stack('scripts')

</body>
</html>
