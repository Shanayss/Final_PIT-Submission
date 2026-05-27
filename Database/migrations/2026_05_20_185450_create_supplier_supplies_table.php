<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_items', function (Blueprint $table) {
            $table->unsignedBigInteger('supplier_id');
            $table->string('item_number', 20);
            $table->decimal('unit_price', 10, 2);
            $table->primary(['supplier_id', 'item_number']);

            $table->foreign('supplier_id')
                ->references('supplier_id')
                ->on('suppliers')
                ->cascadeOnDelete();

            $table->foreign('item_number')
                ->references('item_number')
                ->on('items')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_supplies');
    }
};