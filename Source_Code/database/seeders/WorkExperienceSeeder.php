<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkExperienceSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO work_experiences (
                staff_number,
                position_held,
                start_date,
                finish_date,
                name_of_organization
            ) VALUES

            ('S001', 'Medical Director', '2013-01-10', '2020-12-31', 'King''s College Hospital London'),
            ('S002', 'Resident Physician', '2016-01-01', '2021-12-31', 'University College London Hospital'),
            ('S003', 'HR Assistant', '2011-05-05', '2018-02-28', 'University of Birmingham HR Department'),
            ('S004', 'Staff Nurse', '2015-01-15', '2022-06-30', 'Royal Infirmary of Edinburgh'),
            ('S005', 'Healthcare Assistant', '2018-01-01', '2023-12-31', 'Leeds Teaching Hospitals NHS Trust'),
            ('S006', 'Senior Surgeon', '2011-01-01', '2023-10-15', 'St Thomas'' Hospital London'),
            ('S007', 'Cashier', '2019-01-01', '2023-01-01', 'Glasgow Royal Infirmary Billing Office'),
            ('S008', 'Cashier', '2013-01-01', '2020-05-01', 'NHS London Finance Department'),
            ('S009', 'HR Officer', '2006-01-01', '2022-12-31', 'Manchester City Council HR'),
            ('S010', 'Consultant Physician', '2021-07-01', '2022-06-30', 'University Hospitals Birmingham'),
            ('S011', 'Junior Nurse', '2015-01-01', '2022-03-01', 'Edinburgh Royal Infirmary'),
            ('S012', 'Charge Nurse', '2017-01-01', '2019-12-31', 'Leeds General Infirmary'),
            ('S013', 'Doctor', '2012-01-01', '2020-12-31', 'Liverpool University Hospitals NHS Foundation Trust'),
            ('S014', 'Cashier', '2018-05-01', '2022-04-30', 'NHS Glasgow Finance Unit'),
            ('S015', 'Senior Nurse', '2014-01-01', '2021-08-01', 'King''s College Hospital London'),
            ('S016', 'Medical Researcher', '2009-01-01', '2022-01-01', 'National Health Service Research UK'),
            ('S017', 'General Practitioner', '2008-01-01', '2024-01-01', 'Birmingham Community Health Centre'),
            ('S018', 'Junior Nurse', '2009-01-01', '2021-12-31', 'Edinburgh Royal Infirmary'),
            ('S019', 'Charge Nurse', '2016-01-01', '2023-05-01', 'Leeds Teaching Hospitals NHS Trust'),
            ('S020', 'Lab Technician', '2007-01-01', '2022-09-01', 'NHS Laboratory Services Liverpool'),
            ('S021', 'Charge Nurse', '2015-06-01', '2023-06-01', 'Leeds General Infirmary'),
            ('S022', 'Doctor', '2014-01-10', '2022-12-31', 'Manchester Royal Infirmary'),
            ('S023', 'Junior Nurse', '2019-03-15', '2023-12-31', 'Queen Elizabeth Hospital Birmingham'),
            ('S024', 'Senior Nurse', '2013-04-01', '2022-06-30', 'Royal Infirmary of Edinburgh'),
            ('S025', 'Healthcare Assistant', '2017-05-01', '2024-01-01', 'Leeds Community Health Services'),
            ('S026', 'Charge Nurse', '2016-02-10', '2023-08-20', 'Glasgow Royal Infirmary'),
            ('S027', 'Junior Nurse', '2020-01-15', '2024-01-01', 'Leeds Teaching Hospitals NHS Trust'),
            ('S028', 'Consultant Physician', '2012-06-01', '2023-12-31', 'King''s College Hospital London'),
            ('S029', 'Senior Nurse', '2011-03-10', '2021-12-31', 'Manchester University NHS Foundation Trust'),
            ('S030', 'Charge Nurse', '2015-07-01', '2023-10-01', 'Birmingham Women''s and Children''s Hospital'),
            ('S031', 'Consultant Cardiologist', '2011-01-01', '2023-12-31', 'Liverpool Heart and Chest Hospital'),
            ('S032', 'Junior Nurse', '2021-01-10', '2024-01-01', 'Edinburgh Royal Infirmary'),
            ('S033', 'Healthcare Assistant', '2018-02-01', '2023-06-01', 'Leeds City Care Services'),
            ('S034', 'Charge Nurse', '2014-03-01', '2023-09-01', 'Glasgow Royal Infirmary'),
            ('S035', 'Senior Nurse', '2010-05-01', '2022-12-31', 'University Hospitals Leeds'),
            ('S036', 'Junior Nurse', '2022-01-01', '2024-01-01', 'Birmingham Community Health Centre'),
            ('S037', 'Cashier', '2016-06-01', '2023-01-01', 'London NHS Finance Office'),
            ('S038', 'Doctor', '2013-04-01', '2023-12-31', 'Leeds General Infirmary'),
            ('S039', 'Charge Nurse', '2012-05-01', '2023-06-01', 'Edinburgh Royal Infirmary'),
            ('S040', 'Junior Nurse', '2020-03-01', '2024-01-01', 'Manchester Health Trust'),
            ('S041', 'Personnel Officer', '2012-01-01', '2023-12-31', 'Birmingham HR Department'),
            ('S042', 'Charge Nurse', '2015-02-01', '2023-09-01', 'Leeds Teaching Hospitals NHS Trust'),
            ('S043', 'Junior Nurse', '2021-06-01', '2024-01-01', 'London Health Clinic'),
            ('S044', 'Senior Nurse', '2013-03-01', '2022-12-31', 'Glasgow Royal Infirmary'),
            ('S045', 'Healthcare Assistant', '2018-07-01', '2023-12-31', 'Liverpool Community Health Services'),
            ('S046', 'Consultant Physician', '2012-05-01', '2024-01-01', 'Manchester Royal Infirmary'),
            ('S047', 'Charge Nurse', '2011-04-01', '2023-10-01', 'Queen Elizabeth Hospital Birmingham'),
            ('S048', 'Junior Nurse', '2019-06-01', '2024-01-01', 'Leeds General Infirmary'),
            ('S049', 'Senior Nurse', '2009-01-01', '2021-12-31', 'Royal Infirmary of Edinburgh'),
            ('S050', 'Charge Nurse', '2014-05-01', '2023-12-31', 'King''s College Hospital London'),
            ('S051', 'Junior Nurse', '2020-02-01', '2024-01-01', 'Manchester Community Health Services'),
            ('S052', 'Healthcare Assistant', '2017-03-01', '2023-08-01', 'Leeds City Care Services'),
            ('S053', 'Consultant Orthopedic Surgeon', '2010-01-01', '2023-12-31', 'Birmingham Orthopedic Hospital'),
            ('S054', 'Senior Nurse', '2012-06-01', '2023-05-01', 'Edinburgh Royal Infirmary'),
            ('S055', 'Charge Nurse', '2011-07-01', '2023-12-31', 'Glasgow Royal Infirmary'),
            ('S056', 'Junior Nurse', '2021-05-01', '2024-01-01', 'Leeds Teaching Hospitals NHS Trust'),
            ('S057', 'Healthcare Assistant', '2016-02-01', '2023-06-01', 'London Community Health Services'),
            ('S058', 'Doctor', '2013-03-01', '2023-12-31', 'Manchester Royal Infirmary'),
            ('S059', 'Charge Nurse', '2012-04-01', '2023-10-01', 'Birmingham City Hospital'),
            ('S060', 'Junior Nurse', '2020-07-01', '2024-01-01', 'Edinburgh Royal Infirmary'),
            ('S061', 'Senior Nurse', '2010-05-01', '2022-12-31', 'Leeds General Infirmary'),
            ('S062', 'Charge Nurse', '2014-06-01', '2023-12-31', 'King''s College Hospital London'),
            ('S063', 'Junior Nurse', '2022-01-01', '2024-01-01', 'Manchester Community Health Services'),
            ('S064', 'Healthcare Assistant', '2018-02-01', '2023-07-01', 'Glasgow Care Services'),
            ('S065', 'Charge Nurse', '2013-05-01', '2023-11-01', 'Leeds Teaching Hospitals NHS Trust'),
            ('S066', 'Junior Nurse', '2020-03-01', '2024-01-01', 'Birmingham Community Health Centre'),
            ('S067', 'Senior Nurse', '2011-06-01', '2023-06-01', 'Edinburgh Royal Infirmary'),
            ('S068', 'Charge Nurse', '2012-07-01', '2023-12-31', 'London NHS Trust'),
            ('S069', 'Junior Nurse', '2023-01-01', '2024-01-01', 'Manchester Health Services'),
            ('S070', 'Charge Nurse', '2011-04-01', '2023-10-01', 'Birmingham City Hospital'),
            ('S071', 'Charge Nurse', '2014-06-01', '2023-12-31', 'Leeds General Infirmary'),
            ('S072', 'Consultant Neurologist', '2011-01-01', '2024-01-01', 'Edinburgh Royal Infirmary'),
            ('S073', 'Consultant Pulmonologist', '2010-01-01', '2024-01-01', 'Manchester Royal Infirmary');
        ");
    }
}