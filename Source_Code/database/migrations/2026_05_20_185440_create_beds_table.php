<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beds', function (Blueprint $table) {
            $table->integer('bed_number')->primary();
            $table->integer('ward_number');
            $table->string('status', 20)->default('Available');
            $table->foreign('ward_number')
                ->references('ward_number')
                ->on('wards')
                ->cascadeOnDelete();
            
            $table->unique(['ward_number', 'bed_number']);
        });
    }
  
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};