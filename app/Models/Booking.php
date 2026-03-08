<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'apartment_id',
        'check_in',
        'check_out',
        'nights',
        'total_price',
        'status',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    /**
     * Get the user who made this booking
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the apartment being booked
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Check if booking is pending confirmation
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if booking is confirmed
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if booking is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get messages for this booking
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
