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

       
        // ======================================= MODULE 1 =================================================

        // PURPOSE: 
        // Stores medication administration records.

        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS medication_administrations (
                administration_id BIGSERIAL PRIMARY KEY,
                medication_id BIGINT NOT NULL REFERENCES patient_medications(medication_id) ON DELETE CASCADE,
                patient_number VARCHAR(20) NOT NULL REFERENCES patients(patient_number) ON DELETE CASCADE,
                staff_number VARCHAR(10) NULL REFERENCES staff(staff_number) ON DELETE SET NULL,
                dosage_administered VARCHAR(50) NULL,
                administered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                notes TEXT NULL
            );
        SQL);


        // PURPOSE:
        // Stores patient vital signs and condition monitoring.

        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS patient_condition_updates (
                condition_id BIGSERIAL PRIMARY KEY,
                patient_number VARCHAR(20) NOT NULL REFERENCES patients(patient_number) ON DELETE CASCADE,
                staff_number VARCHAR(10) NULL REFERENCES staff(staff_number) ON DELETE SET NULL,
                condition_status VARCHAR(30) NOT NULL DEFAULT 'Stable',
                blood_pressure VARCHAR(20) NULL,
                temperature VARCHAR(20) NULL,
                heart_rate VARCHAR(20) NULL,
                notes TEXT NULL,
                recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        SQL);



        // PURPOSE:
        // Stores nursing observations and care notes.


        DB::unprepared(<<<'SQL'
            CREATE TABLE IF NOT EXISTS care_notes (
                care_note_id BIGSERIAL PRIMARY KEY,
                patient_number VARCHAR(20) NOT NULL REFERENCES patients(patient_number) ON DELETE CASCADE,
                staff_number VARCHAR(10) NULL REFERENCES staff(staff_number) ON DELETE SET NULL,
                note_type VARCHAR(50) NOT NULL DEFAULT 'General Observation',
                notes TEXT NOT NULL,
                recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            );
        SQL);




        // PURPOSE: (Function)
        // Checks if a bed is occupied.


        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_bed_is_available(
                p_bed_number INTEGER,
                p_exclude_patient_number VARCHAR DEFAULT NULL
            )
            RETURNS BOOLEAN
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RETURN NOT EXISTS (
                    SELECT 1
                    FROM in_patients
                    WHERE bed_number = p_bed_number
                        AND date_actual_leave IS NULL
                        AND (
                            p_exclude_patient_number IS NULL
                            OR patient_number <> p_exclude_patient_number
                        )
                );
            END;
            $$;
        SQL);

         // PURPOSE: (Function)
        // Register a patient with automatic patient number generation.
DB::unprepared(<<<'SQL'
CREATE FUNCTION register_patient(
    p_clinic_number INT,
    p_fname VARCHAR,
    p_lname VARCHAR,
    p_address VARCHAR,
    p_phone VARCHAR,
    p_dob DATE,
    p_sex VARCHAR,
    p_status VARCHAR,
    p_kin_name VARCHAR,
    p_relationship VARCHAR,
    p_kin_address VARCHAR,
    p_kin_phone VARCHAR
)
RETURNS VARCHAR(20)
LANGUAGE plpgsql
AS $$
DECLARE
    last_number INTEGER;
    new_patient_id VARCHAR(20);
BEGIN

    SELECT COALESCE(
        MAX(CAST(SUBSTRING(patient_number FROM 2) AS INTEGER)),
        0
    )
    INTO last_number
    FROM patients
    WHERE patient_number LIKE 'P%';


 
    last_number := last_number + 1;

    new_patient_id := 'P' || LPAD(last_number::TEXT, 5, '0');



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
    ) VALUES (
        new_patient_id,
        p_clinic_number,
        p_fname,
        p_lname,
        p_address,
        p_phone,
        p_dob,
        p_sex,
        p_status,
        CURRENT_DATE
    );


    INSERT INTO next_of_kins (
        patient_number,
        full_name,
        relationship,
        address,
        telephone
    ) VALUES (
        new_patient_id,
        p_kin_name,
        p_relationship,
        p_kin_address,
        p_kin_phone
    );

    RETURN new_patient_id;

