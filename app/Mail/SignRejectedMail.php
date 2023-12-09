<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Document $document)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'เอกสารสพจ. '.$this->document->number.'/'.$this->document->year.' ถูกปฏิเสธการลงลายมือชื่ออิเล็กทรอนิกส์ / E-Sign Request Rejected',
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.sign-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
