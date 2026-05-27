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
            DELETE FROM staff_allocations older
            USING staff_allocations newer
            WHERE older.staff_number = newer.staff_number
                AND older.allocation_id < newer.allocation_id;
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE UNIQUE INDEX IF NOT EXISTS staff_allocations_staff_number_unique
            ON staff_allocations (staff_number);
        SQL);

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
                INSERT INTO staff_allocations (
                    staff_number,
                    ward_number,
                    role_for_week,
                    shift,
                    week_start_date,
                    created_at,
                    updated_at
                ) VALUES (
                    p_staff_number,
                    p_ward_number,
                    p_role_for_week,
                    p_shift,
                    p_week_start_date,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                )
                ON CONFLICT (staff_number)
                DO UPDATE SET
                    ward_number = EXCLUDED.ward_number,
                    role_for_week = EXCLUDED.role_for_week,
                    shift = EXCLUDED.shift,
                    week_start_date = EXCLUDED.week_start_date,
                    updated_at = CURRENT_TIMESTAMP;
            END;
            $$;
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP INDEX IF EXISTS staff_allocations_staff_number_unique;');
    }
};
