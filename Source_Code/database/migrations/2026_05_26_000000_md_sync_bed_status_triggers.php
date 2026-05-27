<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep beds.status synchronized with in_patients.date_actual_leave.
        // PostgreSQL version.
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS md_in_patients_after_ins ON in_patients;');
        DB::unprepared('DROP TRIGGER IF EXISTS md_in_patients_after_upd ON in_patients;');
        DB::unprepared('DROP FUNCTION IF EXISTS md_sync_beds_status_in_patients();');

        // Function body is enclosed in $$ so it won't conflict with PHP quotes.
        $sql = "
        CREATE OR REPLACE FUNCTION md_sync_beds_status_in_patients()
        RETURNS trigger AS $$
        BEGIN
            IF NEW.ward_number IS NOT NULL AND NEW.bed_number IS NOT NULL THEN
                IF NEW.date_actual_leave IS NULL THEN
                    UPDATE beds
                    SET status = 'Occupied'
                    WHERE ward_number = NEW.ward_number AND bed_number = NEW.bed_number;
                ELSE
                    UPDATE beds
                    SET status = 'Available'
                    WHERE ward_number = NEW.ward_number AND bed_number = NEW.bed_number;
                END IF;
            END IF;

            RETURN NEW;
        END;
        $$ LANGUAGE plpgsql;
        ";

        DB::unprepared($sql);

        DB::unprepared("CREATE TRIGGER md_in_patients_after_ins
            AFTER INSERT ON in_patients
            FOR EACH ROW
            EXECUTE FUNCTION md_sync_beds_status_in_patients();");

        DB::unprepared("CREATE TRIGGER md_in_patients_after_upd
            AFTER UPDATE ON in_patients
            FOR EACH ROW
            EXECUTE FUNCTION md_sync_beds_status_in_patients();");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS md_in_patients_after_upd ON in_patients;');
        DB::unprepared('DROP TRIGGER IF EXISTS md_in_patients_after_ins ON in_patients;');
        DB::unprepared('DROP FUNCTION IF EXISTS md_sync_beds_status_in_patients();');
    }
};

