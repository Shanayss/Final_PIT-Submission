<ul class="menu">

    <li class="{{ request()->routeIs('personnel.dashboard') ? 'active' : '' }}">
        <a href="{{ route('personnel.dashboard') }}" class="nav-link">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('personnel.staff-management') ? 'active' : '' }}">
        <a href="{{ route('personnel.staff-management') }}" class="nav-link">
            <i class="fa-solid fa-user-group"></i>
            <span>Staff Management</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('personnel.qualifications') ? 'active' : '' }}">
        <a href="{{ route('personnel.qualifications') }}" class="nav-link">
            <i class="fa-solid fa-award"></i>
            <span>Qualifications</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('personnel.work-experiences') ? 'active' : '' }}">
        <a href="{{ route('personnel.work-experiences') }}" class="nav-link">
            <i class="fa-solid fa-briefcase"></i>
            <span>Work Experiences</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('personnel.staffallocations') ? 'active' : '' }}">
        <a href="{{ route('personnel.staffallocations') }}" class="nav-link">
            <i class="fa-solid fa-user-clock"></i>
            <span>Staff Allocations</span>
        </a>
    </li>

    <li class="{{ request()->routeIs('personnel.reports') ? 'active' : '' }}">
        <a href="{{ route('personnel.reports') }}" class="nav-link">
            <i class="fa-solid fa-chart-column"></i>
            <span>Reports</span>
        </a>
    </li>

</ul>
