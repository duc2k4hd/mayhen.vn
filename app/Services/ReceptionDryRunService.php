<?php

namespace App\Services;

use App\Models\Invitation;

class ReceptionDryRunService
{
    /**
     * Lấy trạng thái dữ liệu diễn tập hiện tại trong Session (khởi tạo nếu chưa có).
     *
     * @return array{
     *     guests: array<int, array<string, mixed>>,
     *     stats: array<string, int>,
     *     tables: array<int, string>
     * }
     */
    public function getDryRunState(Invitation $invitation, bool $reset = false): array
    {
        $sessionKey = 'reception_dry_run_'.$invitation->id;

        if ($reset || ! session()->has($sessionKey)) {
            $initialState = $this->createInitialFixtures();
            session()->put($sessionKey, $initialState);

            return $initialState;
        }

        return session()->get($sessionKey);
    }

    /**
     * Xử lý quét mã QR hoặc check-in trong môi trường diễn tập cách ly.
     *
     * @param  array{token?: ?string, guest_id?: ?int, attendees?: ?int, note?: ?string}  $params
     * @return array<string, mixed>
     */
    public function processScan(Invitation $invitation, array $params): array
    {
        $state = $this->getDryRunState($invitation);
        $guests = $state['guests'];

        $targetKey = null;

        if (! empty($params['token'])) {
            $token = trim($params['token']);
            if (str_contains($token, 'token=')) {
                parse_str((string) parse_url($token, PHP_URL_QUERY), $queryParams);
                $token = $queryParams['token'] ?? $token;
            } elseif (str_contains($token, '/diem-danh/')) {
                $parts = explode('/diem-danh/', $token);
                $token = end($parts);
            }

            foreach ($guests as $k => $g) {
                if ($g['guest_token'] === $token) {
                    $targetKey = $k;
                    break;
                }
            }
        } elseif (! empty($params['guest_id'])) {
            $guestId = (int) $params['guest_id'];
            foreach ($guests as $k => $g) {
                if ($g['id'] === $guestId) {
                    $targetKey = $k;
                    break;
                }
            }
        }

        if ($targetKey === null) {
            return [
                'success' => false,
                'message' => '[Diễn Tập] Không tìm thấy khách mời mô phỏng tương ứng.',
            ];
        }

        $guest = $guests[$targetKey];
        $alreadyCheckedIn = ! empty($guest['checked_in_at']);
        $previousCheckInTime = $guest['checked_in_at'];

        if (! $alreadyCheckedIn) {
            $attendeesCount = ! empty($params['attendees'])
                ? (int) $params['attendees']
                : $guest['expected_attendees'];

            $guest['checked_in_at'] = now()->format('H:i');
            $guest['checked_in_attendees'] = $attendeesCount;
            if (! empty($params['note'])) {
                $guest['check_in_note'] = trim($params['note']);
            }

            $guests[$targetKey] = $guest;
            $state['guests'] = $guests;
            $state['stats'] = $this->calculateStats($guests);

            session()->put('reception_dry_run_'.$invitation->id, $state);
        }

        return [
            'success' => true,
            'is_dry_run' => true,
            'already_checked_in' => $alreadyCheckedIn,
            'previous_time' => $previousCheckInTime,
            'guest' => [
                'id' => $guest['id'],
                'name' => $guest['name'],
                'salutation' => $guest['salutation'],
                'group_name' => $guest['group_name'],
                'table_name' => $guest['table_name'] ?: 'Chưa xếp bàn',
                'expected_attendees' => $guest['expected_attendees'],
                'checked_in_attendees' => $guest['checked_in_attendees'] ?? $guest['expected_attendees'],
                'checked_in_at' => $guest['checked_in_at'],
                'dietary' => $guest['dietary'],
                'wishes' => $guest['wishes'],
            ],
            'stats' => $state['stats'],
        ];
    }

    /**
     * Tra cứu nhanh khách mời mô phỏng trong diễn tập.
     *
     * @return array<int, array<string, mixed>>
     */
    public function quickSearch(Invitation $invitation, string $query): array
    {
        $state = $this->getDryRunState($invitation);
        $q = mb_strtolower(trim($query), 'UTF-8');

        $results = [];
        foreach ($state['guests'] as $g) {
            if ($q === '' ||
                str_contains(mb_strtolower($g['name'], 'UTF-8'), $q) ||
                str_contains(mb_strtolower((string) $g['phone'], 'UTF-8'), $q) ||
                str_contains(mb_strtolower((string) $g['table_name'], 'UTF-8'), $q)
            ) {
                $results[] = [
                    'id' => $g['id'],
                    'name' => $g['name'],
                    'salutation' => $g['salutation'],
                    'group_name' => $g['group_name'],
                    'table_name' => $g['table_name'] ?: 'Chưa xếp bàn',
                    'expected_attendees' => $g['expected_attendees'],
                    'checked_in_at' => $g['checked_in_at'],
                    'phone' => $g['phone'],
                ];
            }
        }

        return $results;
    }

