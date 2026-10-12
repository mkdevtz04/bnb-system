<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An external calendar (an Airbnb listing's export URL, a VRBO feed, ...) the
 * host has pointed at one of our properties, so that its busy dates are
 * mirrored into blocked_dates and this system never offers a night that is
 * already taken elsewhere.
 *
 * App\Console\Commands\SyncIcalFeeds polls every row here on a schedule;
 * App\Services\Ical\IcalImportService does the actual fetch-parse-diff.
 */
class ApartmentIcalFeed extends Model
{
    protected $fillable = [
        'apartment_id',
        'label',
        'url',
        'last_synced_at',
        'last_sync_status',
        'last_sync_error',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function blockedDates()
    {
        return $this->hasMany(BlockedDate::class, 'ical_feed_id');
    }

    public function markSynced(): void
    {
        $this->forceFill([
            'last_synced_at' => now(),
            'last_sync_status' => 'ok',
            'last_sync_error' => null,
        ])->save();
    }

    public function markFailed(string $error): void
    {
        $this->forceFill([
            'last_synced_at' => now(),
            'last_sync_status' => 'error',
            'last_sync_error' => mb_substr($error, 0, 255),
        ])->save();
    }
}
