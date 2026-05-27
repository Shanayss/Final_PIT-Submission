<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_medications', function (Blueprint $table) {
            $table->id('medication_id');
            $table->string('staff_number', 10)->nullable();
            $table->string('patient_number', 20);
            $table->string('drug_number', 20);
            $table->integer('unit_per_day')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->nullOnDelete();

            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('drug_number')
                ->references('drug_number')
                ->on('drugs')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_medications');
    }
};