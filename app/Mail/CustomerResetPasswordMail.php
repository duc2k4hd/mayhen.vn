<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Số lần thử lại tối đa nếu gặp sự cố kết nối SMTP.
     */
    public int $tries = 3;

    /**
     * Thời gian timeout tối đa cho mỗi job gửi mail (giây).
     */
    public int $timeout = 30;

    /**
     * Độ trễ tăng dần giữa các lần thử lại (giây).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 180];

    /**
     * Khởi tạo mailable gửi liên kết đặt lại mật khẩu cho khách hàng.
     *
     * @param  User  $user  Thông tin người dùng
     * @param  string  $resetUrl  Đường dẫn đặt lại mật khẩu an toàn
     * @param  int  $expireHours  Thời hạn hiệu lực của liên kết (mặc định 48 giờ)
     */
    public function __construct(
        public User $user,
        public string $resetUrl,
        public int $expireHours = 48
    ) {}

    /**
     * Cấu hình tiêu đề email và địa chỉ người gửi no-reply động lấy từ Admin Platform Settings.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $noreplyEmail = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        return new Envelope(
            from: new Address($noreplyEmail, $fromName),
            subject: "⚜️ [{$brandName}] Hướng Dẫn Đặt Lại Mật Khẩu Tài Khoản Của Bạn",
        );
    }

    /**
     * Cấu hình nội dung email giao diện Quiet Luxury.
     */
    public function content(): Content
    {
        $noreplyEmail = PlatformSetting::getNoreplyEmail();

        return new Content(
            view: 'emails.customer-reset-password',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'expireHours' => $this->expireHours,
                'noreplyEmail' => $noreplyEmail,
            ],
        );
    }

    /**
     * Danh sách file đính kèm.
     */
    public function attachments(): array
    {
        return [];
    }
}
