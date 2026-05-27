<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id('experience_id');
            $table->string('staff_number', 10);
            $table->string('position_held', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->string('name_of_organization', 100)->nullable();
            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};