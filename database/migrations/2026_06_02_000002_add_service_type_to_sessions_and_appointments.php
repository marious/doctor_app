<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'general' to the appointments service_type enum
        DB::statement("ALTER TABLE appointments MODIFY COLUMN service_type ENUM('pregnant', 'gynecology', 'general') NOT NULL");

        // Add service_type to patient_sessions
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->enum('service_type', ['pregnant', 'gynecology', 'general'])
                ->nullable()
                ->after('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });

        DB::statement("ALTER TABLE appointments MODIFY COLUMN service_type ENUM('pregnant', 'gynecology', 'general') NOT NULL");
    }
};
