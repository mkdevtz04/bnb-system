<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A night the host has deliberately taken off sale.
 *
 * This table no longer mirrors confirmed bookings — availability reads those
 * directly — so every row here is a real closure.
 */
class BlockedDate extends Model
{
    use HasFactory;

    protected $table = 'blocked_dates';

    protected $fillable = [
        'apartment_id',
        'date',
        'reason',
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
}
