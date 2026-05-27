<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrugsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO drugs (
                drug_number,
                drug_name,
                description,
                dosage,
                method_of_admin,
                quantity_of_stock,
                reorder_level,
                cost_per_unit
            ) VALUES

            ('D001', 'Paracetamol', 'Pain Relief', '500mg', 'Oral', 500, 100, 5.50),
            ('D002', 'Amoxicillin', 'Antibiotic', '250mg', 'Oral', 300, 50, 12.00),
            ('D003', 'Ibuprofen', 'Anti-inflammatory', '200mg', 'Oral', 400, 80, 8.50),
            ('D004', 'Metformin', 'Diabetes', '500mg', 'Oral', 600, 100, 15.00),
            ('D005', 'Amlodipine', 'Blood Pressure', '5mg', 'Oral', 450, 50, 18.00),
            ('D006', 'Salbutamol', 'Asthma', '100mcg', 'Inhaler', 100, 20, 250.00),
            ('D007', 'Omeprazole', 'Acid Reflux', '20mg', 'Oral', 350, 60, 22.00),
            ('D008', 'Cetirizine', 'Allergy', '10mg', 'Oral', 400, 100, 7.00),
            ('D009', 'Azithromycin', 'Antibiotic', '500mg', 'Oral', 200, 30, 45.00),
            ('D010', 'Losartan', 'Blood Pressure', '50mg', 'Oral', 500, 100, 14.00),
            ('D011', 'Atorvastatin', 'Cholesterol', '20mg', 'Oral', 300, 50, 35.00),
            ('D012', 'Prednisone', 'Steroid', '10mg', 'Oral', 150, 20, 10.00),
            ('D013', 'Hydrochlorothiazide', 'Diuretic', '25mg', 'Oral', 350, 60, 9.00),
            ('D014', 'Alprazolam', 'Anxiety', '0.5mg', 'Oral', 100, 20, 50.00),
            ('D015', 'Furosemide', 'Diuretic', '40mg', 'Oral', 200, 40, 11.00),
            ('D016', 'Warfarin', 'Blood Thinner', '5mg', 'Oral', 150, 30, 30.00);
        ");
    }
}