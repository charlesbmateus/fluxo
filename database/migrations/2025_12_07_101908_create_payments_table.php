<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // User making the payment
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Related booking
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');

            // Payment details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('CHF');

            $table->enum('status', ['pending', 'completed', 'refunded', 'failed'])->default('pending');

            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->string('payment_method')->nullable(); // e.g., stripe, paypal

            $table->timestamps();

            // Index for faster queries
            $table->index(['user_id', 'booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
