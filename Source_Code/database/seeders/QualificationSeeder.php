<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QualificationSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO qualifications (
                staff_number,
                qualification_type,
                qualification_date,
                institution_name
            ) VALUES

            ('S001', 'Doctor of Medicine', '2012-04-15', 'King''s College London'),
            ('S002', 'Doctor of Medicine', '2015-05-20', 'University of Manchester'),
            ('S003', 'BS Human Resource Management', '2010-03-25', 'University of Birmingham'),
            ('S004', 'BS Nursing', '2014-03-28', 'University of Edinburgh'),
            ('S005', 'Caregiving NC II', '2017-04-02', 'Leeds City College'),
            ('S006', 'Doctor of Medicine - General Surgery', '2010-06-10', 'University of Liverpool'),
            ('S007', 'BS Accountancy', '2018-05-15', 'University of Glasgow'),
            ('S008', 'BS Business Administration', '2012-03-30', 'London South Bank University'),
            ('S009', 'BS Human Resource Management', '2005-05-12', 'University of Manchester'),
            ('S010', 'Doctor of Medicine', '2021-06-30', 'University of Birmingham'),
            ('S011', 'BS Nursing', '2014-04-18', 'Edinburgh Napier University'),
            ('S012', 'BS Nursing', '2016-03-22', 'University of Leeds'),
            ('S013', 'Doctor of Medicine', '2011-03-15', 'University of Liverpool'),
            ('S014', 'BS Accountancy', '2017-04-10', 'University of Glasgow'),
            ('S015', 'BS Nursing', '2013-05-05', 'King''s College London'),
            ('S016', 'Caregiving NC II', '2008-11-20', 'The Manchester College'),
            ('S017', 'Doctor of Medicine', '2007-05-15', 'University of Birmingham'),
            ('S018', 'BS Nursing', '2008-03-12', 'University of Edinburgh'),
            ('S019', 'BS Nursing', '2015-03-18', 'University of Leeds'),
            ('S020', 'BS Nursing', '2006-04-25', 'University of Liverpool'),
            ('S021', 'BS Nursing', '2014-06-10', 'University of Leeds'),
            ('S022', 'Doctor of Medicine', '2013-05-18', 'University of Manchester'),
            ('S023', 'BS Nursing', '2018-04-22', 'University of Birmingham'),
            ('S024', 'BS Nursing', '2012-03-15', 'University of Edinburgh'),
            ('S025', 'Caregiving NC II', '2016-07-01', 'Leeds City College'),
            ('S026', 'BS Nursing', '2015-06-12', 'University of Glasgow'),
            ('S027', 'BS Nursing', '2019-05-20', 'University of Leeds'),
            ('S028', 'Doctor of Medicine', '2011-03-11', 'King''s College London'),
            ('S029', 'BS Nursing', '2010-04-08', 'University of Manchester'),
            ('S030', 'BS Nursing', '2014-09-14', 'University of Birmingham'),
            ('S031', 'Doctor of Medicine - Cardiology', '2010-06-21', 'University of Liverpool'),
            ('S032', 'BS Nursing', '2020-05-10', 'Edinburgh Napier University'),
            ('S033', 'Caregiving NC II', '2017-02-28', 'The Manchester College'),
            ('S034', 'BS Nursing', '2013-06-17', 'University of Glasgow'),
            ('S035', 'BS Nursing', '2009-03-25', 'University of Leeds'),
            ('S036', 'BS Nursing', '2021-04-05', 'University of Birmingham'),
            ('S037', 'BS Accountancy', '2015-05-12', 'London South Bank University'),
            ('S038', 'Doctor of Medicine', '2012-06-18', 'University of Leeds'),
            ('S039', 'BS Nursing', '2011-03-19', 'University of Edinburgh'),
            ('S040', 'BS Nursing', '2019-08-22', 'University of Manchester'),
            ('S041', 'BS Human Resource Management', '2010-05-15', 'University of Birmingham'),
            ('S042', 'BS Nursing', '2014-04-10', 'University of Leeds'),
            ('S043', 'BS Nursing', '2020-06-30', 'University of London'),
            ('S044', 'BS Nursing', '2012-03-21', 'University of Glasgow'),
            ('S045', 'Caregiving NC II', '2018-07-11', 'Leeds City College'),
            ('S046', 'Doctor of Medicine - Internal Medicine', '2011-05-25', 'University of Manchester'),
            ('S047', 'BS Nursing', '2010-04-14', 'University of Birmingham'),
            ('S048', 'BS Nursing', '2018-06-09', 'University of Leeds'),
            ('S049', 'BS Nursing', '2008-03-17', 'University of Edinburgh'),
            ('S050', 'BS Nursing', '2013-05-30', 'King''s College London'),
            ('S051', 'BS Nursing', '2019-06-20', 'University of Manchester'),
            ('S052', 'Caregiving NC II', '2017-05-11', 'The Manchester College'),
            ('S053', 'Doctor of Medicine - Orthopedics', '2009-04-18', 'University of Birmingham'),
            ('S054', 'BS Nursing', '2011-07-22', 'University of Edinburgh'),
            ('S055', 'BS Nursing', '2010-05-15', 'University of Glasgow'),
            ('S056', 'BS Nursing', '2020-06-18', 'University of Leeds'),
            ('S057', 'Caregiving NC II', '2016-03-14', 'Leeds City College'),
            ('S058', 'Doctor of Medicine', '2012-05-10', 'University of Manchester'),
            ('S059', 'BS Nursing', '2011-04-12', 'University of Birmingham'),
            ('S060', 'BS Nursing', '2019-07-19', 'University of Edinburgh'),
            ('S061', 'BS Nursing', '2009-06-21', 'University of Leeds'),
            ('S062', 'BS Nursing', '2013-05-16', 'King''s College London'),
            ('S063', 'BS Nursing', '2021-06-11', 'University of Manchester'),
            ('S064', 'Caregiving NC II', '2017-02-20', 'The Manchester College'),
            ('S065', 'BS Nursing', '2012-05-14', 'University of Leeds'),
            ('S066', 'BS Nursing', '2019-03-18', 'University of Birmingham'),
            ('S067', 'BS Nursing', '2010-06-22', 'University of Edinburgh'),
            ('S068', 'BS Nursing', '2011-07-15', 'University of London'),
            ('S069', 'BS Nursing', '2022-05-10', 'University of Manchester'),
            ('S070', 'BS Nursing', '2010-04-09', 'University of Birmingham'),
            ('S071', 'BS Nursing', '2013-06-17', 'University of Leeds'),
            ('S072', 'Doctor of Medicine - Neurology', '2010-06-15', 'University of Edinburgh'),
            ('S073', 'Doctor of Medicine - Pulmonology', '2009-05-20', 'University of Manchester');
        ");
    }
}