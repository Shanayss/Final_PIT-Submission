<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_allocations', function (Blueprint $table) {
            $table->id('allocation_id');
            $table->string('staff_number', 10);
            $table->integer('ward_number');
            $table->string('role_for_week', 50)->nullable();
            $table->string('shift', 20)->nullable();
            $table->date('week_start_date')->nullable();
            $table->timestamps(); 
            
            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->cascadeOnDelete();

            $table->foreign('ward_number')
                ->references('ward_number')
                ->on('wards')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_allocations');
    }
};
