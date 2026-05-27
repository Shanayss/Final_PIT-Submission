<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("ALTER TABLE qualifications ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL;");
        DB::unprepared("ALTER TABLE work_experiences ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL;");
        DB::unprepared("ALTER TABLE staff_allocations ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL;");

        /*
        |--------------------------------------------------------------------------
        | FUNCTION: Search staff by qualification
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION get_staff_by_qualification(search_qualification VARCHAR)
RETURNS TABLE (
    staff_number VARCHAR,
    full_name TEXT,
    position VARCHAR,
    qualification_type VARCHAR,
    institution_name VARCHAR,
    qualification_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        s.staff_number,
        s.first_name || ' ' || s.last_name AS full_name,
        s.position,
        q.qualification_type,
        q.institution_name,
        q.qualification_date
    FROM staff s
    JOIN qualifications q ON s.staff_number = q.staff_number
    WHERE q.qualification_type ILIKE '%' || search_qualification || '%';
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | FUNCTION: Get work experience by staff
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION get_staff_work_experiences(p_staff_number VARCHAR)
RETURNS TABLE (
    staff_number VARCHAR,
    full_name TEXT,
    name_of_organization VARCHAR,
    position_held VARCHAR,
    start_date DATE,
    finish_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        s.staff_number,
        s.first_name || ' ' || s.last_name AS full_name,
        w.name_of_organization,
        w.position_held,
        w.start_date,
        w.finish_date
    FROM staff s
    JOIN work_experiences w ON s.staff_number = w.staff_number
    WHERE s.staff_number = p_staff_number;
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | FUNCTION: Get staff allocation by staff
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION get_staff_allocations(p_staff_number VARCHAR)
RETURNS TABLE (
    staff_number VARCHAR,
    full_name TEXT,
    ward_number INTEGER,
    role_for_week VARCHAR,
    shift VARCHAR,
    week_start_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        s.staff_number,
        s.first_name || ' ' || s.last_name AS full_name,
        sa.ward_number,
        sa.role_for_week,
        sa.shift,
        sa.week_start_date
    FROM staff s
    JOIN staff_allocations sa ON s.staff_number = sa.staff_number
    WHERE s.staff_number = p_staff_number;
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | PROCEDURE: Add or update qualification
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE PROCEDURE add_or_update_qualification(
    p_staff_number VARCHAR,
    p_qualification_type VARCHAR,
    p_institution_name VARCHAR,
    p_qualification_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM qualifications
        WHERE staff_number = p_staff_number
    ) THEN
        UPDATE qualifications
        SET
            qualification_type = p_qualification_type,
            institution_name = p_institution_name,
            qualification_date = p_qualification_date
        WHERE staff_number = p_staff_number;
    ELSE
        INSERT INTO qualifications (
            staff_number,
            qualification_type,
            institution_name,
            qualification_date
        )
        VALUES (
            p_staff_number,
            p_qualification_type,
            p_institution_name,
            p_qualification_date
        );
    END IF;
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | PROCEDURE: Add or update work experience
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE PROCEDURE add_or_update_work_experience(
    p_staff_number VARCHAR,
    p_name_of_organization VARCHAR,
    p_position_held VARCHAR,
    p_start_date DATE,
    p_finish_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM work_experiences
        WHERE staff_number = p_staff_number
    ) THEN
        UPDATE work_experiences
        SET
            name_of_organization = p_name_of_organization,
            position_held = p_position_held,
            start_date = p_start_date,
            finish_date = p_finish_date
        WHERE staff_number = p_staff_number;
    ELSE
        INSERT INTO work_experiences (
            staff_number,
            name_of_organization,
            position_held,
            start_date,
            finish_date
        )
        VALUES (
            p_staff_number,
            p_name_of_organization,
            p_position_held,
            p_start_date,
            p_finish_date
        );
    END IF;
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | PROCEDURE: Add or update staff allocation
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE PROCEDURE add_or_update_staff_allocation(
    p_staff_number VARCHAR,
    p_ward_number INTEGER,
    p_role_for_week VARCHAR,
    p_shift VARCHAR,
    p_week_start_date DATE
)
LANGUAGE plpgsql
AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM staff_allocations
        WHERE staff_number = p_staff_number
    ) THEN
        UPDATE staff_allocations
        SET
            ward_number = p_ward_number,
            role_for_week = p_role_for_week,
            shift = p_shift,
            week_start_date = p_week_start_date
        WHERE staff_number = p_staff_number;
    ELSE
        INSERT INTO staff_allocations (
            staff_number,
            ward_number,
            role_for_week,
            shift,
            week_start_date
        )
        VALUES (
            p_staff_number,
            p_ward_number,
            p_role_for_week,
            p_shift,
            p_week_start_date
        );
    END IF;
END;
$$;
SQL);

        /*
        |--------------------------------------------------------------------------
        | TRIGGER FUNCTION: Auto-update updated_at
        |--------------------------------------------------------------------------
        */
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION update_modified_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
SQL);

        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_qualifications ON qualifications;");
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_work_experiences ON work_experiences;");
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_staff_allocations ON staff_allocations;");

        DB::unprepared("
            CREATE TRIGGER trigger_update_qualifications
            BEFORE UPDATE ON qualifications
            FOR EACH ROW
            EXECUTE FUNCTION update_modified_column();
        ");

        DB::unprepared("
            CREATE TRIGGER trigger_update_work_experiences
            BEFORE UPDATE ON work_experiences
            FOR EACH ROW
            EXECUTE FUNCTION update_modified_column();
        ");

        DB::unprepared("
            CREATE TRIGGER trigger_update_staff_allocations
            BEFORE UPDATE ON staff_allocations
            FOR EACH ROW
            EXECUTE FUNCTION update_modified_column();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_qualifications ON qualifications;");
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_work_experiences ON work_experiences;");
        DB::unprepared("DROP TRIGGER IF EXISTS trigger_update_staff_allocations ON staff_allocations;");

        DB::unprepared("DROP FUNCTION IF EXISTS update_modified_column() CASCADE;");

        DB::unprepared("DROP PROCEDURE IF EXISTS add_or_update_qualification(VARCHAR, VARCHAR, VARCHAR, DATE);");
        DB::unprepared("DROP PROCEDURE IF EXISTS add_or_update_work_experience(VARCHAR, VARCHAR, VARCHAR, DATE, DATE);");
        DB::unprepared("DROP PROCEDURE IF EXISTS add_or_update_staff_allocation(VARCHAR, INTEGER, VARCHAR, VARCHAR, DATE);");

        DB::unprepared("DROP FUNCTION IF EXISTS get_staff_by_qualification(VARCHAR);");
        DB::unprepared("DROP FUNCTION IF EXISTS get_staff_work_experiences(VARCHAR);");
        DB::unprepared("DROP FUNCTION IF EXISTS get_staff_allocations(VARCHAR);");
    }
};
