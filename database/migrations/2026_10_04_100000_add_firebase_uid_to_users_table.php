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
        Schema::table('users', function (Blueprint $table) {
            // The stable identifier for a Google sign-in. Email is deliberately
            // not the key: a Google account can change its address, and matching
            // on an address is what makes account takeover possible.
            $table->string('firebase_uid', 128)->nullable()->unique()->after('id');

            // Google hands us a profile photo; cheaper and friendlier than the
            // initials chip, and harmless if absent.
            $table->string('avatar_url')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['firebase_uid']);
            $table->dropColumn(['firebase_uid', 'avatar_url']);
        });
    }
};
