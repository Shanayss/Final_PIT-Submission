<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $staff = Staff::whereRaw('LOWER(TRIM(email)) = ?', [
            strtolower(trim($request->email))
        ])->first();

        if (
            !$staff ||
            (
                $staff->password !== $request->password &&
                !Hash::check($request->password, $staff->password)
            )
        ) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ])->onlyInput('email');
        }

        Auth::guard('web')->login($staff);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
