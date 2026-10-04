<?php

namespace App\Casts;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Stores a DATE column as a bare Y-m-d string.
 *
 * Laravel's built-in date cast writes "2027-03-05 00:00:00" regardless of the
 * declared column type. MySQL silently truncates that to a DATE, but SQLite is
 * dynamically typed and keeps the whole string, so a lexicographic comparison
 * against "2027-03-05" reports the value as greater. Availability is decided by
 * exactly that kind of comparison, which made a correct same-day turnover look
 * like a double booking — on SQLite only, so it would have passed in production
 * and failed in tests, or worse, drifted between the two.
 *
 * Writing the date the way the schema declares it makes the comparison exact on
 * every engine, and keeps the column usable by the availability index.
 *
 * @implements CastsAttributes<CarbonImmutable|null, string|null>
 */
class DateOnly implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?CarbonImmutable
    {
        return $value === null ? null : CarbonImmutable::parse($value)->startOfDay();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : CarbonImmutable::parse($value)->toDateString();
    }
}
