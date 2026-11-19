<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminSmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo mailable kiểm tra kết nối máy chủ gửi thư SMTP của Admin.
     *
     * @param  string  $recipientEmail  Địa chỉ email người nhận kiểm thử
     * @param  array<string, mixed>  $smtpDetails  Chi tiết cấu hình SMTP (host, port, encryption, from_address, from_name, sent_at, client_ip)
     * @param  string|null  $adminName  Tên quản trị viên thực hiện kiểm thử
     */
    public function __construct(
        public string $recipientEmail,
        public array $smtpDetails = [],
        public ?string $adminName = null
    ) {}

    /**
     * Tiêu đề email và người gửi đại diện.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $fromAddress = $this->smtpDetails['from_address']
            ?? (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_address', PlatformSetting::getNoreplyEmail());
        $fromName = $this->smtpDetails['from_name']
            ?? (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "⚜️ [{$brandName}] Kiểm Tra Cấu Hình Máy Chủ Thư Điện Tử (SMTP) Thành Công",
        );
    }

    /**
     * Nội dung email giao diện Quiet Luxury cao cấp.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-smtp-test',
            with: [
                'recipientEmail' => $this->recipientEmail,
                'smtpDetails' => $this->smtpDetails,
                'adminName' => $this->adminName,
            ],
        );
    }
}
