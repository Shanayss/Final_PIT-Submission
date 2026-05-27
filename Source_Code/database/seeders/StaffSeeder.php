<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO staff (
                staff_number,
                role_id,
                email,
                password,
                first_name,
                last_name,
                address,
                telephone,
                date_of_birth,
                sex,
                nin,
                position,
                current_salary,
                salary_scale,
                hours_per_week,
                contract_type,
                payment_type
            ) VALUES

            ('S001', 1, 'paulray@gmail.com', 'MedicalDirector001', 'Paul', 'Ray', '15 Cranberry Road, London', '020-4456-7811', '1991-05-05', 'Male', 'AB123456C', 'Medical Director', 85000, 'A1', 40, 'Permanent', 'Monthly'),
            ('S002', 3, 'johndoe@gmail.com', 'Doctor002', 'John', 'Doe', '82 Fairfield Avenue, Manchester', '0161-782-4412', '1990-01-01', 'Male', 'CD789012A', 'Doctor', 65000, 'B1', 40, 'Permanent', 'Monthly'),
            ( 'S003', 2, 'marklee@gmail.com', 'PersonnelOfficer003', 'Mark', 'Lee', '6 Rosemont Street, Birmingham', '0121-663-2281', '1988-03-03', 'Male',  'EF345678B', 'Personnel Officer', 42000, 'C1', 37, 'Permanent', 'Weekly' ),  
            ( 'S004', 4, 'janesmith@gmail.com', 'SeniorNurse004', 'Jane', 'Smith', '41 Glenwood Drive, Edinburgh', '0131-552-7714', '1992-02-02', 'Female', 'GH456123D', 'Senior Nurse', 38000, 'C1', 40, 'Permanent', 'Monthly'), 
            ( 'S005', 4, 'annakim@gmail.com', 'AuxiliaryStaff005', 'Anna', 'Kim', '93 Kingsley Road, Leeds', '0113-774-9902', '1995-04-04', 'Female', 'JK987654E', 'Auxiliary Staff', 22000, 'D1', 40, 'Temporary', 'Monthly'), 
            ( 'S006', 1, 'leovelez@gmail.com', 'MedicalDirector006', 'Leo', 'Velez', '27 Norfolk Street, Liverpool', '0151-334-8820', '1985-06-15', 'Male', 'LX111222', 'Medical Director', 90000, 'A1', 45, 'Permanent', 'Monthly'),        
            ( 'S007', 5, 'mariasantos@gmail.com', 'Cashier007', 'Maria', 'Santos', '58 Belmont Avenue, Glasgow', '0141-887-2213', '1993-08-20', 'Female', 'MS333444', 'Cashier', 18000, 'D1', 40, 'Permanent', 'Monthly'),        
            ( 'S008', 5, 'clarabenson@gmail.com', 'Cashier008', 'Clara', 'Benson', '10 Ashford Lane, London', '020-9981-4421', '1990-11-12', 'Female', 'CB555666', 'Cashier', 17000, 'D1', 40, 'Permanent', 'Monthly'),       
            ( 'S009', 2, 'ricardodalisay@gmail.com', 'PersonnelOfficer009', 'Ricardo', 'Dalisay', '74 Pine Street, Manchester', '0161-554-1109', '1978-02-28', 'Male', 'RD777888', 'Personnel Officer', 32000, 'C1', 20, 'Temporary', 'Monthly'),        
            ( 'S010', 3, 'elenagilbert@gmail.com', 'Consultant010', 'Elena', 'Gilbert', '36 Cedar Close, Birmingham', '0121-771-4438', '1996-12-05', 'Female', 'EG999000', 'Consultant', 70000, 'B1', 40, 'Permanent', 'Monthly'),        
            ( 'S011', 4, 'stefansalvatore@gmail.com', 'JuniorNurse011', 'Stefan', 'Salvatore', '19 Woodland Avenue, Edinburgh', '0131-664-2205', '1992-03-14', 'Male', 'SS121212', 'Junior Nurse', 26000, 'C1', 40, 'Permanent', 'Monthly'),        
            ( 'S012', 4, 'bonniebennett@gmail.com', 'ChargeNurse012', 'Bonnie', 'Bennett', '67 Regent Street, Leeds', '0113-885-4417', '1994-05-22', 'Female', 'BB343434', 'Charge Nurse', 45000, 'B1', 40, 'Permanent', 'Monthly'),       
            ( 'S013', 3, 'damonsalvatore@gmail.com', 'Doctor013', 'Damon', 'Salvatore', '48 Harbour Road, Liverpool', '0151-776-9081', '1989-10-31', 'Male', 'DS565656', 'Doctor', 65000, 'B1', 40, 'Permanent', 'Monthly'),        
            ( 'S014', 5, 'carolineforbes@gmail.com', 'Cashier014', 'Caroline', 'Forbes', '25 Westbrook Lane, Glasgow', '0141-992-6650', '1995-07-07', 'Female', 'CF787878', 'Cashier', 18000, 'D1', 40, 'Permanent', 'Monthly'),        
            ( 'S015', 4, 'tylerlockwood@gmail.com', 'SeniorNurse015', 'Tyler', 'Lockwood', '90 Sycamore Drive, London', '020-7754-1182', '1991-09-09', 'Male', 'TL909090', 'Senior Nurse', 38000, 'C1', 40, 'Permanent', 'Monthly'),        
            ( 'S016', 4, 'niklaus@gmail.com', 'AuxiliaryStaff016', 'Niklaus', 'Mikaelson', '31 Brookfield Road, Manchester', '0161-661-2299', '1980-01-01', 'Male', 'NM131313', 'Auxiliary Staff', 22000, 'D1', 15, 'Temporary', 'Monthly'),       
            ( 'S017', 3, 'elijahmikaelson@gmail.com', 'Doctor017', 'Elijah', 'Mikaelson', '52 Kingston Avenue, Birmingham', '0121-998-7734', '1982-02-02', 'Male', 'EM242424', 'Doctor', 68000, 'B1', 40, 'Permanent', 'Monthly'),        
            ( 'S018', 4, 'rebekahmikaelson@gmail.com', 'JuniorNurse018', 'Rebekah', 'Mikaelson', '14 Evergreen Street, Edinburgh', '0131-442-1190', '1986-03-03', 'Female', 'RM353535', 'Junior Nurse', 27000, 'C1', 40, 'Permanent', 'Monthly'),        
            ( 'S019', 4, 'hayleymarshall@gmail.com', 'ChargeNurse019', 'Hayley', 'Marshall', '85 Carlton Road, Leeds', '0113-551-6720', '1993-04-04', 'Female', 'HM464646', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly'),        
            ( 'S020', 4, 'alaricsaltzman@gmail.com', 'ChargeNurse020', 'Alaric', 'Saltzman', '39 Waterfall Close, Liverpool', '0151-884-2201', '1984-05-05', 'Male', 'AS575757', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly'),       
            ( 'S021', 4, 'olivergrant@gmail.com', 'ChargeNurse021', 'Oliver', 'Grant', '12 Hilltop Road, Leeds', '0113-223-4456', '1992-06-10', 'Male', 'OG101010', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),       
            ( 'S022', 3, 'isabelclarke@gmail.com', 'Doctor022', 'Isabel', 'Clarke', '55 River Street, Manchester', '0161-223-8899', '1987-02-18', 'Female', 'IC202020', 'Doctor', 65000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S023', 4, 'masonreed@gmail.com', 'JuniorNurse023', 'Mason', 'Reed', '18 Oak Avenue, Birmingham', '0121-445-7788', '1996-09-09', 'Male', 'MR303030', 'Junior Nurse', 27000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S024', 4, 'emilywatson@gmail.com', 'SeniorNurse024', 'Emily', 'Watson', '33 Forest Drive, Edinburgh', '0131-556-2211', '1989-11-11', 'Female', 'EW404040', 'Senior Nurse', 38000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S025', 4, 'danielcooper@gmail.com', 'AuxiliaryStaff025', 'Daniel', 'Cooper', '77 Park Lane, London', '020-6677-8899', '1993-04-14', 'Male', 'DC505050', 'Auxiliary Staff', 22000, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S026', 4, 'gracehall@gmail.com', 'ChargeNurse026', 'Grace', 'Hall', '9 Meadow Road, Glasgow', '0141-998-1122', '1991-01-25', 'Female', 'GH606060', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S027', 4, 'lucaswright@gmail.com', 'JuniorNurse027', 'Lucas', 'Wright', '21 Brook Street, Leeds', '0113-334-5566', '1997-07-07', 'Male', 'LW707070', 'Junior Nurse', 26000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S028', 3, 'ameliahughes@gmail.com', 'Doctor028', 'Amelia', 'Hughes', '88 Cedar Road, London', '020-7788-9900', '1988-12-12', 'Female', 'AH808080', 'Doctor', 68000, 'B1', 40, 'Permanent', 'Monthly' ),     
            ( 'S029', 4, 'jacobmorris@gmail.com', 'SeniorNurse029', 'Jacob', 'Morris', '14 Elm Street, Manchester', '0161-445-6677', '1990-03-03', 'Male', 'JM909090', 'Senior Nurse', 40000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S030', 4, 'sophialee@gmail.com', 'ChargeNurse030', 'Sophia', 'Lee', '66 Victoria Road, Birmingham', '0121-8899-1122', '1994-08-08', 'Female', 'SL111111', 'Charge Nurse', 45000, 'B1', 40, 'Permanent', 'Monthly' ),           
            ( 'S031', 3, 'ethanharris@gmail.com', 'Consultant031', 'Ethan', 'Harris', '29 Queen Street, Liverpool', '0151-223-4455', '1985-05-05', 'Male', 'EH121212', 'Consultant', 70000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S032', 4, 'avajames@gmail.com', 'JuniorNurse032', 'Ava', 'James', '10 Rose Street, Edinburgh', '0131-667-8899', '1998-10-10', 'Female', 'AJ131313', 'Junior Nurse', 25000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S033', 4, 'williamscott@gmail.com', 'AuxiliaryStaff033', 'William', 'Scott', '45 Green Lane, Leeds', '0113-7788-4455', '1992-02-02', 'Male', 'WS141414', 'Auxiliary Staff', 21000, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S034', 4, 'chloebrooks@gmail.com', 'ChargeNurse034', 'Chloe', 'Brooks', '71 Sunset Drive, Glasgow', '0141-5566-7788', '1993-06-06', 'Female', 'CB151515', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S035', 4, 'henryyoung@gmail.com', 'SeniorNurse035', 'Henry', 'Young', '8 Pine Avenue, Manchester', '0161-6677-8899', '1987-09-09', 'Male', 'HY161616', 'Senior Nurse', 39000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S036', 4, 'zoebennett@gmail.com', 'JuniorNurse036', 'Zoe', 'Bennett', '23 Lake Road, Birmingham', '0121-9988-7766', '1999-03-15', 'Female', 'ZB171717', 'Junior Nurse', 25500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S037', 5, 'noahprice@gmail.com', 'Cashier037', 'Noah', 'Price', '19 Market Street, London', '020-3344-5566', '1994-12-01', 'Male', 'NP181818', 'Cashier', 18000, 'D1', 40, 'Permanent', 'Monthly' ),
            ( 'S038', 3, 'miaadams@gmail.com', 'Doctor038', 'Mia', 'Adams', '40 King Street, Leeds', '0113-4455-6677', '1991-01-01', 'Female', 'MA191919', 'Doctor', 67000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S039', 4, 'loganwright@gmail.com', 'ChargeNurse039', 'Logan', 'Wright', '16 Hill Street, Edinburgh', '0131-2233-4455', '1989-04-04', 'Male', 'LW202020', 'Charge Nurse', 45500, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S040', 4, 'islagreen@gmail.com', 'JuniorNurse040', 'Isla', 'Green', '52 Forest Lane, Manchester', '0161-7788-9900', '1997-07-07', 'Female', 'IG212121', 'Junior Nurse', 26500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S041', 2, 'jackturner@gmail.com', 'PersonnelOfficer041', 'Jack', 'Turner', '11 Regent Road, Birmingham', '0121-2233-8899', '1986-06-06', 'Male', 'JT222222', 'Personnel Officer', 33000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S042', 4, 'oliviabrown@gmail.com', 'ChargeNurse042', 'Olivia', 'Brown', '5 Park Avenue, Leeds', '0113-6677-8899', '1992-02-14', 'Female', 'OB232323', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S043', 4, 'charlieking@gmail.com', 'JuniorNurse043', 'Charlie', 'King', '77 Hilltop Street, London', '020-9988-7766', '1998-08-08', 'Male', 'CK242424', 'Junior Nurse', 25000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S044', 4, 'evawhite@gmail.com', 'SeniorNurse044', 'Eva', 'White', '34 Riverbank Road, Glasgow', '0141-2233-4455', '1988-03-03', 'Female', 'EW252525', 'Senior Nurse', 39500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S045', 4, 'thomasbell@gmail.com', 'AuxiliaryStaff045', 'Thomas', 'Bell', '9 Oakwood Drive, Liverpool', '0151-5566-7788', '1993-09-09', 'Male', 'TB262626', 'Auxiliary Staff', 21500, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S046', 3, 'lucymorgan@gmail.com', 'Doctor046', 'Lucy', 'Morgan', '21 West Street, Manchester', '0161-3344-5566', '1990-10-10', 'Female', 'LM272727', 'Doctor', 69000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S047', 4, 'georgehill@gmail.com', 'ChargeNurse047', 'George', 'Hill', '12 Cedar Avenue, Birmingham', '0121-4455-6677', '1987-07-07', 'Male', 'GH282828', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S048', 4, 'sarahcollins@gmail.com', 'JuniorNurse048', 'Sarah', 'Collins', '88 Elm Road, Leeds', '0113-7788-9900', '1996-06-06', 'Female', 'SC292929', 'Junior Nurse', 26000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S049', 4, 'davidward@gmail.com', 'SeniorNurse049', 'David', 'Ward', '19 Lake Street, Edinburgh', '0131-6677-8899', '1985-05-15', 'Male', 'DW303030', 'Senior Nurse', 40000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S050', 4, 'emmajones@gmail.com', 'ChargeNurse050', 'Emma', 'Jones', '30 Pine Street, London', '020-4455-6677', '1991-11-11', 'Female', 'EJ313131', 'Charge Nurse', 46500, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S051', 4, 'aidandoyle@gmail.com', 'JuniorNurse051', 'Aidan', 'Doyle', '14 Brook Lane, Manchester', '0161-8899-0011', '1997-02-02', 'Male', 'AD323232', 'Junior Nurse', 25500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S052', 4, 'nataliefox@gmail.com', 'AuxiliaryStaff052', 'Natalie', 'Fox', '67 Meadow Street, Leeds', '0113-2233-4455', '1994-04-04', 'Female', 'NF333333', 'Auxiliary Staff', 22000, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S053', 3, 'ryanclark@gmail.com', 'Consultant053', 'Ryan', 'Clark', '55 Queen Avenue, Birmingham', '0121-6677-8899', '1984-08-08', 'Male', 'RC343434', 'Consultant', 71000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S054', 4, 'hannahprice@gmail.com', 'SeniorNurse054', 'Hannah', 'Price', '21 Rose Lane, Edinburgh', '0131-9988-7766', '1990-12-12', 'Female', 'HP353535', 'Senior Nurse', 39000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S055', 4, 'liamwright@gmail.com', 'ChargeNurse055', 'Liam', 'Wright', '8 Forest Road, Glasgow', '0141-4455-6677', '1989-09-09', 'Male', 'LW363636', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S056', 4, 'chloemartin@gmail.com', 'JuniorNurse056', 'Chloe', 'Martin', '77 Hill Street, Leeds', '0113-8899-1122', '1998-08-18', 'Female', 'CM373737', 'Junior Nurse', 26500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S057', 4, 'benjaminlee@gmail.com', 'AuxiliaryStaff057', 'Benjamin', 'Lee', '33 Oak Street, London', '020-6677-2233', '1992-03-03', 'Male', 'BL383838', 'Auxiliary Staff', 21000, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S058', 3, 'jessicawalker@gmail.com', 'Doctor058', 'Jessica', 'Walker', '18 River Road, Manchester', '0161-3344-7788', '1991-01-21', 'Female', 'JW393939', 'Doctor', 68000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S059', 4, 'kevinross@gmail.com', 'ChargeNurse059', 'Kevin', 'Ross', '29 Green Avenue, Birmingham', '0121-7788-9900', '1988-06-06', 'Male', 'KR404040', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S060', 4, 'lauraclark@gmail.com', 'JuniorNurse060', 'Laura', 'Clark', '50 Lake View, Edinburgh', '0131-4455-6677', '1997-07-21', 'Female', 'LC414141', 'Junior Nurse', 26000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S061', 4, 'michaeladams@gmail.com', 'SeniorNurse061', 'Michael', 'Adams', '12 Pine Road, Leeds', '0113-6677-8899', '1986-06-06', 'Male', 'MA424242', 'Senior Nurse', 39500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S062', 4, 'oliverjames@gmail.com', 'ChargeNurse062', 'Oliver', 'James', '9 Hilltop Lane, London', '020-7788-4455', '1990-10-10', 'Male', 'OJ434343', 'Charge Nurse', 46500, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S063', 4, 'isabellabell@gmail.com', 'JuniorNurse063', 'Isabella', 'Bell', '66 Cedar Street, Manchester', '0161-2233-4455', '1998-12-12', 'Female', 'IB444444', 'Junior Nurse', 25500, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S064', 4, 'ethanwood@gmail.com', 'AuxiliaryStaff064', 'Ethan', 'Wood', '17 Park Lane, Glasgow', '0141-6677-8899', '1993-03-03', 'Male', 'EW454545', 'Auxiliary Staff', 21500, 'D1', 40, 'Temporary', 'Monthly' ),
            ( 'S065', 4, 'graceroberts@gmail.com', 'ChargeNurse065', 'Grace', 'Roberts', '28 Forest Avenue, Leeds', '0113-4455-6677', '1991-09-09', 'Female', 'GR464646', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S066', 4, 'danielgreen@gmail.com', 'JuniorNurse066', 'Daniel', 'Green', '11 Oak Road, Birmingham', '0121-6677-8899', '1997-05-05', 'Male', 'DG474747', 'Junior Nurse', 26000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S067', 4, 'sophiemorgan@gmail.com', 'SeniorNurse067', 'Sophie', 'Morgan', '39 River Street, Edinburgh', '0131-2233-4455', '1989-09-09', 'Female', 'SM484848', 'Senior Nurse', 40000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S068', 4, 'jacksonhill@gmail.com', 'ChargeNurse068', 'Jackson', 'Hill', '6 Meadow Lane, London', '020-8899-0011', '1987-07-07', 'Male', 'JH494949', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S069', 4, 'ninaevans@gmail.com', 'JuniorNurse069', 'Nina', 'Evans', '44 Lake Street, Manchester', '0161-7788-2233', '1999-09-09', 'Female', 'NE505050', 'Junior Nurse', 25000, 'C1', 40, 'Permanent', 'Monthly' ),
            ( 'S070', 4, 'harrymoore@gmail.com', 'ChargeNurse070', 'Harry', 'Moore', '25 Green Road, Birmingham', '0121-4455-8899', '1988-08-08', 'Male', 'HM515151', 'Charge Nurse', 46000, 'B1', 40, 'Permanent', 'Monthly' ),
            ( 'S071', 4, 'elizabethscott@gmail.com', 'ChargeNurse071', 'Elizabeth', 'Scott', '13 Cedar Avenue, Leeds', '0113-9988-1122', '1990-02-02', 'Female', 'ES525252', 'Charge Nurse', 47000, 'B1', 40, 'Permanent', 'Monthly' ),
            ('S072', 3, 'andrewcole@gmail.com', 'Consultant072', 'Andrew', 'Cole', '22 North Street, London', '020-1111-2222', '1983-03-03', 'Male', 'CN707070', 'Consultant', 72000, 'B1', 40, 'Permanent', 'Monthly'),
            ('S073', 3, 'sophiaknight@gmail.com', 'Consultant073', 'Sophia', 'Knight', '55 West Avenue, Manchester', '0161-2222-3333', '1982-02-02', 'Female', 'SK808080', 'Consultant', 73000, 'B1', 40, 'Permanent', 'Monthly');

        ");
    }
}