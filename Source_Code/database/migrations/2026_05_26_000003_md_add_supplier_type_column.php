<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The current suppliers table (created by 2026_05_20_185449_create_suppliers_table.php)
        // does not include supplier_type, but the MedDirector suppliers UI/controller expects it.
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'supplier_type')) {
                $table->string('supplier_type', 50)->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'city')) {
                $table->string('city', 100)->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'supplier_type')) {
                $table->dropColumn('supplier_type');
            }
            if (Schema::hasColumn('suppliers', 'city')) {
                $table->dropColumn('city');
            }
            // created_at/updated_at are optional; only drop if present.
            if (Schema::hasColumn('suppliers', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('suppliers', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
};

