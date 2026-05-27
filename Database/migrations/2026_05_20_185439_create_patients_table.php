<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->string('patient_number', 20)->primary();
            $table->integer('clinic_number')->nullable();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('address', 100)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->date('date_registered')->useCurrent();
            $table->foreign('clinic_number')
                ->references('clinic_number')
                ->on('local_doctors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};