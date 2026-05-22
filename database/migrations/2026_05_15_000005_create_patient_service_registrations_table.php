<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_service_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->foreignId('service_id')->constrained('clinic_services')->cascadeOnDelete();
            $table->string('service_name');
            $table->decimal('total_price', 10, 2);
            $table->date('service_date');
            $table->date('package_end_date')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_service_registrations');
    }
};
