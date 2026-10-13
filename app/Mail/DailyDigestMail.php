<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Your day at a glance — :date', ['date' => now()->format('Y-m-d')]));
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.digest');
    }
}
