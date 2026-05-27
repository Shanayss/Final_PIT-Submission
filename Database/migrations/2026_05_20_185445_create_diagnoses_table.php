<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id('diagnosis_id');
            $table->unsignedBigInteger('appointment_id');
            $table->string('patient_number', 20);
            $table->string('staff_number', 10)->nullable();
            $table->string('diagnosis_details', 255)->nullable();
            $table->date('diagnosis_date')->nullable();
            $table->text('notes')->nullable();

            $table->foreign('appointment_id')
                ->references('appointment_id')
                ->on('appointments')
                ->cascadeOnDelete();

            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
