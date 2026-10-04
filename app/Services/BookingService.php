<?php

namespace App\Services;

use App\Exceptions\DatesUnavailableException;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelledNotification;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\NewBookingNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Owns every booking state transition.
 *
 * Controllers used to check availability and insert in two separate statements
 * with nothing in between, so two requests for the same dates could both pass the
 * check and both succeed. Creation now happens inside a transaction that locks
 * the apartment row first, which serialises concurrent attempts on the same
 * property while leaving different properties free to book in parallel.
 */
class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    /**
     * @throws DatesUnavailableException
     */
    public function create(
        User $user,
        Apartment $apartment,
        CarbonImmutable $checkIn,
        CarbonImmutable $checkOut,
        int $guests,
    ): Booking {
        $booking = DB::transaction(function () use ($user, $apartment, $checkIn, $checkOut, $guests) {
            // Serialise everyone competing for this property's calendar.
            $apartment = Apartment::whereKey($apartment->getKey())->lockForUpdate()->firstOrFail();

            if (! $this->availability->isAvailable($apartment, $checkIn, $checkOut)) {
                throw new DatesUnavailableException(
                    'Those dates have just been taken. Please pick another stay.'
                );
            }

            if ($guests > $apartment->max_guests) {
                throw new DatesUnavailableException(
                    "This property sleeps up to {$apartment->max_guests} guests."
                );
            }

            $quote = $this->pricing->quote($apartment, $checkIn, $checkOut, $guests);

            return $user->bookings()->create([
                'reference' => $this->generateReference(),
                'apartment_id' => $apartment->id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'status' => 'pending',
                ...$quote->toBookingAttributes(),
            ]);
        });

        $booking->load(['apartment', 'user']);

        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewBookingNotification($booking));
        }

        return $booking;
    }

    /**
     * @throws DatesUnavailableException
     */
    public function confirm(Booking $booking): Booking
    {
        $displaced = collect();

        DB::transaction(function () use ($booking, &$displaced) {
            $booking->refresh();

            if ($booking->status !== 'pending') {
                throw new DatesUnavailableException('Only pending bookings can be confirmed.');
            }

            $checkIn = CarbonImmutable::parse($booking->check_in);
            $checkOut = CarbonImmutable::parse($booking->check_out);

            // Only an already-confirmed stay can block this. Counting pending
            // requests here would let two overlapping requests veto each other,
            // leaving the host unable to confirm either one.
            $conflict = $this->availability->hasConflictingBooking(
                $booking->apartment,
                $checkIn,
                $checkOut,
                ignoreBooking: $booking,
                statuses: ['confirmed'],
            );

            if ($conflict) {
                throw new DatesUnavailableException(
                    'Another confirmed booking already covers these dates.'
                );
            }

            $booking->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // Whoever else asked for these nights has just lost them. Cancel the
            // losing requests rather than leaving them pending forever against
            // dates they can never get.
            $displaced = $booking->apartment->bookings()
                ->where('status', 'pending')
                ->whereKeyNot($booking->getKey())
                ->where(fn ($q) => $this->availability->applyOverlap($q, $checkIn, $checkOut))
                ->get();

            foreach ($displaced as $loser) {
                $loser->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            }
        });

        $booking->user->notify(new BookingConfirmedNotification($booking));

        foreach ($displaced as $loser) {
            $loser->loadMissing('apartment', 'user');
            $loser->user->notify(new BookingCancelledNotification($loser));
        }

        return $booking;
    }

    public function cancel(Booking $booking, ?User $cancelledBy = null): Booking
    {
        // Read the state we branch on BEFORE mutating it. The old admin cancel
        // set the status first and then asked "was it confirmed?", which could
        // never be true, so the dates were never released.
        $wasConfirmed = $booking->status === 'confirmed';

        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Cancelled bookings no longer hold inventory, because availability reads
        // the booking's own status. There is nothing to clean up.
        if ($wasConfirmed && $cancelledBy?->isAdmin()) {
            $booking->user->notify(new BookingCancelledNotification($booking));
        }

        return $booking;
    }

    /**
     * Characters a person cannot misread or mistype.
     *
     * Dropped: 0 and O, 1 and I and L, plus U — the first two pairs are the
     * classic misreadings when a code is written down or read over the phone, and
     * removing U means a random code can never spell something unfortunate.
     * 30 symbols over 8 places is still 656 billion combinations.
     */
    private const REFERENCE_ALPHABET = '23456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * The code a guest quotes back to the host.
     *
     * Deliberately not the database id. A sequential id in an email tells every
     * guest how many bookings the business has ever taken, and tells anyone who
     * gets one that #000007 is worth guessing neighbours of. This is opaque,
     * unguessable and carries no information about the business.
     */
    private function generateReference(): string
    {
        $alphabet = self::REFERENCE_ALPHABET;
        $max = strlen($alphabet) - 1;

        do {
            $body = '';
            for ($i = 0; $i < 8; $i++) {
                // random_int, not Str::random: this is an identifier people will
                // quote, and a predictable one invites guessing at other bookings.
                $body .= $alphabet[random_int(0, $max)];
            }

            $reference = 'CC'.$body;
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }
}
