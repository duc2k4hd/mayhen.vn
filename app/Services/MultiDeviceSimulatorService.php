<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Http\Request;

class MultiDeviceSimulatorService
{
    /**
     * Danh sách hồ sơ các thiết bị thực tế tiêu chuẩn được sử dụng phổ biến nhất.
     *
     * @return array<string, array{
     *     key: string,
     *     name: string,
     *     brand: string,
     *     category: string,
     *     width: int,
     *     height: int,
     *     screen_ratio: string,
     *     frame_type: string,
     *     border_radius: int,
     *     bezel_width: int,
     *     description: string
     * }>
     */
    public static function getDeviceProfiles(): array
    {
        return [
            'iphone_16_pro' => [
                'key' => 'iphone_16_pro',
                'name' => 'iPhone 16 Pro / 15 Pro',
                'brand' => 'Apple',
                'category' => 'mobile',
                'width' => 393,
                'height' => 852,
                'screen_ratio' => '19.5:9',
                'frame_type' => 'dynamic_island',
                'border_radius' => 50,
                'bezel_width' => 12,
                'description' => 'Flagship Apple tỷ lệ 19.5:9, Dynamic Island và thanh Home Indicator bo góc sâu.',
            ],
            'galaxy_s24' => [
                'key' => 'galaxy_s24',
                'name' => 'Samsung Galaxy S24 Ultra',
                'brand' => 'Samsung',
                'category' => 'mobile',
                'width' => 412,
                'height' => 915,
                'screen_ratio' => '20:9',
                'frame_type' => 'hole_punch',
                'border_radius' => 38,
                'bezel_width' => 10,
                'description' => 'Màn hình flagship Android tràn viền sắc nét, camera đục lỗ tinh tế ở đỉnh máy.',
            ],
            'iphone_se' => [
                'key' => 'iphone_se',
                'name' => 'iPhone SE / Compact Mobile',
                'brand' => 'Apple',
                'category' => 'mobile',
                'width' => 375,
                'height' => 667,
                'screen_ratio' => '16:9',
                'frame_type' => 'classic_notch',
                'border_radius' => 36,
                'bezel_width' => 12,
                'description' => 'Màn hình cỡ nhỏ tiêu chuẩn (375px), kiểm tra chống tràn chữ và kiểm tra nút bấm.',
            ],
            'ipad_air' => [
                'key' => 'ipad_air',
                'name' => 'iPad Air / Pro (Tablet)',
                'brand' => 'Apple',
                'category' => 'tablet',
                'width' => 820,
                'height' => 1180,
                'screen_ratio' => '4:3',
                'frame_type' => 'tablet_bezel',
                'border_radius' => 32,
                'bezel_width' => 16,
                'description' => 'Máy tính bảng tỷ lệ 4:3, trải nghiệm đọc thiệp như trang tạp chí cưới cao cấp.',
            ],
            'desktop_wide' => [
                'key' => 'desktop_wide',
                'name' => 'MacBook / Desktop Screen',
                'brand' => 'Universal',
                'category' => 'desktop',
                'width' => 1180,
                'height' => 760,
                'screen_ratio' => '16:10',
                'frame_type' => 'laptop_bezel',
                'border_radius' => 16,
                'bezel_width' => 14,
                'description' => 'Màn hình máy tính để bàn rộng lớn, thiệp cưới hiển thị tinh tế trên nền kem ấm.',
            ],
        ];
    }

    /**
     * Chuẩn bị toàn bộ dữ liệu phục vụ màn hình Studio Soi Đa Thiết Bị.
     *
     * @return array{
     *     invitation: Invitation,
     *     wedding: Wedding|null,
     *     devices: array<string, mixed>,
     *     activeDevice: array<string, mixed>,
     *     availableGroups: array<int, string>,
     *     supportedLocales: array<string, array<string, string>>,
     *     activeGroup: string,
     *     activeOrientation: string,
     *     activeZoom: float,
     *     embedUrl: string
     * }
     */
    public function getSimulatorData(Invitation $invitation, Request $request): array
    {
        $wedding = $invitation->wedding;
        $devices = self::getDeviceProfiles();

        // 1. Phân giải thiết bị được chọn
        $requestedDeviceKey = (string) $request->query('device', 'iphone_16_pro');
        $activeDevice = $devices[$requestedDeviceKey] ?? $devices['iphone_16_pro'];

        // 2. Thu thập danh sách các nhóm khách từ DB & preset
        $availableGroups = ['Tất cả khách', 'Bạn Bè', 'Đồng Nghiệp', 'Quan Khách Nhà Trai', 'Quan Khách Nhà Gái', 'Gia Đình'];
        if ($wedding) {
            $dbGroups = $wedding->guests()
                ->whereNotNull('group_name')
                ->where('group_name', '!=', '')
                ->distinct()
                ->pluck('group_name')
                ->all();
            $availableGroups = array_values(array_unique(array_filter(array_merge($availableGroups, $dbGroups))));
        }

        $activeGroup = trim((string) $request->query('group', 'Tất cả khách'));
        if ($activeGroup === '' || $activeGroup === 'all') {
            $activeGroup = 'Tất cả khách';
        }

        // 3. Phân giải hướng hiển thị (Orientation)
        $activeOrientation = $request->query('orientation') === 'landscape' ? 'landscape' : 'portrait';

        // 4. Phân giải tỷ lệ zoom
        $activeZoom = (float) $request->query('zoom', 1.0);
        if ($activeZoom < 0.4 || $activeZoom > 1.5) {
            $activeZoom = 1.0;
        }

        // 5. Danh sách ngôn ngữ hỗ trợ
        $supportedLocales = MultilingualInvitationService::SUPPORTED_LOCALES;
        $activeLang = (string) $request->query('lang', 'vi');

        // 6. Xây dựng URL nhúng thiệp cưới với chế độ diễn tập rehearsal an toàn
        $urlParams = [
            'slug' => $invitation->slug,
            'embed' => 1,
            'rehearsal' => 1,
        ];

        if ($activeGroup !== 'Tất cả khách') {
            $urlParams['group'] = $activeGroup;
            $urlParams['rehearsal_group'] = $activeGroup;
        }

        if ($activeLang !== 'vi') {
            $urlParams['lang'] = $activeLang;
        }

        $embedUrl = route('invitation.show', $urlParams);

        return [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'devices' => $devices,
            'activeDevice' => $activeDevice,
            'availableGroups' => $availableGroups,
            'supportedLocales' => $supportedLocales,
            'activeGroup' => $activeGroup,
            'activeLang' => $activeLang,
            'activeOrientation' => $activeOrientation,
            'activeZoom' => $activeZoom,
            'embedUrl' => $embedUrl,
        ];
    }
}
