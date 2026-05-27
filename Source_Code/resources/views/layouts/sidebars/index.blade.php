<div class="sidebar">

    <div>

        <div class="logo-section">
            <img src="{{ asset('images/logo-green-1.png') }}" alt="Logo">
            <h2>WellMeadows</h2>
        </div>

        {{-- MEDICAL DIRECTOR (Role 1) --}}
        @if(session('role') == 1)
            @include('layouts.sidebars.meddirector')
        @endif

        {{-- PERSONNEL OFFICER (Role 2) --}}
        @if(session('role') == 2)
            @include('layouts.sidebars.personneloff')
        @endif

        {{-- CLINICAL STAFF (Role 3) --}}
        @if(session('role') == 3)
            @include('layouts.sidebars.clinicalstaff')
        @endif

        {{-- NURSING STAFF (Role 4) --}}
        @if(session('role') == 4)
            @include('layouts.sidebars.nursestaff')
        @endif

        {{-- CASHIER (Role 5) --}}
        @if(session('role') == 5)
            @include('layouts.sidebars.cashier')
        @endif

    </div>

</div>
