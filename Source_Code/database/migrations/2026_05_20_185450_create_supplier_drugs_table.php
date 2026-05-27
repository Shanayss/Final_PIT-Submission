<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_drugs', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id');
            $table->string('drug_number', 20);
            $table->decimal('unit_price', 10, 2);
            $table->primary(['supplier_id', 'drug_number']);

            $table->foreign('supplier_id')
                ->references('supplier_id')
                ->on('suppliers')
                ->cascadeOnDelete();

            $table->foreign('drug_number')
                ->references('drug_number')
                ->on('drugs')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_drugs');
    }
};