END;
$$;
SQL);




        // PURPOSE: (Function)
        // Admits a patient into the hospital.


        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_admit_patient(
                p_clinic_number INT,
                p_first_name VARCHAR,
                p_last_name VARCHAR,
                p_address VARCHAR,
                p_telephone VARCHAR,
                p_date_of_birth DATE,
                p_sex VARCHAR,
                p_marital_status VARCHAR,
                p_kin_name VARCHAR,
                p_kin_relationship VARCHAR,
                p_kin_address VARCHAR,
                p_kin_phone VARCHAR,
                p_ward_number INTEGER,
                p_bed_number INTEGER
            )
            RETURNS VARCHAR(20)
            LANGUAGE plpgsql
            AS $$
            DECLARE
                new_patient_number VARCHAR(20);
            BEGIN
                IF p_bed_number IS NOT NULL
                    AND NOT fn_bed_is_available(p_bed_number, NULL) THEN
                    RAISE EXCEPTION 'Bed already occupied.';
                END IF;

                new_patient_number := register_patient(
                    p_clinic_number,
                    p_first_name,
                    p_last_name,
                    p_address,
                    p_telephone,
                    p_date_of_birth,
                    p_sex,
                    p_marital_status,
                    p_kin_name,
                    p_kin_relationship,
                    p_kin_address,
                    p_kin_phone
                );

                INSERT INTO in_patients (
                    patient_number,
                    ward_number,
                    bed_number,
                    date_admitted,
                    status
                ) VALUES (
                    new_patient_number,
                    p_ward_number,
                    p_bed_number,
                    CURRENT_DATE,
                    'Admitted'
                );

                RETURN new_patient_number;
            END;
            $$;
        SQL);




        // PURPOSE: (procedure)
        // Assigns bed to admitted patient.


        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_assign_patient_bed(
                p_patient_number VARCHAR,
                p_ward_number INTEGER,
                p_bed_number INTEGER
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                active_stay_id BIGINT;
            BEGIN
                IF NOT fn_bed_is_available(p_bed_number, p_patient_number) THEN
                    RAISE EXCEPTION 'Bed already occupied.';
                END IF;

                SELECT in_patient_id
                INTO active_stay_id
                FROM in_patients
                WHERE patient_number = p_patient_number
                    AND date_actual_leave IS NULL
                LIMIT 1;

                UPDATE in_patients
                SET ward_number = p_ward_number,
                    bed_number = p_bed_number,
                    status = 'Admitted'
                WHERE in_patient_id = active_stay_id;
            END;
            $$;
        SQL);




        // PURPOSE:
        // Discharges patient from hospital.

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_discharge_patient(
                p_in_patient_id BIGINT
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                UPDATE in_patients
                SET date_actual_leave = CURRENT_DATE,
                    status = 'Discharged'
                WHERE in_patient_id = p_in_patient_id;
            END;
            $$;
        SQL);


        // PURPOSE:
        // Saves patient vital signs and condition.

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_record_patient_condition(
                p_patient_number VARCHAR,
                p_staff_number VARCHAR,
                p_condition_status VARCHAR,
                p_blood_pressure VARCHAR,
                p_temperature VARCHAR,
                p_heart_rate VARCHAR,
                p_notes TEXT
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                INSERT INTO patient_condition_updates (
                    patient_number,
                    staff_number,
                    condition_status,
                    blood_pressure,
                    temperature,
                    heart_rate,
                    notes
                ) VALUES (
                    p_patient_number,
                    p_staff_number,
                    p_condition_status,
                    p_blood_pressure,
                    p_temperature,
                    p_heart_rate,
                    p_notes
                );
            END;
            $$;
        SQL);




        // PURPOSE:
        // Saves nursing care notes.

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_record_care_note(
                p_patient_number VARCHAR,
                p_staff_number VARCHAR,
                p_note_type VARCHAR,
                p_notes TEXT
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                INSERT INTO care_notes (
                    patient_number,
                    staff_number,
                    note_type,
                    notes
                ) VALUES (
                    p_patient_number,
                    p_staff_number,
                    p_note_type,
                    p_notes
                );
            END;
            $$;
        SQL);




        // PURPOSE:
        // Automatically updates bed status.

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_sync_bed_status()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF TG_OP IN ('UPDATE', 'DELETE') AND OLD.bed_number IS NOT NULL THEN

                    IF NOT EXISTS (
                        SELECT 1
                        FROM in_patients
                        WHERE bed_number = OLD.bed_number
                            AND date_actual_leave IS NULL
                    ) THEN

                        UPDATE beds
                        SET status = 'Available'
                        WHERE bed_number = OLD.bed_number;

                    END IF;
                END IF;

                IF TG_OP IN ('INSERT', 'UPDATE')
                    AND NEW.bed_number IS NOT NULL
                    AND NEW.date_actual_leave IS NULL THEN

                    UPDATE beds
                    SET status = 'Occupied'
                    WHERE bed_number = NEW.bed_number;

                END IF;

                RETURN NEW;
            END;
            $$;
        SQL);


        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_in_patients_sync_bed_status
            AFTER INSERT OR UPDATE OR DELETE
            ON in_patients
            FOR EACH ROW
            EXECUTE FUNCTION fn_sync_bed_status();
        SQL);




        // PURPOSE:
        // Automatically gets patient number from prescription.

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_prepare_medication_administration()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                prescription_patient_number VARCHAR(20);
            BEGIN
                SELECT patient_number
                INTO prescription_patient_number
                FROM patient_medications
                WHERE medication_id = NEW.medication_id
                    AND (
                        end_date IS NULL
                        OR end_date >= CURRENT_DATE
                    );

                NEW.patient_number := prescription_patient_number;

                RETURN NEW;
            END;
            $$;
        SQL);


        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_medication_administrations_prepare_record
            BEFORE INSERT
            ON medication_administrations
            FOR EACH ROW
            EXECUTE FUNCTION fn_prepare_medication_administration();
        SQL);



  
        // PURPOSE:
        // Records medication administered by nurse.


        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_record_medication_administration(
                p_medication_id BIGINT,
                p_staff_number VARCHAR,
                p_dosage_administered VARCHAR,
                p_administered_at TIMESTAMP,
                p_notes TEXT
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                INSERT INTO medication_administrations (
                    medication_id,
                    patient_number,
                    staff_number,
                    dosage_administered,
                    administered_at,
                    notes
                ) VALUES (
                    p_medication_id,
                    '',
                    p_staff_number,
                    p_dosage_administered,
                    COALESCE(p_administered_at, CURRENT_TIMESTAMP),
                    p_notes
                );
            END;
            $$;
        SQL);
        
        // ==================================== END OF MODULE 1 ============================================
       







        // ========================== OTHER FUNCTIONS / PROCEDURES / TRIGGERS ===============================
    

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_create_item_requisition(
                p_staff_number VARCHAR,
                p_ward_number INTEGER,
                p_priority VARCHAR,
                p_status VARCHAR,
                p_notes TEXT,
                p_item_numbers VARCHAR[],
                p_quantities INTEGER[]
            )
            RETURNS INTEGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                new_requisition_number INTEGER;
                i INTEGER;
            BEGIN
                SELECT COALESCE(MAX(requisition_number), 0) + 1
                INTO new_requisition_number
                FROM requisitions;

                INSERT INTO requisitions (
                    requisition_number,
                    staff_number,
                    ward_number,
                    date_ordered,
                    status
                ) VALUES (
                    new_requisition_number,
                    p_staff_number,
                    p_ward_number,
                    CURRENT_DATE,
                    COALESCE(p_status, 'Pending')
                );

                FOR i IN 1..array_length(p_item_numbers, 1) LOOP

                    INSERT INTO requisition_items (
                        requisition_number,
                        item_number,
                        quantity_required
                    ) VALUES (
                        new_requisition_number,
                        p_item_numbers[i],
                        p_quantities[i]
                    );

                END LOOP;

                RETURN new_requisition_number;
            END;
            $$;
        SQL);



        // -------------------------------------------------------------------------------------------------
        // FUNCTION: fn_requisition_delivery_stock()
        // -------------------------------------------------------------------------------------------------
        //
        // PURPOSE:
        // Automatically decreases inventory stock
        // after requisition delivery.
        //
        // FLOW:
        // Requisition marked Delivered
        //      ↓
        // Trigger activates
        //      ↓
        // Item stock decreases
        //

        DB::unprepared(<<<'SQL'
            CREATE FUNCTION fn_requisition_delivery_stock()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF NEW.status = 'Delivered'
                    AND COALESCE(OLD.status, '') <> 'Delivered' THEN

                    UPDATE items
                    SET quantity_of_stock =
                        GREATEST(items.quantity_of_stock - ri.quantity_required, 0)
                    FROM requisition_items ri
                    WHERE ri.item_number = items.item_number
                        AND ri.requisition_number = NEW.requisition_number;

                END IF;

                RETURN NEW;
            END;
            $$;
        SQL);



        // -------------------------------------------------------------------------------------------------
        // TRIGGER: trg_requisition_delivery_stock
        // -------------------------------------------------------------------------------------------------
        //
        // PURPOSE:
        // Automatically updates stock after delivery.
        //

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_requisition_delivery_stock
            AFTER UPDATE
            ON requisitions
            FOR EACH ROW
            EXECUTE FUNCTION fn_requisition_delivery_stock();
        SQL);



        // -------------------------------------------------------------------------------------------------
        // PROCEDURE: sp_confirm_requisition_delivery()
        // -------------------------------------------------------------------------------------------------
        //
        // PURPOSE:
        // Marks requisition as delivered.
        //
        // FLOW:
        // Delivery confirmed
        //      ↓
        // Status becomes Delivered
        //      ↓
        // Stock automatically updated
        //

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_confirm_requisition_delivery(
                p_requisition_number INTEGER
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                UPDATE requisitions
                SET status = 'Delivered',
                    date_received = CURRENT_DATE
                WHERE requisition_number = p_requisition_number
                    AND status <> 'Delivered';
            END;
            $$;
        SQL);
    }



    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // MODULE 1
        DB::unprepared('DROP TRIGGER IF EXISTS trg_in_patients_sync_bed_status ON in_patients');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_medication_administrations_prepare_record ON medication_administrations');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_sync_bed_status()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_prepare_medication_administration()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_next_patient_number()');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_bed_is_available(INTEGER, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS register_patient(INT, VARCHAR, VARCHAR, VARCHAR, VARCHAR, DATE, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR)');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_admit_patient(INT, VARCHAR, VARCHAR, VARCHAR, VARCHAR, DATE, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, INTEGER, INTEGER)');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_assign_patient_bed(VARCHAR, INTEGER, INTEGER)');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_discharge_patient(BIGINT)');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_record_patient_condition(VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, TEXT)');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_record_care_note(VARCHAR, VARCHAR, VARCHAR, TEXT)');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_record_medication_administration(BIGINT, VARCHAR, VARCHAR, TIMESTAMP, TEXT)');

        DB::unprepared('DROP TABLE IF EXISTS medication_administrations');
        DB::unprepared('DROP TABLE IF EXISTS patient_condition_updates');
        DB::unprepared('DROP TABLE IF EXISTS care_notes');



        // OTHER MODULES
        DB::unprepared('DROP TRIGGER IF EXISTS trg_requisition_delivery_stock ON requisitions');

        DB::unprepared('DROP FUNCTION IF EXISTS fn_create_item_requisition(VARCHAR, INTEGER, VARCHAR, VARCHAR, TEXT, VARCHAR[], INTEGER[])');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_requisition_delivery_stock()');

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_confirm_requisition_delivery(INTEGER)');
    }
};