<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use Illuminate\Support\Collection;

class SmartGroupInvitationService
{
    /**
     * Danh sách các nhóm khách chuẩn mực phong tục cưới Việt Nam (Mục 2.3 DOCX).
     *
     * @return array<string, array{id: string, name: string, icon: string, description: string, default_greeting: string}>
     */
    public static function getPresetGroups(): array
    {
        return [
            'gia_tien' => [
                'id' => 'gia_tien',
                'name' => 'Họ Hàng — Dự Lễ Gia Tiên & Rước Dâu',
                'icon' => '🏮',
                'description' => 'Hiển thị đầy đủ cả Lễ Gia Tiên tại tư gia và Tiệc cưới, trang trọng và đúng tôn ti trật tự dòng họ.',
                'default_greeting' => 'Kính thưa Quý cô dì chú bác và bà con họ hàng, sự hiện diện chứng kiến giờ phút thiêng liêng nơi bái đường gia tiên là niềm hạnh phúc lớn lao của gia đình.',
            ],
            'dong_nghiep' => [
                'id' => 'dong_nghiep',
                'name' => 'Đồng Nghiệp & Đối Tác',
                'icon' => '💼',
                'description' => 'Tập trung vào Tiệc cưới chiêu đãi, bản đồ sảnh tiệc, thông tin đỗ xe và quy chuẩn trang phục lịch thiệp.',
                'default_greeting' => 'Trân trọng kính mời Quý anh chị đồng nghiệp và đối tác tới chung vui, nâng ly chúc mừng cho ngày vui trọng đại của chúng tôi.',
            ],
            'ban_than' => [
                'id' => 'ban_than',
                'name' => 'Bạn Thân & Bạn Trẻ',
                'icon' => '🥂',
                'description' => 'Văn phong gần gũi, sôi nổi, hiển thị khu vực chụp ảnh Photo Booth và chương trình giao lưu After Party.',
                'default_greeting' => 'Rất vui và hạnh phúc khi được đón tiếp người bạn tuyệt vời! Hãy tới quẩy hết mình cùng tụi mình trong ngày đặc biệt này nha!',
            ],
            'nha_trai' => [
                'id' => 'nha_trai',
                'name' => 'Quan Khách Nhà Trai (Lễ Thành Hôn)',
                'icon' => '👑',
                'description' => 'Ưu tiên thông tin Lễ Thành Hôn, địa chỉ tiệc cưới bên nhà trai và tài khoản mừng cưới họ nhà trai.',
                'default_greeting' => 'Gia đình họ nhà trai trân trọng kính mời quý khách tới dự bữa cơm thân mật chung vui cùng hai cháu.',
            ],
            'nha_gai' => [
                'id' => 'nha_gai',
                'name' => 'Quan Khách Nhà Gái (Lễ Vu Quy)',
                'icon' => '🌸',
                'description' => 'Ưu tiên thông tin Lễ Vu Quy, địa chỉ tiệc cưới bên nhà gái và tài khoản mừng cưới họ nhà gái.',
                'default_greeting' => 'Gia đình họ nhà gái trân trọng kính mời quý khách tới dự bữa cơm thân mật tiễn cháu về nhà chồng.',
            ],
        ];
    }

    /**
     * Xác định mã định danh nhóm (Group Key) chuẩn hóa từ group_name của khách mời.
     */
    public static function normalizeGroupKey(?string $groupName): string
    {
        if (empty($groupName)) {
            return 'default';
        }

        $str = mb_strtolower(trim($groupName));

        if (str_contains($str, 'gia tiên') || str_contains($str, 'họ hàng') || str_contains($str, 'bà con')) {
            return 'gia_tien';
        }
        if (str_contains($str, 'đồng nghiệp') || str_contains($str, 'đối tác') || str_contains($str, 'công ty')) {
            return 'dong_nghiep';
        }
        if (str_contains($str, 'bạn thân') || str_contains($str, 'bạn học') || str_contains($str, 'bạn trẻ') || str_contains($str, 'bạn bè')) {
            return 'ban_than';
        }
        if (str_contains($str, 'nhà trai') || str_contains($str, 'chú rể')) {
            return 'nha_trai';
        }
        if (str_contains($str, 'nhà gái') || str_contains($str, 'cô dâu')) {
            return 'nha_gai';
        }

        return 'custom_'.md5($str);
    }

