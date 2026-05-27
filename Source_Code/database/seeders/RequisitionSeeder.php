<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('requisitions')->insert([
            [
                'requisition_number' => 1,
                'ward_number' => 3,
                'staff_number' => 'S003',
                'date_ordered' => '2026-05-01',
                'date_received' => '2026-05-02',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 2,
                'ward_number' => 7,
                'staff_number' => 'S004',
                'date_ordered' => '2026-05-01',
                'date_received' => '2026-05-03',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 3,
                'ward_number' => 4,
                'staff_number' => 'S006',
                'date_ordered' => '2026-05-02',
                'date_received' => null,
                'status' => 'Pending',
            ],
            [
                'requisition_number' => 4,
                'ward_number' => 17,
                'staff_number' => 'S009',
                'date_ordered' => '2026-05-04',
                'date_received' => '2026-05-04',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 5,
                'ward_number' => 18,
                'staff_number' => 'S005',
                'date_ordered' => '2026-05-02',
                'date_received' => '2026-05-02',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 6,
                'ward_number' => 2,
                'staff_number' => 'S011',
                'date_ordered' => '2026-05-04',
                'date_received' => '2026-05-04',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 7,
                'ward_number' => 15,
                'staff_number' => 'S010',
                'date_ordered' => '2026-05-04',
                'date_received' => null,
                'status' => 'Pending',
            ],
            [
                'requisition_number' => 8,
                'ward_number' => 9,
                'staff_number' => 'S012',
                'date_ordered' => '2026-05-04',
                'date_received' => '2026-05-04',
                'status' => 'Delivered',
            ],
            [
                'requisition_number' => 9,
                'ward_number' => 11,
                'staff_number' => 'S013',
                'date_ordered' => '2026-05-04',
                'date_received' => null,
                'status' => 'Pending',
            ],
            [
                'requisition_number' => 10,
                'ward_number' => 10,
                'staff_number' => 'S013',
                'date_ordered' => '2026-05-04',
                'date_received' => null,
                'status' => 'Pending',
            ],
            [
                'requisition_number' => 11,
                'ward_number' => 13,
                'staff_number' => 'S008',
                'date_ordered' => '2026-05-03',
                'date_received' => null,
                'status' => 'Pending',
            ],
            [
                'requisition_number' => 13,
                'ward_number' => 12,
                'staff_number' => 'S007',
                'date_ordered' => '2026-05-03',
                'date_received' => '2026-05-04',
                'status' => 'Delivered',
            ],
        ]);
    }
}
