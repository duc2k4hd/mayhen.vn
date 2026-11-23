<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewUserRegisteredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    /**
     * Thời gian giãn cách giữa các lần thử lại (giây): 3s -> 15s -> 60s.
     *
     * @var array<int, int>
     */
    public array $backoff = [3, 15, 60];

    public function __construct(
        public User $user,
        public string $registrationMethod = 'email',
        public ?string $ipAddress = null
    ) {}

    /**
     * Gửi từ Mail Không Nhận Lại (No-Reply) về hòm thư Admin đã cấu hình trong Admin Platform Settings.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $noreplyEmail = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        $userName = trim((string) ($this->user->name ?? 'Người dùng mới'));
        $userEmail = (string) ($this->user->email ?? '');

        return new Envelope(
            from: new Address($noreplyEmail, $fromName),
            subject: "🔔 [{$brandName}] Tài Khoản Mới Đăng Ký Thành Công: {$userName} ({$userEmail})",
        );
    }

    public function content(): Content
    {
        $methodLabel = match ($this->registrationMethod) {
            'google' => 'Google OAuth 2.0',
            default => 'Email xác thực',
        };

        return new Content(
            view: 'emails.admin-new-user-registered',
            with: [
                'user' => $this->user,
                'registrationMethod' => $this->registrationMethod,
                'registrationMethodLabel' => $methodLabel,
                'ipAddress' => $this->ipAddress ?: 'Không xác định',
                'registeredAt' => ($this->user->created_at ?? now())->format('H:i:s d/m/Y'),
                'noreplyEmail' => PlatformSetting::getNoreplyEmail(),
                'adminEmail' => PlatformSetting::getAdminEmail(),
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
