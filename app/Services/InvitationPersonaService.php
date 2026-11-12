<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;

class InvitationPersonaService
{
    /**
     * Thư viện văn mẫu lời ngỏ chuẩn Quiet Luxury theo từng nhóm khách (Mục 2.3 DOCX & IDEA-A01).
     *
     * @return array<string, array{id: string, name: string, icon: string, title: string, subtitle: string, content: string}>
     */
    public static function getCuratedPresets(): array
    {
        return [
            'ban_be' => [
                'id' => 'ban_be',
                'name' => 'Bạn Bè & Cạ Cứng',
                'icon' => '🥂',
                'title' => 'Gửi Người Bạn Tuyệt Vời Của Tụi Mình',
                'subtitle' => 'Thanh Xuân & Kỷ Niệm Cùng Nhau',
                'content' => 'Thân gửi {guest_name}, thật vui và may mắn khi trên hành trình trưởng thành và tìm thấy một nửa của đời mình, tụi mình luôn có bạn đồng hành. Ngày {wedding_date} sắp tới, sự hiện diện và nụ cười rạng rỡ của bạn là món quà ý nghĩa nhất. Hẹn gặp bạn cùng tụi mình nâng ly quẩy hết mình nha!',
            ],
            'dong_nghiep' => [
                'id' => 'dong_nghiep',
                'name' => 'Đồng Nghiệp & Đối Tác',
                'icon' => '💼',
                'title' => 'Trân Trọng Kính Gửi Quý Anh Chị',
                'subtitle' => 'Đồng Hành & Chia Ngọt Sẻ Bùi',
                'content' => 'Kính gửi {guest_name}, cảm ơn Anh/Chị đã luôn là người bạn đồng nghiệp tuyệt vời, cùng đồng hành và chia sẻ cùng {groom_name} & {bride_name} trong công việc. Ngày vui trọng đại của cuộc đời, chúng em rất mong được đón tiếp Anh/Chị tới dự bữa cơm thân mật và nâng ly chúc phúc cùng gia đình chúng em.',
            ],
            'gia_dinh' => [
                'id' => 'gia_dinh',
                'name' => 'Gia Đình & Họ Hàng',
                'icon' => '🏮',
                'title' => 'Kính Thưa Quý Cô Dì Chú Bác & Bà Con',
                'subtitle' => 'Nghĩa Tình Gia Tộc & Cội Nguồn Yêu Thương',
                'content' => 'Kính thưa {guest_name}, ngày con cháu thành gia lập thất là ngày vui và trọng đại của toàn thể dòng họ. Sự hiện diện, lời chỉ bảo và chúc phúc của Quý cô dì chú bác là niềm vinh hạnh và phúc lành lớn lao cho {groom_name} & {bride_name} khi bước vào ngưỡng cửa hôn nhân.',
            ],
            'nha_trai' => [
                'id' => 'nha_trai',
                'name' => 'Quan Khách Họ Nhà Trai',
                'icon' => '👑',
                'title' => 'Kính Mời Dự Lễ Thành Hôn',
                'subtitle' => 'Đón Nàng Dâu Hiền Về Tổ Ấm',
                'content' => 'Gia đình họ nhà trai trân trọng kính mời {guest_name} bớt chút thời gian quý báu tới chung vui cùng gia đình chúng tôi trong ngày Lễ Thành Hôn của hai cháu {groom_name} & {bride_name}. Sự hiện diện của Quý khách là niềm vinh dự lớn của gia đình chúng tôi.',
            ],
            'nha_gai' => [
                'id' => 'nha_gai',
                'name' => 'Quan Khách Họ Nhà Gái',
                'icon' => '🌸',
                'title' => 'Kính Mời Dự Lễ Vu Quy',
                'subtitle' => 'Tiễn Nàng Dâu Thảo Về Nhà Chồng',
                'content' => 'Gia đình họ nhà gái trân trọng kính mời {guest_name} tới dự bữa cơm thân mật, tiễn cháu {bride_name} bước vào cuộc sống hôn nhân cùng cháu {groom_name}. Sự có mặt và chúc phúc của Quý vị là nguồn động viên vô giá cho hạnh phúc lứa đôi.',
            ],
        ];
    }

