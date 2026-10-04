<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The slug column was added nullable, and slugs are generated when a model is
     * saved — so rows that existed before the column did never got one. Since the
     * slug is the route key, every link to one of those properties failed to
     * build at all. This fills them in.
     */
    public function up(): void
    {
        DB::table('apartments')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($apartment) {
                $base = Str::slug($apartment->name) ?: 'apartment';
                $slug = $base;
                $suffix = 2;

                while (DB::table('apartments')->where('slug', $slug)->where('id', '!=', $apartment->id)->exists()) {
                    $slug = "{$base}-{$suffix}";
                    $suffix++;
                }

                DB::table('apartments')->where('id', $apartment->id)->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        // Slugs are derived data; nothing to restore.
    }
};
