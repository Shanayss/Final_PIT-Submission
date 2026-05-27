<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;

class StaffLoginController extends Controller
{
    // For Flutter/mobile API login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $staff = Staff::whereRaw('LOWER(TRIM(email)) = ?', [
            strtolower(trim($request->email))
        ])->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'staff' => [
                'staff_number' => $staff->staff_number,
                'name' => trim("{$staff->first_name} {$staff->last_name}"),
                'email' => $staff->email,
                'role_id' => $staff->role_id,
                'position' => $staff->position,
            ]
        ]);
    }

    // For Laravel web login
    public function webLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $staff = Staff::whereRaw('LOWER(TRIM(email)) = ?', [
            strtolower(trim($request->email))
        ])->first();

        if (!$staff || !Hash::check($request->password, $staff->password)) {
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ])->onlyInput('email');
        }

$position = trim((string) ($staff->position ?? ''));

        // Hardening: normalize whitespace and casing so UI role checks remain stable.
        $position = preg_replace('/\s+/', ' ', $position);

        if ($position === '') {
            $position = match ((int) $staff->role_id) {
                1 => 'Medical Director',
                2 => 'Personnel Officer',
                3 => 'Clinical Staff',
                4 => 'Nursing Staff',
                5 => 'Cashier',
                default => 'Staff',
            };
        }


        session([
            'staff_name' => trim("{$staff->first_name} {$staff->last_name}"),
            'staff_position' => $position,
            'role' => $staff->role_id,
            'staff_email' => $staff->email,
            'staff_id' => $staff->staff_number,
        ]);

        Auth::guard('web')->login($staff);

        $request->session()->regenerate();

        return match ((int) $staff->role_id) {
            1 => redirect()->route('meddirector.dashboard'),
            2 => redirect()->route('personnel.dashboard'),
            3 => redirect()->route('clinical.dashboard'),
            4 => redirect()->route('nurse.dashboard'),
            5 => redirect()->route('cashier.dashboard'),
            default => redirect()->route('dashboard'),
        };
    }
}
