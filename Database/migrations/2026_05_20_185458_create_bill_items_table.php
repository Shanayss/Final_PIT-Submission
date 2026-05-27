<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id('bill_item_id');
            $table->unsignedBigInteger('bill_id');
            $table->string('item_type', 50);
            $table->integer('reference_id')->nullable();
            $table->string('description', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);

            $table->foreign('bill_id')
                ->references('bill_id')
                ->on('bills')
                ->cascadeOnDelete();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_items');
    }
};