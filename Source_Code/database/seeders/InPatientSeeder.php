<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InPatientSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO in_patients (
                patient_number,
                ward_number,
                bed_number,
                date_placed_on_waiting_list,
                expected_stay_days,
                date_admitted,
                date_expected_leave,
                date_actual_leave
            )
            VALUES
            ('P00001',1,1,'2026-05-01',5,'2026-05-01','2026-05-06','2026-05-06'),
            ('P00002',2,19,'2026-05-01',10,'2026-05-01','2026-05-11','2026-05-11'),
            ('P00003',3,33,'2026-05-02',10,'2026-05-02','2026-05-12', NULL),
            ('P00004',4,53,'2026-05-02',2,'2026-05-02','2026-05-04','2026-05-04'),
            ('P00005',5,65,'2026-05-03',14,'2026-05-03','2026-05-17', NULL),
            ('P00006',6,83,'2026-05-03',5,'2026-05-03','2026-05-08', NULL),
            ('P00007',7,99,'2026-05-04',4,'2026-05-04','2026-05-08','2026-05-08'),
            ('P00008',8,130,'2026-05-04',6,'2026-05-04','2026-05-10','2026-05-10'),
            ('P00009',9,147,'2026-05-04',6,'2026-05-04','2026-05-10','2026-05-10'),
            ('P00010',10,155,'2026-05-04',1,'2026-05-04','2026-05-05', NULL)

        ");
    }
}
