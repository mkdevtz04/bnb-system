<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'user_id',
        'apartment_id',
        'guests',
        'check_in',
        'check_out',
        'nights',
        'subtotal',
        'service_fee',
        'taxes',
        'total_price',
        'currency',
        'status',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => DateOnly::class,
            'check_out' => DateOnly::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'service_fee' => 'decimal:2',
            'taxes' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * The most recent message, for the inbox preview.
     *
     * A dedicated relation rather than ->messages->last(), so listing twenty
     * threads loads twenty previews in one query instead of twenty.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * The stay is over, so it can be reviewed.
     */
    public function isCompleted(): bool
    {
        return $this->isConfirmed() && $this->check_out->isPast();
    }

    public function isUpcoming(): bool
    {
        return ! $this->isCancelled() && $this->check_in->isFuture();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->check_in->isFuture();
    }

    public function canBeReviewed(): bool
    {
        return $this->isCompleted() && $this->review === null;
    }

    /**
     * Bookings that hold inventory.
     */
    public function scopeHolding(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * The reference as a person should see it: CC-7K2M-9XQ4.
     *
     * Stored compactly without the dashes so a guest who types it either way can
     * still be looked up; the grouping exists only to make it readable and
     * quotable. Falls back to the id only for rows created before references
     * existed.
     */
    public function getDisplayReferenceAttribute(): string
    {
        $raw = $this->reference ?: 'CC'.str_pad((string) $this->id, 8, '0', STR_PAD_LEFT);

        return 'CC-'.implode('-', str_split(substr($raw, 2), 4));
    }

    /**
     * What the guest owes beyond the room rate, as one line.
     */
    public function getExtrasTotalAttribute(): float
    {
        return (float) $this->service_fee + (float) $this->taxes;
    }
}
