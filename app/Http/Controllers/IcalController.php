<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Services\Ical\IcalExportService;
use Illuminate\Http\Response;

class IcalController extends Controller
{
    public function __construct(private readonly IcalExportService $export) {}

    /**
     * The public .ics feed for one property. Deliberately unauthenticated —
     * other calendar platforms cannot log in to fetch it — and protected only
     * by the apartment's own ical_token, which is why this takes the token as
     * a route segment rather than relying on the {apartment} binding alone.
     */
    public function export(Apartment $apartment, string $token): Response
    {
        abort_unless(hash_equals((string) $apartment->ical_token, $token), 404);

        return response($this->export->serialize($apartment), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.$apartment->slug.'.ics"',
        ]);
    }
}