    /**
     * Lọc danh sách sự kiện (Wedding Events) phù hợp với nhóm khách mời cụ thể.
     *
     * @return Collection<int, WeddingEvent>
     */
    public static function filterEventsForGuest(Wedding $wedding, ?Guest $guest, ?Invitation $invitation = null): Collection
    {
        $allEvents = $wedding->events()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('event_time')
            ->get();

        if ($allEvents->isEmpty()) {
            return collect([]);
        }

        // 1. Nếu khách có gán danh sách event_ids cụ thể (Headcount & Event Scope)
        if ($guest && ! empty($guest->event_ids) && is_array($guest->event_ids)) {
            $allowedIds = array_map('intval', $guest->event_ids);
            $filtered = $allEvents->filter(fn ($e) => in_array((int) $e->id, $allowedIds, true));
            if ($filtered->isNotEmpty()) {
                return $filtered;
            }
        }

        // 2. Nếu thiệp có cấu hình phân tuyến sự kiện theo nhóm (Smart Groups Routing)
        if ($invitation && $guest) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);

            $groupKey = self::normalizeGroupKey($guest->group_name);
            $groupConfig = $config['smart_groups'][$groupKey] ?? null;

            if ($groupConfig && ! empty($groupConfig['allowed_event_ids']) && is_array($groupConfig['allowed_event_ids'])) {
                $allowedIds = array_map('intval', $groupConfig['allowed_event_ids']);
                $filtered = $allEvents->filter(fn ($e) => in_array((int) $e->id, $allowedIds, true));
                if ($filtered->isNotEmpty()) {
                    return $filtered;
                }
            }
        }

        // 3. Mặc định hiển thị toàn bộ sự kiện đang hoạt động
        return $allEvents;
    }

    /**
     * Lấy thông điệp cá nhân hóa hoặc lời mời chuyên biệt của nhóm khách mời.
     *
     * @return array{title: ?string, message: ?string, group_name: string}|null
     */
    public static function resolveGroupGreeting(Invitation $invitation, ?Guest $guest): ?array
    {
        if (! $guest) {
            return null;
        }

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $groupKey = self::normalizeGroupKey($guest->group_name);
        $smartGroups = $config['smart_groups'] ?? [];

        $groupData = $smartGroups[$groupKey] ?? null;
        if (! $groupData || empty($groupData['greeting'])) {
            // Lấy từ preset nếu có
            $presets = self::getPresetGroups();
            if (isset($presets[$groupKey])) {
                return [
                    'title' => 'Thư Mời — '.$presets[$groupKey]['name'],
                    'message' => $presets[$groupKey]['default_greeting'],
                    'group_name' => $guest->group_name ?: $presets[$groupKey]['name'],
                ];
            }

            return null;
        }

        $salutation = $guest->salutation ?: 'Quý khách';
        $guestName = $guest->name ?: 'Quý khách';
        $coupleName = ($invitation->wedding?->groom_name ?? 'Chú Rể').' & '.($invitation->wedding?->bride_name ?? 'Cô Dâu');

        $message = str_replace(
            ['{guest_name}', '{salutation}', '{couple_name}'],
            [$guestName, $salutation, $coupleName],
            $groupData['greeting']
        );

        return [
            'title' => $groupData['title'] ?? 'Lời Nhắn Gửi Tới Bạn',
            'message' => $message,
            'group_name' => $guest->group_name ?: 'Khách Quý',
        ];
    }
}

