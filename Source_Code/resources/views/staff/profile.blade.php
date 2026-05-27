@extends('layouts.app')

@section('content')

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card" style="padding: 30px;">
        <h2 style="margin-bottom: 30px;">My Profile</h2>

        @if(session('status') === 'profile-updated')
            <div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 12px; border-radius: 4px; margin-bottom: 20px; color: #155724;">
                Profile updated successfully!
            </div>
        @endif

        <div style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 15px;">Account Information</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                <p style="margin: 10px 0;"><strong>Name:</strong> {{ session('staff_name', $user->name ?? 'N/A') }}</p>
                <p style="margin: 10px 0;"><strong>Email:</strong> {{ $user->email ?? session('staff_email', 'N/A') }}</p>
                <p style="margin: 10px 0;"><strong>Position:</strong> {{ session('staff_position', 'N/A') }}</p>
                <p style="margin: 10px 0;"><strong>Role ID:</strong> {{ session('role', $user->role_id ?? 'N/A') }}</p>
            </div>
        </div>

        <div style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 15px;">Account Settings</h3>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
