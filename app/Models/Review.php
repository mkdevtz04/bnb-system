<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    /**
     * The six things a guest scores out of 10. The overall figure is their mean,
     * not a seventh thing the guest types in, so it cannot be gamed.
     */
    public const CATEGORIES = [
        'cleanliness' => 'Cleanliness',
        'comfort' => 'Comfort',
        'location' => 'Location',
        'facilities' => 'Facilities',
        'staff' => 'Staff',
        'value' => 'Value for money',
    ];

    protected $fillable = [
        'booking_id',
        'user_id',
        'apartment_id',
        'cleanliness',
        'comfort',
        'location',
        'facilities',
        'staff',
        'value',
        'overall',
        'title',
        'liked',
        'disliked',
    ];

    protected function casts(): array
    {
        return [
            'overall' => 'decimal:1',
        ];
    }

    /**
     * Keep the stored overall in step with the category scores.
     *
     * This is done here rather than in a saving() event because events can be
     * muted — WithoutModelEvents in a seeder, or Model::withoutEvents() — and the
     * column is NOT NULL, so a muted event turns into a database error or, worse,
     * a review whose headline score disagrees with the numbers beneath it.
     * Overriding save() means the invariant holds however the row is written.
     */
    public function save(array $options = []): bool
    {
        $this->overall = $this->calculateOverall();

        return parent::save($options);
    }

    public function calculateOverall(): float
    {
        $scores = collect(array_keys(self::CATEGORIES))
            ->map(fn (string $category) => (int) $this->{$category});

        return round($scores->average(), 1);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getScoresAttribute(): array
    {
        return collect(self::CATEGORIES)
            ->map(fn (string $label, string $key) => ['label' => $label, 'score' => (int) $this->{$key}])
            ->values()
            ->all();
    }
}
