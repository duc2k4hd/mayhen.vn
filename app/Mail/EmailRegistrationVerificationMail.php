<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailRegistrationVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 180];

    public function __construct(
        public string $email,
        public string $verificationUrl,
        public int $expireHours = 24
    ) {}

    /**
     * Gửi từ Mail Không Nhận Lại (No-Reply) đã cấu hình trong Admin Platform Settings.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $noreplyEmail = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        return new Envelope(
            from: new Address($noreplyEmail, $fromName),
            subject: "⚜️ [{$brandName}] Xác Thực Email Để Hoàn Tất Đăng Ký Tài Khoản",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-registration-verification',
            with: [
                'email' => $this->email,
                'verificationUrl' => $this->verificationUrl,
                'expireHours' => $this->expireHours,
                'noreplyEmail' => PlatformSetting::getNoreplyEmail(),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
