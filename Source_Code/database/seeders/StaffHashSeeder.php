<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffHashSeeder extends Seeder
{
    public function run(): void
    {
        $staffs = DB::table('staff')->get();

        foreach ($staffs as $staff) {
            DB::table('staff')
                ->where('staff_number', $staff->staff_number)
                ->update([
                    'password' => Hash::make($staff->password)
                ]);
        }
    }
}