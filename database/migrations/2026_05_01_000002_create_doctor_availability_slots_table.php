<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('availability_day_id')
                ->constrained('doctor_availability_days')
                ->onDelete('cascade');
            $table->string('time', 5); // HH:mm
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availability_slots');
    }
};
