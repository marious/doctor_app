<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requested_tests', function (Blueprint $table) {
            $table->enum('type', ['lab', 'scan'])->default('lab')->after('test_name');
        });
    }

    public function down(): void
    {
        Schema::table('requested_tests', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
