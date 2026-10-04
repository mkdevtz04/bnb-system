<?php

namespace App\Support;

/**
 * Formats money without requiring the intl extension.
 *
 * Laravel's Number::currency() delegates to NumberFormatter, which needs ext-intl.
 * That extension is not enabled in this environment and is not installed by the
 * project's Dockerfile either, so every price on the site would have thrown in
 * production. The symbol table below covers the currencies this app actually
 * offers; anything else falls back to the ISO code, which is unambiguous.
 */
class Money
{
    private const SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'TZS' => 'TSh ',
        'KES' => 'KSh ',
        'ZAR' => 'R',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'JPY' => '¥',
    ];

    /** Currencies conventionally written without minor units. */
    private const ZERO_DECIMAL = ['JPY', 'TZS', 'KES'];

    public static function format(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?: (string) config('booking.currency', 'USD'));
        $amount = (float) ($amount ?? 0);

        $decimals = in_array($currency, self::ZERO_DECIMAL, true) ? 0 : 2;
        $formatted = number_format($amount, $decimals);

        $symbol = self::SYMBOLS[$currency] ?? null;

        return $symbol === null
            ? "{$currency} {$formatted}"
            : $symbol.$formatted;
    }

    /**
     * Whole-unit form, for headline prices where the cents add noise.
     */
    public static function round(float|int|string|null $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?: (string) config('booking.currency', 'USD'));
        $symbol = self::SYMBOLS[$currency] ?? null;
        $formatted = number_format((float) ($amount ?? 0), 0);

        return $symbol === null ? "{$currency} {$formatted}" : $symbol.$formatted;
    }
}
