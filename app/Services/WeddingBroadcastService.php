<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Carbon;

class WeddingBroadcastService
{
    /**
     * Mức độ khẩn cấp của thông báo.
     */
    public const URGENCY_CRITICAL = 'critical';

    public const URGENCY_WARNING = 'warning';

    public const URGENCY_INFO = 'info';

    /**
     * Kiểu hiển thị trên thiệp cưới.
     */
    public const DISPLAY_STICKY = 'sticky_banner';

    public const DISPLAY_MODAL = 'modal_popup';

    public const DISPLAY_BOTH = 'both';

    /**
     * Danh sách 5 mẫu thông báo khẩn cấp điển hình trong đám cưới Việt Nam.
     *
     * @return array<string, array{name: string, urgency: string, title: string, message: string, action_text: string, action_type: string}>
     */
    public static function getPresetTemplates(): array
    {
        return [
            'venue_change' => [
                'name' => 'Thay đổi sảnh tiệc / Di chuyển vào trong nhà',
                'urgency' => self::URGENCY_CRITICAL,
                'title' => 'Cập Nhật Khẩn: Thay Đổi Vị Trí Sảnh Tiệc Cưới',
                'message' => 'Do điều kiện thời tiết, lễ cưới và tiệc chiêu đãi được chuyển từ Khu vườn Ngoài trời vào Sảnh Grand Ballroom (Tầng 2). Kính mong quý quan khách lưu ý hướng dẫn của ban lễ tân khi đến nơi.',
                'action_text' => 'Xem Sơ Đồ Lối Vào',
                'action_type' => 'url',
            ],
            'schedule_delay' => [
                'name' => 'Lùi giờ đón khách & khai tiệc (Thời tiết / Kẹt xe)',
                'urgency' => self::URGENCY_WARNING,
                'title' => 'Thông Báo: Lùi Giờ Khai Tiệc 30 Phút',
                'message' => 'Do tình hình giao thông trên tuyến đường chính, ban tổ chức xin phép lùi giờ khai tiệc thêm 30 phút để quý khách mời có thêm thời gian di chuyển thong thả và an toàn.',
                'action_text' => 'Gọi Lễ Tân Hỗ Trợ',
                'action_type' => 'tel',
            ],
            'parking_relocation' => [
                'name' => 'Hướng dẫn bãi đỗ xe mới & xe trung chuyển',
                'urgency' => self::URGENCY_INFO,
                'title' => 'Hướng Dẫn Vị Trí Đỗ Xe Ô Tô & Xe Máy',
                'message' => 'Bãi đỗ xe trung tâm đã đầy. Quý khách vui lòng di chuyển xe ô tô sang Bãi xe Tòa nhà Đối diện (Cách 50m, có xe điện đưa đón miễn phí sang sảnh tiệc).',
                'action_text' => 'Chỉ Đường Bãi Đỗ Xe',
                'action_type' => 'url',
            ],
            'weather_advisory' => [
                'name' => 'Lưu ý thời tiết & trang phục giữ ấm / ô che',
                'urgency' => self::URGENCY_INFO,
                'title' => 'Lưu Ý Thời Tiết Tiệc Cưới Ngoài Trời',
                'message' => 'Hôm nay trời có gió nhẹ và se lạnh về chiều tối. Ban tổ chức đã chuẩn bị sẵn ô che và khăn choàng ấm tại bàn lễ tân để phục vụ quý khách.',
                'action_text' => '',
                'action_type' => 'none',
            ],
            'hotline_assistance' => [
                'name' => 'Hotline đón tiếp & hỗ trợ khẩn cấp',
                'urgency' => self::URGENCY_WARNING,
                'title' => 'Đường Dây Nóng Hỗ Trợ Đón Tiếp Ngày Cưới',
                'message' => 'Nếu quý khách cần hướng dẫn đường đi hoặc có người lớn tuổi cần hỗ trợ xe lăn lên sảnh tiệc, vui lòng liên hệ ngay với đội ngũ điều phối của gia đình.',
                'action_text' => 'Gọi Điều Phối Viên',
                'action_type' => 'tel',
            ],
        ];
    }

