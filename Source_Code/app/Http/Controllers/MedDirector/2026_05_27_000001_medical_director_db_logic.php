<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add Functions, Procedures, and Triggers.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared("
            -- 1. FUNCTION: Calculate Centralized Ward Metrics
            -- Returns a single source of truth for total beds, occupancy, admissions, and discharges
            CREATE OR REPLACE FUNCTION get_ward_metrics(p_ward_number INT)
            RETURNS TABLE(total_beds INT, occupied INT, available INT, admissions INT, discharges INT) AS \$\$
            DECLARE
                v_total_beds INT;
                v_occupied INT;
            BEGIN
                SELECT w.total_beds INTO v_total_beds
                FROM wards w
                WHERE w.ward_number = p_ward_number;

                SELECT COUNT(*)::INT INTO v_occupied
                FROM beds b
                LEFT JOIN in_patients ip ON b.ward_number = ip.ward_number
                                         AND b.bed_number = ip.bed_number
                                         AND ip.date_actual_leave IS NULL
                WHERE b.ward_number = p_ward_number
                  AND (b.status = 'Occupied' OR ip.in_patient_id IS NOT NULL);

                RETURN QUERY
                SELECT
                    v_total_beds,
                    v_occupied,
                    GREATEST(0, v_total_beds - v_occupied),
                    (SELECT COUNT(*)::INT FROM in_patients WHERE ward_number = p_ward_number) AS admissions,
                    (SELECT COUNT(*)::INT FROM in_patients WHERE ward_number = p_ward_number AND date_actual_leave IS NOT NULL) AS discharges;
            END;
            \$\$ LANGUAGE plpgsql;

            -- 2. TRIGGER: Prevent Double Bed Assignment
            -- Ensures a bed isn't assigned to a new patient if someone is still in it.
            CREATE OR REPLACE FUNCTION fn_prevent_duplicate_bed_assignment()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.date_actual_leave IS NULL AND NEW.bed_number IS NOT NULL AND EXISTS (
                    SELECT 1 FROM in_patients
                    WHERE ward_number = NEW.ward_number
                      AND bed_number = NEW.bed_number
                      AND date_actual_leave IS NULL
                      AND in_patient_id != COALESCE(NEW.in_patient_id, -1)
                ) THEN
                    RAISE EXCEPTION 'Bed % in Ward % is already occupied.', NEW.bed_number, NEW.ward_number;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_check_bed_availability ON in_patients;
            CREATE TRIGGER trg_check_bed_availability
            BEFORE INSERT OR UPDATE ON in_patients
            FOR EACH ROW EXECUTE FUNCTION fn_prevent_duplicate_bed_assignment();

            -- 3. TRIGGER: Automated Inventory Fulfillment
            -- Automatically reduces stock in pharmacy when a requisition is marked 'Received'
            CREATE OR REPLACE FUNCTION fn_trg_fulfill_requisition()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.status = 'Received' AND (OLD.status IS NULL OR OLD.status != 'Received') THEN
                    -- Reduce items stock
                    UPDATE items i
                    SET quantity_of_stock = i.quantity_of_stock - ri.quantity_required
                    FROM requisition_items ri
                    WHERE ri.requisition_number = NEW.requisition_number
                      AND ri.item_number = i.item_number;

                    -- Reduce drugs stock
                    UPDATE drugs d
                    SET quantity_of_stock = d.quantity_of_stock - rd.quantity_required
                    FROM requisition_drugs rd
                    WHERE rd.requisition_number = NEW.requisition_number
                      AND rd.drug_number = d.drug_number;

                    NEW.date_received = CURRENT_DATE;
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS trg_fulfill_requisition ON requisitions;
            CREATE TRIGGER trg_fulfill_requisition
            BEFORE UPDATE ON requisitions
            FOR EACH ROW EXECUTE FUNCTION fn_trg_fulfill_requisition();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_fulfill_requisition ON requisitions;
            DROP FUNCTION IF EXISTS fn_trg_fulfill_requisition();
            DROP TRIGGER IF EXISTS trg_check_bed_availability ON in_patients;
            DROP FUNCTION IF EXISTS fn_prevent_duplicate_bed_assignment();
            DROP FUNCTION IF EXISTS get_ward_metrics(INT);
        ");
    }
};