<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medication_administrations')) {
            Schema::create('medication_administrations', function (Blueprint $table) {
                $table->id('administration_id');
                $table->unsignedBigInteger('medication_id');
                $table->string('patient_number', 20);
                $table->string('staff_number', 10)->nullable();
                $table->string('dosage_administered', 50)->nullable();
                $table->dateTime('administered_at')->useCurrent();
                $table->text('notes')->nullable();

                $table->foreign('medication_id')->references('medication_id')->on('patient_medications')->cascadeOnDelete();
                $table->foreign('patient_number')->references('patient_number')->on('patients')->cascadeOnDelete();
                $table->foreign('staff_number')->references('staff_number')->on('staff')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('patient_condition_updates')) {
            Schema::create('patient_condition_updates', function (Blueprint $table) {
                $table->id('condition_id');
                $table->string('patient_number', 20);
                $table->string('staff_number', 10)->nullable();
                $table->string('condition_status', 30)->default('Stable');
                $table->string('blood_pressure', 20)->nullable();
                $table->string('temperature', 20)->nullable();
                $table->string('heart_rate', 20)->nullable();
                $table->text('notes')->nullable();
                $table->dateTime('recorded_at')->useCurrent();

                $table->foreign('patient_number')->references('patient_number')->on('patients')->cascadeOnDelete();
                $table->foreign('staff_number')->references('staff_number')->on('staff')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('care_notes')) {
            Schema::create('care_notes', function (Blueprint $table) {
                $table->id('care_note_id');
                $table->string('patient_number', 20);
                $table->string('staff_number', 10)->nullable();
                $table->string('note_type', 50)->default('General Observation');
                $table->text('notes');
                $table->dateTime('recorded_at')->useCurrent();

                $table->foreign('patient_number')->references('patient_number')->on('patients')->cascadeOnDelete();
                $table->foreign('staff_number')->references('staff_number')->on('staff')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('care_notes');
        Schema::dropIfExists('patient_condition_updates');
        Schema::dropIfExists('medication_administrations');
    }
};
