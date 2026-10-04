<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar_url',
    ];

    /**
     * Note what is absent above: firebase_uid.
     *
     * It is the identity a Google sign-in is matched on, so leaving it out of
     * $fillable means no request payload can ever set it. It is written only by
     * App\Services\Firebase\FirebaseUserResolver, via forceFill, after a token has
     * been verified. If it were fillable, a crafted field on any form that updates
     * a user could rebind an account to someone else's Google identity.
     */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Messages addressed to this person, which is what the unread badge counts.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Initials for the avatar chip in the nav.
     */
    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('') ?: '?';
    }

    /**
     * The name to greet them by, given OTP sign-up generates a placeholder.
     */
    public function getDisplayNameAttribute(): string
    {
        if (str_starts_with($this->name, 'Guest User - ') || blank($this->name)) {
            return Str::before($this->email, '@');
        }

        return $this->name;
    }
}
