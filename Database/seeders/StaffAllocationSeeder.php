<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffAllocationSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO staff_allocations (
                staff_number,
                ward_number,
                role_for_week,
                shift,
                week_start_date
            ) VALUES

            -- WARD 1
            ('S012',1,'Charge Nurse','Early','2026-05-18'),
            ('S004',1,'Senior Nurse','Early','2026-05-18'),
            ('S011',1,'Junior Nurse','Early','2026-05-18'),
            ('S002',1,'Doctor','Early','2026-05-18'),
            ('S005',1,'Auxiliary Staff','Early','2026-05-18'),
            ('S030',1,'Charge Nurse','Late','2026-05-18'),
            ('S028',1,'Doctor','Late','2026-05-18'),
            ('S002',1,'Doctor','Night','2026-05-18'),

            -- WARD 2
            ('S019',2,'Charge Nurse','Early','2026-05-18'),
            ('S015',2,'Senior Nurse','Early','2026-05-18'),
            ('S018',2,'Junior Nurse','Early','2026-05-18'),
            ('S010',2,'Consultant','Early','2026-05-18'),
            ('S016',2,'Auxiliary Staff','Early','2026-05-18'),
            ('S034',2,'Charge Nurse','Late','2026-05-18'),
            ('S038',2,'Doctor','Late','2026-05-18'),

            -- WARD 3
            ('S020',3,'Charge Nurse','Early','2026-05-18'),
            ('S024',3,'Senior Nurse','Early','2026-05-18'),
            ('S023',3,'Junior Nurse','Early','2026-05-18'),
            ('S013',3,'Doctor','Early','2026-05-18'),
            ('S025',3,'Auxiliary Staff','Early','2026-05-18'),
            ('S039',3,'Charge Nurse','Late','2026-05-18'),
            ('S046',3,'Doctor','Late','2026-05-18'),

            -- WARD 4
            ('S021',4,'Charge Nurse','Early','2026-05-18'),
            ('S029',4,'Senior Nurse','Early','2026-05-18'),
            ('S027',4,'Junior Nurse','Early','2026-05-18'),
            ('S031',4,'Consultant','Early','2026-05-18'),
            ('S033',4,'Auxiliary Staff','Early','2026-05-18'),
            ('S042',4,'Charge Nurse','Late','2026-05-18'),
            ('S058',4,'Doctor','Late','2026-05-18'),

            -- WARD 5
            ('S026',5,'Charge Nurse','Early','2026-05-18'),
            ('S035',5,'Senior Nurse','Early','2026-05-18'),
            ('S032',5,'Junior Nurse','Early','2026-05-18'),
            ('S017',5,'Doctor','Early','2026-05-18'),
            ('S045',5,'Auxiliary Staff','Early','2026-05-18'),
            ('S047',5,'Charge Nurse','Night','2026-05-18'),
            ('S013',5,'Doctor','Night','2026-05-18'),
            ('S043',5,'Junior Nurse','Night','2026-05-18'),

            -- WARD 6
            ('S030',6,'Charge Nurse','Early','2026-05-18'),
            ('S044',6,'Senior Nurse','Early','2026-05-18'),
            ('S036',6,'Junior Nurse','Early','2026-05-18'),
            ('S053',6,'Consultant','Early','2026-05-18'),
            ('S052',6,'Auxiliary Staff','Early','2026-05-18'),
            ('S050',6,'Charge Nurse','Night','2026-05-18'),
            ('S048',6,'Junior Nurse','Night','2026-05-18'),

            -- WARD 7
            ('S034',7,'Charge Nurse','Early','2026-05-18'),
            ('S049',7,'Senior Nurse','Early','2026-05-18'),
            ('S040',7,'Junior Nurse','Early','2026-05-18'),
            ('S022',7,'Doctor','Early','2026-05-18'),
            ('S057',7,'Auxiliary Staff','Early','2026-05-18'),
            ('S055',7,'Charge Nurse','Night','2026-05-18'),
            ('S051',7,'Junior Nurse','Night','2026-05-18'),

            -- WARD 8
            ('S039',8,'Charge Nurse','Early','2026-05-18'),
            ('S054',8,'Senior Nurse','Early','2026-05-18'),
            ('S043',8,'Junior Nurse','Early','2026-05-18'),
            ('S072',8,'Consultant','Early','2026-05-18'),
            ('S064',8,'Auxiliary Staff','Early','2026-05-18'),
            ('S059',8,'Charge Nurse','Night','2026-05-18'),
            ('S056',8,'Junior Nurse','Night','2026-05-18'),

            -- WARD 9
            ('S042',9,'Charge Nurse','Early','2026-05-18'),
            ('S061',9,'Senior Nurse','Early','2026-05-18'),
            ('S048',9,'Junior Nurse','Early','2026-05-18'),
            ('S028',9,'Doctor','Early','2026-05-18'),
            ('S062',9,'Charge Nurse','Night','2026-05-18'),
            ('S017',9,'Doctor','Night','2026-05-18'),
            ('S060',9,'Junior Nurse','Night','2026-05-18'),

            -- WARD 10
            ('S047',10,'Charge Nurse','Early','2026-05-18'),
            ('S067',10,'Senior Nurse','Early','2026-05-18'),
            ('S051',10,'Junior Nurse','Early','2026-05-18'),
            ('S073',10,'Consultant','Early','2026-05-18'),
            ('S065',10,'Charge Nurse','Night','2026-05-18'),
            ('S063',10,'Junior Nurse','Night','2026-05-18'),

            -- WARD 11
            ('S050',11,'Charge Nurse','Early','2026-05-18'),
            ('S004',11,'Senior Nurse','Early','2026-05-18'),
            ('S056',11,'Junior Nurse','Early','2026-05-18'),
            ('S038',11,'Doctor','Early','2026-05-18'),
            ('S068',11,'Charge Nurse','Night','2026-05-18'),
            ('S066',11,'Junior Nurse','Night','2026-05-18'),

            -- WARD 12
            ('S055',12,'Charge Nurse','Early','2026-05-18'),
            ('S015',12,'Senior Nurse','Early','2026-05-18'),
            ('S060',12,'Junior Nurse','Early','2026-05-18'),
            ('S046',12,'Doctor','Early','2026-05-18'),
            ('S070',12,'Charge Nurse','Night','2026-05-18'),
            ('S069',12,'Junior Nurse','Night','2026-05-18'),

            -- WARD 13
            ('S059',13,'Charge Nurse','Early','2026-05-18'),
            ('S024',13,'Senior Nurse','Early','2026-05-18'),
            ('S063',13,'Junior Nurse','Early','2026-05-18'),
            ('S058',13,'Doctor','Early','2026-05-18'),
            ('S071',13,'Charge Nurse','Night','2026-05-18'),

            -- WARD 14
            ('S062',14,'Charge Nurse','Early','2026-05-18'),
            ('S029',14,'Senior Nurse','Early','2026-05-18'),
            ('S066',14,'Junior Nurse','Early','2026-05-18'),
            ('S012',14,'Charge Nurse','Late','2026-05-18'),
            ('S002',14,'Doctor','Late','2026-05-18'),
            ('S027',14,'Junior Nurse','Late','2026-05-18'),

            -- WARD 15
            ('S065',15,'Charge Nurse','Early','2026-05-18'),
            ('S035',15,'Senior Nurse','Early','2026-05-18'),
            ('S069',15,'Junior Nurse','Early','2026-05-18'),
            ('S019',15,'Charge Nurse','Late','2026-05-18'),
            ('S013',15,'Doctor','Late','2026-05-18'),
            ('S032',15,'Junior Nurse','Late','2026-05-18'),

            -- WARD 16
            ('S068',16,'Charge Nurse','Early','2026-05-18'),
            ('S044',16,'Senior Nurse','Early','2026-05-18'),
            ('S011',16,'Junior Nurse','Early','2026-05-18'),
            ('S020',16,'Charge Nurse','Late','2026-05-18'),
            ('S017',16,'Doctor','Late','2026-05-18'),
            ('S036',16,'Junior Nurse','Late','2026-05-18'),

            -- WARD 17
            ('S070',17,'Charge Nurse','Early','2026-05-18'),
            ('S049',17,'Senior Nurse','Early','2026-05-18'),
            ('S018',17,'Junior Nurse','Early','2026-05-18'),
            ('S021',17,'Charge Nurse','Late','2026-05-18'),
            ('S022',17,'Doctor','Late','2026-05-18'),
            ('S040',17,'Junior Nurse','Late','2026-05-18'),

            -- WARD 18
            ('S071',18,'Charge Nurse','Early','2026-05-18'),
            ('S054',18,'Senior Nurse','Early','2026-05-18'),
            ('S023',18,'Junior Nurse','Early','2026-05-18'),
            ('S010',18,'Consultant','Early','2026-05-18'),
            ('S022',18,'Doctor','Late','2026-05-18'),
            ('S016',18,'Auxiliary Staff','Late','2026-05-18');
        ");
    }
}