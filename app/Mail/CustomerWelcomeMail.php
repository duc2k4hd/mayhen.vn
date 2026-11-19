<?php

namespace App\Mail;

use App\Models\InvitationTemplate;
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

class CustomerWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 20;

    /**
     * Thời gian giãn cách giữa các lần thử lại (giây): 3s -> 15s -> 60s để khách nhận mail nhanh nhất.
     *
     * @var array<int, int>
     */
    public array $backoff = [3, 15, 60];

    /**
     * @var array<int, array{id: int, name: string, slug: string, category_label: string, tagline: string, thumbnail_url: string, preview_url: string}>
     */
    public array $featuredTemplates = [];

    public function __construct(
        public User $user
    ) {
        $this->featuredTemplates = $this->resolveFeaturedTemplates();
    }

    /**
     * Gửi từ Mail Không Nhận Lại (No-Reply) hoặc Mail Hệ Thống đã cấu hình từ Admin Platform Settings.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $noreplyEmail = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        $customerName = trim((string) ($this->user->name ?? ''));
        $greetingTarget = $customerName !== '' ? $customerName : 'Bạn';

        return new Envelope(
            from: new Address($noreplyEmail, $fromName),
            subject: "🌿 [{$brandName}] Chào Mừng {$greetingTarget} Đến Với Không Gian Thiệp Cưới Quiet Luxury",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-welcome',
            with: [
                'user' => $this->user,
                'featuredTemplates' => $this->featuredTemplates,
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

    /**
     * Trích xuất 3 mẫu thiệp cưới tiêu biểu nổi bật cho nhiều phong cách khác nhau (Đa dạng hóa trải nghiệm khách hàng).
     *
     * @return array<int, array{id: int, name: string, slug: string, category_label: string, tagline: string, thumbnail_url: string, preview_url: string}>
     */
    protected function resolveFeaturedTemplates(): array
    {
        $curatedPresets = [
            'vom-troi-may-ao-cuoi' => [
                'category_label' => 'Quiet Luxury & Lãng Mạn',
                'tagline' => 'Tông kem ngà tinh khiết, cánh mây bay bổng và nghệ thuật bố cục thanh lịch.',
            ],
            'hac-bach-trao-duyen' => [
                'category_label' => 'Cổ Điển & Sang Trọng',
                'tagline' => 'Tương phản đen trắng thanh tao, typography cổ điển trường tồn cùng năm tháng.',
            ],
            'giua-muon-ngan-vi-sao' => [
                'category_label' => 'Hiện Đại & Tỏa Sáng',
                'tagline' => 'Lung linh, trẻ trung và tràn đầy năng lượng tình yêu cho tiệc cưới đương đại.',
            ],
        ];

        $templates = InvitationTemplate::query()
            ->where('is_active', true)
            ->whereIn('slug', array_keys($curatedPresets))
            ->get();

        if ($templates->isEmpty()) {
            $templates = InvitationTemplate::query()
                ->where('is_active', true)
                ->limit(3)
                ->get();
        }

        $result = [];
        foreach ($templates as $tpl) {
            $meta = $curatedPresets[$tpl->slug] ?? [
                'category_label' => 'Thiết Kế Độc Bản',
                'tagline' => 'Phong cách tối giản tinh tế, tôn vinh câu chuyện tình yêu của hai bạn.',
            ];

            $thumb = $tpl->thumbnail_url ?: '/assets/common/images/placeholder-wedding.jpg';
            if (! str_starts_with($thumb, 'http://') && ! str_starts_with($thumb, 'https://')) {
                $thumb = url($thumb);
            }

            $result[] = [
                'id' => $tpl->id,
                'name' => $tpl->name,
                'slug' => $tpl->slug,
                'category_label' => $meta['category_label'],
                'tagline' => $meta['tagline'],
                'thumbnail_url' => $thumb,
                'preview_url' => route('invitation.preview', $tpl->slug),
            ];
        }

        return $result;
    }
}
