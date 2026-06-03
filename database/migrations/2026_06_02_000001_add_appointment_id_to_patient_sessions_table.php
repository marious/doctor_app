<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->foreignId('appointment_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('appointments')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn('appointment_id');
        });
    }
};
