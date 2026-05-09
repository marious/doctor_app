<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('session_id')
                ->nullable()
                ->constrained('patient_sessions')
                ->onDelete('set null');

            $table->string('medication_name');
            $table->string('dosage');                        // e.g. "500mg", "1 tablet", "65mg"

            $table->enum('frequency', [
                'once_daily',
                'twice_daily',
                'three_times_daily',
                'four_times_daily',
                'every_8_hours',
                'every_12_hours',
                'as_needed',
                'weekly',
            ])->default('once_daily');

            $table->unsignedTinyInteger('times_per_day')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('special_instructions')->nullable();

            $table->boolean('patient_reminders')->default(false);
            $table->boolean('auto_stop')->default(false);

            $table->enum('status', ['active', 'completed', 'stopped'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_prescriptions');
    }
};
