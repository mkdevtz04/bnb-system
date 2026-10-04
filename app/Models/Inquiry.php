<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    /**
     * Without this the model was totally guarded, so Inquiry::create() threw a
     * MassAssignmentException on every contact form submission.
     *
     * reply, replied_at and replied_by are deliberately absent: they record what
     * the host actually sent, and are only ever written by the reply action.
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function repliedBy()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeAwaitingReply(Builder $query): Builder
    {
        return $query->whereNull('replied_at');
    }

    public function hasReply(): bool
    {
        return $this->replied_at !== null;
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true]);
        }
    }

    /**
     * What to put in the subject line of a reply.
     */
    public function replySubject(): string
    {
        $subject = trim((string) $this->subject) ?: 'your enquiry';

        return str_starts_with(mb_strtolower($subject), 're:')
            ? $subject
            : "Re: {$subject}";
    }
}
