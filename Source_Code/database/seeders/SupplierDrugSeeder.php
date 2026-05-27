<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierDrugSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO supplier_drugs (supplier_id, drug_number, unit_price)
            VALUES
            (2, 'D001', 3.50),
            (2, 'D002', 12.00),
            (5, 'D003', 45.00),
            (5, 'D004', 25.00),
            (10, 'D005', 18.00),
            (10, 'D006', 55.00),
            (11, 'D007', 8.00),
            (11, 'D008', 150.00),
            (17, 'D009', 35.00),
            (17, 'D010', 22.00),
            (1, 'D011', 65.00),
            (3, 'D012', 40.00),
            (4, 'D013', 110.00),
            (6, 'D014', 95.00),
            (8, 'D015', 20.00),
            (9, 'D016', 120.00)
        ");
    }
}