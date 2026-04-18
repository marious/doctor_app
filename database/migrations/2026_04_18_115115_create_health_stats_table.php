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
        Schema::create('health_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_tracking_id')
                  ->constrained('patient_trackings')
                  ->onDelete('cascade');
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->unsignedSmallInteger('bpm')->nullable();
            $table->enum('weight_status', ['healthy_gain', 'underweight', 'overweight'])->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_stats');
    }
};
