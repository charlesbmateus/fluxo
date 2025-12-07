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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Owner of the service (must be a user with role = "provider")
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Category of the service
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            $table->string('title');                     // e.g. "Deep Home Cleaning"
            $table->string('slug')->unique();           // deep-home-cleaning
            $table->text('description');                // detailed description

            // Pricing
            $table->decimal('price', 10, 2)->default(0); // price per service or per hour
            $table->string('pricing_model')->default('fixed');
            // "fixed" | "hourly"

            // Service location
            $table->string('city')->nullable();
            $table->string('country')->nullable();

            // Whether the service is active and visible
            $table->boolean('is_active')->default(true);

            // Optional media
            $table->string('thumbnail')->nullable();     // image path

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
