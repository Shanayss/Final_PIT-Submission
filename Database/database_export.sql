--
-- PostgreSQL database dump
--

\restrict jeX37tB01Fm62cHfDA1SsbS5RjaP6OopbRgHBQxa5nZWgFt5pGMy3a1o6AYfcWQ

-- Dumped from database version 18.3
-- Dumped by pg_dump version 18.3

-- Started on 2026-05-28 01:09:40

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 296 (class 1255 OID 44779)
-- Name: add_or_update_staff_allocation(character varying, integer, character varying, character varying, date); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.add_or_update_staff_allocation(IN p_staff_number character varying, IN p_ward_number integer, IN p_role_for_week character varying, IN p_shift character varying, IN p_week_start_date date)
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


ALTER PROCEDURE public.add_or_update_staff_allocation(IN p_staff_number character varying, IN p_ward_number integer, IN p_role_for_week character varying, IN p_shift character varying, IN p_week_start_date date) OWNER TO postgres;

--
-- TOC entry 298 (class 1255 OID 44772)
-- Name: fn_admit_patient(integer, character varying, character varying, character varying, character varying, date, character varying, character varying, character varying, character varying, character varying, character varying, integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_admit_patient(p_clinic_number integer, p_first_name character varying, p_last_name character varying, p_address character varying, p_telephone character varying, p_date_of_birth date, p_sex character varying, p_marital_status character varying, p_kin_name character varying, p_kin_relationship character varying, p_kin_address character varying, p_kin_phone character varying, p_ward_number integer, p_bed_number integer) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
    DECLARE
        new_patient_number VARCHAR(20);
    BEGIN
        IF p_bed_number IS NOT NULL AND NOT EXISTS (
            SELECT 1 FROM beds WHERE bed_number = p_bed_number AND ward_number = p_ward_number
        ) THEN
            RAISE EXCEPTION 'The selected bed does not belong to the selected ward.';
        END IF;

        IF p_bed_number IS NOT NULL AND NOT fn_bed_is_available(p_bed_number, NULL) THEN
            RAISE EXCEPTION 'That bed is already assigned to an active patient.';
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


ALTER FUNCTION public.fn_admit_patient(p_clinic_number integer, p_first_name character varying, p_last_name character varying, p_address character varying, p_telephone character varying, p_date_of_birth date, p_sex character varying, p_marital_status character varying, p_kin_name character varying, p_kin_relationship character varying, p_kin_address character varying, p_kin_phone character varying, p_ward_number integer, p_bed_number integer) OWNER TO postgres;

--
-- TOC entry 279 (class 1255 OID 44710)
-- Name: fn_bed_is_available(integer, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_bed_is_available(p_bed_number integer, p_exclude_patient_number character varying DEFAULT NULL::character varying) RETURNS boolean
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


ALTER FUNCTION public.fn_bed_is_available(p_bed_number integer, p_exclude_patient_number character varying) OWNER TO postgres;

--
-- TOC entry 301 (class 1255 OID 44775)
-- Name: fn_create_item_requisition(character varying, integer, character varying, character varying, text, character varying[], integer[]); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_create_item_requisition(p_staff_number character varying, p_ward_number integer, p_priority character varying, p_status character varying, p_notes text, p_item_numbers character varying[], p_quantities integer[]) RETURNS integer
    LANGUAGE plpgsql
    AS $$
    DECLARE
        new_requisition_number INTEGER;
        i INTEGER;
    BEGIN
        IF array_length(p_item_numbers, 1) IS NULL OR array_length(p_item_numbers, 1) = 0 THEN
            RAISE EXCEPTION 'At least one item is required.';
        END IF;

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
            IF p_quantities[i] IS NULL OR p_quantities[i] <= 0 THEN
                RAISE EXCEPTION 'Quantity must be greater than zero.';
            END IF;

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


ALTER FUNCTION public.fn_create_item_requisition(p_staff_number character varying, p_ward_number integer, p_priority character varying, p_status character varying, p_notes text, p_item_numbers character varying[], p_quantities integer[]) OWNER TO postgres;

--
-- TOC entry 278 (class 1255 OID 44709)
-- Name: fn_next_patient_number(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_next_patient_number() RETURNS character varying
    LANGUAGE plpgsql
    AS $$
    DECLARE
        next_number INTEGER;
    BEGIN
        SELECT COALESCE(MAX(CAST(SUBSTRING(patient_number FROM 2) AS INTEGER)), 0) + 1
        INTO next_number
        FROM patients
        WHERE patient_number LIKE 'P%';

        RETURN 'P' || LPAD(next_number::TEXT, 5, '0');
    END;
    $$;


ALTER FUNCTION public.fn_next_patient_number() OWNER TO postgres;

--
-- TOC entry 294 (class 1255 OID 44716)
-- Name: fn_prepare_medication_administration(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_prepare_medication_administration() RETURNS trigger
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

        IF prescription_patient_number IS NULL THEN
            RAISE EXCEPTION 'Medication prescription is not active.';
        END IF;

        NEW.patient_number := prescription_patient_number;

        IF NEW.administered_at IS NULL THEN
            NEW.administered_at := CURRENT_TIMESTAMP;
        END IF;

        RETURN NEW;
    END;
    $$;


ALTER FUNCTION public.fn_prepare_medication_administration() OWNER TO postgres;

--
-- TOC entry 302 (class 1255 OID 44776)
-- Name: fn_requisition_delivery_stock(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_requisition_delivery_stock() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF NEW.status = 'Delivered' AND COALESCE(OLD.status, '') <> 'Delivered' THEN
            UPDATE items
            SET quantity_of_stock = GREATEST(items.quantity_of_stock - ri.quantity_required, 0)
            FROM requisition_items ri
            WHERE ri.item_number = items.item_number
                AND ri.requisition_number = NEW.requisition_number;
        END IF;

        RETURN NEW;
    END;
    $$;


ALTER FUNCTION public.fn_requisition_delivery_stock() OWNER TO postgres;

--
-- TOC entry 280 (class 1255 OID 44711)
-- Name: fn_sync_bed_status(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_sync_bed_status() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF TG_OP IN ('UPDATE', 'DELETE') AND OLD.bed_number IS NOT NULL THEN
            IF NOT EXISTS (
                SELECT 1
                FROM in_patients
                WHERE bed_number = OLD.bed_number
                    AND date_actual_leave IS NULL
                    AND (TG_OP = 'DELETE' OR in_patient_id <> OLD.in_patient_id)
            ) THEN
                UPDATE beds
                SET status = 'Available'
                WHERE bed_number = OLD.bed_number;
            END IF;
        END IF;

        IF TG_OP IN ('INSERT', 'UPDATE') AND NEW.bed_number IS NOT NULL AND NEW.date_actual_leave IS NULL THEN
            UPDATE beds
            SET status = 'Occupied'
            WHERE bed_number = NEW.bed_number;
        END IF;

        IF TG_OP = 'DELETE' THEN
            RETURN OLD;
        END IF;

        RETURN NEW;
    END;
    $$;


ALTER FUNCTION public.fn_sync_bed_status() OWNER TO postgres;

--
-- TOC entry 297 (class 1255 OID 44771)
-- Name: register_patient(integer, character varying, character varying, character varying, character varying, date, character varying, character varying, character varying, character varying, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.register_patient(p_clinic_number integer, p_fname character varying, p_lname character varying, p_address character varying, p_phone character varying, p_dob date, p_sex character varying, p_status character varying, p_kin_name character varying, p_relationship character varying, p_kin_address character varying, p_kin_phone character varying) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
    DECLARE
        next_num INT;
        new_patient_id VARCHAR(20);
    BEGIN
        next_num := nextval('patient_seq');
        new_patient_id := 'P' || LPAD(next_num::TEXT, 5, '0');

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


ALTER FUNCTION public.register_patient(p_clinic_number integer, p_fname character varying, p_lname character varying, p_address character varying, p_phone character varying, p_dob date, p_sex character varying, p_status character varying, p_kin_name character varying, p_relationship character varying, p_kin_address character varying, p_kin_phone character varying) OWNER TO postgres;

--
-- TOC entry 292 (class 1255 OID 44713)
-- Name: sp_assign_patient_bed(character varying, integer, integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_assign_patient_bed(IN p_patient_number character varying, IN p_ward_number integer, IN p_bed_number integer)
    LANGUAGE plpgsql
    AS $$
    DECLARE
        active_stay_id BIGINT;
        current_bed_number INTEGER;
    BEGIN
        IF NOT EXISTS (
            SELECT 1
            FROM beds
            WHERE bed_number = p_bed_number
                AND ward_number = p_ward_number
        ) THEN
            RAISE EXCEPTION 'The selected bed does not belong to the selected ward.';
        END IF;

        IF NOT fn_bed_is_available(p_bed_number, p_patient_number) THEN
            RAISE EXCEPTION 'That bed is already assigned to an active patient.';
        END IF;

        SELECT in_patient_id
        , bed_number
        INTO active_stay_id
        , current_bed_number
        FROM in_patients
        WHERE patient_number = p_patient_number
            AND date_actual_leave IS NULL
        ORDER BY in_patient_id DESC
        LIMIT 1;

        IF active_stay_id IS NULL THEN
            RAISE EXCEPTION 'Patient is not currently awaiting bed assignment.';
        END IF;

        IF current_bed_number IS NOT NULL THEN
            RAISE EXCEPTION 'Patient already has an assigned bed.';
        END IF;

        UPDATE in_patients
        SET ward_number = p_ward_number,
            bed_number = p_bed_number,
            status = 'Admitted'
        WHERE in_patient_id = active_stay_id;
    END;
    $$;


ALTER PROCEDURE public.sp_assign_patient_bed(IN p_patient_number character varying, IN p_ward_number integer, IN p_bed_number integer) OWNER TO postgres;

--
-- TOC entry 303 (class 1255 OID 44778)
-- Name: sp_confirm_requisition_delivery(integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_confirm_requisition_delivery(IN p_requisition_number integer)
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


ALTER PROCEDURE public.sp_confirm_requisition_delivery(IN p_requisition_number integer) OWNER TO postgres;

--
-- TOC entry 293 (class 1255 OID 44715)
-- Name: sp_discharge_patient(bigint); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_discharge_patient(IN p_in_patient_id bigint)
    LANGUAGE plpgsql
    AS $$
    BEGIN
        UPDATE in_patients
        SET date_actual_leave = CURRENT_DATE,
            status = 'Discharged'
        WHERE in_patient_id = p_in_patient_id;
    END;
    $$;


ALTER PROCEDURE public.sp_discharge_patient(IN p_in_patient_id bigint) OWNER TO postgres;

--
-- TOC entry 300 (class 1255 OID 44774)
-- Name: sp_record_care_note(character varying, character varying, character varying, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_record_care_note(IN p_patient_number character varying, IN p_staff_number character varying, IN p_note_type character varying, IN p_notes text)
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF NOT EXISTS (
            SELECT 1 FROM in_patients
            WHERE patient_number = p_patient_number AND date_actual_leave IS NULL
        ) THEN
            RAISE EXCEPTION 'Patient is not currently admitted.';
        END IF;

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


ALTER PROCEDURE public.sp_record_care_note(IN p_patient_number character varying, IN p_staff_number character varying, IN p_note_type character varying, IN p_notes text) OWNER TO postgres;

--
-- TOC entry 295 (class 1255 OID 44718)
-- Name: sp_record_medication_administration(bigint, character varying, character varying, timestamp without time zone, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_record_medication_administration(IN p_medication_id bigint, IN p_staff_number character varying, IN p_dosage_administered character varying, IN p_administered_at timestamp without time zone, IN p_notes text)
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


ALTER PROCEDURE public.sp_record_medication_administration(IN p_medication_id bigint, IN p_staff_number character varying, IN p_dosage_administered character varying, IN p_administered_at timestamp without time zone, IN p_notes text) OWNER TO postgres;

--
-- TOC entry 299 (class 1255 OID 44773)
-- Name: sp_record_patient_condition(character varying, character varying, character varying, character varying, character varying, character varying, text); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_record_patient_condition(IN p_patient_number character varying, IN p_staff_number character varying, IN p_condition_status character varying, IN p_blood_pressure character varying, IN p_temperature character varying, IN p_heart_rate character varying, IN p_notes text)
    LANGUAGE plpgsql
    AS $$
    BEGIN
        IF NOT EXISTS (
            SELECT 1 FROM in_patients
            WHERE patient_number = p_patient_number AND date_actual_leave IS NULL
        ) THEN
            RAISE EXCEPTION 'Patient is not currently admitted.';
        END IF;

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


ALTER PROCEDURE public.sp_record_patient_condition(IN p_patient_number character varying, IN p_staff_number character varying, IN p_condition_status character varying, IN p_blood_pressure character varying, IN p_temperature character varying, IN p_heart_rate character varying, IN p_notes text) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 245 (class 1259 OID 44346)
-- Name: appointments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.appointments (
    appointment_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    clinic_number integer,
    staff_number character varying(10),
    appointment_date date NOT NULL,
    appointment_time time(0) without time zone NOT NULL,
    examination_room character varying(20),
    status character varying(20) DEFAULT 'Scheduled'::character varying NOT NULL
);


ALTER TABLE public.appointments OWNER TO postgres;

--
-- TOC entry 244 (class 1259 OID 44345)
-- Name: appointments_appointment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.appointments_appointment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.appointments_appointment_id_seq OWNER TO postgres;

--
-- TOC entry 5425 (class 0 OID 0)
-- Dependencies: 244
-- Name: appointments_appointment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.appointments_appointment_id_seq OWNED BY public.appointments.appointment_id;


--
-- TOC entry 237 (class 1259 OID 44281)
-- Name: beds; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.beds (
    bed_number integer NOT NULL,
    ward_number integer NOT NULL,
    status character varying(20) DEFAULT 'Available'::character varying NOT NULL
);


ALTER TABLE public.beds OWNER TO postgres;

--
-- TOC entry 268 (class 1259 OID 44643)
-- Name: bill_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bill_items (
    bill_item_id bigint NOT NULL,
    bill_id bigint NOT NULL,
    item_type character varying(50) NOT NULL,
    reference_id integer,
    description character varying(100),
    quantity integer DEFAULT 1 NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    total numeric(10,2) NOT NULL
);


ALTER TABLE public.bill_items OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 44642)
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bill_items_bill_item_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bill_items_bill_item_id_seq OWNER TO postgres;

--
-- TOC entry 5426 (class 0 OID 0)
-- Dependencies: 267
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bill_items_bill_item_id_seq OWNED BY public.bill_items.bill_item_id;


--
-- TOC entry 266 (class 1259 OID 44621)
-- Name: bills; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bills (
    bill_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    in_patient_id bigint,
    total_amount numeric(10,2) NOT NULL,
    bill_date date NOT NULL,
    status character varying(20) NOT NULL
);


ALTER TABLE public.bills OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 44620)
-- Name: bills_bill_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bills_bill_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.bills_bill_id_seq OWNER TO postgres;

--
-- TOC entry 5427 (class 0 OID 0)
-- Dependencies: 265
-- Name: bills_bill_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bills_bill_id_seq OWNED BY public.bills.bill_id;


--
-- TOC entry 225 (class 1259 OID 44154)
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 44165)
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- TOC entry 277 (class 1259 OID 44746)
-- Name: care_notes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.care_notes (
    care_note_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    staff_number character varying(10),
    note_type character varying(50) DEFAULT 'General Observation'::character varying NOT NULL,
    notes text NOT NULL,
    recorded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.care_notes OWNER TO postgres;

--
-- TOC entry 276 (class 1259 OID 44745)
-- Name: care_notes_care_note_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.care_notes_care_note_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.care_notes_care_note_id_seq OWNER TO postgres;

--
-- TOC entry 5428 (class 0 OID 0)
-- Dependencies: 276
-- Name: care_notes_care_note_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.care_notes_care_note_id_seq OWNED BY public.care_notes.care_note_id;


--
-- TOC entry 247 (class 1259 OID 44374)
-- Name: diagnoses; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.diagnoses (
    diagnosis_id bigint NOT NULL,
    appointment_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    staff_number character varying(10),
    diagnosis_details character varying(255),
    diagnosis_date date,
    notes text
);


ALTER TABLE public.diagnoses OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 44373)
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.diagnoses_diagnosis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.diagnoses_diagnosis_id_seq OWNER TO postgres;

--
-- TOC entry 5429 (class 0 OID 0)
-- Dependencies: 246
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.diagnoses_diagnosis_id_seq OWNED BY public.diagnoses.diagnosis_id;


--
-- TOC entry 250 (class 1259 OID 44428)
-- Name: drugs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.drugs (
    drug_number character varying(20) NOT NULL,
    drug_name character varying(100) NOT NULL,
    description character varying(100),
    dosage character varying(50),
    method_of_admin character varying(50),
    quantity_of_stock integer DEFAULT 0 NOT NULL,
    reorder_level integer DEFAULT 0 NOT NULL,
    cost_per_unit numeric(10,2)
);


ALTER TABLE public.drugs OWNER TO postgres;

--
-- TOC entry 231 (class 1259 OID 44207)
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 44206)
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- TOC entry 5430 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- TOC entry 260 (class 1259 OID 44553)
-- Name: in_patients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.in_patients (
    in_patient_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    ward_number integer,
    bed_number integer,
    date_placed_on_waiting_list date,
    expected_stay_days integer,
    date_admitted date NOT NULL,
    date_expected_leave date,
    date_actual_leave date,
    status character varying(20) DEFAULT 'Waiting'::character varying NOT NULL
);


ALTER TABLE public.in_patients OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 44552)
-- Name: in_patients_in_patient_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.in_patients_in_patient_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.in_patients_in_patient_id_seq OWNER TO postgres;

--
-- TOC entry 5431 (class 0 OID 0)
-- Dependencies: 259
-- Name: in_patients_in_patient_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.in_patients_in_patient_id_seq OWNED BY public.in_patients.in_patient_id;


--
-- TOC entry 251 (class 1259 OID 44439)
-- Name: items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.items (
    item_number character varying(20) NOT NULL,
    item_name character varying(100) NOT NULL,
    description character varying(100),
    quantity_of_stock integer DEFAULT 0 NOT NULL,
    reorder_level integer DEFAULT 0 NOT NULL,
    cost_per_unit numeric(10,2)
);


ALTER TABLE public.items OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 44192)
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 44177)
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 44176)
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- TOC entry 5432 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- TOC entry 234 (class 1259 OID 44252)
-- Name: local_doctors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.local_doctors (
    clinic_number integer NOT NULL,
    full_name character varying(100) NOT NULL,
    address character varying(100),
    telephone character varying(20)
);


ALTER TABLE public.local_doctors OWNER TO postgres;

--
-- TOC entry 272 (class 1259 OID 44681)
-- Name: medication_administrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.medication_administrations (
    administration_id bigint NOT NULL,
    medication_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    staff_number character varying(10),
    dosage_administered character varying(50),
    administered_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    notes text
);


ALTER TABLE public.medication_administrations OWNER TO postgres;

--
-- TOC entry 271 (class 1259 OID 44680)
-- Name: medication_administrations_administration_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.medication_administrations_administration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.medication_administrations_administration_id_seq OWNER TO postgres;

--
-- TOC entry 5433 (class 0 OID 0)
-- Dependencies: 271
-- Name: medication_administrations_administration_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.medication_administrations_administration_id_seq OWNED BY public.medication_administrations.administration_id;


--
-- TOC entry 220 (class 1259 OID 44109)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 44108)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 5434 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 262 (class 1259 OID 44580)
-- Name: next_of_kins; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.next_of_kins (
    kin_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    full_name character varying(100) NOT NULL,
    relationship character varying(50) NOT NULL,
    address character varying(100),
    telephone character varying(20)
);


ALTER TABLE public.next_of_kins OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 44579)
-- Name: next_of_kins_kin_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.next_of_kins_kin_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.next_of_kins_kin_id_seq OWNER TO postgres;

--
-- TOC entry 5435 (class 0 OID 0)
-- Dependencies: 261
-- Name: next_of_kins_kin_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.next_of_kins_kin_id_seq OWNED BY public.next_of_kins.kin_id;


--
-- TOC entry 223 (class 1259 OID 44133)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- TOC entry 275 (class 1259 OID 44721)
-- Name: patient_condition_updates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.patient_condition_updates (
    condition_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    staff_number character varying(10),
    condition_status character varying(30) DEFAULT 'Stable'::character varying NOT NULL,
    blood_pressure character varying(20),
    temperature character varying(20),
    heart_rate character varying(20),
    notes text,
    recorded_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.patient_condition_updates OWNER TO postgres;

--
-- TOC entry 274 (class 1259 OID 44720)
-- Name: patient_condition_updates_condition_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.patient_condition_updates_condition_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.patient_condition_updates_condition_id_seq OWNER TO postgres;

--
-- TOC entry 5436 (class 0 OID 0)
-- Dependencies: 274
-- Name: patient_condition_updates_condition_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.patient_condition_updates_condition_id_seq OWNED BY public.patient_condition_updates.condition_id;


--
-- TOC entry 264 (class 1259 OID 44596)
-- Name: patient_medications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.patient_medications (
    medication_id bigint NOT NULL,
    staff_number character varying(10),
    patient_number character varying(20) NOT NULL,
    drug_number character varying(20) NOT NULL,
    unit_per_day integer,
    start_date date,
    end_date date
);


ALTER TABLE public.patient_medications OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 44595)
-- Name: patient_medications_medication_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.patient_medications_medication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.patient_medications_medication_id_seq OWNER TO postgres;

--
-- TOC entry 5437 (class 0 OID 0)
-- Dependencies: 263
-- Name: patient_medications_medication_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.patient_medications_medication_id_seq OWNED BY public.patient_medications.medication_id;


--
-- TOC entry 273 (class 1259 OID 44719)
-- Name: patient_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.patient_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.patient_seq OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 44259)
-- Name: patients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.patients (
    patient_number character varying(20) NOT NULL,
    clinic_number integer,
    first_name character varying(50) NOT NULL,
    last_name character varying(50) NOT NULL,
    address character varying(100),
    telephone character varying(20),
    date_of_birth date,
    sex character varying(10),
    marital_status character varying(20),
    date_registered date DEFAULT CURRENT_DATE NOT NULL
);


