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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // One review per stay, which is what makes the score trustworthy:
            // you cannot review a property you never booked.
            $table->foreignId('booking_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');

            // Scored out of 10, per category, the way the major OTAs do it.
            $table->unsignedTinyInteger('cleanliness');
            $table->unsignedTinyInteger('comfort');
            $table->unsignedTinyInteger('location');
            $table->unsignedTinyInteger('facilities');
            $table->unsignedTinyInteger('staff');
            $table->unsignedTinyInteger('value');
            $table->decimal('overall', 3, 1);

            $table->string('title')->nullable();
            $table->text('liked')->nullable();
            $table->text('disliked')->nullable();
            $table->timestamps();

            $table->index(['apartment_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
