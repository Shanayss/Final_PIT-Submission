<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO patients (
                patient_number,
                clinic_number,
                first_name,
                last_name,
                address,
                telephone,
                date_of_birth,
                sex,
                marital_status,
                date_registered
            ) VALUES
            ('P00001', 105, 'Alice', 'Brown', '12 Baker Street, London', '020-4456-6722', '2000-01-01', 'Female', 'Single', '2026-01-01'),
            ('P00002', 103, 'Bob', 'Johnson', '45 Queen''s Road, London', '020-5567-8890', '1995-02-02', 'Male', 'Married', '2026-01-02'),
            ('P00003', 110, 'Charlie', 'Deen', '78 High Street, Manchester', '0161-778-1203', '1990-03-03', 'Male', 'Single', '2026-01-03'),
            ('P00004', 101, 'Diana', 'Smith', '23 King Avenue, Edinburgh', '0131-334-4121', '2001-04-04', 'Female', 'Single', '2026-01-04'),
            ('P00005', 105, 'Ethan', 'Williams', '9 Victoria Lane, London', '020-9012-3345', '1998-05-05', 'Male', 'Married', '2026-01-05'),
            ('P00006', 102, 'Fiona', 'Gallagher', '101 Castle Road, Edinburgh', '0131-556-8890', '1993-06-06', 'Female', 'Married', '2026-01-06'),
            ('P00007', 108, 'George', 'Miller', '56 Greenfield Drive, Birmingham', '0121-778-9033', '1985-07-07', 'Male', 'Divorced', '2026-02-07'),
            ('P00008', 107, 'Hannah', 'Baker', '34 Church Street, Manchester', '0161-445-6677', '2002-08-08', 'Female', 'Single', '2026-02-08'),
            ('P00009', 104, 'Ian', 'Somerhalder', '88 Rosewood Avenue, Leeds', '0113-234-8891', '1982-09-09', 'Male', 'Married', '2026-02-09'),
            ('P00010', 109, 'Julia', 'Roberts', '17 Riverside Close, London', '020-7123-4567', '1975-10-10', 'Female', 'Married', '2026-02-10'),
            ('P00011', 106, 'Kevin', 'Hart', '65 Oakwood Street, Liverpool', '0151-334-2211', '1988-11-11', 'Male', 'Married', '2026-02-11'),
            ('P00012', 107, 'Luna', 'Brown', '42 Maple Crescent, Edinburgh', '0131-778-4412', '2003-12-12', 'Female', 'Single', '2026-03-12'),
            ('P00013', 110, 'Mike', 'Ross', '73 Wellington Road, London', '020-8832-7710', '1991-01-13', 'Male', 'Single', '2026-03-13'),
            ('P00014', 109, 'Nina', 'Dobrev', '29 Hilltop Avenue, Birmingham', '0121-998-1209', '1994-02-14', 'Female', 'Single', '2026-03-14'),
            ('P00015', 104, 'Oscar', 'Isaac', '90 Prince Street, Manchester', '0161-990-1188', '1983-03-15', 'Male', 'Married', '2026-03-15'),
            ('P00016', 103, 'Piper', 'Halliwell', '14 Elm Park Road, Leeds', '0113-667-4522', '1979-04-16', 'Female', 'Married', '2026-03-16'),
            ('P00017', 107, 'Quinn', 'Fabray', '51 Station Lane, London', '020-7946-8821', '1996-05-17', 'Female', 'Single', '2026-04-17'),
            ('P00018', 108, 'Riley', 'Reid', '36 Willow Drive, Edinburgh', '0131-990-3345', '1995-06-18', 'Female', 'Single', '2026-04-18'),
            ('P00019', 109, 'Seth', 'Rogen', '27 Meadow View, Liverpool', '0151-445-7783', '1987-07-19', 'Male', 'Married', '2026-04-19'),
            ('P00020', 106, 'Tina', 'Fey', '84 Silver Birch Road, Manchester', '0161-556-4420', '1980-08-20', 'Female', 'Married', '2026-05-02')
        ");
    }
}