ALTER TABLE public.patients OWNER TO postgres;

--
-- TOC entry 270 (class 1259 OID 44662)
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    payment_id bigint NOT NULL,
    bill_id bigint NOT NULL,
    payment_date date NOT NULL,
    amount_paid numeric(10,2) NOT NULL,
    payment_method character varying(20) NOT NULL
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- TOC entry 269 (class 1259 OID 44661)
-- Name: payments_payment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_payment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payments_payment_id_seq OWNER TO postgres;

--
-- TOC entry 5438 (class 0 OID 0)
-- Dependencies: 269
-- Name: payments_payment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_payment_id_seq OWNED BY public.payments.payment_id;


--
-- TOC entry 239 (class 1259 OID 44298)
-- Name: qualifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.qualifications (
    qualification_id bigint NOT NULL,
    staff_number character varying(10) NOT NULL,
    qualification_type character varying(100),
    qualification_date date,
    institution_name character varying(100)
);


ALTER TABLE public.qualifications OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 44297)
-- Name: qualifications_qualification_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.qualifications_qualification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.qualifications_qualification_id_seq OWNER TO postgres;

--
-- TOC entry 5439 (class 0 OID 0)
-- Dependencies: 238
-- Name: qualifications_qualification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.qualifications_qualification_id_seq OWNED BY public.qualifications.qualification_id;


--
-- TOC entry 258 (class 1259 OID 44534)
-- Name: requisition_drugs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.requisition_drugs (
    requisition_number integer NOT NULL,
    drug_number character varying(20) NOT NULL,
    quantity_required integer NOT NULL
);


ALTER TABLE public.requisition_drugs OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 44516)
-- Name: requisition_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.requisition_items (
    requisition_number integer NOT NULL,
    item_number character varying(20) NOT NULL,
    quantity_required integer NOT NULL
);


ALTER TABLE public.requisition_items OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 44496)
-- Name: requisitions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.requisitions (
    requisition_number integer NOT NULL,
    staff_number character varying(10),
    ward_number integer NOT NULL,
    date_ordered date NOT NULL,
    date_received date,
    status character varying(20) DEFAULT 'Pending'::character varying NOT NULL
);


ALTER TABLE public.requisitions OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 44225)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    role_id integer NOT NULL,
    role_name character varying(50) NOT NULL
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 44142)
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 44232)
-- Name: staff; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff (
    staff_number character varying(10) NOT NULL,
    role_id integer NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    first_name character varying(255),
    last_name character varying(255),
    address character varying(255),
    telephone character varying(255),
    date_of_birth date,
    sex character varying(255),
    nin character varying(255),
    "position" character varying(255),
    current_salary numeric(10,2),
    salary_scale character varying(255),
    hours_per_week integer,
    contract_type character varying(255),
    payment_type character varying(255)
);


ALTER TABLE public.staff OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 44326)
-- Name: staff_allocations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.staff_allocations (
    allocation_id bigint NOT NULL,
    staff_number character varying(10) NOT NULL,
    ward_number integer NOT NULL,
    role_for_week character varying(50),
    shift character varying(20),
    week_start_date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.staff_allocations OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 44325)
-- Name: staff_allocations_allocation_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.staff_allocations_allocation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.staff_allocations_allocation_id_seq OWNER TO postgres;

--
-- TOC entry 5440 (class 0 OID 0)
-- Dependencies: 242
-- Name: staff_allocations_allocation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.staff_allocations_allocation_id_seq OWNED BY public.staff_allocations.allocation_id;


--
-- TOC entry 254 (class 1259 OID 44460)
-- Name: supplier_drugs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supplier_drugs (
    supplier_id bigint NOT NULL,
    drug_number character varying(20) NOT NULL,
    unit_price numeric(10,2) NOT NULL
);


ALTER TABLE public.supplier_drugs OWNER TO postgres;

--
-- TOC entry 255 (class 1259 OID 44478)
-- Name: supplier_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.supplier_items (
    supplier_id bigint NOT NULL,
    item_number character varying(20) NOT NULL,
    unit_price numeric(10,2) NOT NULL
);


ALTER TABLE public.supplier_items OWNER TO postgres;

--
-- TOC entry 253 (class 1259 OID 44451)
-- Name: suppliers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.suppliers (
    supplier_id bigint NOT NULL,
    supplier_name character varying(100) NOT NULL,
    address character varying(150) NOT NULL,
    telephone character varying(20),
    fax character varying(20)
);


ALTER TABLE public.suppliers OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 44450)
-- Name: suppliers_supplier_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.suppliers_supplier_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.suppliers_supplier_id_seq OWNER TO postgres;

--
-- TOC entry 5441 (class 0 OID 0)
-- Dependencies: 252
-- Name: suppliers_supplier_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.suppliers_supplier_id_seq OWNED BY public.suppliers.supplier_id;


--
-- TOC entry 249 (class 1259 OID 44401)
-- Name: treatments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.treatments (
    treatment_id bigint NOT NULL,
    patient_number character varying(20) NOT NULL,
    diagnosis_id bigint NOT NULL,
    staff_number character varying(10),
    procedure_name character varying(100),
    treatment_date date,
    treatment_time time(0) without time zone NOT NULL,
    results text
);


ALTER TABLE public.treatments OWNER TO postgres;

--
-- TOC entry 248 (class 1259 OID 44400)
-- Name: treatments_treatment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.treatments_treatment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.treatments_treatment_id_seq OWNER TO postgres;

--
-- TOC entry 5442 (class 0 OID 0)
-- Dependencies: 248
-- Name: treatments_treatment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.treatments_treatment_id_seq OWNED BY public.treatments.treatment_id;


--
-- TOC entry 222 (class 1259 OID 44119)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 44118)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 5443 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 236 (class 1259 OID 44274)
-- Name: wards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.wards (
    ward_number integer NOT NULL,
    ward_name character varying(50) NOT NULL,
    location character varying(50),
    total_beds integer,
    tel_extension integer
);


ALTER TABLE public.wards OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 44312)
-- Name: work_experiences; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.work_experiences (
    experience_id bigint NOT NULL,
    staff_number character varying(10) NOT NULL,
    position_held character varying(100),
    start_date date,
    finish_date date,
    name_of_organization character varying(100)
);


ALTER TABLE public.work_experiences OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 44311)
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.work_experiences_experience_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.work_experiences_experience_id_seq OWNER TO postgres;

--
-- TOC entry 5444 (class 0 OID 0)
-- Dependencies: 240
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.work_experiences_experience_id_seq OWNED BY public.work_experiences.experience_id;


--
-- TOC entry 5049 (class 2604 OID 44349)
-- Name: appointments appointment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments ALTER COLUMN appointment_id SET DEFAULT nextval('public.appointments_appointment_id_seq'::regclass);


--
-- TOC entry 5064 (class 2604 OID 44646)
-- Name: bill_items bill_item_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items ALTER COLUMN bill_item_id SET DEFAULT nextval('public.bill_items_bill_item_id_seq'::regclass);


--
-- TOC entry 5063 (class 2604 OID 44624)
-- Name: bills bill_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills ALTER COLUMN bill_id SET DEFAULT nextval('public.bills_bill_id_seq'::regclass);


--
-- TOC entry 5072 (class 2604 OID 44749)
-- Name: care_notes care_note_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.care_notes ALTER COLUMN care_note_id SET DEFAULT nextval('public.care_notes_care_note_id_seq'::regclass);


--
-- TOC entry 5051 (class 2604 OID 44377)
-- Name: diagnoses diagnosis_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses ALTER COLUMN diagnosis_id SET DEFAULT nextval('public.diagnoses_diagnosis_id_seq'::regclass);


--
-- TOC entry 5042 (class 2604 OID 44210)
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- TOC entry 5059 (class 2604 OID 44556)
-- Name: in_patients in_patient_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.in_patients ALTER COLUMN in_patient_id SET DEFAULT nextval('public.in_patients_in_patient_id_seq'::regclass);


--
-- TOC entry 5041 (class 2604 OID 44180)
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- TOC entry 5067 (class 2604 OID 44684)
-- Name: medication_administrations administration_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_administrations ALTER COLUMN administration_id SET DEFAULT nextval('public.medication_administrations_administration_id_seq'::regclass);


--
-- TOC entry 5039 (class 2604 OID 44112)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 5061 (class 2604 OID 44583)
-- Name: next_of_kins kin_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.next_of_kins ALTER COLUMN kin_id SET DEFAULT nextval('public.next_of_kins_kin_id_seq'::regclass);


--
-- TOC entry 5069 (class 2604 OID 44724)
-- Name: patient_condition_updates condition_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_condition_updates ALTER COLUMN condition_id SET DEFAULT nextval('public.patient_condition_updates_condition_id_seq'::regclass);


--
-- TOC entry 5062 (class 2604 OID 44599)
-- Name: patient_medications medication_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_medications ALTER COLUMN medication_id SET DEFAULT nextval('public.patient_medications_medication_id_seq'::regclass);


--
-- TOC entry 5066 (class 2604 OID 44665)
-- Name: payments payment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN payment_id SET DEFAULT nextval('public.payments_payment_id_seq'::regclass);


--
-- TOC entry 5046 (class 2604 OID 44301)
-- Name: qualifications qualification_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.qualifications ALTER COLUMN qualification_id SET DEFAULT nextval('public.qualifications_qualification_id_seq'::regclass);


--
-- TOC entry 5048 (class 2604 OID 44329)
-- Name: staff_allocations allocation_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_allocations ALTER COLUMN allocation_id SET DEFAULT nextval('public.staff_allocations_allocation_id_seq'::regclass);


--
-- TOC entry 5057 (class 2604 OID 44454)
-- Name: suppliers supplier_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN supplier_id SET DEFAULT nextval('public.suppliers_supplier_id_seq'::regclass);


--
-- TOC entry 5052 (class 2604 OID 44404)
-- Name: treatments treatment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments ALTER COLUMN treatment_id SET DEFAULT nextval('public.treatments_treatment_id_seq'::regclass);


--
-- TOC entry 5040 (class 2604 OID 44122)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5047 (class 2604 OID 44315)
-- Name: work_experiences experience_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.work_experiences ALTER COLUMN experience_id SET DEFAULT nextval('public.work_experiences_experience_id_seq'::regclass);


--
-- TOC entry 5387 (class 0 OID 44346)
-- Dependencies: 245
-- Data for Name: appointments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.appointments (appointment_id, patient_number, clinic_number, staff_number, appointment_date, appointment_time, examination_room, status) FROM stdin;
1	P00001	105	S010	2026-05-18	09:00:00	E001	Completed
2	P00002	101	S031	2026-05-27	10:00:00	E002	Scheduled
3	P00003	110	S053	2026-05-18	11:00:00	E003	Completed
4	P00004	103	S072	2026-05-19	09:30:00	E004	Completed
5	P00005	107	S073	2026-05-19	10:30:00	E005	Completed
6	P00006	102	S010	2026-05-19	11:30:00	E006	Completed
7	P00007	109	S031	2026-05-20	09:00:00	E007	Completed
8	P00008	104	S053	2026-05-20	10:00:00	E008	Completed
9	P00009	106	S072	2026-05-20	11:00:00	E009	Completed
10	P00010	108	S073	2026-05-21	09:30:00	E011	Completed
11	P00011	102	S010	2026-05-21	10:30:00	E012	Completed
12	P00012	110	S031	2026-05-21	11:30:00	E013	Completed
13	P00013	105	S053	2026-05-21	09:00:00	E014	Completed
14	P00014	101	S072	2026-05-21	10:00:00	E015	Completed
15	P00015	107	S073	2026-05-21	11:00:00	E016	Completed
16	P00016	104	S010	2026-05-21	09:30:00	E016	Completed
17	P00017	108	S031	2026-05-21	10:30:00	E017	Completed
18	P00018	103	S053	2026-05-21	11:30:00	E018	Completed
19	P00019	106	S072	2026-05-21	09:00:00	E019	Completed
20	P00020	109	S073	2026-05-21	10:00:00	E020	Completed
22	P00021	\N	S004	2026-05-27	12:50:02	Admission	Completed
24	P00022	\N	S010	2026-05-29	09:00:00	E010	Completed
25	P00023	105	S010	2026-05-27	11:10:00	E012	Completed
\.


