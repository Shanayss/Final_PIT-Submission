<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->integer('requisition_number');
            $table->string('item_number', 20);
            $table->integer('quantity_required');
            $table->primary(['requisition_number', 'item_number']);

            $table->foreign('requisition_number')
                ->references('requisition_number')
                ->on('requisitions')
                ->cascadeOnDelete();

            $table->foreign('item_number')
                ->references('item_number')
                ->on('items')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_supplies');
    }
};
