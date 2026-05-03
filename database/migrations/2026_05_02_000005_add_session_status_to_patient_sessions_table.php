<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->enum('session_status', ['completed', 'follow_up_required', 'in_progress'])
                ->default('completed')
                ->after('visit_type');
        });
    }

    public function down(): void
    {
        Schema::table('patient_sessions', function (Blueprint $table) {
            $table->dropColumn('session_status');
        });
    }
};
