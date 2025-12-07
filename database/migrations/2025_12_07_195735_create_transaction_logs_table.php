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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();

            // Optional link to invoice
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');

            // Optional link to a booking
            $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');

            // User who is affected by this entry (payer or receiver)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Type of transaction
            $table->enum('type', [
                'payment_in',      // customer → platform
                'payment_out',     // platform → provider
                'refund',          // platform → customer
                'fee',             // platform fee
                'adjustment',      // manual fix or admin correction
            ]);

            // Money amount (+ or -)
            $table->decimal('amount', 10, 2);

            // Additional structured metadata
            $table->json('meta')->nullable();

            // External payment gateway ID or reference
            $table->string('external_reference')->nullable();

            // FINANCIAL TIMESTAMP
            $table->timestamp('processed_at')->nullable();

            $table->timestamps(); // created_at = log created, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
