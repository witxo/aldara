<?php

namespace App\Mail;

use App\Domains\Checkin\Models\Checkin;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCheckinNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Checkin $checkin,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo check-in completado - ' . ($this->checkin->reservation->property->name ?? 'Aldara'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-checkin-notification',
        );
    }
}
