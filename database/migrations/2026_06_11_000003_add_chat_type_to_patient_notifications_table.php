<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE patient_notifications MODIFY COLUMN type ENUM(
            'appointment','period','fertility','symptoms','pregnancy','medication','general','chat'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE patient_notifications MODIFY COLUMN type ENUM(
            'appointment','period','fertility','symptoms','pregnancy','medication','general'
        ) NOT NULL");
    }
};
