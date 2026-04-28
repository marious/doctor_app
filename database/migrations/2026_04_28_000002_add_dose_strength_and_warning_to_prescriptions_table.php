<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // e.g. "500mg", "5mg" — displayed as a badge next to the medication name
            $table->string('dose_strength')->nullable()->after('medication_name');
            // e.g. "Do not skip doses. Finish the entire course."
            $table->text('warning_note')->nullable()->after('duration_days');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['dose_strength', 'warning_note']);
        });
    }
};
