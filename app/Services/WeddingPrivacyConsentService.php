<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use Carbon\Carbon;

class WeddingPrivacyConsentService
{
    /**
     * Cấu hình mặc định bảo vệ dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP & Luật 91/2025/QH15.
     *
     * @return array<string, mixed>
     */
    public function getDefaultSettings(): array
    {
        return [
            'bank_visibility' => 'everyone', // 'everyone', 'named_guests_only', 'hidden'
            'wishes_visibility' => 'public', // 'public', 'couple_only'
            'photo_drop_moderation' => 'require_approval', // 'require_approval', 'auto_approve'
            'phone_retention_days' => 90, // 30, 90, 180, 0 (không tự xóa)
            'anonymous_rsvp_allowed' => false,
            'guest_location_tracking' => false, // CẤM TUYỆT ĐỐI ngầm thu thập vị trí khách
            'last_updated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Lấy cài đặt quyền riêng tư hiện hành của thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public function getPrivacySettings(Invitation $invitation): array
    {
        $config = $invitation->custom_config ?? [];
        $saved = $config['privacy_settings'] ?? [];

        return array_merge($this->getDefaultSettings(), $saved);
    }

    /**
     * Cập nhật cài đặt quyền riêng tư của thiệp cưới.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updatePrivacySettings(Invitation $invitation, array $payload): array
    {
        $config = $invitation->custom_config ?? [];
        $current = $this->getPrivacySettings($invitation);

        $allowedBank = ['everyone', 'named_guests_only', 'hidden'];
        $allowedWishes = ['public', 'couple_only'];
        $allowedModeration = ['require_approval', 'auto_approve'];
        $allowedRetention = [0, 30, 90, 180];

        $newSettings = [
            'bank_visibility' => in_array($payload['bank_visibility'] ?? '', $allowedBank, true)
                ? $payload['bank_visibility']
                : $current['bank_visibility'],
            'wishes_visibility' => in_array($payload['wishes_visibility'] ?? '', $allowedWishes, true)
                ? $payload['wishes_visibility']
                : $current['wishes_visibility'],
            'photo_drop_moderation' => in_array($payload['photo_drop_moderation'] ?? '', $allowedModeration, true)
                ? $payload['photo_drop_moderation']
                : $current['photo_drop_moderation'],
            'phone_retention_days' => array_key_exists('phone_retention_days', $payload) && in_array((int) $payload['phone_retention_days'], $allowedRetention, true)
                ? (int) $payload['phone_retention_days']
                : (int) ($current['phone_retention_days'] ?? 90),
            'anonymous_rsvp_allowed' => array_key_exists('anonymous_rsvp_allowed', $payload)
                ? (bool) $payload['anonymous_rsvp_allowed']
                : (bool) ($current['anonymous_rsvp_allowed'] ?? false),
            'guest_location_tracking' => false, // Luôn cưỡng chế false để bảo vệ người dùng
            'last_updated_at' => Carbon::now()->toIso8601String(),
        ];

        $config['privacy_settings'] = $newSettings;
        $invitation->custom_config = $config;
        $invitation->save();

        return $newSettings;
    }

    /**
     * Lọc và che giấu dữ liệu nhạy cảm của thiệp cưới dựa trên vai trò người xem (Public Guest vs Named Guest).
     *
     * @param  array<string, mixed>  $weddingData
     * @return array<string, mixed> Dữ liệu đã áp dụng quy tắc quyền riêng tư
     */
    public function filterSensitiveWeddingData(Invitation $invitation, array $weddingData, bool $isNamedGuest = false): array
    {
        $settings = $this->getPrivacySettings($invitation);
        $bankMode = $settings['bank_visibility'];

        // Nếu chế độ là 'hidden', ẩn toàn bộ thông tin ngân hàng
        if ($bankMode === 'hidden') {
            unset(
                $weddingData['groom_bank_account'],
                $weddingData['groom_bank_name'],
                $weddingData['bride_bank_account'],
                $weddingData['bride_bank_name']
            );
            $weddingData['bank_masked'] = true;
            $weddingData['bank_mask_reason'] = 'Cặp đôi đã tắt hiển thị mừng cưới trực tuyến.';
        } elseif ($bankMode === 'named_guests_only' && ! $isNamedGuest) {
            // Khách vãng lai không có token định danh thì chỉ hiện mã che (mask)
            if (! empty($weddingData['groom_bank_account'])) {
                $weddingData['groom_bank_account'] = '•••• •••• '.substr((string) $weddingData['groom_bank_account'], -4);
            }
            if (! empty($weddingData['bride_bank_account'])) {
                $weddingData['bride_bank_account'] = '•••• •••• '.substr((string) $weddingData['bride_bank_account'], -4);
            }
            $weddingData['bank_masked'] = true;
            $weddingData['bank_mask_reason'] = 'Số tài khoản chỉ hiển thị đầy đủ cho khách mời có thiệp gửi riêng.';
        } else {
            $weddingData['bank_masked'] = false;
        }

        return $weddingData;
    }

    /**
     * Báo cáo đánh giá mức độ bảo vệ dữ liệu cá nhân (Privacy Compliance & Safety Audit).
     *
     * @return array{
     *     privacy_score: int,
     *     status: string,
     *     status_label: string,
     *     checks: array<int, array{key: string, label: string, passed: bool, description: string, recommendation: string}>,
     *     statistics: array{
     *         total_guests: int,
     *         guests_with_phone: int,
     *         total_responses: int,
     *         public_wishes_count: int
     *     }
     * }
     */
    public function generatePrivacyAuditReport(Invitation $invitation): array
    {
        $settings = $this->getPrivacySettings($invitation);
        $wedding = $invitation->wedding;

        $allGuests = $wedding ? $wedding->guests()->get() : collect();
        $totalGuests = $allGuests->count();
        $guestsWithPhone = $allGuests->filter(fn (Guest $g) => ! empty($g->phone))->count();

        $allResponses = $wedding ? GuestResponse::where('wedding_id', $wedding->id)->get() : collect();
        $totalResponses = $allResponses->count();
        $publicWishes = $allResponses->where('is_wishes_public', true)->filter(fn ($r) => ! empty($r->wishes))->count();

        $checks = [];
        $score = 100;

        // Kiểm tra 1: Bảo vệ số tài khoản ngân hàng
        $bankProtected = $settings['bank_visibility'] !== 'everyone';
        $checks[] = [
            'key' => 'bank_privacy',
            'label' => 'Bảo vệ số tài khoản mừng cưới',
            'passed' => $bankProtected,
            'description' => $bankProtected
                ? 'Số tài khoản đã được bảo vệ, tránh bị người lạ lợi dụng thông tin ngân hàng.'
                : 'Số tài khoản đang hiển thị công khai cho tất cả mọi người truy cập internet.',
            'recommendation' => 'Nên chọn chế độ "Chỉ khách có thiệp định danh" để tăng tính bảo mật.',
        ];
        if (! $bankProtected) {
            $score -= 15;
        }

        // Kiểm tra 2: Kiểm duyệt ảnh khách tải lên (Photo Drop)
        $photoModerated = $settings['photo_drop_moderation'] === 'require_approval';
        $checks[] = [
            'key' => 'photo_moderation',
            'label' => 'Kiểm duyệt ảnh kỷ niệm khách chụp',
            'passed' => $photoModerated,
            'description' => $photoModerated
                ? 'Ảnh khách chụp phải được cặp đôi phê duyệt trước khi chiếu công khai.'
                : 'Ảnh khách chụp sẽ hiển thị trực tiếp mà không cần duyệt trước.',
            'recommendation' => 'Nên giữ chế độ kiểm duyệt để ngăn ảnh nhạy cảm hoặc không phù hợp xuất hiện trên màn hình tiệc cưới.',
        ];
        if (! $photoModerated) {
            $score -= 15;
        }

        // Kiểm tra 3: Chính sách lưu trữ và hủy số điện thoại sau cưới
        $retentionConfigured = $settings['phone_retention_days'] > 0;
        $checks[] = [
            'key' => 'data_retention',
            'label' => 'Chính sách tự động lưu trữ & xóa SĐT sau cưới',
            'passed' => $retentionConfigured,
            'description' => $retentionConfigured
                ? "Dữ liệu SĐT khách mời sẽ tự động được ẩn/xóa sau {$settings['phone_retention_days']} ngày kể từ ngày cưới theo chuẩn Nghị định 13."
                : 'Dữ liệu số điện thoại khách mời đang được lưu trữ vô thời hạn.',
            'recommendation' => 'Nên bật thời hạn 90 ngày sau ngày cưới để tuân thủ quyền được lãng quên (Right to be forgotten).',
        ];
        if (! $retentionConfigured) {
            $score -= 10;
        }

        // Kiểm tra 4: Không ngầm theo dõi định vị GPS của khách
        $checks[] = [
            'key' => 'zero_location_tracking',
            'label' => 'Cam kết không thu thập định vị vị trí cá nhân',
            'passed' => true,
            'description' => 'Hệ thống Mây Hẹn tuyệt đối không cài cắm mã theo dõi vị trí GPS ngầm của khách mời.',
            'recommendation' => 'Tiêu chuẩn bảo mật tối đa được tuân thủ nghiêm ngặt 100%.',
        ];

        // Kiểm tra 5: Quyền bảo mật sổ lời chúc
        $wishesConfigured = true;
        $checks[] = [
            'key' => 'wishes_privacy',
            'label' => 'Kiểm soát quyền riêng tư sổ chúc phúc',
            'passed' => true,
            'description' => $settings['wishes_visibility'] === 'public'
                ? 'Lời chúc hiển thị công khai trên thiệp cưới để chia sẻ niềm vui.'
                : 'Chế độ riêng tư: Chỉ cô dâu chú rể mới đọc được những lời tâm sự.',
            'recommendation' => 'Tùy theo sở thích của cặp đôi và tính chất trang trọng của đám cưới.',
        ];

        $status = match (true) {
            $score >= 85 => 'excellent',
            $score >= 70 => 'good',
            default => 'warning'
        };

        $statusLabel = match ($status) {
            'excellent' => 'Bảo vệ dữ liệu cá nhân Xuất Sắc',
            'good' => 'Bảo mật tốt, có thể tối ưu thêm',
            default => 'Cần nâng cấp để bảo vệ thông tin'
        };

        return [
            'privacy_score' => $score,
            'status' => $status,
            'status_label' => $statusLabel,
            'checks' => $checks,
            'statistics' => [
                'total_guests' => $totalGuests,
                'guests_with_phone' => $guestsWithPhone,
                'total_responses' => $totalResponses,
                'public_wishes_count' => $publicWishes,
            ],
        ];
    }
}
