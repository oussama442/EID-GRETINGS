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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime_planned');
            $table->dateTime('return_datetime_actual')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('return_location')->nullable();
            $table->decimal('daily_rate_agreed', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('status')->default('reserved'); // reserved/active/completed/cancelled/overdue
            $table->integer('pickup_mileage')->nullable();
            $table->integer('return_mileage')->nullable();
            $table->string('pickup_fuel_level')->nullable(); // e.g., '1/4', '1/2', '3/4', 'Full'
            $table->string('return_fuel_level')->nullable();
            $table->string('contract_pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
