<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->integer('ward_number')->primary();
            $table->string('ward_name', 50);
            $table->string('location', 50)->nullable();
            $table->integer('total_beds')->nullable();
            $table->integer('tel_extension')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
