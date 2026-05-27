<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION nurse_set_in_patient_admission_date()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    NEW.date_admitted := COALESCE(NEW.date_admitted, CURRENT_DATE);
                END IF;

                IF TG_OP = 'UPDATE'
                    AND NEW.bed_number IS NOT NULL
                    AND OLD.bed_number IS NULL THEN
                    NEW.date_admitted := CURRENT_DATE;
                END IF;

                RETURN NEW;
            END;
            $$;
        SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_nurse_set_in_patient_admission_date ON in_patients;');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_nurse_set_in_patient_admission_date
            BEFORE INSERT OR UPDATE OF bed_number
            ON in_patients
            FOR EACH ROW
            EXECUTE FUNCTION nurse_set_in_patient_admission_date();
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_nurse_set_in_patient_admission_date ON in_patients;');
        DB::unprepared('DROP FUNCTION IF EXISTS nurse_set_in_patient_admission_date();');
    }
};
