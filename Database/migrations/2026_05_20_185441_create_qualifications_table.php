<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id('qualification_id');
            $table->string('staff_number', 10);
            $table->string('qualification_type', 100)->nullable();
            $table->date('qualification_date')->nullable();
            $table->string('institution_name', 100)->nullable();
            $table->foreign('staff_number')
                ->references('staff_number')
                ->on('staff')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};
