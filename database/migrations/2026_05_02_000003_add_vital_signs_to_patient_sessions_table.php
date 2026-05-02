<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->string('bp', 20)->nullable()->after('risk_status');      // e.g. "120/80"
            $table->unsignedSmallInteger('hr')->nullable()->after('bp');     // bpm e.g. 72
            $table->string('temp', 10)->nullable()->after('hr');             // e.g. "98.6°F"
            $table->string('weight', 10)->nullable()->after('temp');         // e.g. "68 kg"
        });
    }

    public function down(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->dropColumn(['bp', 'hr', 'temp', 'weight']);
        });
    }
};
