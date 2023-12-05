<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailDeliveryFailureMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Document $document)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ที่อยู่อีเมลสำหรับลงลายมือชื่ออิเล็กทรอนิกส์ผิด / E-Sign Email Delivery Failure',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.email-delivery-failure',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
