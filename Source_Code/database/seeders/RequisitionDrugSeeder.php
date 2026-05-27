<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionDrugSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('requisition_drugs')->insert([
            ['requisition_number' => 1, 'drug_number' => 'D001', 'quantity_required' => 100],
            ['requisition_number' => 1, 'drug_number' => 'D002', 'quantity_required' => 50],
            ['requisition_number' => 2, 'drug_number' => 'D003', 'quantity_required' => 30],
            ['requisition_number' => 3, 'drug_number' => 'D005', 'quantity_required' => 10],
            ['requisition_number' => 4, 'drug_number' => 'D006', 'quantity_required' => 20],
            ['requisition_number' => 5, 'drug_number' => 'D007', 'quantity_required' => 50],
            ['requisition_number' => 6, 'drug_number' => 'D008', 'quantity_required' => 5],
            ['requisition_number' => 7, 'drug_number' => 'D009', 'quantity_required' => 40],
            ['requisition_number' => 8, 'drug_number' => 'D010', 'quantity_required' => 30],
            ['requisition_number' => 9, 'drug_number' => 'D011', 'quantity_required' => 100],
            ['requisition_number' => 10, 'drug_number' => 'D012', 'quantity_required' => 60],
            ['requisition_number' => 11, 'drug_number' => 'D013', 'quantity_required' => 5],
            ['requisition_number' => 11, 'drug_number' => 'D014', 'quantity_required' => 10],
            ['requisition_number' => 13, 'drug_number' => 'D015', 'quantity_required' => 20],
        ]);
    }
}