--
-- TOC entry 5379 (class 0 OID 44281)
-- Dependencies: 237
-- Data for Name: beds; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.beds (bed_number, ward_number, status) FROM stdin;
1	1	Available
2	1	Available
3	1	Available
4	1	Available
5	1	Available
6	1	Available
7	1	Available
8	1	Available
9	1	Available
10	1	Available
11	1	Available
12	1	Available
13	1	Available
14	1	Available
15	1	Available
16	1	Available
17	2	Available
18	2	Available
19	2	Available
20	2	Available
21	2	Available
22	2	Available
23	2	Available
24	2	Available
25	2	Available
26	2	Available
27	2	Available
28	2	Available
29	2	Available
30	2	Available
31	3	Available
32	3	Available
33	3	Available
34	3	Available
35	3	Available
36	3	Available
37	3	Available
38	3	Available
39	3	Available
40	3	Available
41	3	Available
42	3	Available
43	3	Available
44	3	Available
45	3	Available
46	3	Available
47	3	Available
48	3	Available
49	3	Available
50	3	Available
51	3	Available
52	3	Available
53	4	Available
54	4	Available
55	4	Available
56	4	Available
57	4	Available
58	4	Available
59	4	Available
60	4	Available
61	4	Available
62	4	Available
63	4	Available
64	4	Available
65	5	Available
66	5	Available
67	5	Available
68	5	Available
69	5	Available
70	5	Available
71	5	Available
72	5	Available
74	5	Available
75	5	Available
76	5	Available
77	5	Available
78	5	Available
79	5	Available
80	5	Available
81	5	Available
82	5	Available
83	6	Available
84	6	Available
85	6	Available
86	6	Available
87	6	Available
88	6	Available
89	6	Available
90	6	Available
91	6	Available
92	6	Available
93	6	Available
94	6	Available
95	6	Available
96	6	Available
97	6	Available
98	6	Available
99	7	Available
100	7	Available
101	7	Available
102	7	Available
103	7	Available
104	7	Available
105	7	Available
106	7	Available
107	7	Available
108	7	Available
109	7	Available
111	7	Available
112	7	Available
113	7	Available
114	7	Available
115	7	Available
116	7	Available
117	7	Available
118	7	Available
119	8	Available
120	8	Available
121	8	Available
122	8	Available
123	8	Available
124	8	Available
125	8	Available
126	8	Available
127	8	Available
128	8	Available
129	8	Available
130	8	Available
131	8	Available
132	8	Available
133	8	Available
134	8	Available
135	8	Available
136	8	Available
137	9	Available
138	9	Available
139	9	Available
140	9	Available
141	9	Available
142	9	Available
143	9	Available
144	9	Available
145	9	Available
146	9	Available
147	9	Available
148	9	Available
149	10	Available
150	10	Available
151	10	Available
152	10	Available
153	10	Available
154	10	Available
155	10	Available
156	10	Available
157	10	Available
158	10	Available
159	11	Available
161	11	Available
162	11	Available
163	11	Available
164	11	Available
165	11	Available
166	11	Available
167	12	Available
168	12	Available
169	12	Available
170	12	Available
171	12	Available
172	12	Available
173	12	Available
174	12	Available
175	12	Available
176	12	Available
177	13	Available
178	13	Available
179	13	Available
180	13	Available
181	13	Available
182	13	Available
183	13	Available
184	13	Available
185	13	Available
186	13	Available
187	13	Available
188	13	Available
189	14	Available
190	14	Available
191	14	Available
192	14	Available
193	14	Available
194	14	Available
195	14	Available
196	14	Available
197	14	Available
198	14	Available
199	14	Available
200	14	Available
201	15	Available
202	15	Available
203	15	Available
204	15	Available
205	15	Available
206	15	Available
207	15	Available
208	15	Available
209	15	Available
210	15	Available
211	16	Available
212	16	Available
213	16	Available
214	16	Available
215	16	Available
216	16	Available
217	16	Available
218	16	Available
219	16	Available
220	16	Available
221	16	Available
222	16	Available
223	16	Available
224	16	Available
225	16	Available
227	17	Available
228	17	Available
229	17	Available
230	17	Available
231	17	Available
232	17	Available
233	17	Available
234	17	Available
235	17	Available
236	17	Available
237	17	Available
238	17	Available
239	17	Available
240	17	Available
160	11	Available
226	17	Occupied
73	5	Occupied
110	7	Occupied
\.


--
-- TOC entry 5410 (class 0 OID 44643)
-- Dependencies: 268
-- Data for Name: bill_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bill_items (bill_item_id, bill_id, item_type, reference_id, description, quantity, unit_price, total) FROM stdin;
1	1	Room	\N	General Ward	3	1500.00	4500.00
2	2	Treatment	\N	ICU Monitoring	1	5000.00	5000.00
3	2	Service	\N	Lab Test	1	3500.00	3500.00
4	3	Service	\N	Consultation	2	1500.00	3000.00
5	4	Room	\N	Dialysis Ward	2	2500.00	5000.00
6	4	Treatment	\N	Dialysis	1	1500.00	1500.00
7	5	Room	\N	Emergency Ward	2	3000.00	6000.00
8	5	Service	\N	X-Ray	1	1200.00	1200.00
9	6	Treatment	\N	Surgery	1	9500.00	9500.00
10	7	Room	\N	Rehab Ward	2	1800.00	3600.00
11	7	Treatment	\N	Physical Therapy	2	800.00	1600.00
12	8	Treatment	\N	Cardiac Monitoring	1	5500.00	5500.00
13	8	Service	\N	ECG	1	2600.00	2600.00
14	9	Room	\N	Neurology Ward	1	4000.00	4000.00
15	10	Room	\N	Recovery Ward	1	3500.00	3500.00
16	11	Service	\N	Consultation	1	1500.00	1500.00
17	12	Service	\N	Consultation + Lab	1	2200.00	2200.00
18	13	Service	\N	Checkup	1	1800.00	1800.00
19	14	Service	\N	Emergency Visit	1	2500.00	2500.00
20	15	Service	\N	Follow-up	1	1700.00	1700.00
21	16	Service	\N	Consultation	1	2000.00	2000.00
22	17	Service	\N	Checkup	1	1600.00	1600.00
23	18	Service	\N	Lab Test	1	1900.00	1900.00
24	19	Service	\N	Consultation	1	2100.00	2100.00
25	20	Service	\N	Final Checkup	1	2300.00	2300.00
\.


--
-- TOC entry 5408 (class 0 OID 44621)
-- Dependencies: 266
-- Data for Name: bills; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bills (bill_id, patient_number, in_patient_id, total_amount, bill_date, status) FROM stdin;
1	P00001	1	4500.00	2026-05-06	Paid
2	P00002	2	8500.00	2026-05-08	Paid
3	P00003	3	3000.00	2026-05-12	Unpaid
4	P00004	4	6500.00	2026-05-05	Paid
5	P00005	5	7200.00	2026-05-17	Partial
6	P00006	6	9500.00	2026-05-09	Unpaid
7	P00007	7	5200.00	2026-05-08	Paid
8	P00008	8	8100.00	2026-05-10	Paid
9	P00009	9	4000.00	2026-05-10	Paid
10	P00010	10	6700.00	2026-05-05	Unpaid
11	P00011	\N	1500.00	2026-05-18	Paid
12	P00012	\N	2200.00	2026-05-18	Paid
13	P00013	\N	1800.00	2026-05-19	Paid
14	P00014	\N	2500.00	2026-05-19	Paid
15	P00015	\N	1700.00	2026-05-20	Paid
16	P00016	\N	2000.00	2026-05-20	Paid
17	P00017	\N	1600.00	2026-05-21	Paid
18	P00018	\N	1900.00	2026-05-21	Paid
19	P00019	\N	2100.00	2026-05-22	Paid
20	P00020	\N	2300.00	2026-05-22	Paid
\.


--
-- TOC entry 5367 (class 0 OID 44154)
-- Dependencies: 225
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- TOC entry 5368 (class 0 OID 44165)
-- Dependencies: 226
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- TOC entry 5419 (class 0 OID 44746)
-- Dependencies: 277
-- Data for Name: care_notes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.care_notes (care_note_id, patient_number, staff_number, note_type, notes, recorded_at) FROM stdin;
\.


--
-- TOC entry 5389 (class 0 OID 44374)
-- Dependencies: 247
-- Data for Name: diagnoses; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.diagnoses (diagnosis_id, appointment_id, patient_number, staff_number, diagnosis_details, diagnosis_date, notes) FROM stdin;
1	1	P00001	S001	Acute Gastritis	2026-05-10	Patient advised to avoid spicy food.
2	2	P00002	S006	Hypertension Stage 1	2026-05-10	Prescribed Amlodipine.
3	3	P00003	S010	Common Cold	2026-05-11	Rest and hydration advised.
4	4	P00004	S013	Sprained Ankle	2026-05-11	Apply ice and elevate.
5	6	P00005	S002	Type 2 Diabetes	2026-05-12	Monitor blood sugar daily.
6	7	P00006	S020	Migraine	2026-05-13	Avoid bright lights and loud noises.
7	8	P00007	S006	Bronchitis	2026-05-13	Completed 7-day course of antibiotics.
8	9	P00008	S010	Allergic Rhinitis	2026-05-14	Prescribed antihistamines.
9	10	P00009	S013	Lower Back Pain	2026-05-14	Recommend physical therapy.
10	11	P00010	S017	Urinary Tract Infection	2026-05-15	Increase fluid intake.
11	12	P00011	S020	Iron Deficiency Anemia	2026-05-15	Prescribed iron supplements.
12	13	P00012	S002	Tonsillitis	2026-05-16	Possible surgery if recurring.
13	14	P00013	S006	Dermatitis	2026-05-16	Prescribed topical steroid cream.
14	15	P00014	S010	Asthma Attack	2026-05-17	Nebulized in ER, prescribed inhaler.
15	16	P00015	S013	Peptic Ulcer	2026-05-17	Start on proton-pump inhibitors.
16	17	P00016	S017	Conjunctivitis	2026-05-18	Use antibiotic eye drops.
17	18	P00017	S020	Generalized Anxiety	2026-05-18	Referral to counseling.
18	19	P00018	S002	Influenza A	2026-05-19	Self-isolation for 5 days.
19	20	P00019	S006	Hyperthyroidism	2026-05-19	Blood tests scheduled for next week.
20	1	P00020	S002	Vitamin D Deficiency	2026-05-20	Supplementation recommended.
22	22	P00021	S004	labad ulo	2026-05-27	Recorded during patient admission.
23	24	P00022	S010	sakit ang heart	2026-05-27	Medication: D002\r\nStatus: Completed
24	25	P00023	S010	hilanat	2026-05-27	\N
\.


--
-- TOC entry 5392 (class 0 OID 44428)
-- Dependencies: 250
-- Data for Name: drugs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.drugs (drug_number, drug_name, description, dosage, method_of_admin, quantity_of_stock, reorder_level, cost_per_unit) FROM stdin;
D001	Paracetamol	Pain Relief	500mg	Oral	500	100	5.50
D002	Amoxicillin	Antibiotic	250mg	Oral	300	50	12.00
D003	Ibuprofen	Anti-inflammatory	200mg	Oral	400	80	8.50
D004	Metformin	Diabetes	500mg	Oral	600	100	15.00
D005	Amlodipine	Blood Pressure	5mg	Oral	450	50	18.00
D006	Salbutamol	Asthma	100mcg	Inhaler	100	20	250.00
D007	Omeprazole	Acid Reflux	20mg	Oral	350	60	22.00
D008	Cetirizine	Allergy	10mg	Oral	400	100	7.00
D009	Azithromycin	Antibiotic	500mg	Oral	200	30	45.00
D010	Losartan	Blood Pressure	50mg	Oral	500	100	14.00
D011	Atorvastatin	Cholesterol	20mg	Oral	300	50	35.00
D012	Prednisone	Steroid	10mg	Oral	150	20	10.00
D013	Hydrochlorothiazide	Diuretic	25mg	Oral	350	60	9.00
D014	Alprazolam	Anxiety	0.5mg	Oral	100	20	50.00
D015	Furosemide	Diuretic	40mg	Oral	200	40	11.00
D016	Warfarin	Blood Thinner	5mg	Oral	150	30	30.00
\.


--
-- TOC entry 5373 (class 0 OID 44207)
-- Dependencies: 231
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- TOC entry 5402 (class 0 OID 44553)
-- Dependencies: 260
-- Data for Name: in_patients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.in_patients (in_patient_id, patient_number, ward_number, bed_number, date_placed_on_waiting_list, expected_stay_days, date_admitted, date_expected_leave, date_actual_leave, status) FROM stdin;
1	P00001	1	1	2026-05-01	5	2026-05-01	2026-05-06	2026-05-06	Waiting
2	P00002	2	19	2026-05-01	10	2026-05-01	2026-05-11	2026-05-11	Waiting
3	P00003	3	33	2026-05-02	10	2026-05-02	2026-05-12	\N	Waiting
4	P00004	4	53	2026-05-02	2	2026-05-02	2026-05-04	2026-05-04	Waiting
5	P00005	5	65	2026-05-03	14	2026-05-03	2026-05-17	\N	Waiting
6	P00006	6	83	2026-05-03	5	2026-05-03	2026-05-08	\N	Waiting
7	P00007	7	99	2026-05-04	4	2026-05-04	2026-05-08	2026-05-08	Waiting
8	P00008	8	130	2026-05-04	6	2026-05-04	2026-05-10	2026-05-10	Waiting
9	P00009	9	147	2026-05-04	6	2026-05-04	2026-05-10	2026-05-10	Waiting
10	P00010	10	155	2026-05-04	1	2026-05-04	2026-05-05	\N	Waiting
12	P00008	\N	\N	\N	\N	2026-05-26	\N	2026-05-27	Discharged
13	P00021	17	226	\N	\N	2026-05-27	\N	\N	Admitted
15	P00022	5	73	2026-05-27	5	2026-05-27	2026-06-02	\N	Admitted
16	P00023	7	110	2026-05-27	2	2026-05-27	2026-05-30	\N	Admitted
\.


--
-- TOC entry 5393 (class 0 OID 44439)
-- Dependencies: 251
-- Data for Name: items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.items (item_number, item_name, description, quantity_of_stock, reorder_level, cost_per_unit) FROM stdin;
I001	Surgical Gloves	Latex-free, Medium	500	100	15.50
I002	Face Masks	3-Ply Surgical Mask	2000	500	5.00
I003	Syringe 5ml	Disposable with needle	1000	200	12.00
I004	Cotton Balls	Sterile, 50pcs per pack	300	50	45.00
I005	IV Cannula	Gage 22 Blue	150	30	85.00
I006	Alcohol 70%	Isopropyl, 500ml	100	20	75.00
I007	Gauze Pad	4x4 Sterile	400	100	25.00
I008	Medical Tape	Micropore 1 inch	200	40	60.00
I009	Digital Thermometer	Fast read, battery incl.	50	10	150.00
I010	Stethoscope	Dual head, black	20	5	850.00
I011	Wheelchair	Standard Foldable	10	2	4500.00
I012	Oxygen Tank	Portable 5lbs	15	3	3200.00
I013	Bed Sheet	Hospital Grade, White	100	20	250.00
I014	Patient Gown	Blue, Cotton	120	25	180.00
I015	Disinfectant Spray	Hospital Grade 500ml	60	15	350.00
I016	Hand Sanitizer	Gel type, 1 Liter	40	10	220.00
I017	Nebulizer Kit	Adult size mask	30	10	120.00
I018	Catheter	Foley Catheter Fr 16	50	15	95.00
I019	Biohazard Bags	Yellow, Large	200	50	10.00
I020	First Aid Kit	Travel size, basic	25	5	450.00
\.


--
-- TOC entry 5371 (class 0 OID 44192)
-- Dependencies: 229
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- TOC entry 5370 (class 0 OID 44177)
-- Dependencies: 228
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- TOC entry 5376 (class 0 OID 44252)
-- Dependencies: 234
-- Data for Name: local_doctors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.local_doctors (clinic_number, full_name, address, telephone) FROM stdin;
101	James Anderson	12 King Street, Manchester	0161-856-1111
102	Emily Carter	45 Oxford Road, Manchester	0161-858-2222
103	Michael Johnson	8 Baker Street, London	020-856-3333
104	Sarah Williams	102 Camden High Street, London	020-231-4444
105	David Miller	17 Queen's Road, Birmingham	0121-857-5555
106	Jessica Brown	33 New Street, Birmingham	0121-881-6666
107	Christopher Davis	56 Princes Street, Edinburgh	0131-700-1234
108	Ashley Martinez	21 George Square, Glasgow	0141-800-5678
109	Matthew Thompson	9 High Street, Liverpool	0151-856-9999
110	Olivia Garcia	88 Church Street, Liverpool	0151-231-0000
\.


