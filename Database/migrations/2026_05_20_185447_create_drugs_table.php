<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drugs', function (Blueprint $table) {
            $table->string('drug_number', 20)->primary();
            $table->string('drug_name', 100);
            $table->string('description', 100)->nullable();
            $table->string('dosage', 50)->nullable();
            $table->string('method_of_admin', 50)->nullable();
            $table->integer('quantity_of_stock')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->decimal('cost_per_unit', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
