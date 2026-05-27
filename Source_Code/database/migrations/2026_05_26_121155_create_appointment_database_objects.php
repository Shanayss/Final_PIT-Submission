<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | FUNCTION: Appointment Dashboard Counts
        |--------------------------------------------------------------------------
        | Used for the appointment dashboard cards:
        | - Total appointments
        | - Pending/Scheduled appointments
        | - Completed appointments
        */
        DB::unprepared("
            CREATE OR REPLACE FUNCTION get_appointment_dashboard_counts()
            RETURNS TABLE (
                total_appointments BIGINT,
                pending_appointments BIGINT,
                completed_appointments BIGINT
            )
            LANGUAGE plpgsql
            AS $$
            BEGIN
                RETURN QUERY
                SELECT
                    COUNT(*) AS total_appointments,
                    COUNT(*) FILTER (WHERE status IN ('Scheduled', 'Pending')) AS pending_appointments,
                    COUNT(*) FILTER (WHERE status = 'Completed') AS completed_appointments
                FROM appointments;
            END;
            $$;
        ");


        /*
        |--------------------------------------------------------------------------
        | PROCEDURE: Schedule Clinical Appointment
        |--------------------------------------------------------------------------
        | Inserts a new appointment.
        | It automatically gets the clinic_number from the selected patient.
        */
        DB::unprepared("
            CREATE OR REPLACE PROCEDURE schedule_clinical_appointment(
                p_patient_number TEXT,
                p_staff_number TEXT,
                p_appointment_date DATE,
                p_appointment_time TIME,
                p_examination_room TEXT,
                p_status TEXT
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                v_clinic_number INTEGER;
            BEGIN
                SELECT clinic_number
                INTO v_clinic_number
                FROM patients
                WHERE patient_number = p_patient_number;

                IF v_clinic_number IS NULL THEN
                    RAISE EXCEPTION 'Patient % does not exist or has no clinic number', p_patient_number;
                END IF;

                INSERT INTO appointments (
                    patient_number,
                    clinic_number,
                    staff_number,
                    appointment_date,
                    appointment_time,
                    examination_room,
                    status
                )
                VALUES (
                    p_patient_number,
                    v_clinic_number,
                    p_staff_number,
                    p_appointment_date,
                    p_appointment_time,
                    p_examination_room,
                    p_status
                );
            END;
            $$;
        ");


        /*
        |--------------------------------------------------------------------------
        | TRIGGER FUNCTION: Prevent Duplicate Appointment
        |--------------------------------------------------------------------------
        | Prevents double-booking:
        | - same room, date, and time
        | - same consultant, date, and time
        */
        DB::unprepared("
            CREATE OR REPLACE FUNCTION prevent_duplicate_appointment()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM appointments
                    WHERE appointment_date = NEW.appointment_date
                      AND appointment_time = NEW.appointment_time
                      AND examination_room = NEW.examination_room
                      AND appointment_id <> COALESCE(NEW.appointment_id, -1)
                ) THEN
                    RAISE EXCEPTION 'This room is already booked at this date and time.';
                END IF;

                IF EXISTS (
                    SELECT 1
                    FROM appointments
                    WHERE appointment_date = NEW.appointment_date
                      AND appointment_time = NEW.appointment_time
                      AND staff_number = NEW.staff_number
                      AND appointment_id <> COALESCE(NEW.appointment_id, -1)
                ) THEN
                    RAISE EXCEPTION 'This doctor/consultant already has an appointment at this date and time.';
                END IF;

                RETURN NEW;
            END;
            $$;
        ");


        /*
        |--------------------------------------------------------------------------
        | TRIGGER: Attach Duplicate Checker to Appointments Table
        |--------------------------------------------------------------------------
        */
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_prevent_duplicate_appointment ON appointments;

            CREATE TRIGGER trg_prevent_duplicate_appointment
            BEFORE INSERT OR UPDATE ON appointments
            FOR EACH ROW
            EXECUTE FUNCTION prevent_duplicate_appointment();
        ");
    }

    public function down(): void
    {
        DB::unprepared("
            DROP TRIGGER IF EXISTS trg_prevent_duplicate_appointment ON appointments;
        ");

        DB::unprepared("
            DROP FUNCTION IF EXISTS prevent_duplicate_appointment();
        ");

        DB::unprepared("
            DROP PROCEDURE IF EXISTS schedule_clinical_appointment(
                TEXT,
                TEXT,
                DATE,
                TIME,
                TEXT,
                TEXT
            );
        ");

        DB::unprepared("
            DROP FUNCTION IF EXISTS get_appointment_dashboard_counts();
        ");
    }
};