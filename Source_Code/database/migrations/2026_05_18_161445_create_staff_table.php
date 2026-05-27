<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('staff', function (Blueprint $table) {
        $table->string('staff_number', 10)->primary();
        //$table->foreignId('role_id')->constrained('roles', 'role_id');

        $table->integer('role_id');

$table->foreign('role_id')
      ->references('role_id')
      ->on('roles');

        $table->string('email')->unique();
        $table->string('password');

        $table->string('first_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('address')->nullable();
        $table->string('telephone')->nullable();

        $table->date('date_of_birth')->nullable();
        $table->string('sex')->nullable();
        $table->string('nin')->nullable()->unique();

        $table->string('position')->nullable();
        $table->decimal('current_salary', 10, 2)->nullable();
        $table->string('salary_scale')->nullable();
        $table->integer('hours_per_week')->nullable();

        $table->string('contract_type')->nullable();
        $table->string('payment_type')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
