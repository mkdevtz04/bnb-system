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
        Schema::table('apartments', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('address')->nullable()->after('description');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->json('amenities')->nullable()->after('country');
            // The show page hard-coded eight amenities into the markup for every
            // property; this lets each one declare its own.
            $table->time('check_in_from')->default('14:00:00')->after('bathrooms');
            $table->time('check_out_until')->default('11:00:00')->after('check_in_from');
            $table->unsignedTinyInteger('min_nights')->default(1)->after('check_out_until');
            $table->decimal('cleaning_fee', 8, 2)->default(0)->after('price_per_night');
            $table->index(['status', 'max_guests']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropIndex(['status', 'max_guests']);
            $table->dropColumn([
                'slug',
                'address',
                'city',
                'country',
                'amenities',
                'check_in_from',
                'check_out_until',
                'min_nights',
                'cleaning_fee',
            ]);
        });
    }
};
