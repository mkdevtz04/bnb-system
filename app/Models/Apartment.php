<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'floor',
        'description',
        'price_per_night',
        'max_guests',
        'bedrooms',
        'bathrooms',
        'status',
    ];

    /**
     * Get all bookings for this apartment
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get all images for this apartment
     */
    public function images()
    {
        return $this->hasMany(ApartmentImage::class);
    }

    /**
     * Get all blocked dates for this apartment
     */
    public function blockedDates()
    {
        return $this->hasMany(BlockedDate::class);
    }

    /**
     * Check if apartment is available for given dates
     */
    public function isAvailable($checkIn, $checkOut)
    {
        $existingBooking = $this->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })->exists();

        return !$existingBooking;
    }
}
