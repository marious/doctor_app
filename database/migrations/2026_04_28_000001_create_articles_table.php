<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('short_description');
            $table->longText('full_text')->nullable();
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
        Schema::dropIfExists('articles');
    }
};
