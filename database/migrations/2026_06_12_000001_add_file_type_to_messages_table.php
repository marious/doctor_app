<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text','image','audio','file') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN type ENUM('text','image','audio') NOT NULL");
    }
};
