<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('reference', 12)->nullable()->unique()->after('id');
            $table->unsignedInteger('guests')->default(1)->after('apartment_id');
            $table->decimal('subtotal', 10, 2)->default(0)->after('nights');
            $table->decimal('service_fee', 10, 2)->default(0)->after('subtotal');
            $table->decimal('taxes', 10, 2)->default(0)->after('service_fee');
            $table->char('currency', 3)->default('USD')->after('total_price');
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
        });

        // Backfill: existing rows stored only total_price, which was nights * rate.
        // That figure was the subtotal, so carry it across and leave fees at zero
        // rather than inventing charges that were never quoted.
        DB::table('bookings')->update(['subtotal' => DB::raw('total_price')]);

        DB::table('bookings')->whereNull('reference')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                DB::table('bookings')
                    ->where('id', $row->id)
                    ->update(['reference' => 'CC'.str_pad((string) $row->id, 8, '0', STR_PAD_LEFT)]);
            }
        });

        // A confirmed booking holds inventory; the status/date pair is the hot path
        // for every availability lookup.
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['apartment_id', 'status', 'check_in', 'check_out'], 'bookings_availability_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_availability_index');
            $table->dropColumn([
                'reference',
                'guests',
                'subtotal',
                'service_fee',
                'taxes',
                'currency',
                'confirmed_at',
                'cancelled_at',
            ]);
        });
    }
};
