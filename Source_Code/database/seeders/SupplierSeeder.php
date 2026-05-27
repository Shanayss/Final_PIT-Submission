<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO suppliers (
            supplier_name,
            address, 
            telephone, 
            fax
            )
            VALUES
            ('Cagayan Medical Supplies', 'Velez St, CDO', '088-856-1234', '088-856-1235'),
            ('Northern Mindanao Pharma', 'Lapasan, CDO', '088-231-5566', '088-231-5567'),
            ('Medi-Link Distro', 'Gusa Highway, CDO', '0917-555-8899', '0917-555-8900'),
            ('Zamboanga Med-Tech', 'Divisoria, CDO', '088-880-9900', '088-880-9901'),
            ('Global Health Corp', 'Makati City, Manila', '02-888-0000', '02-888-0001'),
            ('Cebu Medical Hub', 'Mandaue City, Cebu', '032-412-3344', '032-412-3345'),
            ('Oro Surgical Solutions', 'Carmen, CDO', '088-858-7788', '088-858-7789'),
            ('Vital Care Trading', 'Nazareth, CDO', '0922-444-5566', '0922-444-5567'),
            ('St. Jude Medical Supplies', 'Bulua, CDO', '088-321-4455', '088-321-4456'),
            ('HealthFirst Phils', 'Quezon City, Manila', '02-775-1122', '02-775-1123'),
            ('Pharma-North Logistics', 'Kauswagan, CDO', '088-881-2233', '088-881-2234'),
            ('LifeLine Equipment', 'Pasig City, Manila', '02-998-3344', '02-998-3345'),
            ('Southern Med Sales', 'Davao City', '082-221-8899', '082-221-8900'),
            ('Emerald Medical Gear', 'Patag, CDO', '0905-123-4567', '0905-123-4568'),
            ('Prime Care Distro', 'Iponan, CDO', '088-851-9900', '088-851-9901'),
            ('Apex Bio-Medical', 'Taguig City, Manila', '02-556-7788', '02-556-7789'),
            ('Reliable Pharma', 'Macasandig, CDO', '088-857-1122', '088-857-1123'),
            ('Metro Med Mart', 'Divisoria, CDO', '088-231-4400', '088-231-4401'),
            ('Island Medical Inc', 'Mactan, Cebu', '032-340-1122', '032-340-1123'),
            ('Well-Stock Supplies', 'Puerto, CDO', '0936-777-8899', '0936-777-8900');
        ");
    }
}