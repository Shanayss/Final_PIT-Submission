<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id('bill_id');
            $table->string('patient_number', 20);
            $table->unsignedBigInteger('in_patient_id')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->date('bill_date');
            $table->string('status', 20);

            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

            $table->foreign('in_patient_id')
                ->references('in_patient_id')
                ->on('in_patients')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