    /**
     * Lấy dữ liệu tổng hợp toàn diện phân hệ Persona Studio.
     *
     * @return array{
     *     invitation: Invitation,
     *     wedding: Wedding,
     *     is_enabled: bool,
     *     groups: array<string, array{
     *         name: string,
     *         guest_count: int,
     *         is_customized: bool,
     *         title: string,
     *         subtitle: string,
     *         content: string,
     *         preset_key: ?string
     *     }>,
     *     stats: array{
     *         total_guests: int,
     *         total_groups: int,
     *         customized_groups_count: int,
     *         covered_guests_count: int,
     *         coverage_percent: int
     *     },
     *     presets: array<string, mixed>
     * }
     */
    public function getPersonaSummary(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?: []);

        $personaConfig = $customConfig['persona_messages'] ?? [];
        $isEnabled = ! empty($personaConfig['enabled']);
        $savedGroups = $personaConfig['groups'] ?? [];

        // 1. Quét danh sách nhóm khách thực tế từ bảng guests
        $dbGroups = [];
        $totalGuests = 0;

        if ($wedding) {
            $guestsQuery = Guest::where('wedding_id', $wedding->id);
            $totalGuests = $guestsQuery->count();

            $groupRows = Guest::where('wedding_id', $wedding->id)
                ->whereNotNull('group_name')
                ->where('group_name', '!=', '')
                ->select('group_name', DB::raw('count(*) as count'))
                ->groupBy('group_name')
                ->get();

            foreach ($groupRows as $row) {
                $gName = trim((string) $row->group_name);
                if ($gName !== '') {
                    $dbGroups[$gName] = (int) $row->count;
                }
            }
        }

        // 2. Nhóm mặc định gợi ý nếu chưa có khách trong DB
        $defaultSuggestedGroups = [
            'Bạn Bè & Bạn Thân' => 0,
            'Đồng Nghiệp & Đối Tác' => 0,
            'Gia Đình & Họ Hàng' => 0,
            'Quan Khách Nhà Trai' => 0,
            'Quan Khách Nhà Gái' => 0,
        ];

        $mergedGroupNames = array_unique(array_merge(
            array_keys($dbGroups),
            array_keys($savedGroups),
            empty($dbGroups) ? array_keys($defaultSuggestedGroups) : []
        ));

        // 3. Xây dựng chi tiết từng nhóm
        $groups = [];
        $customizedCount = 0;
        $coveredGuestsCount = 0;

        foreach ($mergedGroupNames as $name) {
            $saved = $savedGroups[$name] ?? [];
            $guestCount = $dbGroups[$name] ?? 0;

            $hasCustomTitle = ! empty($saved['title']);
            $hasCustomSubtitle = ! empty($saved['subtitle']);
            $hasCustomContent = ! empty($saved['content']) || ! empty($saved['message']);

            $isCustomized = $hasCustomTitle || $hasCustomSubtitle || $hasCustomContent;
            if ($isCustomized) {
                $customizedCount++;
                $coveredGuestsCount += $guestCount;
            }

            // Gợi ý preset phù hợp với tên nhóm
            $matchedPreset = $this->matchPresetByGroupName($name);

            $groups[$name] = [
                'name' => $name,
                'guest_count' => $guestCount,
                'is_customized' => $isCustomized,
                'title' => (string) ($saved['title'] ?? ($matchedPreset['title'] ?? '')),
                'subtitle' => (string) ($saved['subtitle'] ?? ($matchedPreset['subtitle'] ?? '')),
                'content' => (string) ($saved['content'] ?? ($saved['message'] ?? ($matchedPreset['content'] ?? ''))),
                'preset_key' => $matchedPreset['id'] ?? null,
            ];
        }

        $totalGroups = count($groups);
        $coveragePercent = $totalGuests > 0 ? (int) round(($coveredGuestsCount / $totalGuests) * 100) : ($customizedCount > 0 ? 100 : 0);

