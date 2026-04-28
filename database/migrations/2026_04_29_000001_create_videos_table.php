<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('short_description')->nullable();
            $table->string('video_url');
            $table->enum('target_audience', [
                'all',
                'pregnancy',
                'pregnancy_1st',
                'pregnancy_2nd',
                'pregnancy_3rd',
                'gynecology',
            ])->default('all');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
