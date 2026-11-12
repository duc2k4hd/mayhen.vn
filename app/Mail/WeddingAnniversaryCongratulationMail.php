<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeddingAnniversaryCongratulationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Wedding $wedding,
        public Invitation $invitation,
        public int $yearsMarried = 1,
        public ?string $milestoneName = 'Đám Cưới Giấy'
    ) {}

    public function envelope(): Envelope
    {
        $coupleNames = "{$this->wedding->groom_name} & {$this->wedding->bride_name}";

        return new Envelope(
            subject: "⚜️ Mừng Kỷ Niệm {$this->yearsMarried} Năm Ngày Cưới — {$coupleNames} ({$this->milestoneName})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.anniversary-congratulation',
            with: [
                'wedding' => $this->wedding,
                'invitation' => $this->invitation,
                'yearsMarried' => $this->yearsMarried,
                'milestoneName' => $this->milestoneName,
                'loungeUrl' => route('anniversary.lounge', $this->invitation->slug),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
