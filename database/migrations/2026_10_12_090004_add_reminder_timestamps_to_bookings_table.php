<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stamped by bookings:send-arrival-reminders and bookings:send-review-requests
     * right after each send, the same way confirmed_at is stamped by
     * BookingService::confirm(). A scheduled command that reselects "bookings
     * checking in tomorrow" every day needs this column, or it mails the same
     * guest once per run for the whole of that day.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('arrival_reminder_sent_at')->nullable()->after('cancelled_at');
            $table->timestamp('review_request_sent_at')->nullable()->after('arrival_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['arrival_reminder_sent_at', 'review_request_sent_at']);
        });
    }
};
