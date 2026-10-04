<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The host's answer to a contact-form message.
 *
 * Deliberately NOT queued, unlike the booking notifications.
 *
 * Those are side effects of something else — a booking must still succeed if the
 * mail server is slow, so they go to the queue. This is the opposite: the host
 * typed these words and is waiting to hear they were delivered. Queuing it would
 * report "sent" the instant it was written to a table, and if no worker were
 * running the reply would simply never arrive while the screen insisted it had.
 * Sending it inline costs a second or two and makes a failure visible to the one
 * person who can do something about it.
 */
class InquiryReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Inquiry $inquiry,
        public readonly string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->inquiry->replySubject(),
            // The person who wrote in must be able to answer the answer.
            replyTo: [new Address(
                config('mail.reply_to.address'),
                config('mail.reply_to.name'),
            )],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-reply',
            with: [
                'inquiry' => $this->inquiry,
                'body' => $this->body,
            ],
        );
    }
}
