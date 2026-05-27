<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO items (
                item_number,
                item_name,
                description,
                quantity_of_stock,
                reorder_level,
                cost_per_unit
            ) VALUES

            ('I001', 'Surgical Gloves', 'Latex-free, Medium', 500, 100, 15.50),
            ('I002', 'Face Masks', '3-Ply Surgical Mask', 2000, 500, 5.00),
            ('I003', 'Syringe 5ml', 'Disposable with needle', 1000, 200, 12.00),
            ('I004', 'Cotton Balls', 'Sterile, 50pcs per pack', 300, 50, 45.00),
            ('I005', 'IV Cannula', 'Gage 22 Blue', 150, 30, 85.00),
            ('I006', 'Alcohol 70%', 'Isopropyl, 500ml', 100, 20, 75.00),
            ('I007', 'Gauze Pad', '4x4 Sterile', 400, 100, 25.00),
            ('I008', 'Medical Tape', 'Micropore 1 inch', 200, 40, 60.00),
            ('I009', 'Digital Thermometer', 'Fast read, battery incl.', 50, 10, 150.00),
            ('I010', 'Stethoscope', 'Dual head, black', 20, 5, 850.00),

            ('I011', 'Wheelchair', 'Standard Foldable', 10, 2, 4500.00),
            ('I012', 'Oxygen Tank', 'Portable 5lbs', 15, 3, 3200.00),
            ('I013', 'Bed Sheet', 'Hospital Grade, White', 100, 20, 250.00),
            ('I014', 'Patient Gown', 'Blue, Cotton', 120, 25, 180.00),
            ('I015', 'Disinfectant Spray', 'Hospital Grade 500ml', 60, 15, 350.00),

            ('I016', 'Hand Sanitizer', 'Gel type, 1 Liter', 40, 10, 220.00),
            ('I017', 'Nebulizer Kit', 'Adult size mask', 30, 10, 120.00),
            ('I018', 'Catheter', 'Foley Catheter Fr 16', 50, 15, 95.00),
            ('I019', 'Biohazard Bags', 'Yellow, Large', 200, 50, 10.00),
            ('I020', 'First Aid Kit', 'Travel size, basic', 25, 5, 450.00);
        ");
    }
}