<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('role_id')->nullable()->after('password')->default(2);

            $table->unsignedTinyInteger('age');
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->enum('marital_status', ['single', 'married']);

            $table->date('date_of_marriage')->nullable();
            $table->string('husband_name')->nullable();

            $table->string('emergency_number')->nullable();
            $table->text('address')->nullable();

            $table->string('phone')->unique();

            // Biometric preference
            $table->boolean('biometric_enabled')->default(false);

            $table->boolean('notification_enabled')->default(true);

            $table->boolean('active')->default(false);

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_id');
            $table->dropColumn('age');
            $table->dropColumn('blood_group');
            $table->dropColumn('marital_status');
            $table->dropColumn('date_of_marriage');
            $table->dropColumn('husband_name');
            $table->dropColumn('emergency_number');
            $table->dropColumn('address');
            $table->dropColumn('phone');
            $table->dropColumn('biometric_enabled');
            $table->dropColumn('notification_enabled');
            $table->dropColumn('active');
            $table->dropSoftDeletes();
        });
    }
};
