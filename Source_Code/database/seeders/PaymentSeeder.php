<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO payments
            (bill_id, payment_date, amount_paid, payment_method)
            VALUES

            (1, '2026-05-06', 4500, 'Cash'),
            (2, '2026-05-08', 8500, 'GCash'),
            (3, '2026-05-12', 0, 'Pending'),
            (4, '2026-05-05', 6500, 'Credit Card'),
            (5, '2026-05-17', 3000, 'Cash'),
            (6, '2026-05-09', 0, 'Pending'),
            (7, '2026-05-08', 5200, 'Debit Card'),
            (8, '2026-05-10', 8100, 'GCash'),
            (9, '2026-05-10', 4000, 'Cash'),
            (10, '2026-05-05', 0, 'Pending'),

            (11, '2026-05-18', 1500, 'Cash'),
            (12, '2026-05-18', 2200, 'GCash'),
            (13, '2026-05-19', 1800, 'Cash'),
            (14, '2026-05-19', 2500, 'Cash'),
            (15, '2026-05-20', 1700, 'GCash'),
            (16, '2026-05-20', 2000, 'Cash'),
            (17, '2026-05-21', 1600, 'GCash'),
            (18, '2026-05-21', 1900, 'Cash'),
            (19, '2026-05-22', 2100, 'GCash'),
            (20, '2026-05-22', 2300, 'Cash')
        ");
    }
}