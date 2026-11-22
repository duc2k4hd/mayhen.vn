<?php

namespace App\Mail;

use App\Models\NewsPost;
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

/**
 * Thư tóm tắt bản tin & cập nhật mới của dự án Mây Hẹn gửi tới thành viên đã đăng ký.
 * Tự động đẩy vào hàng đợi (ShouldQueue) để gửi bất đồng bộ, an toàn cho hệ thống.
 */
class ProjectNewsSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Số lần thử lại tối đa nếu dịch vụ SMTP tạm thời gián đoạn.
     */
    public int $tries = 3;

    /**
     * Thời gian chờ tối đa cho mỗi lần gửi (giây).
     */
    public int $timeout = 20;

    /**
     * Khoảng nghỉ giữa các lần thử lại (giây).
     *
     * @var array<int, int>
     */
    public array $backoff = [3, 15, 60];

    public function __construct(
        public NewsPost $news,
        public User $user
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $fromAddress = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(
            PlatformSetting::GROUP_MAIL,
            'mail_from_name',
            $brandName
        );

        $categoryPrefix = ! empty($this->news->category_label)
            ? '['.$this->news->category_label.'] '
            : '['.$brandName.'] ';

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            replyTo: [new Address($fromAddress, $fromName)],
            subject: $categoryPrefix.$this->news->title.' — '.$brandName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.project-news-summary',
            with: [
                'news' => $this->news,
                'user' => $this->user,
                'newsUrl' => route('news.show', $this->news->slug),
                'allNewsUrl' => route('news.index'),
                'noreplyEmail' => PlatformSetting::getNoreplyEmail(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
