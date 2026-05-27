<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO wards (
                ward_number,
                ward_name,
                location,
                total_beds,
                tel_extension
            ) VALUES
            (1, 'Orthopedic Ward', 'Block A', 16, '101'),
            (2, 'Cardiology Ward', 'Block A', 14, '102'),
            (3, 'General Medical Ward', 'Block A', 22, '103'),
            (4, 'Neurology Ward', 'Block A', 12, '104'),
            (5, 'Emergency', 'Block B', 18, '105'),
            (6, 'Surgical Unit', 'Block B', 16, '106'),
            (7, 'Rehabilitation', 'Block B', 20, '107'),
            (8, 'Geriatric Care Ward', 'Block C', 18, '108'),
            (9, 'Dementia Care Ward', 'Block C', 12, '109'),
            (10, 'Isolation Ward', 'Block C', 10, '110'),
            (11, 'Intensive Care Unit', 'Block C', 8, '111'),
            (12, 'Dialysis Ward', 'Block D', 10, '112'),
            (13, 'Respiratory Care Ward', 'Block D', 12, '113'),
            (14, 'Psychiatric Ward', 'Block E', 12, '114'),
            (15, 'Recovery Ward', 'Block E', 10, '115'),
            (16, 'Endocrinology Ward', 'Block E', 14, '116'),
            (17, 'Chronic Care Ward', 'Block E', 16, '117'),
            (18, 'Out Patient Clinic', 'Block E', 0, '118')
        ");
    }
}