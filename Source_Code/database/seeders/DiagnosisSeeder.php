<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiagnosisSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO diagnoses (
                appointment_id,
                staff_number,
                patient_number,
                diagnosis_details,
                diagnosis_date,
                notes
            ) VALUES

            (1, 'S001', 'P00001', 'Acute Gastritis', '2026-05-10', 'Patient advised to avoid spicy food.'),
            (2, 'S006', 'P00002', 'Hypertension Stage 1', '2026-05-10', 'Prescribed Amlodipine.'),
            (3, 'S010', 'P00003', 'Common Cold', '2026-05-11', 'Rest and hydration advised.'),
            (4, 'S013', 'P00004', 'Sprained Ankle', '2026-05-11', 'Apply ice and elevate.'),
            (6, 'S002', 'P00005', 'Type 2 Diabetes', '2026-05-12', 'Monitor blood sugar daily.'),

            (7, 'S020', 'P00006', 'Migraine', '2026-05-13', 'Avoid bright lights and loud noises.'),
            (8, 'S006', 'P00007', 'Bronchitis', '2026-05-13', 'Completed 7-day course of antibiotics.'),
            (9, 'S010', 'P00008', 'Allergic Rhinitis', '2026-05-14', 'Prescribed antihistamines.'),
            (10, 'S013', 'P00009', 'Lower Back Pain', '2026-05-14', 'Recommend physical therapy.'),
            (11, 'S017', 'P00010', 'Urinary Tract Infection', '2026-05-15', 'Increase fluid intake.'),

            (12, 'S020', 'P00011', 'Iron Deficiency Anemia', '2026-05-15', 'Prescribed iron supplements.'),
            (13, 'S002', 'P00012', 'Tonsillitis', '2026-05-16', 'Possible surgery if recurring.'),
            (14, 'S006', 'P00013', 'Dermatitis', '2026-05-16', 'Prescribed topical steroid cream.'),
            (15, 'S010', 'P00014', 'Asthma Attack', '2026-05-17', 'Nebulized in ER, prescribed inhaler.'),
            (16, 'S013', 'P00015', 'Peptic Ulcer', '2026-05-17', 'Start on proton-pump inhibitors.'),

            (17, 'S017', 'P00016', 'Conjunctivitis', '2026-05-18', 'Use antibiotic eye drops.'),
            (18, 'S020', 'P00017', 'Generalized Anxiety', '2026-05-18', 'Referral to counseling.'),
            (19, 'S002', 'P00018', 'Influenza A', '2026-05-19', 'Self-isolation for 5 days.'),
            (20, 'S006', 'P00019', 'Hyperthyroidism', '2026-05-19', 'Blood tests scheduled for next week.'),
            (1, 'S002', 'P00020', 'Vitamin D Deficiency', '2026-05-20', 'Supplementation recommended.');
        ");
    }
}