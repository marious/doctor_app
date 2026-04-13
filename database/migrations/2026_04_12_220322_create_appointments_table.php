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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->onDelete('cascade');

            $table->enum('service_type', ['pregnant', 'gynecology']);
            $table->enum('visit_type', ['appointment', 'new_visit'])->default('new_visit');

            $table->date('appointment_date');
            $table->time('appointment_time');

            $table->enum('status', [
                'pending',       // Request Submitted
                'under_review',  // Under Review by clinic
                'confirmed',     // Confirmed by secretary
                'not_approved',  // Rejected
                'completed',     // Visit done
                'cancelled',     // Cancelled by patient
            ])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
