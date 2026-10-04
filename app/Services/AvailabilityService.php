<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;

/**
 * The single source of truth for "can this apartment be booked on these dates".
 *
 * A stay occupies the half-open interval [check_in, check_out): the guest holds
 * the nights from check_in up to but NOT including check_out. That is what makes
 * same-day turnover work — one guest leaving on the 10th and another arriving on
 * the 10th do not overlap. The previous inclusive whereBetween() checks rejected
 * that perfectly valid booking.
 */
class AvailabilityService
{
    /**
     * Statuses that hold inventory. A cancelled booking releases its dates.
     */
    public const HOLDING_STATUSES = ['pending', 'confirmed'];

    /**
     * Is this apartment bookable for the whole stay?
     *
     * @param  Booking|int|null  $ignoreBooking  Exclude a booking from the check,
     *                                           so an existing stay does not block
     *                                           its own modification.
     */
    public function isAvailable(
        Apartment $apartment,
        CarbonImmutable $checkIn,
        CarbonImmutable $checkOut,
        Booking|int|null $ignoreBooking = null,
    ): bool {
        if ($apartment->status !== 'available') {
            return false;
        }

        if ($checkOut <= $checkIn) {
            return false;
        }

        return ! $this->hasConflictingBooking($apartment, $checkIn, $checkOut, $ignoreBooking)
            && ! $this->hasBlockedDate($apartment, $checkIn, $checkOut);
    }

    /**
     * Does another live booking overlap this stay?
     *
     * @param  array<int, string>|null  $statuses  Narrow which statuses count as a
     *                                             conflict. Confirming a booking
     *                                             passes ['confirmed'], because a
     *                                             competing *request* must not be
     *                                             able to veto the host's decision.
     */
    public function hasConflictingBooking(
        Apartment $apartment,
        CarbonImmutable $checkIn,
        CarbonImmutable $checkOut,
        Booking|int|null $ignoreBooking = null,
        ?array $statuses = null,
    ): bool {
        $query = $apartment->bookings()
            ->whereIn('status', $statuses ?? self::HOLDING_STATUSES)
            ->where(fn (Builder $q) => $this->applyOverlap($q, $checkIn, $checkOut));

        if ($ignoreBooking !== null) {
            $query->whereKeyNot($ignoreBooking instanceof Booking ? $ignoreBooking->getKey() : $ignoreBooking);
        }

        return $query->exists();
    }

    /**
     * Has the host manually blocked any night in this stay?
     *
     * This check was missing entirely: blocked dates were only ever used to grey
     * out the calendar, so a direct POST booked straight through them.
     */
    public function hasBlockedDate(Apartment $apartment, CarbonImmutable $checkIn, CarbonImmutable $checkOut): bool
    {
        return $apartment->blockedDates()
            ->where('date', '>=', $checkIn->toDateString())
            ->where('date', '<', $checkOut->toDateString())
            ->exists();
    }

    /**
     * Constrain a bookings query to rows overlapping [checkIn, checkOut).
     *
     * Two half-open intervals overlap iff each starts before the other ends.
     */
    public function applyOverlap(Builder $query, CarbonImmutable $checkIn, CarbonImmutable $checkOut): Builder
    {
        return $query
            ->where('check_in', '<', $checkOut->toDateString())
            ->where('check_out', '>', $checkIn->toDateString());
    }

    /**
     * Narrow an Apartment query to those free for the whole stay.
     *
     * Used by search so the listing and the booking form agree on availability.
     */
    public function scopeAvailableBetween(Builder $query, CarbonImmutable $checkIn, CarbonImmutable $checkOut): Builder
    {
        return $query
            ->whereDoesntHave('bookings', function (Builder $q) use ($checkIn, $checkOut) {
                $q->whereIn('status', self::HOLDING_STATUSES);
                $this->applyOverlap($q, $checkIn, $checkOut);
            })
            ->whereDoesntHave('blockedDates', function (Builder $q) use ($checkIn, $checkOut) {
                $q->where('date', '>=', $checkIn->toDateString())
                    ->where('date', '<', $checkOut->toDateString());
            });
    }

    /**
     * Every date the calendar should disable, as Y-m-d strings.
     *
     * Note the check_out date of a stay is deliberately left selectable: it is a
     * valid arrival day for the next guest.
     */
    public function unavailableDates(Apartment $apartment, int $monthsAhead = 12): array
    {
        $horizon = CarbonImmutable::today()->addMonths($monthsAhead);

        $dates = $apartment->blockedDates()
            ->where('date', '>=', CarbonImmutable::today()->toDateString())
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date)->toDateString())
            ->all();

        $bookings = $apartment->bookings()
            ->whereIn('status', self::HOLDING_STATUSES)
            ->where('check_out', '>=', CarbonImmutable::today()->toDateString())
            ->where('check_in', '<=', $horizon->toDateString())
            ->get(['check_in', 'check_out']);

        foreach ($bookings as $booking) {
            $nights = CarbonPeriod::create(
                CarbonImmutable::parse($booking->check_in),
                CarbonImmutable::parse($booking->check_out)->subDay(),
            );

            foreach ($nights as $night) {
                $dates[] = $night->toDateString();
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        return $dates;
    }
}
