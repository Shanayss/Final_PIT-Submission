<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierItemsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO supplier_items (
            supplier_id, 
            item_number, 
            unit_price
            )
            VALUES
            (1, 'I001', 14.75),
            (1, 'I002', 4.50),
            (2, 'I003', 11.50),
            (3, 'I004', 42.00),
            (4, 'I005', 82.00),
            (7, 'I006', 70.00),
            (8, 'I007', 23.00),
            (9, 'I008', 58.00),
            (12, 'I009', 145.00),
            (12, 'I010', 800.00),
            (13, 'I011', 4400.00),
            (13, 'I012', 3100.00),
            (14, 'I013', 240.00),
            (15, 'I014', 175.00),
            (16, 'I015', 340.00),
            (17, 'I016', 210.00),
            (18, 'I017', 115.00),
            (19, 'I018', 90.00),
            (20, 'I019', 9.50),
            (20, 'I020', 430.00);
        ");
    }
}