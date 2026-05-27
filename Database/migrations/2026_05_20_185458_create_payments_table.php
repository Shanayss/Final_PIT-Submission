<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('bill_id');
            $table->date('payment_date');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method', 20);
            $table->foreign('bill_id')
                ->references('bill_id')
                ->on('bills')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