    /**
     * Phân giải cấu hình thông báo khẩn cấp đầy đủ và an toàn cho thiệp cưới.
     *
     * @return array{
     *     is_active: bool,
     *     template_key: string,
     *     urgency_level: string,
     *     display_type: string,
     *     title: string,
     *     message: string,
     *     action_text: string,
     *     action_url: string,
     *     updated_at: ?string,
     *     formatted_time: string
     * }
     */
    public static function resolveBroadcastConfig(?Invitation $invitation = null, ?array $customConfig = null): array
    {
        $broadcast = [];

        if ($invitation && is_array($invitation->custom_config) && isset($invitation->custom_config['broadcast'])) {
            $broadcast = $invitation->custom_config['broadcast'];
        } elseif (is_array($customConfig) && isset($customConfig['broadcast'])) {
            $broadcast = $customConfig['broadcast'];
        }

        $isActive = (bool) ($broadcast['is_active'] ?? false);
        $urgency = in_array($broadcast['urgency_level'] ?? '', [self::URGENCY_CRITICAL, self::URGENCY_WARNING, self::URGENCY_INFO], true)
            ? $broadcast['urgency_level']
            : self::URGENCY_WARNING;

        $display = in_array($broadcast['display_type'] ?? '', [self::DISPLAY_STICKY, self::DISPLAY_MODAL, self::DISPLAY_BOTH], true)
            ? $broadcast['display_type']
            : self::DISPLAY_BOTH;

        $updatedAt = $broadcast['updated_at'] ?? null;
        $formattedTime = '';

        if (! empty($updatedAt)) {
            try {
                $carbon = Carbon::parse($updatedAt)->setTimezone('Asia/Ho_Chi_Minh');
                $formattedTime = $carbon->format('H:i - d/m/Y');
            } catch (\Throwable) {
                $formattedTime = '';
            }
        }

        return [
            'is_active' => $isActive,
            'template_key' => (string) ($broadcast['template_key'] ?? 'custom'),
            'urgency_level' => $urgency,
            'display_type' => $display,
            'title' => trim((string) ($broadcast['title'] ?? '')),
            'message' => trim((string) ($broadcast['message'] ?? '')),
            'action_text' => trim((string) ($broadcast['action_text'] ?? '')),
            'action_url' => trim((string) ($broadcast['action_url'] ?? '')),
            'updated_at' => $updatedAt,
            'formatted_time' => $formattedTime,
        ];
    }

    /**
     * Cập nhật hoặc phát thông báo khẩn cấp mới lên thiệp cưới.
     */
    public static function updateBroadcast(Invitation $invitation, array $data): array
    {
        $current = self::resolveBroadcastConfig($invitation);

        $isActive = array_key_exists('is_active', $data)
            ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)
            : true;

        $urgency = in_array($data['urgency_level'] ?? '', [self::URGENCY_CRITICAL, self::URGENCY_WARNING, self::URGENCY_INFO], true)
            ? $data['urgency_level']
            : ($current['urgency_level'] ?: self::URGENCY_WARNING);

        $display = in_array($data['display_type'] ?? '', [self::DISPLAY_STICKY, self::DISPLAY_MODAL, self::DISPLAY_BOTH], true)
            ? $data['display_type']
            : ($current['display_type'] ?: self::DISPLAY_BOTH);

        $title = ! empty(trim($data['title'] ?? '')) ? trim($data['title']) : 'Thông Báo Cập Nhật Ngày Cưới';
        $message = ! empty(trim($data['message'] ?? '')) ? trim($data['message']) : '';

        $updated = [
            'is_active' => $isActive,
            'template_key' => (string) ($data['template_key'] ?? $current['template_key']),
            'urgency_level' => $urgency,
            'display_type' => $display,
            'title' => $title,
            'message' => $message,
            'action_text' => trim((string) ($data['action_text'] ?? '')),
            'action_url' => trim((string) ($data['action_url'] ?? '')),
            'updated_at' => now()->toIso8601String(),
        ];

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $customConfig['broadcast'] = $updated;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_BROADCAST_UPDATE',
            'Invitation',
            $invitation->id,
            "Cập nhật thông báo khẩn cấp ngày cưới: [{$urgency}] {$title}",
            $updated
        );

        return self::resolveBroadcastConfig($invitation);
    }

    /**
     * Hủy kích hoạt hoặc tắt thông báo khẩn cấp trên thiệp cưới.
     */
    public static function deactivateBroadcast(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];

        if (isset($customConfig['broadcast'])) {
            $customConfig['broadcast']['is_active'] = false;
            $customConfig['broadcast']['updated_at'] = now()->toIso8601String();
            $invitation->custom_config = $customConfig;
            $invitation->save();
        }

        AuditLogger::log(
            'INVITATION_BROADCAST_DEACTIVATE',
            'Invitation',
            $invitation->id,
            'Tắt thông báo khẩn cấp ngày cưới thành công',
            ['is_active' => false]
        );

        return self::resolveBroadcastConfig($invitation);
    }
}
