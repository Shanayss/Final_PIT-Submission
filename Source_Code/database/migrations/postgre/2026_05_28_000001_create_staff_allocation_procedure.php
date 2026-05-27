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
                    SELECT 1
                    FROM staff_allocations
                    WHERE staff_number = p_staff_number
                ) THEN
                    UPDATE staff_allocations
                    SET ward_number = p_ward_number,
                        role_for_week = p_role_for_week,
                        shift = p_shift,
                        week_start_date = p_week_start_date,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE staff_number = p_staff_number;
                ELSE
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
                    );
                END IF;
            END;
            $$;
        SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS add_or_update_staff_allocation(VARCHAR, INTEGER, VARCHAR, VARCHAR, DATE);');
    }
};
