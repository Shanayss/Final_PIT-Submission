<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
        INSERT INTO bill_items (bill_id,item_type,description,quantity,unit_price,total)
        VALUES
        (1, 'Room', 'General Ward', 3, 1500, 4500),
        (2, 'Treatment', 'ICU Monitoring', 1, 5000, 5000),
        (2, 'Service', 'Lab Test', 1, 3500, 3500),
        (3, 'Service', 'Consultation', 2, 1500, 3000),
        (4, 'Room', 'Dialysis Ward', 2, 2500, 5000),
        (4, 'Treatment', 'Dialysis', 1, 1500, 1500),
        (5, 'Room', 'Emergency Ward', 2, 3000, 6000),
        (5, 'Service', 'X-Ray', 1, 1200, 1200),
        (6, 'Treatment', 'Surgery', 1, 9500, 9500),
        (7, 'Room', 'Rehab Ward', 2, 1800, 3600),
        (7, 'Treatment', 'Physical Therapy', 2, 800, 1600),
        (8, 'Treatment', 'Cardiac Monitoring', 1, 5500, 5500),
        (8, 'Service', 'ECG', 1, 2600, 2600),
        (9, 'Room', 'Neurology Ward', 1, 4000, 4000),
        (10, 'Room', 'Recovery Ward', 1, 3500, 3500),       
        
        (11, 'Service', 'Consultation', 1, 1500, 1500),
        (12, 'Service', 'Consultation + Lab', 1, 2200, 2200),
        (13, 'Service', 'Checkup', 1, 1800, 1800),
        (14, 'Service', 'Emergency Visit', 1, 2500, 2500),
        (15, 'Service', 'Follow-up', 1, 1700, 1700),
        (16, 'Service', 'Consultation', 1, 2000, 2000),
        (17, 'Service', 'Checkup', 1, 1600, 1600),
        (18, 'Service', 'Lab Test', 1, 1900, 1900),
        (19, 'Service', 'Consultation', 1, 2100, 2100),
        (20, 'Service', 'Final Checkup', 1, 2300, 2300)
    ");
    }
}