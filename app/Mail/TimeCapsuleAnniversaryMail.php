<?php

namespace App\Mail;

use App\Models\WeddingTimeCapsule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TimeCapsuleAnniversaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WeddingTimeCapsule $capsule) {}

    public function envelope(): Envelope
    {
        $coupleNames = $this->capsule->wedding
            ? "{$this->capsule->wedding->groom_name} & {$this->capsule->wedding->bride_name}"
            : 'Đôi Uyên Ương';

        return new Envelope(
            subject: "⚜️ Kỷ Niệm Ngày Cưới: Đã Đến Ngày Mở Niêm Phong Hộp Thời Gian ({$coupleNames})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.time-capsule-anniversary',
            with: [
                'capsule' => $this->capsule,
                'wedding' => $this->capsule->wedding,
                'invitation' => $this->capsule->invitation,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
