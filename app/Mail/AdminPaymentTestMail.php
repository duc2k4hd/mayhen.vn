<?php

namespace App\Mail;

use App\Models\PlatformSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPaymentTestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo mailable kiểm tra mẫu email xác nhận thanh toán thành công (VietQR SePay Demo).
     *
     * @param  string  $recipientEmail  Email người nhận kiểm thử
     * @param  array<string, mixed>  $paymentDetails  Chi tiết thanh toán mẫu (order_code, plan_name, amount, bank_code, bank_name, bank_account, bank_account_name, memo, paid_at, qr_data_uri)
     * @param  string|null  $adminName  Tên quản trị viên thực hiện
     */
    public function __construct(
        public string $recipientEmail,
        public array $paymentDetails = [],
        public ?string $adminName = null
    ) {}

    /**
     * Cấu hình tiêu đề email và địa chỉ người gửi.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $fromAddress = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_address', PlatformSetting::getNoreplyEmail());
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);
        $orderCode = $this->paymentDetails['order_code'] ?? 'MH-TEST-ORD8899';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: "⚜️ [{$brandName}] [Thử Nghiệm Mẫu] Xác Nhận Kích Hoạt Gói Thiệp Cưới & Thanh Toán Thành Công #{$orderCode}",
        );
    }

    /**
     * Nội dung email giao diện Quiet Luxury cao cấp.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-payment-test',
            with: [
                'recipientEmail' => $this->recipientEmail,
                'paymentDetails' => $this->paymentDetails,
                'adminName' => $this->adminName,
            ],
        );
    }
}
