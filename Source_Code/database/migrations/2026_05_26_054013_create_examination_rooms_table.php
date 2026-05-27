<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the examination_rooms table.
     */
    public function up(): void
    {
        Schema::create('examination_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('status')->default('Available');
            $table->timestamps();
        });
    }

    /**
     * Drops the examination_rooms table.
     */
    public function down(): void
    {
        Schema::dropIfExists('examination_rooms');
    }
};