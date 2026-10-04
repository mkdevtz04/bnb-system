<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'floor',
        'description',
        'address',
        'city',
        'country',
        'amenities',
        'price_per_night',
        'cleaning_fee',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'check_in_from',
        'check_out_until',
        'min_nights',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'price_per_night' => 'decimal:2',
            'cleaning_fee' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Apartment $apartment) {
            if (blank($apartment->slug)) {
                $apartment->slug = static::uniqueSlug($apartment->name, $apartment->getKey());
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'apartment';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    public function blockedDates()
    {
        return $this->hasMany(BlockedDate::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Only properties a guest is allowed to see and book.
     */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeSleeps(Builder $query, ?int $guests): Builder
    {
        return $query->when($guests, fn (Builder $q) => $q->where('max_guests', '>=', $guests));
    }

    /**
     * Guest-facing review score out of 10, or null until the property has one.
     */
    public function getReviewScoreAttribute(): ?float
    {
        $average = $this->reviews_avg_overall ?? $this->reviews()->avg('overall');

        return $average === null ? null : round((float) $average, 1);
    }

    public function getReviewCountAttribute(): int
    {
        return (int) ($this->reviews_count ?? $this->reviews()->count());
    }

    /**
     * The word shown beside the score, the way the big travel sites label it.
     */
    public function getReviewLabelAttribute(): ?string
    {
        return match (true) {
            $this->review_score === null => null,
            $this->review_score >= 9.0 => 'Exceptional',
            $this->review_score >= 8.5 => 'Superb',
            $this->review_score >= 8.0 => 'Very good',
            $this->review_score >= 7.0 => 'Good',
            $this->review_score >= 6.0 => 'Pleasant',
            default => 'Review score',
        };
    }

    public function getLocationLineAttribute(): string
    {
        return collect([$this->city, $this->country])->filter()->implode(', ')
            ?: ucfirst((string) $this->floor).' floor';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Never hand the URL generator a null.
     *
     * A row saved before the slug column existed has no slug, and route() throws
     * UrlGenerationException rather than degrading — one such row took down every
     * page that linked to it. Falling back to the id keeps links working, and
     * resolveRouteBinding() below accepts ids too.
     */
    public function getRouteKey(): mixed
    {
        return $this->slug ?: $this->getKey();
    }

    /**
     * Keep numeric ids working for links created before slugs existed.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null && is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }
}
