<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->enum('position_title', ['left', 'center', 'right'])->default('left')->after('title');
            $table->enum('position_description', ['left', 'center', 'right'])->default('left')->after('description');
            $table->enum('position_button', ['left', 'center', 'right'])->default('left')->after('button_link');
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropColumn(['position_title', 'position_description', 'position_button']);
        });
    }
};
