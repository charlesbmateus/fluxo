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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // e.g. Cleaning, Electrician, Babysitting
            $table->string('slug')->unique();           // cleaning, electrician, babysitting
            $table->text('description')->nullable();    // Optional description of the category
            $table->string('icon')->nullable();         // Optional icon for UI (e.g. "mdi-home")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
