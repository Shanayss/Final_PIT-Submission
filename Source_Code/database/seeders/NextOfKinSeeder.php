<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NextOfKinSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("
            INSERT INTO next_of_kins (
                patient_number,
                full_name,
                relationship,
                address,
                telephone
            ) VALUES

            ('P00001', 'James Phelps', 'Father', '145 Rowlands Street, Paisley, PA2 5FE', '0141-848-2211'),
            ('P00002', 'Margaret Jones', 'Mother', '45 Royal Mile, Edinburgh, EH1 1RB', '0131-555-5678'),
            ('P00003', 'Robert Miller', 'Father', '88 Ferry Road, Edinburgh, EH6 4AQ', '0131-555-9012'),
            ('P00004', 'Jeanette Taylor', 'Sister', '32 Lothian Rd, Edinburgh, EH3 9BY', '0131-555-3456'),
            ('P00005', 'William Brown', 'Brother', '15 Cowgate, Edinburgh, EH1 1JR', '0131-555-7890'),
            ('P00006', 'Helen Wilson', 'Aunt', '74 Leith Walk, Edinburgh, EH6 5HB', '0131-555-2345'),
            ('P00007', 'David Johnston', 'Uncle', '22 Grassmarket, Edinburgh, EH1 2JU', '0131-555-6789'),
            ('P00008', 'Agnes Morrison', 'Cousin', '50 Morningside Rd, Edinburgh, EH10 4BF', '0131-555-0123'),
            ('P00009', 'Elizabeth Campbell', 'Sister', '11 Holyrood Rd, Edinburgh, EH8 8AS', '0131-555-8901'),
            ('P00010', 'Thomas Stewart', 'Father', '67 Dundee Terrace, Edinburgh, EH11 1DL', '0131-444-2323'),

            ('P00011', 'Janet Anderson', 'Mother', '12 Newhaven Road, Edinburgh, EH6 4QA', '0131-222-4545'),
            ('P00012', 'Charles Scott', 'Brother', '89 Colinton Road, Edinburgh, EH10 5BT', '0131-333-6767'),
            ('P00013', 'Mary Innes', 'Sister', '4 North Bridge, Edinburgh, EH1 1SB', '0131-555-9898'),
            ('P00014', 'Duncan Macleod', 'Spouse', '102 West Port, Edinburgh, EH3 9DN', '0131-777-1111'),
            ('P00015', 'Fiona Graham', 'Mother', '14 Comiston Road, Edinburgh, EH10 5QE', '0131-888-2222'),
            ('P00016', 'Patrick Reid', 'Father', '27 Hanover Street, Edinburgh, EH2 2EN', '0131-111-3434'),
            ('P00017', 'Sarah Douglas', 'Mother', '63 Easter Road, Edinburgh, EH7 5PL', '0131-222-5656'),
            ('P00018', 'Andrew Kerr', 'Brother', '91 Nicolson Street, Edinburgh, EH8 9BZ', '0131-333-7878'),
            ('P00019', 'Isabella Fraser', 'Spouse', '18 George IV Bridge, Edinburgh, EH1 1EN', '0131-444-9090'),
            ('P00020', 'Michael Boyd', 'Uncle', '76 Dalry Road, Edinburgh, EH11 2BA', '0131-555-1212');
        ");
    }
}