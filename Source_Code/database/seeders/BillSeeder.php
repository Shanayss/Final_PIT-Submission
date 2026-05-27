<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO bills (
                patient_number,
                in_patient_id,
                bill_date,
                total_amount,
                status
            ) VALUES
            
            ('P00001', 1, '2026-05-06', 4500.00, 'Paid'),
            ('P00002', 2, '2026-05-08', 8500.00, 'Paid'),
            ('P00003', 3, '2026-05-12', 3000.00, 'Unpaid'),
            ('P00004', 4, '2026-05-05', 6500.00, 'Paid'),
            ('P00005', 5, '2026-05-17', 7200.00, 'Partial'),
            ('P00006', 6, '2026-05-09', 9500.00, 'Unpaid'),
            ('P00007', 7, '2026-05-08', 5200.00, 'Paid'),
            ('P00008', 8, '2026-05-10', 8100.00, 'Paid'),
            ('P00009', 9, '2026-05-10', 4000.00, 'Paid'),
            ('P00010', 10, '2026-05-05', 6700.00, 'Unpaid'),
            
            ('P00011', NULL, '2026-05-18', 1500.00, 'Paid'),
            ('P00012', NULL, '2026-05-18', 2200.00, 'Paid'),
            ('P00013', NULL, '2026-05-19', 1800.00, 'Paid'),
            ('P00014', NULL, '2026-05-19', 2500.00, 'Paid'),
            ('P00015', NULL, '2026-05-20', 1700.00, 'Paid'),
            ('P00016', NULL, '2026-05-20', 2000.00, 'Paid'),
            ('P00017', NULL, '2026-05-21', 1600.00, 'Paid'),
            ('P00018', NULL, '2026-05-21', 1900.00, 'Paid'),
            ('P00019', NULL, '2026-05-22', 2100.00, 'Paid'),
            ('P00020', NULL, '2026-05-22', 2300.00, 'Paid')
        ");
    }
}