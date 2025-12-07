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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Reference to the booking
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');

            // Reference to the client who pays
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');

            // Reference to the service provider
            $table->foreignId('provider_id')->constrained('users')->onDelete('cascade');

            $table->decimal('amount', 10, 2); // Total paid
            $table->decimal('platform_fee', 10, 2)->default(0); // Platform commission
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            $table->string('payment_method')->nullable(); // e.g., card, Twint, PayPal
            $table->string('transaction_id')->nullable(); // External payment ID
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
