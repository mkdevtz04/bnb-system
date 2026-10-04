<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replying used to be a mailto: link, which hands the job to whatever desktop
     * mail client the machine has configured — and does nothing at all when there
     * isn't one, which is the normal case for anyone using webmail. Replies are
     * now sent by the application, so they need somewhere to live: a record of
     * what was said, by whom and when, that survives whoever happened to be
     * logged in at the time.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('message');
            $table->timestamp('replied_at')->nullable()->after('reply');
            $table->foreignId('replied_by')->nullable()->after('replied_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['is_read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropForeign(['replied_by']);
            $table->dropIndex(['is_read', 'created_at']);
            $table->dropColumn(['reply', 'replied_at', 'replied_by']);
        });
    }
};
