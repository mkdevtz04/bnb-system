<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A night taken off sale.
 *
 * This table no longer mirrors confirmed bookings — availability reads those
 * directly — so every row here is one of two real closures: a host's own manual
 * block (source = manual, the default), or a mirror of a busy date on an
 * external calendar the host has connected (source = ical). The latter are
 * owned by a specific ApartmentIcalFeed and are added and removed automatically
 * by bookings:sync-ical-feeds; a host's manual rows are never touched by that
 * sync.
 */
class BlockedDate extends Model
{
    use HasFactory;

    protected $table = 'blocked_dates';

    protected $fillable = [
        'apartment_id',
        'date',
        'reason',
        'source',
        'ical_feed_id',
        'external_uid',
    ];

    protected function casts(): array
    {
        return [
            'date' => DateOnly::class,
        ];
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function icalFeed()
    {
        return $this->belongsTo(ApartmentIcalFeed::class, 'ical_feed_id');
    }

    /**
     * A host's own deliberate closure — never written or removed by a sync.
     */
    public function scopeManual(Builder $query): Builder
    {
        return $query->where('source', 'manual');
    }

    /**
     * Mirrored from an external calendar. Optionally narrow to one feed, which
     * is how a re-sync finds exactly the rows it is allowed to delete.
     */
    public function scopeFromIcal(Builder $query, ?int $feedId = null): Builder
    {
        return $query->where('source', 'ical')->when($feedId, fn ($q) => $q->where('ical_feed_id', $feedId));
    }
}
