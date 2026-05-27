<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('procedure_name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('procedures')->insert([
            [
                'procedure_name' => 'Intravenous Fluid Administration',
                'description' => 'Administration of fluids directly into the vein.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Wound Dressing',
                'description' => 'Cleaning and covering wounds to prevent infection.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Blood Pressure Monitoring',
                'description' => 'Monitoring and recording patient blood pressure.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Medication Administration',
                'description' => 'Giving prescribed medication to the patient.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Physical Examination',
                'description' => 'General examination of patient condition.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Patient Monitoring',
                'description' => 'Monitoring patient progress and vital condition.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procedure_name' => 'Outpatient Consultation',
                'description' => 'Consultation and treatment for non-admitted patients.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};