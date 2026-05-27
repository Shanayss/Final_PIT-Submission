<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {

            $table->id('appointment_id');
            $table->string('patient_number', 20);
            $table->integer('clinic_number')->nullable();
            $table->string('staff_number', 10)->nullable();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('examination_room', 20)->nullable();
            $table->string('status', 20)
                  ->default('Scheduled');

            
            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('clinic_number')
                ->references('clinic_number')
                ->on('local_doctors')
                ->nullOnDelete();

            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
