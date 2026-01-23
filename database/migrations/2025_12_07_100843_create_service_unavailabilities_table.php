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
        Schema::create('service_unavailabilities', function (Blueprint $table) {
            $table->id();

            // Linked to a specific service
            $table->foreignId('service_id')->constrained()->onDelete('cascade');

            // Start and end of the block
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');

            // Optional description (ex: Vacation, Medical leave)
            $table->string('reason')->nullable();

            $table->timestamps();

            // 🔹 Index for fast availability checks
            $table->index(['service_id', 'start_datetime', 'end_datetime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_unavailabilities');
    }
};
