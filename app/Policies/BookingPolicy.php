<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, ['user', 'admin'], true);
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin();
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin();
    }

    /**
     * Only the guest who stayed may review, only after checking out, and only
     * once. An admin cannot review on someone's behalf.
     */
    public function review(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id && $booking->canBeReviewed();
    }

    /**
     * Either party to the stay can use its message thread.
     */
    public function message(User $user, Booking $booking): bool
    {
        return $user->id === $booking->user_id || $user->isAdmin();
    }
}
