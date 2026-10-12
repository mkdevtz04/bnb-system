<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * blocked_dates was made "manual closures only" in
     * make_blocked_dates_manual_only — this migration deliberately reopens one
     * exception: a date blocked because an *external* calendar (Airbnb, VRBO)
     * says it's booked there. That is still not a decision this app made, so it
     * is tagged with its source and the feed that produced it, which lets
     * bookings:sync-ical-feeds add and remove its own rows on every poll without
     * ever touching a host's real manual closures (source = manual, the
     * unchanged default).
     */
    public function up(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('reason');
            $table->foreignId('ical_feed_id')->nullable()->after('source')
                ->constrained('apartment_ical_feeds')->nullOnDelete();
            $table->string('external_uid')->nullable()->after('ical_feed_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ical_feed_id');
            $table->dropColumn(['source', 'external_uid']);
        });
    }
};
