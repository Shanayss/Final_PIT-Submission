<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id('treatment_id');
            $table->string('patient_number', 20);
            $table->unsignedBigInteger('diagnosis_id');
            $table->string('staff_number', 10)->nullable();
            $table->string('procedure_name', 100)->nullable();
            $table->date('treatment_date')->nullable();
            $table->time('treatment_time');
            $table->text('results')->nullable();

            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('diagnosis_id')
                ->references('diagnosis_id')
                ->on('diagnoses')
                ->cascadeOnDelete();

            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