--
-- TOC entry 5414 (class 0 OID 44681)
-- Dependencies: 272
-- Data for Name: medication_administrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.medication_administrations (administration_id, medication_id, patient_number, staff_number, dosage_administered, administered_at, notes) FROM stdin;
\.


--
-- TOC entry 5362 (class 0 OID 44109)
-- Dependencies: 220
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_05_18_161426_create_roles_table	1
5	2026_05_18_161445_create_staff_table	1
6	2026_05_20_185438_create_local_doctors_table	1
7	2026_05_20_185439_create_patients_table	1
8	2026_05_20_185439_create_wards_table	1
9	2026_05_20_185440_create_beds_table	1
10	2026_05_20_185441_create_qualifications_table	1
11	2026_05_20_185442_create_work_experiences_table	1
12	2026_05_20_185443_create_staff_allocations_table	1
13	2026_05_20_185444_create_appointments_table	1
14	2026_05_20_185445_create_diagnoses_table	1
15	2026_05_20_185446_create_treatments_table	1
16	2026_05_20_185447_create_drugs_table	1
17	2026_05_20_185448_create_items_table	1
18	2026_05_20_185449_create_suppliers_table	1
19	2026_05_20_185450_create_supplier_drugs_table	1
20	2026_05_20_185450_create_supplier_supplies_table	1
21	2026_05_20_185451_create_requisitions_table	1
22	2026_05_20_185452_create_requisition_supplies_table	1
23	2026_05_20_185453_create_requisition_drugs_table	1
24	2026_05_20_185454_create_in_patients_table	1
25	2026_05_20_185455_create_next_of_kins_table	1
26	2026_05_20_185456_create_patient_medications_table	1
27	2026_05_20_185457_create_bills_table	1
28	2026_05_20_185458_create_bill_items_table	1
29	2026_05_20_185458_create_payments_table	1
30	2026_05_26_000001_create_nursing_staff_postgresql_routines_and_triggers	2
31	2026_05_26_000002_create_nursing_care_supply_routines	3
32	2026_05_28_000001_create_staff_allocation_procedure	4
33	2026_05_28_000002_dedupe_staff_allocations	5
\.


--
-- TOC entry 5404 (class 0 OID 44580)
-- Dependencies: 262
-- Data for Name: next_of_kins; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.next_of_kins (kin_id, patient_number, full_name, relationship, address, telephone) FROM stdin;
1	P00001	James Phelps	Father	145 Rowlands Street, Paisley, PA2 5FE	0141-848-2211
2	P00002	Margaret Jones	Mother	45 Royal Mile, Edinburgh, EH1 1RB	0131-555-5678
3	P00003	Robert Miller	Father	88 Ferry Road, Edinburgh, EH6 4AQ	0131-555-9012
4	P00004	Jeanette Taylor	Sister	32 Lothian Rd, Edinburgh, EH3 9BY	0131-555-3456
5	P00005	William Brown	Brother	15 Cowgate, Edinburgh, EH1 1JR	0131-555-7890
6	P00006	Helen Wilson	Aunt	74 Leith Walk, Edinburgh, EH6 5HB	0131-555-2345
7	P00007	David Johnston	Uncle	22 Grassmarket, Edinburgh, EH1 2JU	0131-555-6789
8	P00008	Agnes Morrison	Cousin	50 Morningside Rd, Edinburgh, EH10 4BF	0131-555-0123
9	P00009	Elizabeth Campbell	Sister	11 Holyrood Rd, Edinburgh, EH8 8AS	0131-555-8901
10	P00010	Thomas Stewart	Father	67 Dundee Terrace, Edinburgh, EH11 1DL	0131-444-2323
11	P00011	Janet Anderson	Mother	12 Newhaven Road, Edinburgh, EH6 4QA	0131-222-4545
12	P00012	Charles Scott	Brother	89 Colinton Road, Edinburgh, EH10 5BT	0131-333-6767
13	P00013	Mary Innes	Sister	4 North Bridge, Edinburgh, EH1 1SB	0131-555-9898
14	P00014	Duncan Macleod	Spouse	102 West Port, Edinburgh, EH3 9DN	0131-777-1111
15	P00015	Fiona Graham	Mother	14 Comiston Road, Edinburgh, EH10 5QE	0131-888-2222
16	P00016	Patrick Reid	Father	27 Hanover Street, Edinburgh, EH2 2EN	0131-111-3434
17	P00017	Sarah Douglas	Mother	63 Easter Road, Edinburgh, EH7 5PL	0131-222-5656
18	P00018	Andrew Kerr	Brother	91 Nicolson Street, Edinburgh, EH8 9BZ	0131-333-7878
19	P00019	Isabella Fraser	Spouse	18 George IV Bridge, Edinburgh, EH1 1EN	0131-444-9090
20	P00020	Michael Boyd	Uncle	76 Dalry Road, Edinburgh, EH11 2BA	0131-555-1212
21	P00021	sheena	Sister	balingasag	1548789532323
23	P00022	chanice caboverde	friend	Bayabas Cagayan de Oro City	\N
24	P00023	Rhia	Mother	Bayabas Cagayan de Oro City	09562314588
\.


--
-- TOC entry 5365 (class 0 OID 44133)
-- Dependencies: 223
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- TOC entry 5417 (class 0 OID 44721)
-- Dependencies: 275
-- Data for Name: patient_condition_updates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.patient_condition_updates (condition_id, patient_number, staff_number, condition_status, blood_pressure, temperature, heart_rate, notes, recorded_at) FROM stdin;
\.


--
-- TOC entry 5406 (class 0 OID 44596)
-- Dependencies: 264
-- Data for Name: patient_medications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.patient_medications (medication_id, staff_number, patient_number, drug_number, unit_per_day, start_date, end_date) FROM stdin;
1	S002	P00001	D001	3	2026-05-01	2026-05-06
2	S058	P00002	D002	2	2026-05-01	2026-05-11
3	S022	P00003	D003	2	2026-05-02	2026-05-05
4	S013	P00004	D004	3	2026-05-03	2026-05-10
5	S017	P00005	D005	1	2026-05-04	2026-05-18
6	S002	P00006	D006	2	2026-05-04	2026-05-08
7	S046	P00007	D007	3	2026-05-04	2026-05-06
8	S038	P00008	D008	1	2026-05-04	2026-05-09
9	S022	P00009	D009	3	2026-05-04	2026-05-24
10	S017	P00010	D010	1	2026-05-04	2026-05-10
11	S022	P00011	D011	1	2026-04-30	2026-05-10
12	S058	P00012	D012	1	2026-05-04	2026-05-07
13	S038	P00013	D013	3	2026-05-04	2026-05-16
14	S046	P00014	D014	2	2026-05-04	2026-05-09
15	S028	P00015	D015	3	2026-05-04	2026-05-17
17	S010	P00022	D002	2	2026-05-30	2026-06-01
18	S010	P00022	D002	2	2026-05-27	2026-05-30
19	S010	P00023	D003	1	2026-05-27	2026-05-30
\.


--
-- TOC entry 5377 (class 0 OID 44259)
-- Dependencies: 235
-- Data for Name: patients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.patients (patient_number, clinic_number, first_name, last_name, address, telephone, date_of_birth, sex, marital_status, date_registered) FROM stdin;
P00001	105	Alice	Brown	12 Baker Street, London	020-4456-6722	2000-01-01	Female	Single	2026-01-01
P00002	103	Bob	Johnson	45 Queen's Road, London	020-5567-8890	1995-02-02	Male	Married	2026-01-02
P00003	110	Charlie	Deen	78 High Street, Manchester	0161-778-1203	1990-03-03	Male	Single	2026-01-03
P00004	101	Diana	Smith	23 King Avenue, Edinburgh	0131-334-4121	2001-04-04	Female	Single	2026-01-04
P00005	105	Ethan	Williams	9 Victoria Lane, London	020-9012-3345	1998-05-05	Male	Married	2026-01-05
P00006	102	Fiona	Gallagher	101 Castle Road, Edinburgh	0131-556-8890	1993-06-06	Female	Married	2026-01-06
P00007	108	George	Miller	56 Greenfield Drive, Birmingham	0121-778-9033	1985-07-07	Male	Divorced	2026-02-07
P00008	107	Hannah	Baker	34 Church Street, Manchester	0161-445-6677	2002-08-08	Female	Single	2026-02-08
P00009	104	Ian	Somerhalder	88 Rosewood Avenue, Leeds	0113-234-8891	1982-09-09	Male	Married	2026-02-09
P00010	109	Julia	Roberts	17 Riverside Close, London	020-7123-4567	1975-10-10	Female	Married	2026-02-10
P00011	106	Kevin	Hart	65 Oakwood Street, Liverpool	0151-334-2211	1988-11-11	Male	Married	2026-02-11
P00012	107	Luna	Brown	42 Maple Crescent, Edinburgh	0131-778-4412	2003-12-12	Female	Single	2026-03-12
P00013	110	Mike	Ross	73 Wellington Road, London	020-8832-7710	1991-01-13	Male	Single	2026-03-13
P00014	109	Nina	Dobrev	29 Hilltop Avenue, Birmingham	0121-998-1209	1994-02-14	Female	Single	2026-03-14
P00015	104	Oscar	Isaac	90 Prince Street, Manchester	0161-990-1188	1983-03-15	Male	Married	2026-03-15
P00016	103	Piper	Halliwell	14 Elm Park Road, Leeds	0113-667-4522	1979-04-16	Female	Married	2026-03-16
P00017	107	Quinn	Fabray	51 Station Lane, London	020-7946-8821	1996-05-17	Female	Single	2026-04-17
P00018	108	Riley	Reid	36 Willow Drive, Edinburgh	0131-990-3345	1995-06-18	Female	Single	2026-04-18
P00019	109	Seth	Rogen	27 Meadow View, Liverpool	0151-445-7783	1987-07-19	Male	Married	2026-04-19
P00020	106	Tina	Fey	84 Silver Birch Road, Manchester	0161-556-4420	1980-08-20	Female	Married	2026-05-02
P00021	102	lovely	Alabe	balingasag	06266889785	2026-05-12	Female	Single	2026-05-26
P00022	105	kelly	anggaon	kauswagan, Cagayan de Oro City	09562884574	2026-04-30	\N	\N	2026-05-27
P00023	105	Edrhia	Caboverde	Bayabas, Cagayan de Oro City	09568744213	2014-05-18	Female	Single	2026-05-27
\.


--
-- TOC entry 5412 (class 0 OID 44662)
-- Dependencies: 270
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (payment_id, bill_id, payment_date, amount_paid, payment_method) FROM stdin;
1	1	2026-05-06	4500.00	Cash
2	2	2026-05-08	8500.00	GCash
3	3	2026-05-12	0.00	Pending
4	4	2026-05-05	6500.00	Credit Card
5	5	2026-05-17	3000.00	Cash
6	6	2026-05-09	0.00	Pending
7	7	2026-05-08	5200.00	Debit Card
8	8	2026-05-10	8100.00	GCash
9	9	2026-05-10	4000.00	Cash
10	10	2026-05-05	0.00	Pending
11	11	2026-05-18	1500.00	Cash
12	12	2026-05-18	2200.00	GCash
13	13	2026-05-19	1800.00	Cash
14	14	2026-05-19	2500.00	Cash
15	15	2026-05-20	1700.00	GCash
16	16	2026-05-20	2000.00	Cash
17	17	2026-05-21	1600.00	GCash
18	18	2026-05-21	1900.00	Cash
19	19	2026-05-22	2100.00	GCash
20	20	2026-05-22	2300.00	Cash
\.


--
-- TOC entry 5381 (class 0 OID 44298)
-- Dependencies: 239
-- Data for Name: qualifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.qualifications (qualification_id, staff_number, qualification_type, qualification_date, institution_name) FROM stdin;
1	S001	Doctor of Medicine	2012-04-15	King's College London
2	S002	Doctor of Medicine	2015-05-20	University of Manchester
3	S003	BS Human Resource Management	2010-03-25	University of Birmingham
4	S004	BS Nursing	2014-03-28	University of Edinburgh
5	S005	Caregiving NC II	2017-04-02	Leeds City College
6	S006	Doctor of Medicine - General Surgery	2010-06-10	University of Liverpool
7	S007	BS Accountancy	2018-05-15	University of Glasgow
8	S008	BS Business Administration	2012-03-30	London South Bank University
9	S009	BS Human Resource Management	2005-05-12	University of Manchester
10	S010	Doctor of Medicine	2021-06-30	University of Birmingham
11	S011	BS Nursing	2014-04-18	Edinburgh Napier University
12	S012	BS Nursing	2016-03-22	University of Leeds
13	S013	Doctor of Medicine	2011-03-15	University of Liverpool
14	S014	BS Accountancy	2017-04-10	University of Glasgow
15	S015	BS Nursing	2013-05-05	King's College London
16	S016	Caregiving NC II	2008-11-20	The Manchester College
17	S017	Doctor of Medicine	2007-05-15	University of Birmingham
18	S018	BS Nursing	2008-03-12	University of Edinburgh
19	S019	BS Nursing	2015-03-18	University of Leeds
20	S020	BS Nursing	2006-04-25	University of Liverpool
21	S021	BS Nursing	2014-06-10	University of Leeds
22	S022	Doctor of Medicine	2013-05-18	University of Manchester
23	S023	BS Nursing	2018-04-22	University of Birmingham
24	S024	BS Nursing	2012-03-15	University of Edinburgh
25	S025	Caregiving NC II	2016-07-01	Leeds City College
26	S026	BS Nursing	2015-06-12	University of Glasgow
27	S027	BS Nursing	2019-05-20	University of Leeds
28	S028	Doctor of Medicine	2011-03-11	King's College London
29	S029	BS Nursing	2010-04-08	University of Manchester
30	S030	BS Nursing	2014-09-14	University of Birmingham
31	S031	Doctor of Medicine - Cardiology	2010-06-21	University of Liverpool
32	S032	BS Nursing	2020-05-10	Edinburgh Napier University
33	S033	Caregiving NC II	2017-02-28	The Manchester College
34	S034	BS Nursing	2013-06-17	University of Glasgow
35	S035	BS Nursing	2009-03-25	University of Leeds
36	S036	BS Nursing	2021-04-05	University of Birmingham
37	S037	BS Accountancy	2015-05-12	London South Bank University
38	S038	Doctor of Medicine	2012-06-18	University of Leeds
39	S039	BS Nursing	2011-03-19	University of Edinburgh
40	S040	BS Nursing	2019-08-22	University of Manchester
41	S041	BS Human Resource Management	2010-05-15	University of Birmingham
42	S042	BS Nursing	2014-04-10	University of Leeds
43	S043	BS Nursing	2020-06-30	University of London
44	S044	BS Nursing	2012-03-21	University of Glasgow
45	S045	Caregiving NC II	2018-07-11	Leeds City College
46	S046	Doctor of Medicine - Internal Medicine	2011-05-25	University of Manchester
47	S047	BS Nursing	2010-04-14	University of Birmingham
48	S048	BS Nursing	2018-06-09	University of Leeds
49	S049	BS Nursing	2008-03-17	University of Edinburgh
50	S050	BS Nursing	2013-05-30	King's College London
51	S051	BS Nursing	2019-06-20	University of Manchester
52	S052	Caregiving NC II	2017-05-11	The Manchester College
53	S053	Doctor of Medicine - Orthopedics	2009-04-18	University of Birmingham
54	S054	BS Nursing	2011-07-22	University of Edinburgh
55	S055	BS Nursing	2010-05-15	University of Glasgow
56	S056	BS Nursing	2020-06-18	University of Leeds
57	S057	Caregiving NC II	2016-03-14	Leeds City College
58	S058	Doctor of Medicine	2012-05-10	University of Manchester
59	S059	BS Nursing	2011-04-12	University of Birmingham
60	S060	BS Nursing	2019-07-19	University of Edinburgh
61	S061	BS Nursing	2009-06-21	University of Leeds
62	S062	BS Nursing	2013-05-16	King's College London
63	S063	BS Nursing	2021-06-11	University of Manchester
64	S064	Caregiving NC II	2017-02-20	The Manchester College
65	S065	BS Nursing	2012-05-14	University of Leeds
66	S066	BS Nursing	2019-03-18	University of Birmingham
67	S067	BS Nursing	2010-06-22	University of Edinburgh
68	S068	BS Nursing	2011-07-15	University of London
69	S069	BS Nursing	2022-05-10	University of Manchester
70	S070	BS Nursing	2010-04-09	University of Birmingham
71	S071	BS Nursing	2013-06-17	University of Leeds
72	S072	Doctor of Medicine - Neurology	2010-06-15	University of Edinburgh
73	S073	Doctor of Medicine - Pulmonology	2009-05-20	University of Manchester
\.


--
-- TOC entry 5400 (class 0 OID 44534)
-- Dependencies: 258
-- Data for Name: requisition_drugs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.requisition_drugs (requisition_number, drug_number, quantity_required) FROM stdin;
1	D001	100
1	D002	50
2	D003	30
3	D005	10
4	D006	20
5	D007	50
6	D008	5
7	D009	40
8	D010	30
9	D011	100
10	D012	60
11	D013	5
11	D014	10
13	D015	20
\.


