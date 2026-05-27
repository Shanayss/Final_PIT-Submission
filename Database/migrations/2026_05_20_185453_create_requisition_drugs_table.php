<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisition_drugs', function (Blueprint $table) {
            $table->integer('requisition_number');
            $table->string('drug_number', 20);
            $table->integer('quantity_required');
            $table->primary(['requisition_number', 'drug_number']);

            $table->foreign('requisition_number')
                ->references('requisition_number')
                ->on('requisitions')
                ->cascadeOnDelete();

            $table->foreign('drug_number')
                ->references('drug_number')
                ->on('drugs')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_drugs');
    }
};
