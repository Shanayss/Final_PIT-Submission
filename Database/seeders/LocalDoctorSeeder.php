<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalDoctorSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO local_doctors (
                clinic_number,
                full_name,
                address,
                telephone
            ) VALUES
            (101, 'James Anderson', '12 King Street, Manchester', '0161-856-1111'),
            (102, 'Emily Carter', '45 Oxford Road, Manchester', '0161-858-2222'),
            (103, 'Michael Johnson', '8 Baker Street, London', '020-856-3333'),
            (104, 'Sarah Williams', '102 Camden High Street, London', '020-231-4444'),
            (105, 'David Miller', '17 Queen''s Road, Birmingham', '0121-857-5555'),
            (106, 'Jessica Brown', '33 New Street, Birmingham', '0121-881-6666'),
            (107, 'Christopher Davis', '56 Princes Street, Edinburgh', '0131-700-1234'),
            (108, 'Ashley Martinez', '21 George Square, Glasgow', '0141-800-5678'),
            (109, 'Matthew Thompson', '9 High Street, Liverpool', '0151-856-9999'),
            (110, 'Olivia Garcia', '88 Church Street, Liverpool', '0151-231-0000')
        ");
    }
}