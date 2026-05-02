<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');

            // Basic Information
            $table->date('session_date');
            $table->enum('visit_type', ['new_visit', 'follow_up', 'emergency', 'consultation'])->default('follow_up');
            $table->enum('risk_status', ['stable', 'high_risk', 'monitor'])->nullable();

            // Clinical Details
            $table->text('symptoms')->nullable();           // comma-separated
            $table->text('diagnosis');
            $table->text('treatment_plan')->nullable();

            // Examination Notes (files uploaded via media library)
            $table->text('ultrasound_notes')->nullable();
            $table->text('lab_results_notes')->nullable();
            $table->text('pelvic_examination_notes')->nullable();

            // Required Lab Tests
            $table->json('required_lab_tests')->nullable();  // e.g. ["CBC","Blood Sugar","Other: custom name"]

            // Quick Notes & Medications
            $table->text('quick_notes')->nullable();
            $table->text('medications')->nullable();         // comma-separated
            $table->date('follow_up_date')->nullable();
            $table->text('private_doctor_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_sessions');
    }
};
