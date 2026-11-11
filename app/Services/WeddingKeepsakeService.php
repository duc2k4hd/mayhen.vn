<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingKeepsake;

class WeddingKeepsakeService
{
    /**
     * Mẫu lời cảm ơn mặc định mang tinh thần Quiet Luxury chân thành và trang nhã.
     */
    public const DEFAULT_THANK_YOU_MESSAGE = "Ngày vui của chúng mình đã trọn vẹn và ngập tràn hạnh phúc nhờ có sự hiện diện, sẻ chia và những lời chúc phúc ấm áp từ Quý vị cùng các bạn.\n\nMỗi nụ cười, mỗi cái ôm và những khoảnh khắc quý giá trong hôn lễ sẽ mãi là kỷ niệm thiêng liêng đồng hành cùng hai đứa trên chặng đường đời phía trước. Chúng mình xin gửi lời cảm ơn chân thành và sâu sắc nhất từ đáy lòng!";

    /**
     * Lấy hoặc khởi tạo cấu hình kỷ niệm cho đám cưới.
     */
    public static function getOrCreateKeepsake(Wedding $wedding, ?Invitation $invitation = null): WeddingKeepsake
    {
        $invitationId = $invitation?->id ?: $wedding->invitations()->value('id');

        return WeddingKeepsake::firstOrCreate(
            ['wedding_id' => $wedding->id],
            [
                'invitation_id' => $invitationId,
                'is_enabled' => false,
                'thank_you_title' => 'Lời Cảm Ơn Chân Thành Từ Chúng Mình',
                'thank_you_message' => self::DEFAULT_THANK_YOU_MESSAGE,
                'video_url' => null,
                'highlight_photos' => [],
                'show_wishes' => true,
                'show_photos' => true,
                'show_video' => true,
                'hide_rsvp' => true,
                'hide_gifts' => false,
                'anniversary_date' => $wedding->wedding_date,
            ]
        );
    }

    /**
     * Cập nhật thông tin và cấu hình trang kỷ niệm.
     */
    public static function updateKeepsake(WeddingKeepsake $keepsake, array $data, ?User $user = null): WeddingKeepsake
    {
        $keepsake->update([
            'thank_you_title' => $data['thank_you_title'] ?? $keepsake->thank_you_title,
            'thank_you_message' => $data['thank_you_message'] ?? $keepsake->thank_you_message,
            'video_url' => array_key_exists('video_url', $data) ? $data['video_url'] : $keepsake->video_url,
            'highlight_photos' => $data['highlight_photos'] ?? $keepsake->highlight_photos,
            'show_wishes' => (bool) ($data['show_wishes'] ?? $keepsake->show_wishes),
            'show_photos' => (bool) ($data['show_photos'] ?? $keepsake->show_photos),
            'show_video' => (bool) ($data['show_video'] ?? $keepsake->show_video),
            'hide_rsvp' => (bool) ($data['hide_rsvp'] ?? $keepsake->hide_rsvp),
            'hide_gifts' => (bool) ($data['hide_gifts'] ?? $keepsake->hide_gifts),
            'anniversary_date' => $data['anniversary_date'] ?? $keepsake->anniversary_date,
        ]);

        AuditLogger::log(
            action: 'KEEPSAKE_UPDATED',
            targetType: 'WeddingKeepsake',
            targetId: $keepsake->id,
            description: "Cập nhật cấu hình kỷ niệm hậu đám cưới #{$keepsake->wedding_id}",
            details: [
                'wedding_id' => $keepsake->wedding_id,
                'is_enabled' => $keepsake->is_enabled,
                'user_id' => $user?->id,
            ]
        );

        return $keepsake;
    }

    /**
     * Bật hoặc tắt Chế độ Thiệp Kỷ Niệm (Memorial Mode).
     */
    public static function toggleKeepsakeMode(WeddingKeepsake $keepsake, bool $enabled, ?User $user = null): bool
    {
        $keepsake->update(['is_enabled' => $enabled]);

        AuditLogger::log(
            action: 'KEEPSAKE_MODE_TOGGLED',
            targetType: 'WeddingKeepsake',
            targetId: $keepsake->id,
            description: ($enabled ? 'Bật' : 'Tắt')." chế độ thiệp kỷ niệm hậu sự kiện đám cưới #{$keepsake->wedding_id}",
            details: [
                'wedding_id' => $keepsake->wedding_id,
                'is_enabled' => $enabled,
                'user_id' => $user?->id,
            ]
        );

        return $enabled;
    }

    /**
     * Sinh mẫu tin nhắn cảm ơn sau đám cưới cá nhân hóa cho từng khách mời.
     */
    public static function generateThankYouMessage(Guest $guest, Wedding $wedding, ?WeddingKeepsake $keepsake = null): string
    {
        $salutation = trim((string) $guest->salutation) ?: 'Quý khách';
        $couple = "{$wedding->groom_name} & {$wedding->bride_name}";

        $invitation = $wedding->invitations()->first();
        $slug = $invitation?->slug ?: ('dam-cuoi-'.$wedding->id);
        $link = url("/invitation/{$slug}?guest={$guest->guest_token}");

        return "Kính gửi {$salutation} {$guest->name}!\n\n{$couple} xin gửi lời cảm ơn chân thành và sâu sắc nhất tới {$salutation} đã dành thời gian quý báu tới chung vui và chúc phúc cho ngày trọng đại của hai đứa chúng mình.\n\nSự hiện diện ấm áp của {$salutation} là món quà vô giá mà gia đình chúng mình luôn trân quý.\n\nKính mời {$salutation} xem lại những hình ảnh kỷ niệm đẹp và lưu giữ khoảnh khắc ngày chung đôi tại:\n{$link}\n\nKính chúc {$salutation} cùng gia đình luôn dồi dào sức khỏe, an khang và vạn sự như ý!";
    }

    /**
     * Thu thập toàn bộ dữ liệu phục vụ xuất Sổ Lưu Niệm Ngày Cưới (Memorial Keepsake Book).
     */
    public static function getMemorialBookData(Wedding $wedding, Invitation $invitation, WeddingKeepsake $keepsake): array
    {
        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->latest('id')
            ->get();

        $wishesCount = $wishes->count();
        $attendedGuestsCount = $wedding->guests()->whereNotNull('checked_in_at')->count();
        if ($attendedGuestsCount === 0) {
            $attendedGuestsCount = $wedding->guestResponses()->where('attending_status', 'attending')->count();
        }

        $keepsake->update(['last_exported_at' => now()]);

        return [
            'wedding' => $wedding,
            'invitation' => $invitation,
            'keepsake' => $keepsake,
            'wishes' => $wishes,
            'wishes_count' => $wishesCount,
            'attended_guests_count' => $attendedGuestsCount,
            'generated_at' => now(),
        ];
    }
}
