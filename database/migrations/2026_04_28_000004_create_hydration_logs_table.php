<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hydration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->unsignedTinyInteger('cups_count')->default(0); // 0–8 cups
            $table->timestamps();

            $table->unique(['patient_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hydration_logs');
    }
};
