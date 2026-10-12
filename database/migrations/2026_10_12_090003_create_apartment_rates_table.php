<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A per-night override on top of apartments.price_per_night. A date with no
     * row here simply costs the apartment's base rate — PricingService falls
     * back to that so every property behaves exactly as before until a host
     * actually sets an override.
     */
    public function up(): void
    {
        Schema::create('apartment_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('price_per_night', 8, 2);
            $table->timestamps();

            $table->unique(['apartment_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_rates');
    }
};
