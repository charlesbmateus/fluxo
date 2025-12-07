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
        Schema::create('service_availabilities', function (Blueprint $table) {
            $table->id();

            // Service this availability belongs to
            $table->foreignId('service_id')->constrained()->onDelete('cascade');

            // Day of the week (0 = Sunday, 6 = Saturday)
            $table->unsignedTinyInteger('day_of_week');

            // Start and end time (HH:MM:SS)
            $table->time('start_time');
            $table->time('end_time');

            // Optional: mark if provider temporarily disabled this slot
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate entries for the same day and time
            $table->unique(['service_id', 'day_of_week', 'start_time', 'end_time'], 'availability_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_availabilities');
    }
};
