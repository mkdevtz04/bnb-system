<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Money
    |--------------------------------------------------------------------------
    |
    | Rates applied by App\Services\PricingService. These were previously
    | hard-coded into a <script> block in the booking form, where the server
    | could not see them and therefore never charged them.
    |
    */

    'currency' => env('BOOKING_CURRENCY', 'USD'),

    'service_fee_rate' => (float) env('BOOKING_SERVICE_FEE_RATE', 0.05),

    'tax_rate' => (float) env('BOOKING_TAX_RATE', 0.10),

    /*
    |--------------------------------------------------------------------------
    | Stay rules
    |--------------------------------------------------------------------------
    */

    // How far ahead the calendar and search will accept dates.
    'booking_window_months' => (int) env('BOOKING_WINDOW_MONTHS', 12),

    // Upper bound on a single stay, to stop one booking swallowing the calendar.
    'max_nights' => (int) env('BOOKING_MAX_NIGHTS', 30),

];