--
-- TOC entry 5399 (class 0 OID 44516)
-- Dependencies: 257
-- Data for Name: requisition_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.requisition_items (requisition_number, item_number, quantity_required) FROM stdin;
1	I001	50
1	I002	100
2	I005	20
2	I007	30
3	I006	10
3	I015	5
4	I003	100
4	I001	20
5	I011	2
6	I009	5
7	I010	2
8	I019	50
8	I020	5
9	I013	20
9	I014	20
10	I018	10
1	I004	10
2	I008	5
3	I016	4
4	I017	3
\.


--
-- TOC entry 5398 (class 0 OID 44496)
-- Dependencies: 256
-- Data for Name: requisitions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.requisitions (requisition_number, staff_number, ward_number, date_ordered, date_received, status) FROM stdin;
1	S003	3	2026-05-01	2026-05-02	Delivered
2	S004	7	2026-05-01	2026-05-03	Delivered
3	S006	4	2026-05-02	\N	Pending
4	S009	17	2026-05-04	2026-05-04	Delivered
5	S005	18	2026-05-02	2026-05-02	Delivered
6	S011	2	2026-05-04	2026-05-04	Delivered
7	S010	15	2026-05-04	\N	Pending
8	S012	9	2026-05-04	2026-05-04	Delivered
9	S013	11	2026-05-04	\N	Pending
10	S013	10	2026-05-04	\N	Pending
11	S008	13	2026-05-03	\N	Pending
13	S007	12	2026-05-03	2026-05-04	Delivered
\.


--
-- TOC entry 5374 (class 0 OID 44225)
-- Dependencies: 232
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (role_id, role_name) FROM stdin;
1	Medical Director
2	Personnel Officer
3	Clinical Staff
4	Nursing Staff
5	Cashier
\.


--
-- TOC entry 5366 (class 0 OID 44142)
-- Dependencies: 224
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- TOC entry 5375 (class 0 OID 44232)
-- Dependencies: 233
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff (staff_number, role_id, email, password, first_name, last_name, address, telephone, date_of_birth, sex, nin, "position", current_salary, salary_scale, hours_per_week, contract_type, payment_type) FROM stdin;
S002	3	johndoe@gmail.com	$2y$12$r5ydt4ocFZsfd64WLMdfVeMbCa2aLbTZ5XWiv9NgVQGT/QJsBybLS	John	Doe	82 Fairfield Avenue, Manchester	0161-782-4412	1990-01-01	Male	CD789012A	Doctor	65000.00	B1	40	Permanent	Monthly
S003	2	marklee@gmail.com	$2y$12$64ROKQfevSzkOI/ed9XxgO7DAfUrcPfLuwk7QtGR2obn2TQ5r0ovK	Mark	Lee	6 Rosemont Street, Birmingham	0121-663-2281	1988-03-03	Male	EF345678B	Personnel Officer	42000.00	C1	37	Permanent	Weekly
S004	4	janesmith@gmail.com	$2y$12$ZfZkAXca2.p9K7SlS/CmU.xDPStx2X4e3loqVk2Gye4e3hrq9qmNa	Jane	Smith	41 Glenwood Drive, Edinburgh	0131-552-7714	1992-02-02	Female	GH456123D	Senior Nurse	38000.00	C1	40	Permanent	Monthly
S006	1	leovelez@gmail.com	$2y$12$o7P9grE5ICIV2djUMTrG3Olh/bNJ4XlOTc1aJoPWJvitPWbij9mWe	Leo	Velez	27 Norfolk Street, Liverpool	0151-334-8820	1985-06-15	Male	LX111222	Medical Director	90000.00	A1	45	Permanent	Monthly
S007	5	mariasantos@gmail.com	$2y$12$L86eHOyJCHnDBEbrNgLsZuVwunIcOVUQu9.KKvJi.35tplhsvvZcq	Maria	Santos	58 Belmont Avenue, Glasgow	0141-887-2213	1993-08-20	Female	MS333444	Cashier	18000.00	D1	40	Permanent	Monthly
S008	5	clarabenson@gmail.com	$2y$12$DJRTNxW1S1s/0gYpKjCWQe5JCi8xeX/u96KAqfy5ySiXN.goXrEQS	Clara	Benson	10 Ashford Lane, London	020-9981-4421	1990-11-12	Female	CB555666	Cashier	17000.00	D1	40	Permanent	Monthly
S010	3	elenagilbert@gmail.com	$2y$12$Q6.UvLEcxcPOEVjeXiaQ7uYWgy6kaeOyGHUuU0rUmQ5xKD9o/Mb3u	Elena	Gilbert	36 Cedar Close, Birmingham	0121-771-4438	1996-12-05	Female	EG999000	Consultant	70000.00	B1	40	Permanent	Monthly
S011	4	stefansalvatore@gmail.com	$2y$12$kru/eWvWOWUBZH1aAUlcLOTFxyLpJBHrSqUBxGoEiAVdNrX/zNLMW	Stefan	Salvatore	19 Woodland Avenue, Edinburgh	0131-664-2205	1992-03-14	Male	SS121212	Junior Nurse	26000.00	C1	40	Permanent	Monthly
S012	4	bonniebennett@gmail.com	$2y$12$e2HszJu.kSYJFXWZU665Tue9OB8I9Owmh9aboLK0qrdEivoaQDDZC	Bonnie	Bennett	67 Regent Street, Leeds	0113-885-4417	1994-05-22	Female	BB343434	Charge Nurse	45000.00	B1	40	Permanent	Monthly
S013	3	damonsalvatore@gmail.com	$2y$12$GtuVj.r8m791qgElzu.jMOGx.gPYrWLJFafUZ.Hu/p/ZRfIjQAcAe	Damon	Salvatore	48 Harbour Road, Liverpool	0151-776-9081	1989-10-31	Male	DS565656	Doctor	65000.00	B1	40	Permanent	Monthly
S014	5	carolineforbes@gmail.com	$2y$12$s0..GMHzHjc/rLzNgiSf6.dMnSTDjKV49uubeDwc0369dBjGd39S.	Caroline	Forbes	25 Westbrook Lane, Glasgow	0141-992-6650	1995-07-07	Female	CF787878	Cashier	18000.00	D1	40	Permanent	Monthly
S016	4	niklaus@gmail.com	$2y$12$Ebhxl8geGN0FJ9hvxECz6u33sNnsd.UF105WYWwSfHhE7F9PIna6O	Niklaus	Mikaelson	31 Brookfield Road, Manchester	0161-661-2299	1980-01-01	Male	NM131313	Auxiliary Staff	22000.00	D1	15	Temporary	Monthly
S017	3	elijahmikaelson@gmail.com	$2y$12$G4t4kVlJ7PRUAExTcW5wzOwnNjslc.Hatj8rUA.I6DMyKgK4Z2W5e	Elijah	Mikaelson	52 Kingston Avenue, Birmingham	0121-998-7734	1982-02-02	Male	EM242424	Doctor	68000.00	B1	40	Permanent	Monthly
S018	4	rebekahmikaelson@gmail.com	$2y$12$VVCvOMF4XFG6chbj40ujD.slEJpvy03KFDx2J1PjSiqWLuZowTzQO	Rebekah	Mikaelson	14 Evergreen Street, Edinburgh	0131-442-1190	1986-03-03	Female	RM353535	Junior Nurse	27000.00	C1	40	Permanent	Monthly
S019	4	hayleymarshall@gmail.com	$2y$12$yLKkwGsPOesJJ8pjAphEOOY3tKYuW5kxqWdrcl51Xm1s7awGeiJpK	Hayley	Marshall	85 Carlton Road, Leeds	0113-551-6720	1993-04-04	Female	HM464646	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S021	4	olivergrant@gmail.com	$2y$12$Kca4oKIry8p82bxrfY0f6eDfL927FM5eQ9wXuH1G0ZXbKKwiFAf2C	Oliver	Grant	12 Hilltop Road, Leeds	0113-223-4456	1992-06-10	Male	OG101010	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S022	3	isabelclarke@gmail.com	$2y$12$RoAZyucnWG6BFp/fCh66yeYNx6Wo4dpJmNEgbg4yz/E437RHzFR7q	Isabel	Clarke	55 River Street, Manchester	0161-223-8899	1987-02-18	Female	IC202020	Doctor	65000.00	B1	40	Permanent	Monthly
S023	4	masonreed@gmail.com	$2y$12$TwJOnZ3YalqJIIlmxG7Ltu07vA.4gdfvZa53MRKNQ77NwSqq4k7aq	Mason	Reed	18 Oak Avenue, Birmingham	0121-445-7788	1996-09-09	Male	MR303030	Junior Nurse	27000.00	C1	40	Permanent	Monthly
S024	4	emilywatson@gmail.com	$2y$12$JFvUFpKeeZ33xnvov1UvkeEyv9spGt8nRAy91trNZB0xbqIXJEzRu	Emily	Watson	33 Forest Drive, Edinburgh	0131-556-2211	1989-11-11	Female	EW404040	Senior Nurse	38000.00	C1	40	Permanent	Monthly
S026	4	gracehall@gmail.com	$2y$12$Lv.EXTzWs.wEqpqGFZnMH.Laasz/KB2CAnf4Sm3zzY8sFvBGFaWRa	Grace	Hall	9 Meadow Road, Glasgow	0141-998-1122	1991-01-25	Female	GH606060	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S027	4	lucaswright@gmail.com	$2y$12$ajq6EgxZGmMy6zcBzosIwON6J3oeA62ce9WcwgYuNARFrFpFcBC3C	Lucas	Wright	21 Brook Street, Leeds	0113-334-5566	1997-07-07	Male	LW707070	Junior Nurse	26000.00	C1	40	Permanent	Monthly
S028	3	ameliahughes@gmail.com	$2y$12$yaiXN.OYg3XJEmzI8uLSu.J1gHiQtIczsRxr0RodBeFSOhIAYxtzu	Amelia	Hughes	88 Cedar Road, London	020-7788-9900	1988-12-12	Female	AH808080	Doctor	68000.00	B1	40	Permanent	Monthly
S029	4	jacobmorris@gmail.com	$2y$12$XvgfdQ30HWPkJhg6CBt7sOI6UDBtZdhJcRv1/ngCmqdpSmzyB251G	Jacob	Morris	14 Elm Street, Manchester	0161-445-6677	1990-03-03	Male	JM909090	Senior Nurse	40000.00	C1	40	Permanent	Monthly
S031	3	ethanharris@gmail.com	$2y$12$T97tzEUnuZT8H8xt4/EFse03JyC6194Kco2VWxshZYN.yv1V1zHc2	Ethan	Harris	29 Queen Street, Liverpool	0151-223-4455	1985-05-05	Male	EH121212	Consultant	70000.00	B1	40	Permanent	Monthly
S032	4	avajames@gmail.com	$2y$12$8IUdr2xysKKs7CCY0.nM0OT07iqd/H2sWXWq43bql5Ugpa3uIoXCe	Ava	James	10 Rose Street, Edinburgh	0131-667-8899	1998-10-10	Female	AJ131313	Junior Nurse	25000.00	C1	40	Permanent	Monthly
S033	4	williamscott@gmail.com	$2y$12$CQ.xL97nHAOOGYY1uaXBduJRNepD4LuPpk7N8uZ9QUS.OCeljnX0i	William	Scott	45 Green Lane, Leeds	0113-7788-4455	1992-02-02	Male	WS141414	Auxiliary Staff	21000.00	D1	40	Temporary	Monthly
S034	4	chloebrooks@gmail.com	$2y$12$bNP3HX.Ulo9O4W4EaP7hFO63qo3r1u97dWrE3.b7gzc1M5oEXtbJK	Chloe	Brooks	71 Sunset Drive, Glasgow	0141-5566-7788	1993-06-06	Female	CB151515	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S036	4	zoebennett@gmail.com	$2y$12$hoF4.qLRXLA5AluolmG9Rutn4iZHGr./wgFHbxsVTzipIwC2fqxaK	Zoe	Bennett	23 Lake Road, Birmingham	0121-9988-7766	1999-03-15	Female	ZB171717	Junior Nurse	25500.00	C1	40	Permanent	Monthly
S037	5	noahprice@gmail.com	$2y$12$fhSSfu/i8gWr7r0r3VAl8.uGXj0cRpn9DJanqS5soYFdqPbYIjF/i	Noah	Price	19 Market Street, London	020-3344-5566	1994-12-01	Male	NP181818	Cashier	18000.00	D1	40	Permanent	Monthly
S038	3	miaadams@gmail.com	$2y$12$w3ysJrTNj6IQ/P0ddGvpnu.uIRX6iXTGVuMK8wzA8ydGotSpiDNJ2	Mia	Adams	40 King Street, Leeds	0113-4455-6677	1991-01-01	Female	MA191919	Doctor	67000.00	B1	40	Permanent	Monthly
S040	4	islagreen@gmail.com	$2y$12$xBFoX62Iaz.9jga89pUxIOBzY5xs9pzK7GH2uHaWbw1IVpooZ4oQC	Isla	Green	52 Forest Lane, Manchester	0161-7788-9900	1997-07-07	Female	IG212121	Junior Nurse	26500.00	C1	40	Permanent	Monthly
S001	1	paulray@gmail.com	$2y$12$M/5GdPvA3YVjtFePk2PMrOQkHqqRYCr5VD0ZbSB/jLaB89QdsQXru	Paul	Ray	15 Cranberry Road, London	020-4456-7811	1991-05-05	Male	AB123456C	Medical Director	85000.00	A1	40	Permanent	Monthly
S005	4	annakim@gmail.com	$2y$12$B77MVmJAiVnwB0u1af5QsOsSvibTEtAL5c1F3kzHs5Q4cxsocXJVa	Anna	Kim	93 Kingsley Road, Leeds	0113-774-9902	1995-04-04	Female	JK987654E	Auxiliary Staff	22000.00	D1	40	Temporary	Monthly
S009	2	ricardodalisay@gmail.com	$2y$12$bQ79977I/AoMhfBzye/L1.jbj.sbQ2nLLuuJ6HEjJjhZdMTRPcoK2	Ricardo	Dalisay	74 Pine Street, Manchester	0161-554-1109	1978-02-28	Male	RD777888	Personnel Officer	32000.00	C1	20	Temporary	Monthly
S015	4	tylerlockwood@gmail.com	$2y$12$.iz56qtxxNu1qpQcmcu46uULhbfN9O2tJ1hVcyN4CJed2i2P3UvA6	Tyler	Lockwood	90 Sycamore Drive, London	020-7754-1182	1991-09-09	Male	TL909090	Senior Nurse	38000.00	C1	40	Permanent	Monthly
S020	4	alaricsaltzman@gmail.com	$2y$12$Qb2kTxlPqSe5iuT9EFr7SO/0JW2uh2FhR4IHNMyh8.ffN0IG/Pd/K	Alaric	Saltzman	39 Waterfall Close, Liverpool	0151-884-2201	1984-05-05	Male	AS575757	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S025	4	danielcooper@gmail.com	$2y$12$jn40UyxzyHQcrhwV0gIs1uF74OuSjU/JVPv3v8q9wR8t/1QecxZAq	Daniel	Cooper	77 Park Lane, London	020-6677-8899	1993-04-14	Male	DC505050	Auxiliary Staff	22000.00	D1	40	Temporary	Monthly
S042	4	oliviabrown@gmail.com	$2y$12$1vI81vOg9wnVCQYVr4fVMurqpCRrtmpZwenkzSNCffoVRYVw039MK	Olivia	Brown	5 Park Avenue, Leeds	0113-6677-8899	1992-02-14	Female	OB232323	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S044	4	evawhite@gmail.com	$2y$12$EI3P//LtbTEKG91shF/3M.UpcktsRJ0/e3NcRdpqKVRXSXEiF5QCO	Eva	White	34 Riverbank Road, Glasgow	0141-2233-4455	1988-03-03	Female	EW252525	Senior Nurse	39500.00	C1	40	Permanent	Monthly
S045	4	thomasbell@gmail.com	$2y$12$5rv/6PHKWMZLSwS2gKZvquBrcrj/5R9aubyj/oFnav3xyY5XN/4zy	Thomas	Bell	9 Oakwood Drive, Liverpool	0151-5566-7788	1993-09-09	Male	TB262626	Auxiliary Staff	21500.00	D1	40	Temporary	Monthly
S046	3	lucymorgan@gmail.com	$2y$12$0H.kFCrentCdlzldHkrBqObx.XOnQxUULKzZYf8uKNkT5sihcnmqW	Lucy	Morgan	21 West Street, Manchester	0161-3344-5566	1990-10-10	Female	LM272727	Doctor	69000.00	B1	40	Permanent	Monthly
S047	4	georgehill@gmail.com	$2y$12$R5hLNW5PiHONgxhl79BkRutsLxO/lNi0opls1.CjmNVj.928FdX5e	George	Hill	12 Cedar Avenue, Birmingham	0121-4455-6677	1987-07-07	Male	GH282828	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S049	4	davidward@gmail.com	$2y$12$/YRdiGEqRB./W1/gY.q2j.jox5R4a8t4bQ3PeDyVtWLFZDdNWYo0S	David	Ward	19 Lake Street, Edinburgh	0131-6677-8899	1985-05-15	Male	DW303030	Senior Nurse	40000.00	C1	40	Permanent	Monthly
S050	4	emmajones@gmail.com	$2y$12$60bGVlc/ijNhrWifr6vzNuwW../7UgPgfP3fOtTgVkbaVBa7A62gm	Emma	Jones	30 Pine Street, London	020-4455-6677	1991-11-11	Female	EJ313131	Charge Nurse	46500.00	B1	40	Permanent	Monthly
S051	4	aidandoyle@gmail.com	$2y$12$GVw2mkNBFDmXYVi3EyrhheuRuovdi./ELXp19LkTcL/aW0mOvjVLC	Aidan	Doyle	14 Brook Lane, Manchester	0161-8899-0011	1997-02-02	Male	AD323232	Junior Nurse	25500.00	C1	40	Permanent	Monthly
S052	4	nataliefox@gmail.com	$2y$12$/aE2Ey5ugYPzojan4AD.QuUI2xA64gKQat7PqgBm4OjuQuuxxFIq6	Natalie	Fox	67 Meadow Street, Leeds	0113-2233-4455	1994-04-04	Female	NF333333	Auxiliary Staff	22000.00	D1	40	Temporary	Monthly
S054	4	hannahprice@gmail.com	$2y$12$wIagLb/VZIKNm9mLXtqIC.xd6TcGc67xsMG0nhzUC/sKA58a2VYSW	Hannah	Price	21 Rose Lane, Edinburgh	0131-9988-7766	1990-12-12	Female	HP353535	Senior Nurse	39000.00	C1	40	Permanent	Monthly
S055	4	liamwright@gmail.com	$2y$12$UxHWbAYs.aW/zp0QxLfvKuGB.2LcasvqnLqPqNFa/Zc7UXinkBgSu	Liam	Wright	8 Forest Road, Glasgow	0141-4455-6677	1989-09-09	Male	LW363636	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S056	4	chloemartin@gmail.com	$2y$12$ZqQ1F3hjz50A/1iW.qTCFuF/1zcKsxs1qWRFOvTphPH54vMnKuzvS	Chloe	Martin	77 Hill Street, Leeds	0113-8899-1122	1998-08-18	Female	CM373737	Junior Nurse	26500.00	C1	40	Permanent	Monthly
S057	4	benjaminlee@gmail.com	$2y$12$yUeoXZg/rp86o9neRd1gjOFCWezK3QzY3uMH28ILqmWXmhpOpfm9C	Benjamin	Lee	33 Oak Street, London	020-6677-2233	1992-03-03	Male	BL383838	Auxiliary Staff	21000.00	D1	40	Temporary	Monthly
S059	4	kevinross@gmail.com	$2y$12$6J9FjE3el7DMxbTZJaZKCO0f5NKZBdX72zDPSXPxhuOyEPRnTWtRu	Kevin	Ross	29 Green Avenue, Birmingham	0121-7788-9900	1988-06-06	Male	KR404040	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S060	4	lauraclark@gmail.com	$2y$12$m3jD6X4vD8hccWjxsZU7GOmmvE//Zngc8OlUtxTXX2P0omgGKQXCS	Laura	Clark	50 Lake View, Edinburgh	0131-4455-6677	1997-07-21	Female	LC414141	Junior Nurse	26000.00	C1	40	Permanent	Monthly
S061	4	michaeladams@gmail.com	$2y$12$EEee2eUeIQsP5Zmycjchj.bQ89udIX2paJ8sKw6fNNt/N49.4012a	Michael	Adams	12 Pine Road, Leeds	0113-6677-8899	1986-06-06	Male	MA424242	Senior Nurse	39500.00	C1	40	Permanent	Monthly
S062	4	oliverjames@gmail.com	$2y$12$GgSU5bOyZ949ej3bnHjHcOF58LK55FpQ9NcO9B.Vj7OL1tlodmQ7C	Oliver	James	9 Hilltop Lane, London	020-7788-4455	1990-10-10	Male	OJ434343	Charge Nurse	46500.00	B1	40	Permanent	Monthly
S064	4	ethanwood@gmail.com	$2y$12$5CmNFjertH2VRRsxdoM7we227CXwYh88INbkFBtPwRn7uuqTjsKFK	Ethan	Wood	17 Park Lane, Glasgow	0141-6677-8899	1993-03-03	Male	EW454545	Auxiliary Staff	21500.00	D1	40	Temporary	Monthly
S065	4	graceroberts@gmail.com	$2y$12$AD606i/9KnHReerqgTqYU.XfTy61MSxD45SyOQB3eQ6kSppQ9amcG	Grace	Roberts	28 Forest Avenue, Leeds	0113-4455-6677	1991-09-09	Female	GR464646	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S066	4	danielgreen@gmail.com	$2y$12$M81ldFD6K15BsA9oeOUa7elTXOOlSqePyR8lIR/TLdlzDowj.gz3W	Daniel	Green	11 Oak Road, Birmingham	0121-6677-8899	1997-05-05	Male	DG474747	Junior Nurse	26000.00	C1	40	Permanent	Monthly
S067	4	sophiemorgan@gmail.com	$2y$12$G37T8dQHOBhW64wJNKPk4Oheob.b6IuXt9zb8ikEpgyqJAjmmMntG	Sophie	Morgan	39 River Street, Edinburgh	0131-2233-4455	1989-09-09	Female	SM484848	Senior Nurse	40000.00	C1	40	Permanent	Monthly
S069	4	ninaevans@gmail.com	$2y$12$hsNRp895CRFDmULPwRB0Rui75a77yFy3iXEuT6n1G2dQDtF.zcTS2	Nina	Evans	44 Lake Street, Manchester	0161-7788-2233	1999-09-09	Female	NE505050	Junior Nurse	25000.00	C1	40	Permanent	Monthly
S070	4	harrymoore@gmail.com	$2y$12$O7GjCSpa72P//q9o8Veqa.M3ihw6F/PvoE61E0eT47Kv8LFSMCPuu	Harry	Moore	25 Green Road, Birmingham	0121-4455-8899	1988-08-08	Male	HM515151	Charge Nurse	46000.00	B1	40	Permanent	Monthly
S071	4	elizabethscott@gmail.com	$2y$12$dCXeOIo6lL7SA7iia385C.f9Zl05WNrUuIBFdg11BD5eM/qzNYUwG	Elizabeth	Scott	13 Cedar Avenue, Leeds	0113-9988-1122	1990-02-02	Female	ES525252	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S072	3	consultant072@gmail.com	$2y$12$YWtfid.KgB1flMANyaW5QOKFbd70XKQH/5kw.9NRs5lr3y8nbT9Rq	Andrew	Cole	22 North Street, London	020-1111-2222	1983-03-03	Male	CN707070	Consultant	72000.00	B1	40	Permanent	Monthly
S030	4	sophialee@gmail.com	$2y$12$qv/DcfNrwqKEH.kwVK81Ku2C6p8OnK71Ji/a/eE6g9erd8Q0wj.Mq	Sophia	Lee	66 Victoria Road, Birmingham	0121-8899-1122	1994-08-08	Female	SL111111	Charge Nurse	45000.00	B1	40	Permanent	Monthly
S035	4	henryyoung@gmail.com	$2y$12$hq4fmgHfHFU9RaCYd9jj6..m9E3kjWFLLKwimyL7rK/NPK01fL8IC	Henry	Young	8 Pine Avenue, Manchester	0161-6677-8899	1987-09-09	Male	HY161616	Senior Nurse	39000.00	C1	40	Permanent	Monthly
S039	4	loganwright@gmail.com	$2y$12$hTdehM/FQdOBbzZHyftBA.1FRQOhkHIQdp.QzJxwkcYffJk7N3QY.	Logan	Wright	16 Hill Street, Edinburgh	0131-2233-4455	1989-04-04	Male	LW202020	Charge Nurse	45500.00	B1	40	Permanent	Monthly
S041	2	jackturner@gmail.com	$2y$12$UBffFTTprUv19p0V2a8O6uI2d9l6nYiR47Oh7n3SQdQHI.Ic.uLYe	Jack	Turner	11 Regent Road, Birmingham	0121-2233-8899	1986-06-06	Male	JT222222	Personnel Officer	33000.00	C1	40	Permanent	Monthly
S043	4	charlieking@gmail.com	$2y$12$8wDB5FMGccDECJZ9AAHRRORvnw8EbMa/OvKhB5SkQsQ0mv8JWF3yq	Charlie	King	77 Hilltop Street, London	020-9988-7766	1998-08-08	Male	CK242424	Junior Nurse	25000.00	C1	40	Permanent	Monthly
S048	4	sarahcollins@gmail.com	$2y$12$UA3eq58j.k.1mpQgycrlP.JKh5QeZDsEuMgVsHouGdUUoEyu7Ft/.	Sarah	Collins	88 Elm Road, Leeds	0113-7788-9900	1996-06-06	Female	SC292929	Junior Nurse	26000.00	C1	40	Permanent	Monthly
S053	3	ryanclark@gmail.com	$2y$12$UcVRmUkRa2ZOxvUMAvx9beOnrxUlwzIbEKubzhkKsBypcQrzClM8G	Ryan	Clark	55 Queen Avenue, Birmingham	0121-6677-8899	1984-08-08	Male	RC343434	Consultant	71000.00	B1	40	Permanent	Monthly
S058	3	jessicawalker@gmail.com	$2y$12$UZRwPk3ke5ysTNdicAJI7.UJsfXwVPsNQwiLB9fLuoEwnq3G/eGMG	Jessica	Walker	18 River Road, Manchester	0161-3344-7788	1991-01-21	Female	JW393939	Doctor	68000.00	B1	40	Permanent	Monthly
S063	4	isabellabell@gmail.com	$2y$12$3wEAYOB6UZBtR32TMdYjaOCdRsddj2/LFB9ZLrh3Zmfx9nrFO8gzK	Isabella	Bell	66 Cedar Street, Manchester	0161-2233-4455	1998-12-12	Female	IB444444	Junior Nurse	25500.00	C1	40	Permanent	Monthly
S068	4	jacksonhill@gmail.com	$2y$12$q0sdAAfiyKwS67mrHPvu5eTc2ABOrIROz6.xWp/8ljTPHvZrdsrym	Jackson	Hill	6 Meadow Lane, London	020-8899-0011	1987-07-07	Male	JH494949	Charge Nurse	47000.00	B1	40	Permanent	Monthly
S073	3	consultant073@gmail.com	$2y$12$QcEEhz5TBNL.UW6SaOFuPOG25bBJlb08H66UzjI4SqafVFJRtsskG	Sophia	Knight	55 West Avenue, Manchester	0161-2222-3333	1982-02-02	Female	SK808080	Consultant	73000.00	B1	40	Permanent	Monthly
\.


