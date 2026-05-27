<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id('supplier_id');
            $table->string('supplier_name', 100);
            $table->string('address', 150);
            $table->string('telephone', 20)->nullable();
            $table->string('fax', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};