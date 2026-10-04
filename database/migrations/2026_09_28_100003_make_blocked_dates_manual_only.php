<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * blocked_dates used to hold two different things: nights a host deliberately
     * closed, and a mirror of every confirmed booking's nights. The mirror was
     * written on confirm but never removed on cancel (the status was overwritten
     * before the branch that cleaned it up could read it), so cancelled stays
     * silently kept their inventory off the market forever.
     *
     * Availability now reads bookings directly, so the mirror is redundant. This
     * drops the mirrored rows and labels what remains as a real host decision.
     */
    public function up(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('date');
        });

        // Remove rows that merely shadow a live booking. A host block that happens
        // to sit outside any booking is a genuine closure and is left alone.
        DB::table('blocked_dates')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('bookings')
                    ->whereColumn('bookings.apartment_id', 'blocked_dates.apartment_id')
                    ->whereIn('bookings.status', ['pending', 'confirmed'])
                    ->whereColumn('bookings.check_in', '<=', 'blocked_dates.date')
                    ->whereColumn('bookings.check_out', '>', 'blocked_dates.date');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * The deleted rows are not restored: they were derived data, and the bookings
     * they were derived from are still present.
     */
    public function down(): void
    {
        Schema::table('blocked_dates', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
