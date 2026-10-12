<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentRate;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

/**
 * Lets the host override price_per_night for specific dates — a higher
 * weekend or peak-season rate, say — without touching the base rate every
 * other night still falls back to. Split out of AdminController, which was
 * already large, but guarded by the same 'admin' middleware group.
 *
 * App\Services\PricingService::nightlyBreakdown() is the only reader of
 * apartment_rates; this controller only ever writes to it.
 */
class ApartmentRateController extends Controller
{
    /**
     * The calendar widget's data source: every date in range, with whatever
     * it would actually cost a guest tonight.
     */
    public function index(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after:from'],
        ]);

        $from = isset($validated['from'])
            ? CarbonImmutable::parse($validated['from'])
            : CarbonImmutable::today();
        $to = isset($validated['to'])
            ? CarbonImmutable::parse($validated['to'])
            : $from->addMonths(2);

        $overrides = $apartment->rates()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('price_per_night', 'date')
            ->map(fn ($price) => (float) $price)
            ->all();

        $baseRate = (float) $apartment->price_per_night;

        $days = collect(CarbonPeriod::create($from, $to))->map(function ($date) use ($overrides, $baseRate) {
            $key = $date->toDateString();

            return [
                'date' => $key,
                'price' => $overrides[$key] ?? $baseRate,
                'overridden' => isset($overrides[$key]),
            ];
        });

        return response()->json([
            'base_rate' => $baseRate,
            'days' => $days,
        ]);
    }

    /**
     * Set (or replace) an override across a date range, optionally limited to
     * specific days of the week — "every Friday and Saturday this summer".
     */
    public function store(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'between:0,6'],
        ]);

        $start = CarbonImmutable::parse($validated['start_date']);
        $end = CarbonImmutable::parse($validated['end_date']);
        $daysOfWeek = $validated['days_of_week'] ?? null;

        $rows = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if ($daysOfWeek !== null && ! in_array($date->dayOfWeek, $daysOfWeek, true)) {
                continue;
            }

            $rows[] = [
                'apartment_id' => $apartment->id,
                'date' => $date->toDateString(),
                'price_per_night' => $validated['price_per_night'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === []) {
            return back()->withErrors(['rate' => 'No nights matched that range and selection.']);
        }

        ApartmentRate::upsert($rows, ['apartment_id', 'date'], ['price_per_night', 'updated_at']);

        return back()->with('success', count($rows).' night(s) priced at '.number_format((float) $validated['price_per_night'], 2).'.');
    }

    /**
     * Revert a date range to the apartment's base rate.
     */
    public function destroy(Request $request, Apartment $apartment)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $cleared = $apartment->rates()
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->delete();

        return back()->with('success', "{$cleared} night(s) reverted to the base rate.");
    }
}
