<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            WardSeeder::class,
            BedSeeder::class,
            StaffSeeder::class,
            StaffHashSeeder::class,
            LocalDoctorSeeder::class,
            PatientSeeder::class,
            QualificationSeeder::class,
            WorkExperienceSeeder::class,
            StaffAllocationSeeder::class,
            AppointmentSeeder::class,
            DiagnosisSeeder::class,
            TreatmentSeeder::class,
            DrugsSeeder::class,
            ItemsSeeder::class,
            SupplierSeeder::class,
            SupplierItemsSeeder::class,
            SupplierDrugSeeder::class,
            RequisitionSeeder::class,
            RequisitionItemsSeeder::class,
            RequisitionDrugSeeder::class,
            InPatientSeeder::class,
            NextOfKinSeeder::class,
            PatientMedicationSeeder::class,
            BillSeeder::class,
            BillItemSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
