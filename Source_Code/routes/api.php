<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Staff;
use App\Http\Controllers\Auth\StaffLoginController;

/*
|--------------------------------------------------------------------------
| API LOGIN
|--------------------------------------------------------------------------
*/

Route::post('/staff/login', [StaffLoginController::class, 'login']);

/*
|--------------------------------------------------------------------------
| GET ALL STAFF
|--------------------------------------------------------------------------
*/

Route::get('/staff', function () {

    return response()->json(
        Staff::orderBy('staff_number')->get()
    );

});

/*
|--------------------------------------------------------------------------
| ADD STAFF
|--------------------------------------------------------------------------
*/

Route::post('/staff', function (Request $request) {
    $data = $request->validate([
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email',
    ]);

    $last = Staff::orderBy('staff_number', 'desc')->first();
    $nextNumber = $last ? ((int) substr($last->staff_number, 1)) + 1 : 1;

    $staff = Staff::create([
        'staff_number' => 'S' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT),
        'role_id' => 4,
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'password' => bcrypt('Password123'),
        'position' => 'Nursing Staff',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Staff added successfully',
        'staff' => $staff,
    ]);
});

/*
|--------------------------------------------------------------------------
| UPDATE STAFF
|--------------------------------------------------------------------------
*/

Route::put('/staff/{staff_number}', function (Request $request, $staff_number) {
    $staff = Staff::where('staff_number', $staff_number)->first();

    if (!$staff) {
        return response()->json([
            'success' => false,
            'message' => 'Staff not found'
        ], 404);
    }

    $staff->first_name = $request->first_name;
    $staff->last_name = $request->last_name;
    $staff->email = $request->email;
    $staff->save();

    return response()->json([
        'success' => true,
        'message' => 'Staff updated successfully',
        'staff' => $staff
    ]);
});

/*
|--------------------------------------------------------------------------
| DELETE STAFF
|--------------------------------------------------------------------------
*/

Route::delete('/staff/{staff_number}', function ($staff_number) {

    $staff = Staff::where('staff_number', $staff_number)->first();

    if (!$staff) {

        return response()->json([
            'success' => false,
            'message' => 'Staff not found'
        ], 404);

    }

    $staff->delete();

    return response()->json([
        'success' => true,
        'message' => 'Staff deleted successfully'
    ]);

});
