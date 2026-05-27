<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No DB changes required for supplier update workflow.
        // This migration exists only as a placeholder in case your collaborator expects a migration for every DB-layer change.
    }

    public function down(): void
    {
        // No rollback needed.
    }
};

