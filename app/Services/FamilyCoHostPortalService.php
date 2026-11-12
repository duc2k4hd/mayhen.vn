<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FamilyCoHostPortalService
{
    public const SIDE_GROOM = 'nha-trai';

    public const SIDE_BRIDE = 'nha-gai';

    /**
     * Lấy cấu hình phân quyền gia đình hai bên, tự động khởi tạo mặc định nếu chưa có.
     *
     * @return array{
     *     enabled: bool,
     *     groom: array{
     *         enabled: bool,
     *         manager_name: string,
     *         pin: string,
     *         token: string,
     *         allowed_groups: array<int, string>,
     *         default_group: string
     *     },
     *     bride: array{
     *         enabled: bool,
     *         manager_name: string,
     *         pin: string,
     *         token: string,
     *         allowed_groups: array<int, string>,
     *         default_group: string
     *     }
     * }
     */
    public function getConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $portalConfig = $customConfig['family_portal'] ?? [];

        $defaultGroomGroups = ['Nhà Trai', 'Họ Hàng Nhà Trai', 'Bạn Bố Mẹ Chú Rể'];
        $defaultBrideGroups = ['Nhà Gái', 'Họ Hàng Nhà Gái', 'Bạn Bố Mẹ Cô Dâu'];

        $resolved = [
            'enabled' => (bool) ($portalConfig['enabled'] ?? true),
            'groom' => [
                'enabled' => (bool) ($portalConfig['groom']['enabled'] ?? true),
                'manager_name' => (string) ($portalConfig['groom']['manager_name'] ?? 'Đại Diện Họ Nhà Trai'),
                'pin' => (string) ($portalConfig['groom']['pin'] ?? '2468'),
                'token' => (string) ($portalConfig['groom']['token'] ?? Str::random(32)),
                'allowed_groups' => (array) ($portalConfig['groom']['allowed_groups'] ?? $defaultGroomGroups),
                'default_group' => (string) ($portalConfig['groom']['default_group'] ?? 'Nhà Trai'),
            ],
            'bride' => [
                'enabled' => (bool) ($portalConfig['bride']['enabled'] ?? true),
                'manager_name' => (string) ($portalConfig['bride']['manager_name'] ?? 'Đại Diện Họ Nhà Gái'),
                'pin' => (string) ($portalConfig['bride']['pin'] ?? '1357'),
                'token' => (string) ($portalConfig['bride']['token'] ?? Str::random(32)),
                'allowed_groups' => (array) ($portalConfig['bride']['allowed_groups'] ?? $defaultBrideGroups),
                'default_group' => (string) ($portalConfig['bride']['default_group'] ?? 'Nhà Gái'),
            ],
        ];

        // Nếu trong DB chưa có token, lưu lại để bảo toàn phiên truy cập 1-chạm
        if (empty($portalConfig['groom']['token']) || empty($portalConfig['bride']['token'])) {
            $customConfig['family_portal'] = $resolved;
            $invitation->custom_config = $customConfig;
            $invitation->saveQuietly();
        }

        return $resolved;
    }

    /**
     * Cập nhật cấu hình phân quyền gia đình từ Studio của cặp đôi.
     */
    public function updateConfig(Invitation $invitation, array $data): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $current = $this->getConfig($invitation);

        $current['enabled'] = isset($data['enabled']) ? (bool) $data['enabled'] : false;

        if (isset($data['groom']) && is_array($data['groom'])) {
            $current['groom']['enabled'] = isset($data['groom']['enabled']) ? (bool) $data['groom']['enabled'] : false;
            $current['groom']['manager_name'] = trim((string) ($data['groom']['manager_name'] ?? $current['groom']['manager_name']));
            if (! empty($data['groom']['pin'])) {
                $current['groom']['pin'] = trim((string) $data['groom']['pin']);
            }
            if (isset($data['groom']['allowed_groups']) && is_array($data['groom']['allowed_groups'])) {
                $current['groom']['allowed_groups'] = array_values(array_filter(array_map('trim', $data['groom']['allowed_groups'])));
            }
            if (! empty($data['groom']['default_group'])) {
                $current['groom']['default_group'] = trim((string) $data['groom']['default_group']);
            }
        }

        if (isset($data['bride']) && is_array($data['bride'])) {
            $current['bride']['enabled'] = isset($data['bride']['enabled']) ? (bool) $data['bride']['enabled'] : false;
            $current['bride']['manager_name'] = trim((string) ($data['bride']['manager_name'] ?? $current['bride']['manager_name']));
            if (! empty($data['bride']['pin'])) {
                $current['bride']['pin'] = trim((string) $data['bride']['pin']);
            }
            if (isset($data['bride']['allowed_groups']) && is_array($data['bride']['allowed_groups'])) {
                $current['bride']['allowed_groups'] = array_values(array_filter(array_map('trim', $data['bride']['allowed_groups'])));
            }
            if (! empty($data['bride']['default_group'])) {
                $current['bride']['default_group'] = trim((string) $data['bride']['default_group']);
            }
        }

        $customConfig['family_portal'] = $current;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        return $current;
    }

    /**
     * Tái tạo mã token truy cập bí mật cho một bên (Nhà Trai hoặc Nhà Gái).
     */
    public function regenerateToken(Invitation $invitation, string $side): string
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $config = $this->getConfig($invitation);
        $key = $side === self::SIDE_GROOM ? 'groom' : 'bride';

        $newToken = Str::random(32);
        $config[$key]['token'] = $newToken;

        $customConfig['family_portal'] = $config;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        return $newToken;
    }

    /**
     * Xác thực quyền truy cập Cổng Gia Đình bằng mã Token hoặc mã PIN.
     */
    public function authenticate(Invitation $invitation, string $side, ?string $token = null, ?string $pin = null): bool
    {
        $config = $this->getConfig($invitation);

        if (! $config['enabled']) {
            return false;
        }

        $key = $side === self::SIDE_GROOM ? 'groom' : 'bride';
        $sideConfig = $config[$key] ?? null;

        if (! $sideConfig || empty($sideConfig['enabled'])) {
            return false;
        }

        // 1. Kiểm tra mã Token truy cập trực tiếp 1-chạm
        if ($token !== null && ! empty($sideConfig['token']) && hash_equals($sideConfig['token'], trim($token))) {
            return true;
        }

        // 2. Kiểm tra mã PIN bảo vệ
        if ($pin !== null && ! empty($sideConfig['pin']) && hash_equals($sideConfig['pin'], trim($pin))) {
            return true;
        }

        return false;
    }

    /**
     * Thống kê các chỉ số quan khách riêng của một bên gia đình.
     *
     * @param  array<int, string>  $allowedGroups
     * @return array{
     *     total_guests: int,
     *     total_expected: int,
     *     attending_count: int,
     *     attending_attendees: int,
     *     declined_count: int,
     *     pending_count: int,
     *     opened_count: int
     * }
     */
    public function getSideStats(Wedding $wedding, array $allowedGroups): array
    {
        $guests = $this->buildSideQuery($wedding, $allowedGroups)->with('response')->get();

        $totalGuests = $guests->count();
        $totalExpected = (int) $guests->sum('expected_attendees');
        $attendingCount = 0;
        $attendingAttendees = 0;
        $declinedCount = 0;
        $pendingCount = 0;
        $openedCount = 0;

        foreach ($guests as $guest) {
            if ($guest->isOpened()) {
                $openedCount++;
            }

            $response = $guest->response;
            if (! $response) {
                $pendingCount++;
            } elseif ($response->attending_status === 'attending') {
                $attendingCount++;
                $attendingAttendees += ((int) $response->adults_count + (int) $response->children_count);
            } elseif ($response->attending_status === 'declined') {
                $declinedCount++;
            } else {
                $pendingCount++;
            }
        }

        return [
            'total_guests' => $totalGuests,
            'total_expected' => $totalExpected,
            'attending_count' => $attendingCount,
            'attending_attendees' => $attendingAttendees ?: $attendingCount,
            'declined_count' => $declinedCount,
            'pending_count' => $pendingCount,
            'opened_count' => $openedCount,
        ];
    }

    /**
     * Lấy danh sách khách mời riêng của một bên gia đình (Cách ly dữ liệu 100%).
     *
     * @param  array<int, string>  $allowedGroups
     */
    public function getSideGuests(
        Wedding $wedding,
        array $allowedGroups,
        ?string $search = null,
        ?string $status = null
    ): Collection {
        $query = $this->buildSideQuery($wedding, $allowedGroups)
            ->with(['response', 'table'])
            ->latest();

        if (! empty($search)) {
            $term = trim($search);
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('table_name', 'like', "%{$term}%")
                    ->orWhere('salutation', 'like', "%{$term}%");
            });
        }

        if (! empty($status)) {
            if ($status === 'attending') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($status === 'declined') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($status === 'pending') {
                $query->whereDoesntHave('response');
            }
        }

        return $query->get();
    }

    /**
     * Thêm khách mời mới từ Cổng Gia Đình (tự động gán đúng nhóm được ủy quyền).
     */
    public function createSideGuest(Wedding $wedding, array $data, string $side): Guest
    {
        $config = $this->getConfig($wedding->invitation ?: $wedding->invitations()->first());
        $key = $side === self::SIDE_GROOM ? 'groom' : 'bride';
        $sideConfig = $config[$key];

        $groupName = ! empty($data['group_name']) ? trim((string) $data['group_name']) : $sideConfig['default_group'];

        // Kiểm tra bảo mật: Không được gán nhóm ngoài phạm vi được ủy quyền
        if (! in_array($groupName, $sideConfig['allowed_groups'], true)) {
            $groupName = $sideConfig['default_group'];
        }

        $expectedAttendees = max(1, (int) ($data['expected_attendees'] ?? 1));

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => trim((string) $data['name']),
            'salutation' => ! empty($data['salutation']) ? trim((string) $data['salutation']) : 'Quý khách',
            'group_name' => $groupName,
            'phone' => ! empty($data['phone']) ? trim((string) $data['phone']) : null,
            'expected_attendees' => $expectedAttendees,
            'max_attendees' => $expectedAttendees,
            'guest_token' => Str::random(32),
        ]);

        AuditLogger::log(
            'CO_HOST_GUEST_CREATED',
            'Guest',
            $guest->id,
            "Đại diện {$sideConfig['manager_name']} đã thêm khách '{$guest->name}' vào nhóm {$guest->group_name}"
        );

        return $guest;
    }

    /**
     * Cập nhật phản hồi tham dự cho khách từ Cổng Gia Đình (khi người quen gọi điện báo tin).
     */
    public function updateSideGuestRsvp(
        Guest $guest,
        string $status,
        ?int $adults = null,
        ?string $note = null,
        string $side = self::SIDE_GROOM
    ): void {
        $wedding = $guest->wedding;
        $prevResponse = $guest->response;
        $prevStatus = $prevResponse?->attending_status ?? 'pending';
        $prevAdults = (int) ($prevResponse?->adults_count ?? 1);

        $newAdults = $status === 'attending' ? max(1, $adults ?? $guest->expected_attendees) : 0;

        $response = GuestResponse::updateOrCreate(
            ['guest_id' => $guest->id],
            [
                'wedding_id' => $wedding->id,
                'guest_name' => $guest->name,
                'attending_status' => $status,
                'adults_count' => $newAdults,
                'children_count' => 0,
                'wishes' => $note ?: null,
                'needs_reconfirmation' => false,
                'reconfirmed_at' => now(),
            ]
        );

        // Ghi nhận nhật ký biến động
        GuestResponseLog::create([
            'guest_response_id' => $response->id,
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'previous_status' => $prevStatus,
            'new_status' => $status,
            'previous_adults' => $prevAdults,
            'new_adults' => $newAdults,
            'previous_children' => 0,
            'new_children' => 0,
            'previous_dietary' => null,
            'new_dietary' => null,
            'previous_wishes' => $prevResponse?->wishes,
            'new_wishes' => $note,
            'source' => 'co_host_'.$side,
            'ip_address' => request()->ip(),
        ]);

        $sideLabel = $side === self::SIDE_GROOM ? 'Họ Nhà Trai' : 'Họ Nhà Gái';
        AuditLogger::log(
            'CO_HOST_RSVP_UPDATED',
            'GuestResponse',
            $response->id,
            "Đại diện {$sideLabel} đã cập nhật phản hồi của khách '{$guest->name}' sang {$status} ({$newAdults} người)"
        );
    }

    /**
     * Dựng query lọc khách mời theo các nhóm của một bên gia đình.
     *
     * @param  array<int, string>  $allowedGroups
     */
    protected function buildSideQuery(Wedding $wedding, array $allowedGroups): Builder|HasMany
    {
        return $wedding->guests()->where(function (Builder $q) use ($allowedGroups) {
            foreach ($allowedGroups as $idx => $group) {
                if ($idx === 0) {
                    $q->where('group_name', $group)
                        ->orWhere('group_name', 'like', "%{$group}%");
                } else {
                    $q->orWhere('group_name', $group)
                        ->orWhere('group_name', 'like', "%{$group}%");
                }
            }
        });
    }
}
