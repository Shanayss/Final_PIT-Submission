<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisitions', function (Blueprint $table) {
            $table->integer('requisition_number')->primary();
            $table->string('staff_number', 10)->nullable();
            $table->integer('ward_number');
            $table->date('date_ordered');
            $table->date('date_received')->nullable();
            $table->string('status', 20)->default('Pending');

            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->nullOnDelete();

            $table->foreign('ward_number')
                ->references('ward_number')
                ->on('wards')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};