        return [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'is_enabled' => $isEnabled,
            'groups' => $groups,
            'stats' => [
                'total_guests' => $totalGuests,
                'total_groups' => $totalGroups,
                'customized_groups_count' => $customizedCount,
                'covered_guests_count' => $coveredGuestsCount,
                'coverage_percent' => min(100, max(0, $coveragePercent)),
            ],
            'presets' => self::getCuratedPresets(),
        ];
    }

    /**
     * Cập nhật cấu hình Persona Studio.
     *
     * @param  array{
     *     enabled?: bool,
     *     groups?: array<string, array{title?: ?string, subtitle?: ?string, content?: ?string}>
     * }  $data
     */
    public function updatePersonaConfig(Invitation $invitation, array $data): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?: []);

        $currentPersona = $customConfig['persona_messages'] ?? [];
        $currentGroups = $currentPersona['groups'] ?? [];

        $isEnabled = isset($data['enabled']) ? (bool) $data['enabled'] : (bool) ($currentPersona['enabled'] ?? true);

        if (isset($data['groups']) && is_array($data['groups'])) {
            foreach ($data['groups'] as $groupName => $groupData) {
                $cleanName = trim((string) $groupName);
                if ($cleanName === '') {
                    continue;
                }

                $title = trim((string) ($groupData['title'] ?? ''));
                $subtitle = trim((string) ($groupData['subtitle'] ?? ''));
                $content = trim((string) ($groupData['content'] ?? ''));

                if ($title === '' && $subtitle === '' && $content === '') {
                    unset($currentGroups[$cleanName]);
                } else {
                    $currentGroups[$cleanName] = [
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'content' => $content,
                        'message' => $content,
                    ];
                }
            }
        }

        $customConfig['persona_messages'] = [
            'enabled' => $isEnabled,
            'groups' => $currentGroups,
            'updated_at' => now()->toIso8601String(),
        ];

        $invitation->custom_config = $customConfig;
        $invitation->save();

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log(
                'INVITATION_PERSONA_STUDIO_UPDATED',
                $invitation,
                'Cập nhật phân hệ Studio Cá nhân hóa lời ngỏ theo nhóm khách (IDEA-A01)'
            );
        }

        return $customConfig['persona_messages'];
    }

    /**
     * Đặt lại lời ngỏ của một nhóm về mặc định.
     */
    public function resetGroup(Invitation $invitation, string $groupName): void
    {
        $cleanName = trim($groupName);
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) ($invitation->custom_config ?? '{}'), true) ?: []);

        if (isset($customConfig['persona_messages']['groups'][$cleanName])) {
            unset($customConfig['persona_messages']['groups'][$cleanName]);
            $customConfig['persona_messages']['updated_at'] = now()->toIso8601String();
            $invitation->custom_config = $customConfig;
            $invitation->save();
        }
    }

    /**
     * Tự động nhận diện preset phù hợp từ tên nhóm khách.
     *
     * @return ?array{id: string, name: string, icon: string, title: string, subtitle: string, content: string}
     */
    protected function matchPresetByGroupName(string $groupName): ?array
    {
        $str = mb_strtolower(trim($groupName));
        $presets = self::getCuratedPresets();

        if (str_contains($str, 'bạn') || str_contains($str, 'cạ cứng') || str_contains($str, 'bạn thân') || str_contains($str, 'bạn học')) {
            return $presets['ban_be'];
        }
        if (str_contains($str, 'đồng nghiệp') || str_contains($str, 'đối tác') || str_contains($str, 'công ty') || str_contains($str, 'cơ quan')) {
            return $presets['dong_nghiep'];
        }
        if (str_contains($str, 'gia đình') || str_contains($str, 'họ hàng') || str_contains($str, 'bà con') || str_contains($str, 'gia tiên')) {
            return $presets['gia_dinh'];
        }
        if (str_contains($str, 'nhà trai') || str_contains($str, 'thành hôn') || str_contains($str, 'chú rể')) {
            return $presets['nha_trai'];
        }
        if (str_contains($str, 'nhà gái') || str_contains($str, 'vu quy') || str_contains($str, 'cô dâu')) {
            return $presets['nha_gai'];
        }

        return null;
    }
}
