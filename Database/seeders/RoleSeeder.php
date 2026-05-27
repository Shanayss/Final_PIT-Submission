<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'role_id' => 1,
                'role_name' => 'Medical Director'
            ],
            [
                'role_id' => 2,
                'role_name' => 'Personnel Officer'
            ],
            [
                'role_id' => 3,
                'role_name' => 'Clinical Staff'
            ],
            [
                'role_id' => 4,
                'role_name' => 'Nursing Staff'
            ],
            [
                'role_id' => 5,
                'role_name' => 'Cashier'
            ]
        ]);
    }
}