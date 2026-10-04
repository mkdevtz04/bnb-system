<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The unread badge is counted on every page an authenticated person loads, so
     * the pair it filters on is indexed rather than scanned.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read'], 'messages_unread_index');

            // Threads are listed newest-first within a booking.
            $table->index(['booking_id', 'created_at'], 'messages_thread_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_unread_index');
            $table->dropIndex('messages_thread_index');
        });
    }
};
