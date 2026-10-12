<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One row per external calendar a host has pointed at this property (their
     * Airbnb listing's export URL, a VRBO feed, and so on). bookings:sync-ical-feeds
     * polls each of these hourly and mirrors their busy dates into blocked_dates,
     * tagged with this row's id so a re-sync only ever touches the dates it owns.
     */
    public function up(): void
    {
        Schema::create('apartment_ical_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('url', 2048);
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->string('last_sync_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_ical_feeds');
    }
};
