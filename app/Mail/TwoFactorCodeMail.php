<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $nombre;

    public function __construct(string $code, string $nombre)
    {
        $this->code = $code;
        $this->nombre = $nombre;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de verificación - ITSSMT',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two_factor_code',
        );
    }
}
