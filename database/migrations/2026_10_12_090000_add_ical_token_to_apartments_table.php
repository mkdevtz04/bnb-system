<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The token is what makes the per-property .ics export URL guessable only
     * by someone who already has it, rather than by walking the apartment id or
     * slug. It is generated here for existing rows and by Apartment::booted()
     * for every new one, so the column is never null in practice.
     */
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->string('ical_token', 40)->nullable()->unique()->after('slug');
        });

        DB::table('apartments')->whereNull('ical_token')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('apartments')
                    ->where('id', $row->id)
                    ->update(['ical_token' => Str::random(40)]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('ical_token');
        });
    }
};
