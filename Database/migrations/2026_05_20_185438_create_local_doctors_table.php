<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('local_doctors', function (Blueprint $table) {
            $table->integer('clinic_number')->primary();
            $table->string('full_name', 100);
            $table->string('address', 100)->nullable();
            $table->string('telephone', 20)->nullable();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('local_doctors');
    }
};