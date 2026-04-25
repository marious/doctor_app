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
        Schema::drop('cycle_symptom_logs');

        Schema::create('cycle_symptom_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_tracking_id')->constrained('patient_trackings')->onDelete('cascade');
            $table->date('logged_date');
            $table->foreignId('symptom_id')->constrained('symptoms')->onDelete('cascade');
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('mild');
            $table->timestamps();

            $table->unique(['patient_tracking_id', 'logged_date', 'symptom_id'], 'csl_tracking_date_symptom_unique');
        });
    }

    public function down(): void
    {
        Schema::drop('cycle_symptom_logs');

        Schema::create('cycle_symptom_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_tracking_id')->constrained('patient_trackings')->onDelete('cascade');
            $table->date('logged_date');
            $table->enum('symptom', [
                'cramps','fatigue','headache','bloating',
                'mood_swings','backache','nausea','breast_tenderness',
            ]);
            $table->enum('severity', ['mild', 'moderate', 'severe'])->default('mild');
            $table->timestamps();

            $table->unique(['patient_tracking_id', 'logged_date', 'symptom'], 'csl_tracking_date_symptom_unique');
        });
    }
};
