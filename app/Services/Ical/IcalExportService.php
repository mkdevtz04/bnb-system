<?php

namespace App\Services\Ical;

use App\Models\Apartment;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Sabre\VObject\Component\VCalendar;

/**
 * Builds the public .ics feed for one apartment: every night it is
 * unavailable, as a deliberately anonymous busy block. Other platforms
 * (Airbnb, VRBO, Google Calendar) subscribe to this at the URL
 * App\Http\Controllers\IcalController::export() serves, so they never offer a
 * night this system has already taken — closing the same gap
 * App\Services\Ical\IcalImportService closes in the other direction.
 *
 * Never includes a guest's name, email or booking reference: this feed's only
 * protection is its token (Apartment::$ical_token), and that token is handed
 * to a third party by design.
 */
class IcalExportService
{
    public function __construct(private readonly AvailabilityService $availability) {}

    public function serialize(Apartment $apartment): string
    {
        return $this->build($apartment)->serialize();
    }

    public function build(Apartment $apartment): VCalendar
    {
        $calendar = new VCalendar;
        $calendar->add('PRODID', '-//CoastalCharmz//Apartment Availability//EN');
        $calendar->add('METHOD', 'PUBLISH');

        foreach ($this->bookingRanges($apartment) as $range) {
            $this->addEvent($calendar, 'booking-'.$range['id'], $range['start'], $range['end'], 'Reserved');
        }

        foreach ($this->blockedRanges($apartment) as $index => $range) {
            $this->addEvent($calendar, 'closure-'.$apartment->id.'-'.$index, $range['start'], $range['end'], 'Not available');
        }

        return $calendar;
    }

    /**
     * @return array<int, array{id:int, start:CarbonImmutable, end:CarbonImmutable}>
     */
    private function bookingRanges(Apartment $apartment): array
    {
        return $apartment->bookings()
            ->whereIn('status', AvailabilityService::HOLDING_STATUSES)
            ->get(['id', 'check_in', 'check_out'])
            ->map(fn ($booking) => [
                'id' => $booking->id,
                'start' => CarbonImmutable::parse($booking->check_in),
                'end' => CarbonImmutable::parse($booking->check_out),
            ])
            ->all();
    }

    /**
     * Manual closures only. A date mirrored in from a connected feed
     * (source = ical) is not this property's own decision, so it is never
     * re-exported back out to a third calendar.
     *
     * @return array<int, array{start:CarbonImmutable, end:CarbonImmutable}>
     */
    private function blockedRanges(Apartment $apartment): array
    {
        $dates = $apartment->blockedDates()->manual()->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => CarbonImmutable::parse($date))
            ->all();

        return $this->collapseConsecutive($dates);
    }

    /**
     * Turns a sorted list of individual dates into [start, end) ranges, so ten
     * consecutive closed nights become one VEVENT instead of ten.
     *
     * @param  array<int, CarbonImmutable>  $dates
     * @return array<int, array{start:CarbonImmutable, end:CarbonImmutable}>
     */
    private function collapseConsecutive(array $dates): array
    {
        $ranges = [];
        $start = null;
        $previous = null;

        foreach ($dates as $date) {
            if ($start === null) {
                $start = $date;
            } elseif (! $previous->addDay()->isSameDay($date)) {
                $ranges[] = ['start' => $start, 'end' => $previous->addDay()];
                $start = $date;
            }

            $previous = $date;
        }

        if ($start !== null) {
            $ranges[] = ['start' => $start, 'end' => $previous->addDay()];
        }

        return $ranges;
    }

    private function addEvent(VCalendar $calendar, string $uidSeed, CarbonImmutable $start, CarbonImmutable $end, string $summary): void
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'coastalcharmz.local';

        $vevent = $calendar->add('VEVENT');
        $vevent->add('UID', $uidSeed.'@'.$host);
        $vevent->add('DTSTAMP', gmdate('Ymd\THis\Z'));
        $vevent->add('DTSTART', $start->format('Ymd'), ['VALUE' => 'DATE']);
        $vevent->add('DTEND', $end->format('Ymd'), ['VALUE' => 'DATE']);
        $vevent->add('SUMMARY', $summary);
        $vevent->add('TRANSP', 'OPAQUE');
    }
}
