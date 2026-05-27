<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('in_patients', function (Blueprint $table) {
            $table->id('in_patient_id');
            $table->string('patient_number', 20);
            $table->integer('ward_number')->nullable();
            $table->integer('bed_number')->nullable();
            $table->date('date_placed_on_waiting_list')->nullable();
            $table->integer('expected_stay_days')->nullable();
            $table->date('date_admitted');
            $table->date('date_expected_leave')->nullable();
            $table->date('date_actual_leave')->nullable();
            $table->string('status', 20)
                  ->default('Waiting');

            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('ward_number')
                ->references('ward_number')
                ->on('wards')
                ->nullOnDelete();

            $table->foreign('bed_number')
                ->references('bed_number')
                ->on('beds')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('in_patients');
    }
};
