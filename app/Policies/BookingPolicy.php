<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can create a booking
     */
    public function create(User $user): bool
    {
        return $user->role === 'user';
    }

    /**
     * Determine if the user can view this booking
     */
    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete this booking
     */
    public function delete(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin();
    }
}
