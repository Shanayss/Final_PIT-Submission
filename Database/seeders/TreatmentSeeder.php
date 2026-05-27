<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TreatmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO treatments (
                patient_number,
                diagnosis_id,
                staff_number,
                procedure_name,
                treatment_date,
                treatment_time,
                results
            ) VALUES

            ('P00001', 1, 'S001', 'Intravenous Fluid Administration','2026-05-10', '08:30:00', 'First dose given. No allergic reaction.'),
            ('P00002', 2, 'S002', 'Blood Pressure Monitoring and Counseling', '2026-05-10', '07:45:00', 'Blood pressure stabilized.'),
            ('P00003', 3, 'S003', 'Nebulization Therapy', '2026-05-11', '12:00:00', 'Breathing improved after treatment.'),
            ('P00004', 4, 'S004', 'Ankle X-ray and Cold Compress Application', '2026-05-11', '20:15:00', 'Pain reduced after treatment.'),
            ('P00005', 5, 'S005', 'Blood Sugar Level Testing (FBS)', '2026-05-12', '09:00:00', 'Oral treatment tolerated well.'),
            ('P00006', 6, 'S006', 'Oxygen Therapy and Rest', '2026-05-13', '10:30:00', 'Condition improved.'),
            ('P00007', 7, 'S007', 'Chest X-ray and Sputum Test', '2026-05-13', '14:20:00', 'Patient stable after procedure.'),
            ('P00008', 8, 'S008', 'Allergy Skin Prick Test', '2026-05-14', '11:00:00', 'No severe allergic reaction detected.'),
            ('P00009', 9, 'S009', 'Lumbar Physical Therapy Session', '2026-05-14', '16:00:00', 'Pain reduced after session.'),
            ('P00010', 10,'S010', 'Urinalysis and Antibiotic Injection', '2026-05-15', '09:30:00', 'Infection improving.'),
            ('P00011', 11, 'S011', 'CBC and Iron Infusion', '2026-05-15', '10:15:00', 'Blood levels improving.'),
            ('P00012', 12, 'S012', 'Throat Swab Culture', '2026-05-16', '13:00:00', 'Awaiting lab results.'),
            ('P00013', 13, 'S013', 'Topical Ointment Application', '2026-05-16', '14:45:00', 'Wound healing properly.'),
            ('P00014', 14, 'S014', 'Emergency Nebulization', '2026-05-17', '16:30:00', 'Breathing stabilized.'),
            ('P00015', 15, 'S015', 'Endoscopy Procedure', '2026-05-17', '08:00:00', 'No complications found.'),
            ('P00016', 16, 'S016', 'Eye Irrigation and Cleaning', '2026-05-18', '09:45:00', 'Eye irritation reduced.'),
            ('P00017', 17, 'S017', 'Psychological Assessment', '2026-05-18', '11:15:00', 'Patient cooperative.'),
            ('P00018', 18, 'S018', 'Vital Signs Monitoring', '2026-05-19', '14:00:00', 'Vitals stable.'),
            ('P00019', 19, 'S019', 'Thyroid Function Test', '2026-05-19', '15:30:00', 'Results pending.'),
            ('P00020', 20, 'S020', 'Nutritional Counseling', '2026-05-20', '09:00:00', 'Patient responding well.');
        ");
    }
}