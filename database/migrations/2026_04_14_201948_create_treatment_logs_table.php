<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('treatment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->date('date');
            $table->enum('time_of_day', ['morning', 'afternoon', 'evening', 'night']);
            $table->enum('status', ['taken', 'skipped']);
            $table->timestamp('action_at')->nullable();
            $table->timestamps();
            
            // Allow only one log per dose slot per day
            $table->unique(['patient_id', 'prescription_id', 'date', 'time_of_day'], 'unique_treatment_log');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_logs');
    }
};