--
-- TOC entry 5385 (class 0 OID 44326)
-- Dependencies: 243
-- Data for Name: staff_allocations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.staff_allocations (allocation_id, staff_number, ward_number, role_for_week, shift, week_start_date, created_at, updated_at) FROM stdin;
5	S005	1	Auxiliary Staff	Early	2026-05-18	\N	\N
20	S025	3	Auxiliary Staff	Early	2026-05-18	\N	\N
26	S031	4	Consultant	Early	2026-05-18	\N	\N
27	S033	4	Auxiliary Staff	Early	2026-05-18	\N	\N
30	S026	5	Charge Nurse	Early	2026-05-18	\N	\N
34	S045	5	Auxiliary Staff	Early	2026-05-18	\N	\N
41	S053	6	Consultant	Early	2026-05-18	\N	\N
42	S052	6	Auxiliary Staff	Early	2026-05-18	\N	\N
45	S034	7	Charge Nurse	Early	2026-05-18	\N	\N
49	S057	7	Auxiliary Staff	Early	2026-05-18	\N	\N
52	S039	8	Charge Nurse	Early	2026-05-18	\N	\N
54	S043	8	Junior Nurse	Early	2026-05-18	\N	\N
55	S072	8	Consultant	Early	2026-05-18	\N	\N
56	S064	8	Auxiliary Staff	Early	2026-05-18	\N	\N
60	S061	9	Senior Nurse	Early	2026-05-18	\N	\N
61	S048	9	Junior Nurse	Early	2026-05-18	\N	\N
62	S028	9	Doctor	Early	2026-05-18	\N	\N
66	S047	10	Charge Nurse	Early	2026-05-18	\N	\N
67	S067	10	Senior Nurse	Early	2026-05-18	\N	\N
68	S051	10	Junior Nurse	Early	2026-05-18	\N	\N
69	S073	10	Consultant	Early	2026-05-18	\N	\N
72	S050	11	Charge Nurse	Early	2026-05-18	\N	\N
73	S004	11	Senior Nurse	Early	2026-05-18	\N	\N
74	S056	11	Junior Nurse	Early	2026-05-18	\N	\N
75	S038	11	Doctor	Early	2026-05-18	\N	\N
78	S055	12	Charge Nurse	Early	2026-05-18	\N	\N
79	S015	12	Senior Nurse	Early	2026-05-18	\N	\N
80	S060	12	Junior Nurse	Early	2026-05-18	\N	\N
81	S046	12	Doctor	Early	2026-05-18	\N	\N
84	S059	13	Charge Nurse	Early	2026-05-18	\N	\N
85	S024	13	Senior Nurse	Early	2026-05-18	\N	\N
86	S063	13	Junior Nurse	Early	2026-05-18	\N	\N
87	S058	13	Doctor	Early	2026-05-18	\N	\N
89	S062	14	Charge Nurse	Early	2026-05-18	\N	\N
90	S029	14	Senior Nurse	Early	2026-05-18	\N	\N
91	S066	14	Junior Nurse	Early	2026-05-18	\N	\N
93	S002	14	Doctor	Late	2026-05-18	\N	\N
94	S027	14	Junior Nurse	Late	2026-05-18	\N	\N
95	S065	15	Charge Nurse	Early	2026-05-18	\N	\N
96	S035	15	Senior Nurse	Early	2026-05-18	\N	\N
97	S069	15	Junior Nurse	Early	2026-05-18	\N	\N
98	S019	15	Charge Nurse	Late	2026-05-18	\N	\N
99	S013	15	Doctor	Late	2026-05-18	\N	\N
100	S032	15	Junior Nurse	Late	2026-05-18	\N	\N
101	S068	16	Charge Nurse	Early	2026-05-18	\N	\N
102	S044	16	Senior Nurse	Early	2026-05-18	\N	\N
103	S011	16	Junior Nurse	Early	2026-05-18	\N	\N
104	S020	16	Charge Nurse	Late	2026-05-18	\N	\N
105	S017	16	Doctor	Late	2026-05-18	\N	\N
106	S036	16	Junior Nurse	Late	2026-05-18	\N	\N
107	S070	17	Charge Nurse	Early	2026-05-18	\N	\N
108	S049	17	Senior Nurse	Early	2026-05-18	\N	\N
109	S018	17	Junior Nurse	Early	2026-05-18	\N	\N
59	S042	7	Charge Nurse	Early	2026-05-28	\N	2026-05-28 00:47:56
38	S030	5	Charge Nurse	Early	2026-05-28	\N	2026-05-28 00:15:50
110	S021	17	Charge Nurse	Late	2026-05-18	\N	\N
112	S040	17	Junior Nurse	Late	2026-05-18	\N	\N
113	S071	18	Charge Nurse	Early	2026-05-18	\N	\N
114	S054	18	Senior Nurse	Early	2026-05-18	\N	\N
115	S023	18	Junior Nurse	Early	2026-05-18	\N	\N
116	S010	18	Consultant	Early	2026-05-18	\N	\N
117	S022	18	Doctor	Late	2026-05-18	\N	\N
118	S016	18	Auxiliary Staff	Late	2026-05-18	\N	\N
92	S012	5	Charge Nurse	Early	2026-05-28	\N	2026-05-28 00:14:23
\.


--
-- TOC entry 5396 (class 0 OID 44460)
-- Dependencies: 254
-- Data for Name: supplier_drugs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supplier_drugs (supplier_id, drug_number, unit_price) FROM stdin;
2	D001	3.50
2	D002	12.00
5	D003	45.00
5	D004	25.00
10	D005	18.00
10	D006	55.00
11	D007	8.00
11	D008	150.00
17	D009	35.00
17	D010	22.00
1	D011	65.00
3	D012	40.00
4	D013	110.00
6	D014	95.00
8	D015	20.00
9	D016	120.00
\.


--
-- TOC entry 5397 (class 0 OID 44478)
-- Dependencies: 255
-- Data for Name: supplier_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.supplier_items (supplier_id, item_number, unit_price) FROM stdin;
1	I001	14.75
1	I002	4.50
2	I003	11.50
3	I004	42.00
4	I005	82.00
7	I006	70.00
8	I007	23.00
9	I008	58.00
12	I009	145.00
12	I010	800.00
13	I011	4400.00
13	I012	3100.00
14	I013	240.00
15	I014	175.00
16	I015	340.00
17	I016	210.00
18	I017	115.00
19	I018	90.00
20	I019	9.50
20	I020	430.00
\.


