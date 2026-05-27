<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('next_of_kins', function (Blueprint $table) {

            $table->id('kin_id');
            $table->string('patient_number', 20);
            $table->string('full_name', 100);
            $table->string('relationship', 50);
            $table->string('address', 100)->nullable();
            $table->string('telephone', 20)->nullable();
            $table->foreign('patient_number')
                ->references('patient_number')
                ->on('patients')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('next_of_kins');
    }
};
