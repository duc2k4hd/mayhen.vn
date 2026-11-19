<?php

namespace App\Mail;

use App\Models\Invitation;
use App\Models\PlatformSetting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeddingInvitationCreatedMail extends Mailable implements ShouldQueue
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

    public function __construct(
        public Invitation $invitation
    ) {
        $this->invitation->loadMissing(['wedding.user', 'template']);
    }

    /**
     * Gửi từ Mail Không Nhận Lại (No-Reply) hoặc Mail Hệ Thống đã cấu hình từ Admin Platform Settings.
     */
    public function envelope(): Envelope
    {
        $brandName = PlatformSetting::getBrandName();
        $noreplyEmail = PlatformSetting::getNoreplyEmail();
        $fromName = (string) PlatformSetting::getValue(PlatformSetting::GROUP_MAIL, 'mail_from_name', $brandName);

        $wedding = $this->invitation->wedding;
        $coupleText = '';
        if ($wedding && (! empty($wedding->groom_name) || ! empty($wedding->bride_name))) {
            $groom = trim((string) $wedding->groom_name);
            $bride = trim((string) $wedding->bride_name);
            $coupleText = $groom !== '' && $bride !== '' ? "{$groom} & {$bride}" : ($groom ?: $bride);
        }

        $subjectTitle = $coupleText !== ''
            ? "💍 [{$brandName}] Thiệp Cưới Của {$coupleText} Đã Được Tạo Thành Công!"
            : "💍 [{$brandName}] Tác Phẩm Thiệp Cưới Của Bạn Đã Sẵn Sàng Chia Sẻ!";

        return new Envelope(
            from: new Address($noreplyEmail, $fromName),
            subject: $subjectTitle,
        );
    }

    public function content(): Content
    {
        $wedding = $this->invitation->wedding;
        $template = $this->invitation->template;

        $formattedWeddingDate = null;
        if ($wedding?->wedding_date) {
            try {
                $date = Carbon::parse($wedding->wedding_date);
                $dayOfWeek = match ($date->dayOfWeek) {
                    0 => 'Chủ Nhật',
                    1 => 'Thứ Hai',
                    2 => 'Thứ Ba',
                    3 => 'Thứ Tư',
                    4 => 'Thứ Năm',
                    5 => 'Thứ Sáu',
                    6 => 'Thứ Bảy',
                    default => 'Ngày',
                };
                $formattedWeddingDate = "{$dayOfWeek}, ngày {$date->format('d/m/Y')}";
            } catch (\Throwable) {
                $formattedWeddingDate = (string) $wedding->wedding_date;
            }
        }

        return new Content(
            view: 'emails.wedding-invitation-created',
            with: [
                'invitation' => $this->invitation,
                'wedding' => $wedding,
                'template' => $template,
                'formattedWeddingDate' => $formattedWeddingDate,
                'publicUrl' => route('invitation.show', $this->invitation->slug),
                'editorUrl' => route('invitation.create', ['edit' => $this->invitation->slug]),
                'guestsUrl' => route('guests.index', $this->invitation->id),
                'accountUrl' => route('account.dashboard'),
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