--
-- TOC entry 5395 (class 0 OID 44451)
-- Dependencies: 253
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.suppliers (supplier_id, supplier_name, address, telephone, fax) FROM stdin;
1	Cagayan Medical Supplies	Velez St, CDO	088-856-1234	088-856-1235
2	Northern Mindanao Pharma	Lapasan, CDO	088-231-5566	088-231-5567
3	Medi-Link Distro	Gusa Highway, CDO	0917-555-8899	0917-555-8900
4	Zamboanga Med-Tech	Divisoria, CDO	088-880-9900	088-880-9901
5	Global Health Corp	Makati City, Manila	02-888-0000	02-888-0001
6	Cebu Medical Hub	Mandaue City, Cebu	032-412-3344	032-412-3345
7	Oro Surgical Solutions	Carmen, CDO	088-858-7788	088-858-7789
8	Vital Care Trading	Nazareth, CDO	0922-444-5566	0922-444-5567
9	St. Jude Medical Supplies	Bulua, CDO	088-321-4455	088-321-4456
10	HealthFirst Phils	Quezon City, Manila	02-775-1122	02-775-1123
11	Pharma-North Logistics	Kauswagan, CDO	088-881-2233	088-881-2234
12	LifeLine Equipment	Pasig City, Manila	02-998-3344	02-998-3345
13	Southern Med Sales	Davao City	082-221-8899	082-221-8900
14	Emerald Medical Gear	Patag, CDO	0905-123-4567	0905-123-4568
15	Prime Care Distro	Iponan, CDO	088-851-9900	088-851-9901
16	Apex Bio-Medical	Taguig City, Manila	02-556-7788	02-556-7789
17	Reliable Pharma	Macasandig, CDO	088-857-1122	088-857-1123
18	Metro Med Mart	Divisoria, CDO	088-231-4400	088-231-4401
19	Island Medical Inc	Mactan, Cebu	032-340-1122	032-340-1123
20	Well-Stock Supplies	Puerto, CDO	0936-777-8899	0936-777-8900
\.


--
-- TOC entry 5391 (class 0 OID 44401)
-- Dependencies: 249
-- Data for Name: treatments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.treatments (treatment_id, patient_number, diagnosis_id, staff_number, procedure_name, treatment_date, treatment_time, results) FROM stdin;
1	P00001	1	S001	Intravenous Fluid Administration	2026-05-10	08:30:00	First dose given. No allergic reaction.
2	P00002	2	S002	Blood Pressure Monitoring and Counseling	2026-05-10	07:45:00	Blood pressure stabilized.
3	P00003	3	S003	Nebulization Therapy	2026-05-11	12:00:00	Breathing improved after treatment.
4	P00004	4	S004	Ankle X-ray and Cold Compress Application	2026-05-11	20:15:00	Pain reduced after treatment.
5	P00005	5	S005	Blood Sugar Level Testing (FBS)	2026-05-12	09:00:00	Oral treatment tolerated well.
6	P00006	6	S006	Oxygen Therapy and Rest	2026-05-13	10:30:00	Condition improved.
7	P00007	7	S007	Chest X-ray and Sputum Test	2026-05-13	14:20:00	Patient stable after procedure.
8	P00008	8	S008	Allergy Skin Prick Test	2026-05-14	11:00:00	No severe allergic reaction detected.
9	P00009	9	S009	Lumbar Physical Therapy Session	2026-05-14	16:00:00	Pain reduced after session.
10	P00010	10	S010	Urinalysis and Antibiotic Injection	2026-05-15	09:30:00	Infection improving.
11	P00011	11	S011	CBC and Iron Infusion	2026-05-15	10:15:00	Blood levels improving.
12	P00012	12	S012	Throat Swab Culture	2026-05-16	13:00:00	Awaiting lab results.
13	P00013	13	S013	Topical Ointment Application	2026-05-16	14:45:00	Wound healing properly.
14	P00014	14	S014	Emergency Nebulization	2026-05-17	16:30:00	Breathing stabilized.
15	P00015	15	S015	Endoscopy Procedure	2026-05-17	08:00:00	No complications found.
16	P00016	16	S016	Eye Irrigation and Cleaning	2026-05-18	09:45:00	Eye irritation reduced.
17	P00017	17	S017	Psychological Assessment	2026-05-18	11:15:00	Patient cooperative.
18	P00018	18	S018	Vital Signs Monitoring	2026-05-19	14:00:00	Vitals stable.
19	P00019	19	S019	Thyroid Function Test	2026-05-19	15:30:00	Results pending.
20	P00020	20	S020	Nutritional Counseling	2026-05-20	09:00:00	Patient responding well.
21	P00022	23	S012	Observation and Monitoring	2026-05-27	16:02:31	Medication: D002\nStatus: Completed\nMedication: D002\r\nStatus: Completed
22	P00023	24	S032	Observation and Monitoring	2026-05-27	16:45:51	Medication: D003\nStatus: Completed
\.


--
-- TOC entry 5364 (class 0 OID 44119)
-- Dependencies: 222
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
\.


--
-- TOC entry 5378 (class 0 OID 44274)
-- Dependencies: 236
-- Data for Name: wards; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.wards (ward_number, ward_name, location, total_beds, tel_extension) FROM stdin;
1	Orthopedic Ward	Block A	16	101
2	Cardiology Ward	Block A	14	102
3	General Medical Ward	Block A	22	103
4	Neurology Ward	Block A	12	104
5	Emergency	Block B	18	105
6	Surgical Unit	Block B	16	106
7	Rehabilitation	Block B	20	107
8	Geriatric Care Ward	Block C	18	108
9	Dementia Care Ward	Block C	12	109
10	Isolation Ward	Block C	10	110
11	Intensive Care Unit	Block C	8	111
12	Dialysis Ward	Block D	10	112
13	Respiratory Care Ward	Block D	12	113
14	Psychiatric Ward	Block E	12	114
15	Recovery Ward	Block E	10	115
16	Endocrinology Ward	Block E	14	116
17	Chronic Care Ward	Block E	16	117
18	Out Patient Clinic	Block E	0	118
\.


--
-- TOC entry 5383 (class 0 OID 44312)
-- Dependencies: 241
-- Data for Name: work_experiences; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.work_experiences (experience_id, staff_number, position_held, start_date, finish_date, name_of_organization) FROM stdin;
1	S001	Medical Director	2013-01-10	2020-12-31	King's College Hospital London
2	S002	Resident Physician	2016-01-01	2021-12-31	University College London Hospital
3	S003	HR Assistant	2011-05-05	2018-02-28	University of Birmingham HR Department
4	S004	Staff Nurse	2015-01-15	2022-06-30	Royal Infirmary of Edinburgh
5	S005	Healthcare Assistant	2018-01-01	2023-12-31	Leeds Teaching Hospitals NHS Trust
6	S006	Senior Surgeon	2011-01-01	2023-10-15	St Thomas' Hospital London
7	S007	Cashier	2019-01-01	2023-01-01	Glasgow Royal Infirmary Billing Office
8	S008	Cashier	2013-01-01	2020-05-01	NHS London Finance Department
9	S009	HR Officer	2006-01-01	2022-12-31	Manchester City Council HR
10	S010	Consultant Physician	2021-07-01	2022-06-30	University Hospitals Birmingham
11	S011	Junior Nurse	2015-01-01	2022-03-01	Edinburgh Royal Infirmary
12	S012	Charge Nurse	2017-01-01	2019-12-31	Leeds General Infirmary
13	S013	Doctor	2012-01-01	2020-12-31	Liverpool University Hospitals NHS Foundation Trust
14	S014	Cashier	2018-05-01	2022-04-30	NHS Glasgow Finance Unit
15	S015	Senior Nurse	2014-01-01	2021-08-01	King's College Hospital London
16	S016	Medical Researcher	2009-01-01	2022-01-01	National Health Service Research UK
17	S017	General Practitioner	2008-01-01	2024-01-01	Birmingham Community Health Centre
18	S018	Junior Nurse	2009-01-01	2021-12-31	Edinburgh Royal Infirmary
19	S019	Charge Nurse	2016-01-01	2023-05-01	Leeds Teaching Hospitals NHS Trust
20	S020	Lab Technician	2007-01-01	2022-09-01	NHS Laboratory Services Liverpool
21	S021	Charge Nurse	2015-06-01	2023-06-01	Leeds General Infirmary
22	S022	Doctor	2014-01-10	2022-12-31	Manchester Royal Infirmary
23	S023	Junior Nurse	2019-03-15	2023-12-31	Queen Elizabeth Hospital Birmingham
24	S024	Senior Nurse	2013-04-01	2022-06-30	Royal Infirmary of Edinburgh
25	S025	Healthcare Assistant	2017-05-01	2024-01-01	Leeds Community Health Services
26	S026	Charge Nurse	2016-02-10	2023-08-20	Glasgow Royal Infirmary
27	S027	Junior Nurse	2020-01-15	2024-01-01	Leeds Teaching Hospitals NHS Trust
28	S028	Consultant Physician	2012-06-01	2023-12-31	King's College Hospital London
29	S029	Senior Nurse	2011-03-10	2021-12-31	Manchester University NHS Foundation Trust
30	S030	Charge Nurse	2015-07-01	2023-10-01	Birmingham Women's and Children's Hospital
31	S031	Consultant Cardiologist	2011-01-01	2023-12-31	Liverpool Heart and Chest Hospital
32	S032	Junior Nurse	2021-01-10	2024-01-01	Edinburgh Royal Infirmary
33	S033	Healthcare Assistant	2018-02-01	2023-06-01	Leeds City Care Services
34	S034	Charge Nurse	2014-03-01	2023-09-01	Glasgow Royal Infirmary
35	S035	Senior Nurse	2010-05-01	2022-12-31	University Hospitals Leeds
36	S036	Junior Nurse	2022-01-01	2024-01-01	Birmingham Community Health Centre
37	S037	Cashier	2016-06-01	2023-01-01	London NHS Finance Office
38	S038	Doctor	2013-04-01	2023-12-31	Leeds General Infirmary
39	S039	Charge Nurse	2012-05-01	2023-06-01	Edinburgh Royal Infirmary
40	S040	Junior Nurse	2020-03-01	2024-01-01	Manchester Health Trust
41	S041	Personnel Officer	2012-01-01	2023-12-31	Birmingham HR Department
42	S042	Charge Nurse	2015-02-01	2023-09-01	Leeds Teaching Hospitals NHS Trust
43	S043	Junior Nurse	2021-06-01	2024-01-01	London Health Clinic
44	S044	Senior Nurse	2013-03-01	2022-12-31	Glasgow Royal Infirmary
45	S045	Healthcare Assistant	2018-07-01	2023-12-31	Liverpool Community Health Services
46	S046	Consultant Physician	2012-05-01	2024-01-01	Manchester Royal Infirmary
47	S047	Charge Nurse	2011-04-01	2023-10-01	Queen Elizabeth Hospital Birmingham
48	S048	Junior Nurse	2019-06-01	2024-01-01	Leeds General Infirmary
49	S049	Senior Nurse	2009-01-01	2021-12-31	Royal Infirmary of Edinburgh
50	S050	Charge Nurse	2014-05-01	2023-12-31	King's College Hospital London
51	S051	Junior Nurse	2020-02-01	2024-01-01	Manchester Community Health Services
52	S052	Healthcare Assistant	2017-03-01	2023-08-01	Leeds City Care Services
53	S053	Consultant Orthopedic Surgeon	2010-01-01	2023-12-31	Birmingham Orthopedic Hospital
54	S054	Senior Nurse	2012-06-01	2023-05-01	Edinburgh Royal Infirmary
55	S055	Charge Nurse	2011-07-01	2023-12-31	Glasgow Royal Infirmary
56	S056	Junior Nurse	2021-05-01	2024-01-01	Leeds Teaching Hospitals NHS Trust
57	S057	Healthcare Assistant	2016-02-01	2023-06-01	London Community Health Services
58	S058	Doctor	2013-03-01	2023-12-31	Manchester Royal Infirmary
59	S059	Charge Nurse	2012-04-01	2023-10-01	Birmingham City Hospital
60	S060	Junior Nurse	2020-07-01	2024-01-01	Edinburgh Royal Infirmary
61	S061	Senior Nurse	2010-05-01	2022-12-31	Leeds General Infirmary
62	S062	Charge Nurse	2014-06-01	2023-12-31	King's College Hospital London
63	S063	Junior Nurse	2022-01-01	2024-01-01	Manchester Community Health Services
64	S064	Healthcare Assistant	2018-02-01	2023-07-01	Glasgow Care Services
65	S065	Charge Nurse	2013-05-01	2023-11-01	Leeds Teaching Hospitals NHS Trust
66	S066	Junior Nurse	2020-03-01	2024-01-01	Birmingham Community Health Centre
67	S067	Senior Nurse	2011-06-01	2023-06-01	Edinburgh Royal Infirmary
68	S068	Charge Nurse	2012-07-01	2023-12-31	London NHS Trust
69	S069	Junior Nurse	2023-01-01	2024-01-01	Manchester Health Services
70	S070	Charge Nurse	2011-04-01	2023-10-01	Birmingham City Hospital
71	S071	Charge Nurse	2014-06-01	2023-12-31	Leeds General Infirmary
72	S072	Consultant Neurologist	2011-01-01	2024-01-01	Edinburgh Royal Infirmary
73	S073	Consultant Pulmonologist	2010-01-01	2024-01-01	Manchester Royal Infirmary
\.


--
-- TOC entry 5445 (class 0 OID 0)
-- Dependencies: 244
-- Name: appointments_appointment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.appointments_appointment_id_seq', 25, true);


--
-- TOC entry 5446 (class 0 OID 0)
-- Dependencies: 267
-- Name: bill_items_bill_item_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bill_items_bill_item_id_seq', 25, true);


--
-- TOC entry 5447 (class 0 OID 0)
-- Dependencies: 265
-- Name: bills_bill_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bills_bill_id_seq', 20, true);


--
-- TOC entry 5448 (class 0 OID 0)
-- Dependencies: 276
-- Name: care_notes_care_note_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.care_notes_care_note_id_seq', 1, false);


--
-- TOC entry 5449 (class 0 OID 0)
-- Dependencies: 246
-- Name: diagnoses_diagnosis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.diagnoses_diagnosis_id_seq', 24, true);


--
-- TOC entry 5450 (class 0 OID 0)
-- Dependencies: 230
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- TOC entry 5451 (class 0 OID 0)
-- Dependencies: 259
-- Name: in_patients_in_patient_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.in_patients_in_patient_id_seq', 16, true);


--
-- TOC entry 5452 (class 0 OID 0)
-- Dependencies: 227
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- TOC entry 5453 (class 0 OID 0)
-- Dependencies: 271
-- Name: medication_administrations_administration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.medication_administrations_administration_id_seq', 1, true);


--
-- TOC entry 5454 (class 0 OID 0)
-- Dependencies: 219
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 33, true);


--
-- TOC entry 5455 (class 0 OID 0)
-- Dependencies: 261
-- Name: next_of_kins_kin_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.next_of_kins_kin_id_seq', 24, true);


--
-- TOC entry 5456 (class 0 OID 0)
-- Dependencies: 274
-- Name: patient_condition_updates_condition_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.patient_condition_updates_condition_id_seq', 1, false);


--
-- TOC entry 5457 (class 0 OID 0)
-- Dependencies: 263
-- Name: patient_medications_medication_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.patient_medications_medication_id_seq', 19, true);


--
-- TOC entry 5458 (class 0 OID 0)
-- Dependencies: 273
-- Name: patient_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.patient_seq', 23, true);


--
-- TOC entry 5459 (class 0 OID 0)
-- Dependencies: 269
-- Name: payments_payment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_payment_id_seq', 20, true);


--
-- TOC entry 5460 (class 0 OID 0)
-- Dependencies: 238
-- Name: qualifications_qualification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.qualifications_qualification_id_seq', 73, true);


--
-- TOC entry 5461 (class 0 OID 0)
-- Dependencies: 242
-- Name: staff_allocations_allocation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.staff_allocations_allocation_id_seq', 119, true);


--
-- TOC entry 5462 (class 0 OID 0)
-- Dependencies: 252
-- Name: suppliers_supplier_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.suppliers_supplier_id_seq', 20, true);


--
-- TOC entry 5463 (class 0 OID 0)
-- Dependencies: 248
-- Name: treatments_treatment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.treatments_treatment_id_seq', 22, true);


--
-- TOC entry 5464 (class 0 OID 0)
-- Dependencies: 221
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, false);


--
-- TOC entry 5465 (class 0 OID 0)
-- Dependencies: 240
-- Name: work_experiences_experience_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.work_experiences_experience_id_seq', 73, true);


--
-- TOC entry 5128 (class 2606 OID 44357)
-- Name: appointments appointments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_pkey PRIMARY KEY (appointment_id);


--
-- TOC entry 5117 (class 2606 OID 44296)
-- Name: beds beds_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds
    ADD CONSTRAINT beds_pkey PRIMARY KEY (bed_number);


