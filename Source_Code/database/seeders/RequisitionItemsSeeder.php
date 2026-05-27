<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionItemsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO requisition_items (requisition_number, item_number, quantity_required)
            VALUES
            (1, 'I001', 50),
            (1, 'I002', 100),
            (2, 'I005', 20),
            (2, 'I007', 30),
            (3, 'I006', 10),
            (3, 'I015', 5),
            (4, 'I003', 100),
            (4, 'I001', 20),
            (5, 'I011', 2),
            (6, 'I009', 5),
            (7, 'I010', 2),
            (8, 'I019', 50),
            (8, 'I020', 5),
            (9, 'I013', 20),
            (9, 'I014', 20),
            (10, 'I018', 10),
            (1, 'I004', 10),
            (2, 'I008', 5),
            (3, 'I016', 4),
            (4, 'I017', 3);
        ");
    }
}