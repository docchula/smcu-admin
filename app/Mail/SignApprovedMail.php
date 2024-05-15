<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignApprovedMail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public function __construct(public Document $document) {
    }

    public function envelope(): Envelope {
        return new Envelope(
            subject: 'เอกสารสพจ. '.$this->document->number.'/'.$this->document->year.' ได้รับการลงลายมือชื่อแล้ว / E-Sign Request Approved',
        );
    }

    public function content(): Content {
        return new Content(
            view: 'emails.sign-approved',
        );
    }

    public function attachments(): array {
        return [];
    }
}
