<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LoveStoryTimelineService
{
    /**
     * Danh sách 6 biểu tượng cột mốc tình yêu khả dụng.
     *
     * @return array<string, array{name: string, symbol: string}>
     */
    public static function getAvailableIcons(): array
    {
        return [
            'heart' => ['name' => 'Trái Tim Yêu Thương', 'symbol' => '♥'],
            'sparkles' => ['name' => 'Tia Sáng Định Mệnh', 'symbol' => '✦'],
            'ring' => ['name' => 'Nhẫn Cầu Hôn', 'symbol' => '💍'],
            'plane' => ['name' => 'Chuyến Đi Kỷ Niệm', 'symbol' => '✈'],
            'rose' => ['name' => 'Bông Hồng Tình Yêu', 'symbol' => '🌹'],
            'home' => ['name' => 'Tổ Ấm Chung Đôi', 'symbol' => '🏡'],
        ];
    }

    /**
     * Sinh danh sách 4 cột mốc tình yêu lãng mạn mặc định.
     *
     * @return array<int, array{id: string, date_label: string, title: string, description: string, photo_url: string, icon: string}>
     */
    public static function getDefaultMilestones(?Invitation $invitation = null): array
    {
        $groom = $invitation->wedding->groom_name ?? $invitation->groom_name ?? 'Anh';
        $bride = $invitation->wedding->bride_name ?? $invitation->bride_name ?? 'Em';
        $currentYear = (int) date('Y');

        return [
            [
                'id' => 'milestone_first_meet',
                'date_label' => 'Mùa Thu '.($currentYear - 4),
                'title' => 'Ánh Mắt Đầu Tiên Chạm Nhau',
                'description' => "Vào một chiều thu dịu mát, định mệnh đã sắp đặt để {$groom} và {$bride} tình cờ gặp gỡ. Khoảnh khắc ấy, dường như cả thế giới xung quanh đều chậm lại.",
                'photo_url' => asset('assets/client/images/template-placeholder.webp'),
                'icon' => 'sparkles',
            ],
            [
                'id' => 'milestone_first_date',
                'date_label' => '14 Tháng 02 '.($currentYear - 3),
                'title' => 'Buổi Hò Hẹn Đầu Tiên & Lời Tỏ Tình',
                'description' => 'Tách cà phê quen thuộc và những câu chuyện không bao giờ dứt. Chúng mình nhận ra trái tim đã cùng chung một nhịp đập tự lúc nào.',
                'photo_url' => asset('assets/client/images/template-placeholder.webp'),
                'icon' => 'heart',
            ],
            [
                'id' => 'milestone_proposal',
                'date_label' => 'Mùa Đông '.($currentYear - 1),
                'title' => 'Khoảnh Khắc "Em Đồng Ý"',
                'description' => 'Dưới ánh hoàng hôn rực rỡ và tiếng sóng vỗ dịu êm, chiếc nhẫn đính ước đã được trao cùng lời hẹn ước đi trọn cuộc đời.',
                'photo_url' => asset('assets/client/images/template-placeholder.webp'),
                'icon' => 'ring',
            ],
            [
                'id' => 'milestone_wedding_day',
                'date_label' => 'Mùa Cưới '.$currentYear,
                'title' => 'Ngày Về Chung Một Nhà',
                'description' => 'Hành trình yêu đơm hoa kết trái. Hôm nay, trước sự chứng kiến và chúc phúc của những người thân yêu nhất, hai ta chính thức nên duyên vợ chồng.',
                'photo_url' => asset('assets/client/images/template-placeholder.webp'),
                'icon' => 'home',
            ],
        ];
    }

    /**
     * Phân giải cấu hình và danh sách cột mốc tình yêu của thiệp cưới.
     *
     * @return array{
     *     enabled: bool,
     *     title: string,
     *     subtitle: string,
     *     milestones: array<int, array{id: string, date_label: string, title: string, description: string, photo_url: string, icon: string}>
     * }
     */
    public static function resolveLoveStoryConfig(?Invitation $invitation = null, ?array $customConfig = null): array
    {
        $storyData = [];

        if ($invitation && is_array($invitation->custom_config) && isset($invitation->custom_config['love_story'])) {
            $storyData = $invitation->custom_config['love_story'];
        } elseif (is_array($customConfig) && isset($customConfig['love_story'])) {
            $storyData = $customConfig['love_story'];
        }

        $enabled = array_key_exists('enabled', $storyData) ? (bool) $storyData['enabled'] : true;
        $title = ! empty(trim($storyData['title'] ?? '')) ? trim($storyData['title']) : 'Hành Trình Yêu Thương';
        $subtitle = ! empty(trim($storyData['subtitle'] ?? '')) ? trim($storyData['subtitle']) : 'Từng cột mốc thăng hoa ghi dấu những khoảnh khắc ngọt ngào nhất của hai ta';

        $rawMilestones = $storyData['milestones'] ?? null;
        if (is_array($rawMilestones) && ! empty($rawMilestones)) {
            $milestones = [];
            foreach ($rawMilestones as $idx => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $milestones[] = [
                    'id' => (string) ($item['id'] ?? ('milestone_'.($idx + 1))),
                    'date_label' => trim((string) ($item['date_label'] ?? '')),
                    'title' => trim((string) ($item['title'] ?? '')),
                    'description' => trim((string) ($item['description'] ?? '')),
                    'photo_url' => trim((string) ($item['photo_url'] ?? asset('assets/client/images/template-placeholder.webp'))),
                    'icon' => trim((string) ($item['icon'] ?? 'heart')),
                ];
            }
        } else {
            $milestones = self::getDefaultMilestones($invitation);
        }

        return [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'milestones' => $milestones,
        ];
    }

    /**
     * Cập nhật danh sách cột mốc tình yêu cho thiệp cưới.
     */
    public static function updateLoveStory(Invitation $invitation, array $data): array
    {
        $current = self::resolveLoveStoryConfig($invitation);

        $enabled = array_key_exists('enabled', $data)
            ? filter_var($data['enabled'], FILTER_VALIDATE_BOOLEAN)
            : $current['enabled'];

        $title = ! empty(trim($data['title'] ?? '')) ? trim($data['title']) : $current['title'];
        $subtitle = ! empty(trim($data['subtitle'] ?? '')) ? trim($data['subtitle']) : $current['subtitle'];

        $cleanMilestones = [];
        if (isset($data['milestones']) && is_array($data['milestones'])) {
            foreach ($data['milestones'] as $idx => $item) {
                if (! is_array($item)) {
                    continue;
                }
                $t = trim((string) ($item['title'] ?? ''));
                if (empty($t)) {
                    continue; // bỏ qua nếu thiếu tiêu đề
                }

                $cleanMilestones[] = [
                    'id' => (string) ($item['id'] ?? ('milestone_'.Str::random(8))),
                    'date_label' => trim((string) ($item['date_label'] ?? '')),
                    'title' => $t,
                    'description' => trim((string) ($item['description'] ?? '')),
                    'photo_url' => trim((string) ($item['photo_url'] ?? asset('assets/client/images/template-placeholder.webp'))),
                    'icon' => in_array($item['icon'] ?? '', array_keys(self::getAvailableIcons()), true) ? $item['icon'] : 'heart',
                ];
            }
        }

        if (empty($cleanMilestones)) {
            $cleanMilestones = self::getDefaultMilestones($invitation);
        }

        $payload = [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'milestones' => $cleanMilestones,
        ];

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $customConfig['love_story'] = $payload;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_LOVE_STORY_UPDATE',
            'Invitation',
            $invitation->id,
            "Cập nhật hành trình yêu: {$title} (".count($cleanMilestones).' cột mốc)',
            $payload
        );

        return self::resolveLoveStoryConfig($invitation);
    }

    /**
     * Tải lên ảnh kỷ niệm cho một cột mốc tình yêu.
     */
    public static function uploadMilestonePhoto(Invitation $invitation, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic'];

        if (! in_array($extension, $allowed, true)) {
            throw new \InvalidArgumentException('Định dạng ảnh không được hỗ trợ. Vui lòng tải file JPG, PNG, WEBP hoặc HEIC.');
        }

        // Tối đa 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new \InvalidArgumentException('Dung lượng ảnh vượt quá giới hạn 10MB.');
        }

        $filename = 'milestone_'.Str::random(12).'.'.$extension;
        $relativeDir = "invitations/{$invitation->id}/love-story";
        $targetDir = storage_path("app/public/{$relativeDir}");

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return asset("storage/{$relativeDir}/{$filename}");
    }
}
