<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO appointments (
                patient_number,
                clinic_number,
                staff_number,
                appointment_date,
                appointment_time,
                examination_room,
                status
            ) VALUES

            ('P00001', '105', 'S010', '2026-05-18', '09:00:00', 'E001', 'Completed'),
            ('P00002', '101', 'S031', '2026-05-27', '10:00:00', 'E002', 'Scheduled'),
            ('P00003', '110', 'S053', '2026-05-18', '11:00:00', 'E003', 'Completed'),
            ('P00004', '103', 'S072', '2026-05-19', '09:30:00', 'E004', 'Completed'),
            ('P00005', '107', 'S073', '2026-05-19', '10:30:00', 'E005', 'Completed'),
            ('P00006', '102', 'S010', '2026-05-19', '11:30:00', 'E006', 'Completed'),
            ('P00007', '109', 'S031', '2026-05-20', '09:00:00', 'E007', 'Completed'),
            ('P00008', '104', 'S053', '2026-05-20', '10:00:00', 'E008', 'Completed'),
            ('P00009', '106', 'S072', '2026-05-20', '11:00:00', 'E009', 'Completed'),
            ('P00010', '108', 'S073', '2026-05-21', '09:30:00', 'E011', 'Completed'),
            ('P00011', '102', 'S010', '2026-05-21', '10:30:00', 'E012', 'Completed'),
            ('P00012', '110', 'S031', '2026-05-21', '11:30:00', 'E013', 'Completed'),
            ('P00013', '105', 'S053', '2026-05-21', '09:00:00', 'E014', 'Completed'),
            ('P00014', '101', 'S072', '2026-05-21', '10:00:00', 'E015', 'Completed'),
            ('P00015', '107', 'S073', '2026-05-21', '11:00:00', 'E016', 'Completed'),
            ('P00016', '104', 'S010', '2026-05-21', '09:30:00', 'E016', 'Completed'),
            ('P00017', '108', 'S031', '2026-05-21', '10:30:00', 'E017', 'Completed'),
            ('P00018', '103', 'S053', '2026-05-21', '11:30:00', 'E018', 'Completed'),
            ('P00019', '106', 'S072', '2026-05-21', '09:00:00', 'E019', 'Completed'),
            ('P00020', '109', 'S073', '2026-05-21', '10:00:00', 'E020', 'Completed');
        ");
    }
}