--
-- TOC entry 5119 (class 2606 OID 44294)
-- Name: beds beds_ward_number_bed_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds
    ADD CONSTRAINT beds_ward_number_bed_number_unique UNIQUE (ward_number, bed_number);


--
-- TOC entry 5158 (class 2606 OID 44655)
-- Name: bill_items bill_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items
    ADD CONSTRAINT bill_items_pkey PRIMARY KEY (bill_item_id);


--
-- TOC entry 5156 (class 2606 OID 44631)
-- Name: bills bills_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_pkey PRIMARY KEY (bill_id);


--
-- TOC entry 5092 (class 2606 OID 44174)
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- TOC entry 5089 (class 2606 OID 44163)
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- TOC entry 5166 (class 2606 OID 44760)
-- Name: care_notes care_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.care_notes
    ADD CONSTRAINT care_notes_pkey PRIMARY KEY (care_note_id);


--
-- TOC entry 5130 (class 2606 OID 44384)
-- Name: diagnoses diagnoses_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_pkey PRIMARY KEY (diagnosis_id);


--
-- TOC entry 5134 (class 2606 OID 44438)
-- Name: drugs drugs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.drugs
    ADD CONSTRAINT drugs_pkey PRIMARY KEY (drug_number);


--
-- TOC entry 5099 (class 2606 OID 44222)
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5101 (class 2606 OID 44224)
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- TOC entry 5150 (class 2606 OID 44563)
-- Name: in_patients in_patients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.in_patients
    ADD CONSTRAINT in_patients_pkey PRIMARY KEY (in_patient_id);


--
-- TOC entry 5136 (class 2606 OID 44449)
-- Name: items items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.items
    ADD CONSTRAINT items_pkey PRIMARY KEY (item_number);


--
-- TOC entry 5097 (class 2606 OID 44205)
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- TOC entry 5094 (class 2606 OID 44190)
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- TOC entry 5111 (class 2606 OID 44258)
-- Name: local_doctors local_doctors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.local_doctors
    ADD CONSTRAINT local_doctors_pkey PRIMARY KEY (clinic_number);


--
-- TOC entry 5162 (class 2606 OID 44693)
-- Name: medication_administrations medication_administrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_administrations
    ADD CONSTRAINT medication_administrations_pkey PRIMARY KEY (administration_id);


--
-- TOC entry 5076 (class 2606 OID 44117)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 5152 (class 2606 OID 44589)
-- Name: next_of_kins next_of_kins_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.next_of_kins
    ADD CONSTRAINT next_of_kins_pkey PRIMARY KEY (kin_id);


--
-- TOC entry 5082 (class 2606 OID 44141)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 5164 (class 2606 OID 44734)
-- Name: patient_condition_updates patient_condition_updates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_condition_updates
    ADD CONSTRAINT patient_condition_updates_pkey PRIMARY KEY (condition_id);


--
-- TOC entry 5154 (class 2606 OID 44604)
-- Name: patient_medications patient_medications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_medications
    ADD CONSTRAINT patient_medications_pkey PRIMARY KEY (medication_id);


--
-- TOC entry 5113 (class 2606 OID 44273)
-- Name: patients patients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patients
    ADD CONSTRAINT patients_pkey PRIMARY KEY (patient_number);


--
-- TOC entry 5160 (class 2606 OID 44672)
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (payment_id);


--
-- TOC entry 5121 (class 2606 OID 44305)
-- Name: qualifications qualifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.qualifications
    ADD CONSTRAINT qualifications_pkey PRIMARY KEY (qualification_id);


--
-- TOC entry 5148 (class 2606 OID 44541)
-- Name: requisition_drugs requisition_drugs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_drugs
    ADD CONSTRAINT requisition_drugs_pkey PRIMARY KEY (requisition_number, drug_number);


--
-- TOC entry 5146 (class 2606 OID 44523)
-- Name: requisition_items requisition_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_items
    ADD CONSTRAINT requisition_items_pkey PRIMARY KEY (requisition_number, item_number);


--
-- TOC entry 5144 (class 2606 OID 44515)
-- Name: requisitions requisitions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisitions
    ADD CONSTRAINT requisitions_pkey PRIMARY KEY (requisition_number);


--
-- TOC entry 5103 (class 2606 OID 44231)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_id);


--
-- TOC entry 5085 (class 2606 OID 44151)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5125 (class 2606 OID 44334)
-- Name: staff_allocations staff_allocations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_allocations
    ADD CONSTRAINT staff_allocations_pkey PRIMARY KEY (allocation_id);


--
-- TOC entry 5105 (class 2606 OID 44249)
-- Name: staff staff_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_email_unique UNIQUE (email);


--
-- TOC entry 5107 (class 2606 OID 44251)
-- Name: staff staff_nin_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_nin_unique UNIQUE (nin);


--
-- TOC entry 5109 (class 2606 OID 44247)
-- Name: staff staff_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_pkey PRIMARY KEY (staff_number);


--
-- TOC entry 5140 (class 2606 OID 44467)
-- Name: supplier_drugs supplier_drugs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_drugs
    ADD CONSTRAINT supplier_drugs_pkey PRIMARY KEY (supplier_id, drug_number);


--
-- TOC entry 5142 (class 2606 OID 44485)
-- Name: supplier_items supplier_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_items
    ADD CONSTRAINT supplier_items_pkey PRIMARY KEY (supplier_id, item_number);


--
-- TOC entry 5138 (class 2606 OID 44459)
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (supplier_id);


--
-- TOC entry 5132 (class 2606 OID 44412)
-- Name: treatments treatments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_pkey PRIMARY KEY (treatment_id);


--
-- TOC entry 5078 (class 2606 OID 44132)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 5080 (class 2606 OID 44130)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5115 (class 2606 OID 44280)
-- Name: wards wards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.wards
    ADD CONSTRAINT wards_pkey PRIMARY KEY (ward_number);


--
-- TOC entry 5123 (class 2606 OID 44319)
-- Name: work_experiences work_experiences_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.work_experiences
    ADD CONSTRAINT work_experiences_pkey PRIMARY KEY (experience_id);


--
-- TOC entry 5087 (class 1259 OID 44164)
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- TOC entry 5090 (class 1259 OID 44175)
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- TOC entry 5095 (class 1259 OID 44191)
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- TOC entry 5083 (class 1259 OID 44153)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 5086 (class 1259 OID 44152)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 5126 (class 1259 OID 44780)
-- Name: staff_allocations_staff_number_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX staff_allocations_staff_number_unique ON public.staff_allocations USING btree (staff_number);


--
-- TOC entry 5212 (class 2620 OID 44712)
-- Name: in_patients trg_in_patients_sync_bed_status; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_in_patients_sync_bed_status AFTER INSERT OR DELETE OR UPDATE ON public.in_patients FOR EACH ROW EXECUTE FUNCTION public.fn_sync_bed_status();


--
-- TOC entry 5213 (class 2620 OID 44717)
-- Name: medication_administrations trg_medication_administrations_prepare_record; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_medication_administrations_prepare_record BEFORE INSERT ON public.medication_administrations FOR EACH ROW EXECUTE FUNCTION public.fn_prepare_medication_administration();


--
-- TOC entry 5211 (class 2620 OID 44777)
-- Name: requisitions trg_requisition_delivery_stock; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_requisition_delivery_stock AFTER UPDATE ON public.requisitions FOR EACH ROW EXECUTE FUNCTION public.fn_requisition_delivery_stock();


--
-- TOC entry 5174 (class 2606 OID 44363)
-- Name: appointments appointments_clinic_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_clinic_number_foreign FOREIGN KEY (clinic_number) REFERENCES public.local_doctors(clinic_number) ON DELETE SET NULL;


--
-- TOC entry 5175 (class 2606 OID 44358)
-- Name: appointments appointments_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5176 (class 2606 OID 44368)
-- Name: appointments appointments_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.appointments
    ADD CONSTRAINT appointments_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5169 (class 2606 OID 44288)
-- Name: beds beds_ward_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.beds
    ADD CONSTRAINT beds_ward_number_foreign FOREIGN KEY (ward_number) REFERENCES public.wards(ward_number) ON DELETE CASCADE;


--
-- TOC entry 5202 (class 2606 OID 44656)
-- Name: bill_items bill_items_bill_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bill_items
    ADD CONSTRAINT bill_items_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES public.bills(bill_id) ON DELETE CASCADE;


--
-- TOC entry 5200 (class 2606 OID 44637)
-- Name: bills bills_in_patient_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_in_patient_id_foreign FOREIGN KEY (in_patient_id) REFERENCES public.in_patients(in_patient_id) ON DELETE SET NULL;


--
-- TOC entry 5201 (class 2606 OID 44632)
-- Name: bills bills_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bills
    ADD CONSTRAINT bills_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5209 (class 2606 OID 44761)
-- Name: care_notes care_notes_patient_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.care_notes
    ADD CONSTRAINT care_notes_patient_number_fkey FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5210 (class 2606 OID 44766)
-- Name: care_notes care_notes_staff_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.care_notes
    ADD CONSTRAINT care_notes_staff_number_fkey FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5177 (class 2606 OID 44385)
-- Name: diagnoses diagnoses_appointment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_appointment_id_foreign FOREIGN KEY (appointment_id) REFERENCES public.appointments(appointment_id) ON DELETE CASCADE;


--
-- TOC entry 5178 (class 2606 OID 44390)
-- Name: diagnoses diagnoses_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5179 (class 2606 OID 44395)
-- Name: diagnoses diagnoses_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.diagnoses
    ADD CONSTRAINT diagnoses_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5193 (class 2606 OID 44574)
-- Name: in_patients in_patients_bed_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.in_patients
    ADD CONSTRAINT in_patients_bed_number_foreign FOREIGN KEY (bed_number) REFERENCES public.beds(bed_number) ON DELETE SET NULL;


--
-- TOC entry 5194 (class 2606 OID 44564)
-- Name: in_patients in_patients_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.in_patients
    ADD CONSTRAINT in_patients_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5195 (class 2606 OID 44569)
-- Name: in_patients in_patients_ward_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.in_patients
    ADD CONSTRAINT in_patients_ward_number_foreign FOREIGN KEY (ward_number) REFERENCES public.wards(ward_number) ON DELETE SET NULL;


--
-- TOC entry 5204 (class 2606 OID 44694)
-- Name: medication_administrations medication_administrations_medication_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_administrations
    ADD CONSTRAINT medication_administrations_medication_id_fkey FOREIGN KEY (medication_id) REFERENCES public.patient_medications(medication_id) ON DELETE CASCADE;


--
-- TOC entry 5205 (class 2606 OID 44699)
-- Name: medication_administrations medication_administrations_patient_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_administrations
    ADD CONSTRAINT medication_administrations_patient_number_fkey FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5206 (class 2606 OID 44704)
-- Name: medication_administrations medication_administrations_staff_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.medication_administrations
    ADD CONSTRAINT medication_administrations_staff_number_fkey FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5196 (class 2606 OID 44590)
-- Name: next_of_kins next_of_kins_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.next_of_kins
    ADD CONSTRAINT next_of_kins_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5207 (class 2606 OID 44735)
-- Name: patient_condition_updates patient_condition_updates_patient_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_condition_updates
    ADD CONSTRAINT patient_condition_updates_patient_number_fkey FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5208 (class 2606 OID 44740)
-- Name: patient_condition_updates patient_condition_updates_staff_number_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_condition_updates
    ADD CONSTRAINT patient_condition_updates_staff_number_fkey FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5197 (class 2606 OID 44615)
-- Name: patient_medications patient_medications_drug_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_medications
    ADD CONSTRAINT patient_medications_drug_number_foreign FOREIGN KEY (drug_number) REFERENCES public.drugs(drug_number) ON DELETE CASCADE;


--
-- TOC entry 5198 (class 2606 OID 44610)
-- Name: patient_medications patient_medications_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_medications
    ADD CONSTRAINT patient_medications_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5199 (class 2606 OID 44605)
-- Name: patient_medications patient_medications_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patient_medications
    ADD CONSTRAINT patient_medications_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5168 (class 2606 OID 44267)
-- Name: patients patients_clinic_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.patients
    ADD CONSTRAINT patients_clinic_number_foreign FOREIGN KEY (clinic_number) REFERENCES public.local_doctors(clinic_number) ON DELETE SET NULL;


--
-- TOC entry 5203 (class 2606 OID 44673)
-- Name: payments payments_bill_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES public.bills(bill_id) ON DELETE CASCADE;


--
-- TOC entry 5170 (class 2606 OID 44306)
-- Name: qualifications qualifications_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.qualifications
    ADD CONSTRAINT qualifications_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE CASCADE;


--
-- TOC entry 5191 (class 2606 OID 44547)
-- Name: requisition_drugs requisition_drugs_drug_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_drugs
    ADD CONSTRAINT requisition_drugs_drug_number_foreign FOREIGN KEY (drug_number) REFERENCES public.drugs(drug_number) ON DELETE CASCADE;


--
-- TOC entry 5192 (class 2606 OID 44542)
-- Name: requisition_drugs requisition_drugs_requisition_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_drugs
    ADD CONSTRAINT requisition_drugs_requisition_number_foreign FOREIGN KEY (requisition_number) REFERENCES public.requisitions(requisition_number) ON DELETE CASCADE;


--
-- TOC entry 5189 (class 2606 OID 44529)
-- Name: requisition_items requisition_items_item_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_items
    ADD CONSTRAINT requisition_items_item_number_foreign FOREIGN KEY (item_number) REFERENCES public.items(item_number) ON DELETE CASCADE;


--
-- TOC entry 5190 (class 2606 OID 44524)
-- Name: requisition_items requisition_items_requisition_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisition_items
    ADD CONSTRAINT requisition_items_requisition_number_foreign FOREIGN KEY (requisition_number) REFERENCES public.requisitions(requisition_number) ON DELETE CASCADE;


--
-- TOC entry 5187 (class 2606 OID 44504)
-- Name: requisitions requisitions_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisitions
    ADD CONSTRAINT requisitions_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5188 (class 2606 OID 44509)
-- Name: requisitions requisitions_ward_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.requisitions
    ADD CONSTRAINT requisitions_ward_number_foreign FOREIGN KEY (ward_number) REFERENCES public.wards(ward_number) ON DELETE CASCADE;


--
-- TOC entry 5172 (class 2606 OID 44335)
-- Name: staff_allocations staff_allocations_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_allocations
    ADD CONSTRAINT staff_allocations_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE CASCADE;


--
-- TOC entry 5173 (class 2606 OID 44340)
-- Name: staff_allocations staff_allocations_ward_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff_allocations
    ADD CONSTRAINT staff_allocations_ward_number_foreign FOREIGN KEY (ward_number) REFERENCES public.wards(ward_number) ON DELETE CASCADE;


--
-- TOC entry 5167 (class 2606 OID 44241)
-- Name: staff staff_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(role_id);


--
-- TOC entry 5183 (class 2606 OID 44473)
-- Name: supplier_drugs supplier_drugs_drug_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_drugs
    ADD CONSTRAINT supplier_drugs_drug_number_foreign FOREIGN KEY (drug_number) REFERENCES public.drugs(drug_number) ON DELETE CASCADE;


--
-- TOC entry 5184 (class 2606 OID 44468)
-- Name: supplier_drugs supplier_drugs_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_drugs
    ADD CONSTRAINT supplier_drugs_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(supplier_id) ON DELETE CASCADE;


--
-- TOC entry 5185 (class 2606 OID 44491)
-- Name: supplier_items supplier_items_item_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_items
    ADD CONSTRAINT supplier_items_item_number_foreign FOREIGN KEY (item_number) REFERENCES public.items(item_number) ON DELETE CASCADE;


--
-- TOC entry 5186 (class 2606 OID 44486)
-- Name: supplier_items supplier_items_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.supplier_items
    ADD CONSTRAINT supplier_items_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(supplier_id) ON DELETE CASCADE;


--
-- TOC entry 5180 (class 2606 OID 44418)
-- Name: treatments treatments_diagnosis_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_diagnosis_id_foreign FOREIGN KEY (diagnosis_id) REFERENCES public.diagnoses(diagnosis_id) ON DELETE CASCADE;


--
-- TOC entry 5181 (class 2606 OID 44413)
-- Name: treatments treatments_patient_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_patient_number_foreign FOREIGN KEY (patient_number) REFERENCES public.patients(patient_number) ON DELETE CASCADE;


--
-- TOC entry 5182 (class 2606 OID 44423)
-- Name: treatments treatments_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.treatments
    ADD CONSTRAINT treatments_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE SET NULL;


--
-- TOC entry 5171 (class 2606 OID 44320)
-- Name: work_experiences work_experiences_staff_number_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.work_experiences
    ADD CONSTRAINT work_experiences_staff_number_foreign FOREIGN KEY (staff_number) REFERENCES public.staff(staff_number) ON DELETE CASCADE;


-- Completed on 2026-05-28 01:09:40

--
-- PostgreSQL database dump complete
--

\unrestrict jeX37tB01Fm62cHfDA1SsbS5RjaP6OopbRgHBQxa5nZWgFt5pGMy3a1o6AYfcWQ

