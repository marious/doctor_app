<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patient_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->enum('tracking_type', ['menstrual', 'pregnancy']);
            // Menstrual fields
            $table->date('last_menstruation_date')->nullable();
            $table->unsignedTinyInteger('cycle_length')->nullable()->comment('In days');
            $table->unsignedTinyInteger('period_duration')->nullable()->comment('In days');

            // Pregnancy fields
            $table->date('lmp_date')->nullable()->comment('First day of Last Menstrual Period');
            $table->enum('pregnancy_test_status', ['positive', 'negative', 'not_sure'])->nullable();

            // Computed / cached fields
            $table->date('next_period_date')->nullable();
            $table->date('ovulation_date')->nullable();
            $table->date('estimated_due_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_trackings');
    }
};