    /**
     * Cập nhật nhanh bàn tiệc cho khách trong diễn tập.
     */
    public function updateTable(Invitation $invitation, int $guestId, string $tableName): bool
    {
        $state = $this->getDryRunState($invitation);
        $guests = $state['guests'];

        foreach ($guests as $k => $g) {
            if ($g['id'] === $guestId) {
                $guests[$k]['table_name'] = trim($tableName);
                $state['guests'] = $guests;
                session()->put('reception_dry_run_'.$invitation->id, $state);

                return true;
            }
        }

        return false;
    }

    /**
     * Đặt lại phiên diễn tập.
     */
    public function resetDryRun(Invitation $invitation): void
    {
        session()->forget('reception_dry_run_'.$invitation->id);
    }

    /**
     * Tạo 5 kịch bản khách mẫu mô phỏng ngày cưới.
     */
    private function createInitialFixtures(): array
    {
        $guests = [
            [
                'id' => 9001,
                'name' => 'Bác Trần Văn Tuấn',
                'salutation' => 'Bác',
                'group_name' => 'Họ Hàng Nhà Trai',
                'table_name' => 'Bàn VIP 01',
                'guest_token' => 'dryrun_vip01',
                'phone' => '0901112222',
                'expected_attendees' => 2,
                'checked_in_at' => null,
                'checked_in_attendees' => null,
                'dietary' => 'Không ăn cay (Người cao tuổi)',
                'wishes' => 'Chúc hai cháu trăm năm đầu bạc răng long!',
            ],
            [
                'id' => 9002,
                'name' => 'Gia đình anh Lê Hoàng Nam',
                'salutation' => 'Anh',
                'group_name' => 'Bạn Đại Học',
                'table_name' => 'Bàn 08',
                'guest_token' => 'dryrun_family',
                'phone' => '0913334444',
                'expected_attendees' => 4,
                'checked_in_at' => null,
                'checked_in_attendees' => null,
                'dietary' => null,
                'wishes' => 'Cả nhà anh chúc hai em mãi mãi hạnh phúc như ngày hôm nay!',
            ],
            [
                'id' => 9003,
                'name' => 'Bạn Nguyễn Thu Hà',
                'salutation' => 'Bạn',
                'group_name' => 'Đồng Nghiệp',
                'table_name' => null,
                'guest_token' => 'dryrun_unseated',
                'phone' => '0985556666',
                'expected_attendees' => 1,
                'checked_in_at' => null,
                'checked_in_attendees' => null,
                'dietary' => 'Ăn chay',
                'wishes' => 'Happy Wedding cô bạn đồng nghiệp dễ thương!',
            ],
            [
                'id' => 9004,
                'name' => 'Chị Phạm Mai Hương',
                'salutation' => 'Chị',
                'group_name' => 'Bạn Cô Dâu',
                'table_name' => 'Bàn 04',
                'guest_token' => 'dryrun_duplicate',
                'phone' => '0977778888',
                'expected_attendees' => 1,
                'checked_in_at' => '17:45',
                'checked_in_attendees' => 1,
                'dietary' => null,
                'wishes' => 'Yêu cô dâu nhiều lắm!',
            ],
            [
                'id' => 9005,
                'name' => 'Anh Đỗ Minh Khang',
                'salutation' => 'Anh',
                'group_name' => 'Hội Moto Phượt',
                'table_name' => 'Bàn 12',
                'guest_token' => 'dryrun_standard',
                'phone' => '0969990000',
                'expected_attendees' => 2,
                'checked_in_at' => null,
                'checked_in_attendees' => null,
                'dietary' => null,
                'wishes' => 'Chúc hai vợ chồng luôn đồng hành trên mọi cung đường!',
            ],
        ];

        return [
            'guests' => $guests,
            'stats' => $this->calculateStats($guests),
            'tables' => ['Bàn VIP 01', 'Bàn VIP 02', 'Bàn 04', 'Bàn 08', 'Bàn 12', 'Bàn Dự Phòng 01'],
        ];
    }

    private function calculateStats(array $guests): array
    {
        $totalExpected = 0;
        $checkedInCount = 0;
        $checkedInGuests = 0;

        foreach ($guests as $g) {
            $totalExpected += (int) $g['expected_attendees'];
            if (! empty($g['checked_in_at'])) {
                $checkedInGuests++;
                $checkedInCount += ! empty($g['checked_in_attendees']) ? (int) $g['checked_in_attendees'] : (int) $g['expected_attendees'];
            }
        }

        return [
            'total_guests' => count($guests),
            'total_expected' => $totalExpected,
            'checked_in_guests' => $checkedInGuests,
            'checked_in_count' => $checkedInCount,
        ];
    }
}

