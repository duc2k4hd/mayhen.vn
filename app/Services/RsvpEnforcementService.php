<?php

namespace App\Services;

use App\Models\GuestResponse;
use App\Models\Invitation;
use Carbon\Carbon;

class RsvpEnforcementService
{
    /**
     * Cấu hình mặc định cho phân hệ Hạn chót RSVP & Khảo sát đưa đón / thực đơn.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultConfig(?Invitation $invitation = null): array
    {
        $wedding = $invitation?->wedding;
        $weddingDate = $wedding?->wedding_date ? Carbon::parse($wedding->wedding_date) : null;
        $defaultDeadline = $weddingDate ? $weddingDate->copy()->subDays(7)->format('Y-m-d') : null;

        return [
            'deadline' => $defaultDeadline,
            'is_manually_closed' => false,
            'lock_after_deadline' => true,
            'closed_notice' => 'Hạn chót xác nhận tham dự đã khép lại để gia đình chốt số lượng bàn tiệc và chuẩn bị đón tiếp chu đáo nhất. Nếu quý khách có thay đổi lịch trình, xin vui lòng liên hệ trực tiếp cô dâu hoặc chú rể.',
            'contact_phone' => $wedding?->extra_data['groom_phone'] ?? ($wedding?->extra_data['bride_phone'] ?? ''),
            'shuttle_bus' => [
                'enabled' => false,
                'title' => 'Dịch Vụ Xe Buýt Đưa Đón Khách Mời',
                'description' => 'Gia đình có bố trí xe buýt đưa đón quý khách từ trung tâm thành phố đến địa điểm tổ chức tiệc cưới. Quý khách vui lòng chọn điểm đón thuận tiện nhất.',
                'pickup_points' => [
                    [
                        'id' => 'point-1',
                        'name' => 'Điểm đón Trung Tâm (Nhà Hát Thành Phố)',
                        'address' => 'Số 07 Lam Sơn, Phường Bến Nghé, Quận 1',
                        'departure_time' => '15:30',
                        'coordinator_name' => 'Trưởng xe 1',
                        'coordinator_phone' => '',
                    ],
                ],
            ],
            'dietary_survey' => [
                'enabled' => false,
                'title' => 'Khảo Sát Khẩu Vị & Thực Đơn Tiệc Cưới',
                'description' => 'Để bữa tiệc thêm trọn vẹn và chu đáo, xin quý khách vui lòng thông tin cho gia đình nếu có nhu cầu dùng thực đơn đặc biệt.',
                'options' => [
                    'standard' => 'Thực đơn tiêu chuẩn (Đầy đủ món tiệc)',
                    'vegetarian' => 'Thực đơn chay thanh tịnh (Vegetarian)',
                    'vegan' => 'Thực đơn thuần chay (Vegan)',
                    'halal' => 'Thực đơn Halal / Không thịt heo',
                    'kids' => 'Thực đơn riêng cho trẻ em',
                    'allergies' => 'Dị ứng thực phẩm đặc biệt (Hải sản, đậu phộng, gluten...)',
                ],
            ],
        ];
    }

    /**
     * Phân giải cấu hình hiện thời của thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public static function resolveConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $default = self::getDefaultConfig($invitation);
        $enforcement = $customConfig['rsvp_enforcement'] ?? [];

        // Tương thích ngược với các trường gốc rsvp_deadline, rsvp_lock_after_deadline, rsvp_closed_notice
        $deadline = $enforcement['deadline'] ?? ($customConfig['rsvp_deadline'] ?? $default['deadline']);
        $lockAfterDeadline = isset($enforcement['lock_after_deadline'])
            ? (bool) $enforcement['lock_after_deadline']
            : (bool) ($customConfig['rsvp_lock_after_deadline'] ?? $default['lock_after_deadline']);
        $closedNotice = ! empty($enforcement['closed_notice'])
            ? (string) $enforcement['closed_notice']
            : (string) ($customConfig['rsvp_closed_notice'] ?? $default['closed_notice']);

        $shuttle = array_merge($default['shuttle_bus'], (array) ($enforcement['shuttle_bus'] ?? []));
        if (empty($shuttle['pickup_points']) || ! is_array($shuttle['pickup_points'])) {
            $shuttle['pickup_points'] = $default['shuttle_bus']['pickup_points'];
        }

        $dietary = array_merge($default['dietary_survey'], (array) ($enforcement['dietary_survey'] ?? []));
        if (empty($dietary['options']) || ! is_array($dietary['options'])) {
            $dietary['options'] = $default['dietary_survey']['options'];
        }

        return [
            'deadline' => $deadline,
            'is_manually_closed' => (bool) ($enforcement['is_manually_closed'] ?? false),
            'lock_after_deadline' => $lockAfterDeadline,
            'closed_notice' => $closedNotice,
            'contact_phone' => (string) ($enforcement['contact_phone'] ?? $default['contact_phone']),
            'shuttle_bus' => $shuttle,
            'dietary_survey' => $dietary,
        ];
    }

    /**
     * Cập nhật và lưu trữ cấu hình vào thiệp cưới.
     *
     * @param  array<string, mixed>  $data
     */
    public static function saveConfig(Invitation $invitation, array $data): Invitation
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $current = self::resolveConfig($invitation);

        $deadline = ! empty($data['deadline']) ? trim((string) $data['deadline']) : null;
        $isManuallyClosed = ! empty($data['is_manually_closed']);
        $lockAfterDeadline = isset($data['lock_after_deadline']) ? (bool) $data['lock_after_deadline'] : true;
        $closedNotice = ! empty($data['closed_notice']) ? trim((string) $data['closed_notice']) : $current['closed_notice'];
        $contactPhone = ! empty($data['contact_phone']) ? trim((string) $data['contact_phone']) : '';

        // Xử lý Shuttle Bus
        $shuttleEnabled = ! empty($data['shuttle_bus_enabled']);
        $shuttleTitle = ! empty($data['shuttle_bus_title']) ? trim((string) $data['shuttle_bus_title']) : $current['shuttle_bus']['title'];
        $shuttleDesc = ! empty($data['shuttle_bus_description']) ? trim((string) $data['shuttle_bus_description']) : $current['shuttle_bus']['description'];

        $pickupPoints = [];
        if (! empty($data['pickup_points']) && is_array($data['pickup_points'])) {
            foreach ($data['pickup_points'] as $index => $point) {
                if (! empty($point['name'])) {
                    $pickupPoints[] = [
                        'id' => ! empty($point['id']) ? (string) $point['id'] : 'point-'.($index + 1),
                        'name' => trim((string) $point['name']),
                        'address' => trim((string) ($point['address'] ?? '')),
                        'departure_time' => trim((string) ($point['departure_time'] ?? '')),
                        'coordinator_name' => trim((string) ($point['coordinator_name'] ?? '')),
                        'coordinator_phone' => trim((string) ($point['coordinator_phone'] ?? '')),
                    ];
                }
            }
        }
        if (empty($pickupPoints)) {
            $pickupPoints = $current['shuttle_bus']['pickup_points'];
        }

        // Xử lý Thực đơn
        $dietaryEnabled = ! empty($data['dietary_survey_enabled']);
        $dietaryTitle = ! empty($data['dietary_survey_title']) ? trim((string) $data['dietary_survey_title']) : $current['dietary_survey']['title'];
        $dietaryDesc = ! empty($data['dietary_survey_description']) ? trim((string) $data['dietary_survey_description']) : $current['dietary_survey']['description'];

        $enforcementConfig = [
            'deadline' => $deadline,
            'is_manually_closed' => $isManuallyClosed,
            'lock_after_deadline' => $lockAfterDeadline,
            'closed_notice' => $closedNotice,
            'contact_phone' => $contactPhone,
            'shuttle_bus' => [
                'enabled' => $shuttleEnabled,
                'title' => $shuttleTitle,
                'description' => $shuttleDesc,
                'pickup_points' => $pickupPoints,
            ],
            'dietary_survey' => [
                'enabled' => $dietaryEnabled,
                'title' => $dietaryTitle,
                'description' => $dietaryDesc,
                'options' => $current['dietary_survey']['options'],
            ],
        ];

        // Đồng bộ các trường tương thích ngược trong custom_config
        $customConfig['rsvp_enforcement'] = $enforcementConfig;
        $customConfig['rsvp_deadline'] = $deadline;
        $customConfig['rsvp_lock_after_deadline'] = $lockAfterDeadline;
        $customConfig['rsvp_closed_notice'] = $closedNotice;

        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'RSVP_ENFORCEMENT_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật hạn chót và khảo sát RSVP cho thiệp #{$invitation->id}",
            [
                'invitation_id' => $invitation->id,
                'deadline' => $deadline,
                'is_manually_closed' => $isManuallyClosed,
                'shuttle_bus_enabled' => $shuttleEnabled,
                'dietary_survey_enabled' => $dietaryEnabled,
            ]
        );

        return $invitation;
    }

    /**
     * Kiểm tra trạng thái form RSVP đang mở hay đã đóng.
     */
    public static function isRsvpOpen(Invitation $invitation): bool
    {
        if ($invitation->isRsvpFrozen()) {
            return false;
        }

        $config = self::resolveConfig($invitation);

        if ($config['is_manually_closed']) {
            return false;
        }

        if (! empty($config['deadline']) && $config['lock_after_deadline']) {
            $deadline = Carbon::parse($config['deadline'])->endOfDay();
            if (Carbon::now()->isAfter($deadline)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Lấy chi tiết trạng thái hạn chót (thời gian còn lại, nhãn hiển thị).
     *
     * @return array<string, mixed>
     */
    public static function getDeadlineStatus(Invitation $invitation): array
    {
        $config = self::resolveConfig($invitation);
        $isOpen = self::isRsvpOpen($invitation);

        $isExpired = false;
        $remainingDays = null;
        $remainingHours = null;
        $formattedDeadline = null;
        $statusLabel = 'Đang mở nhận phản hồi';
        $badgeClass = 'badge--success';

        if ($invitation->isRsvpFrozen()) {
            $statusLabel = 'Đã đóng băng hậu hôn lễ';
            $badgeClass = 'badge--frozen';
        } elseif ($config['is_manually_closed']) {
            $statusLabel = 'Đã chủ động đóng nhận phản hồi';
            $badgeClass = 'badge--closed';
        } elseif (! empty($config['deadline'])) {
            $deadline = Carbon::parse($config['deadline'])->endOfDay();
            $formattedDeadline = $deadline->format('d/m/Y');
            $now = Carbon::now();

            if ($now->isAfter($deadline)) {
                $isExpired = true;
                $daysOverdue = (int) $now->diffInDays($deadline);
                $statusLabel = 'Đã hết hạn chót (quá '.$daysOverdue.' ngày)';
                $badgeClass = $config['lock_after_deadline'] ? 'badge--closed' : 'badge--warning';
            } else {
                $remainingDays = (int) $now->diffInDays($deadline);
                $remainingHours = (int) $now->diffInHours($deadline) % 24;
                if ($remainingDays > 0) {
                    $statusLabel = "Còn {$remainingDays} ngày {$remainingHours} giờ";
                } else {
                    $statusLabel = "Hôm nay là hạn chót (còn {$remainingHours} giờ)";
                }
                $badgeClass = $remainingDays <= 2 ? 'badge--urgent' : 'badge--active';
            }
        } else {
            $statusLabel = 'Không giới hạn thời hạn';
            $badgeClass = 'badge--open';
        }

        return [
            'is_open' => $isOpen,
            'is_manually_closed' => $config['is_manually_closed'],
            'is_expired' => $isExpired,
            'deadline' => $config['deadline'],
            'formatted_deadline' => $formattedDeadline,
            'remaining_days' => $remainingDays,
            'remaining_hours' => $remainingHours,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'closed_notice' => $config['closed_notice'],
            'contact_phone' => $config['contact_phone'],
        ];
    }

    /**
     * Báo cáo điều phối xe buýt đưa đón (Shuttle Bus Manifest).
     *
     * @return array<string, mixed>
     */
    public static function getShuttleBusReport(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            return [
                'total_registrations' => 0,
                'total_passengers' => 0,
                'by_point' => [],
                'unassigned_list' => [],
            ];
        }

        $config = self::resolveConfig($invitation);
        $pickupPoints = $config['shuttle_bus']['pickup_points'];

        $responses = GuestResponse::with('guest')
            ->where('wedding_id', $wedding->id)
            ->where('attending_status', 'attending')
            ->where('shuttle_bus_needed', true)
            ->get();

        $totalRegistrations = $responses->count();
        $totalPassengers = 0;

        $pointMap = [];
        foreach ($pickupPoints as $p) {
            $pointMap[$p['name']] = [
                'point_id' => $p['id'],
                'name' => $p['name'],
                'address' => $p['address'],
                'departure_time' => $p['departure_time'],
                'coordinator_name' => $p['coordinator_name'],
                'coordinator_phone' => $p['coordinator_phone'],
                'passengers_count' => 0,
                'registrations_count' => 0,
                'guests' => [],
            ];
        }

        $unassignedList = [];

        foreach ($responses as $resp) {
            $passengers = max(1, (int) ($resp->shuttle_passengers ?: ($resp->adults_count + $resp->children_count)));
            $totalPassengers += $passengers;

            $pointName = trim((string) $resp->shuttle_pickup_point);
            $matchedKey = null;

            foreach (array_keys($pointMap) as $k) {
                if ($k === $pointName || str_contains(mb_strtolower($pointName), mb_strtolower($k)) || str_contains(mb_strtolower($k), mb_strtolower($pointName))) {
                    $matchedKey = $k;
                    break;
                }
            }

            $guestData = [
                'id' => $resp->id,
                'guest_name' => $resp->guest_name,
                'phone' => $resp->phone ?: ($resp->guest?->phone ?? '—'),
                'passengers' => $passengers,
                'table_name' => $resp->guest?->table_name ?: 'Chưa xếp bàn',
                'dietary' => $resp->dietary_requirements,
                'wishes' => $resp->wishes,
            ];

            if ($matchedKey && isset($pointMap[$matchedKey])) {
                $pointMap[$matchedKey]['passengers_count'] += $passengers;
                $pointMap[$matchedKey]['registrations_count']++;
                $pointMap[$matchedKey]['guests'][] = $guestData;
            } else {
                $unassignedList[] = array_merge($guestData, [
                    'requested_point' => $pointName ?: 'Chưa chọn điểm cụ thể',
                ]);
            }
        }

        return [
            'total_registrations' => $totalRegistrations,
            'total_passengers' => $totalPassengers,
            'by_point' => array_values($pointMap),
            'unassigned_list' => $unassignedList,
        ];
    }

    /**
     * Báo cáo khảo sát khẩu vị & thực đơn tiệc cưới (Dietary & Menu Report).
     *
     * @return array<string, mixed>
     */
    public static function getDietaryReport(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            return [
                'total_attending_responses' => 0,
                'total_confirmed_guests' => 0,
                'preference_counts' => [],
                'special_dietary_list' => [],
            ];
        }

        $responses = GuestResponse::with('guest')
            ->where('wedding_id', $wedding->id)
            ->where('attending_status', 'attending')
            ->get();

        $totalAttending = $responses->count();
        $totalGuests = 0;

        $preferenceCounts = [
            'standard' => 0,
            'vegetarian' => 0,
            'vegan' => 0,
            'halal' => 0,
            'kids' => 0,
            'other_special' => 0,
        ];

        $specialDietaryList = [];

        foreach ($responses as $resp) {
            $adults = max(1, (int) ($resp->adults_count ?: 1));
            $children = max(0, (int) ($resp->children_count ?: 0));
            $guestCount = $adults + $children;
            $totalGuests += $guestCount;

            $pref = (string) ($resp->menu_preference ?: 'standard');
            $dietText = trim((string) $resp->dietary_requirements);
            $dietLower = mb_strtolower($dietText);

            // Tự động phân loại khẩu vị dựa trên pref hoặc từ khóa ghi chú
            if ($pref === 'vegetarian' || str_contains($dietLower, 'chay') && ! str_contains($dietLower, 'thuần chay')) {
                $preferenceCounts['vegetarian'] += $guestCount;
            } elseif ($pref === 'vegan' || str_contains($dietLower, 'thuần chay')) {
                $preferenceCounts['vegan'] += $guestCount;
            } elseif ($pref === 'halal' || str_contains($dietLower, 'halal') || str_contains($dietLower, 'không ăn thịt heo') || str_contains($dietLower, 'không heo')) {
                $preferenceCounts['halal'] += $guestCount;
            } elseif ($pref === 'kids' || ($children > 0 && empty($pref))) {
                $preferenceCounts['kids'] += $children;
                $preferenceCounts['standard'] += $adults;
            } else {
                $preferenceCounts['standard'] += $guestCount;
            }

            if (! empty($dietText) || in_array($pref, ['vegetarian', 'vegan', 'halal'], true)) {
                $specialDietaryList[] = [
                    'id' => $resp->id,
                    'guest_name' => $resp->guest_name,
                    'phone' => $resp->phone ?: ($resp->guest?->phone ?? '—'),
                    'table_name' => $resp->guest?->table_name ?: 'Chưa xếp bàn',
                    'menu_preference' => $pref,
                    'requirements' => $dietText ?: ($pref === 'vegetarian' ? 'Thực đơn chay' : $pref),
                    'adults' => $adults,
                    'children' => $children,
                    'total' => $guestCount,
                ];
            }
        }

        return [
            'total_attending_responses' => $totalAttending,
            'total_confirmed_guests' => $totalGuests,
            'preference_counts' => $preferenceCounts,
            'special_dietary_list' => $specialDietaryList,
        ];
    }

    /**
     * Xuất tệp CSV Bảng Kê Xe Buýt Đưa Đón (Shuttle Bus Manifest) UTF-8 BOM.
     */
    public static function exportShuttleBusCsv(Invitation $invitation): string
    {
        $report = self::getShuttleBusReport($invitation);

        $csvLines = [];
        $csvLines[] = "\xEF\xBB\xBF".implode(',', [
            '"STT"',
            '"Điểm Đón Xe"',
            '"Giờ Khởi Hành"',
            '"Họ & Tên Khách Mời"',
            '"Số Điện Thoại"',
            '"Số Suất Chỗ"',
            '"Bàn Tiệc"',
            '"Ghi Chú Đưa Đón / Khẩu Vị"',
        ]);

        $stt = 1;
        foreach ($report['by_point'] as $point) {
            foreach ($point['guests'] as $guest) {
                $csvLines[] = implode(',', [
                    $stt++,
                    '"'.str_replace('"', '""', $point['name']).'"',
                    '"'.str_replace('"', '""', $point['departure_time']).'"',
                    '"'.str_replace('"', '""', $guest['guest_name']).'"',
                    '"'.str_replace('"', '""', $guest['phone']).'"',
                    $guest['passengers'],
                    '"'.str_replace('"', '""', $guest['table_name']).'"',
                    '"'.str_replace('"', '""', trim(($guest['dietary'] ? 'Ăn kiêng: '.$guest['dietary'].' | ' : '').($guest['wishes'] ?? ''))).'"',
                ]);
            }
        }

        foreach ($report['unassigned_list'] as $guest) {
            $csvLines[] = implode(',', [
                $stt++,
                '"'.str_replace('"', '""', 'Tự Chọn: '.$guest['requested_point']).'"',
                '"—"',
                '"'.str_replace('"', '""', $guest['guest_name']).'"',
                '"'.str_replace('"', '""', $guest['phone']).'"',
                $guest['passengers'],
                '"'.str_replace('"', '""', $guest['table_name']).'"',
                '"'.str_replace('"', '""', trim(($guest['dietary'] ? 'Ăn kiêng: '.$guest['dietary'].' | ' : '').($guest['wishes'] ?? ''))).'"',
            ]);
        }

        return implode("\r\n", $csvLines);
    }

    /**
     * Xuất tệp CSV Bảng Kê Thực Đơn & Dị Ứng Cho Bếp Trưởng (Kitchen Brief) UTF-8 BOM.
     */
    public static function exportDietaryCsv(Invitation $invitation): string
    {
        $report = self::getDietaryReport($invitation);

        $csvLines = [];
        $csvLines[] = "\xEF\xBB\xBF".implode(',', [
            '"STT"',
            '"Bàn Tiệc"',
            '"Họ & Tên Khách Mời"',
            '"Số Điện Thoại"',
            '"Khẩu Vị Đăng Ký"',
            '"Chi Tiết Yêu Cầu / Dị Ứng"',
            '"Người Lớn (Suất)"',
            '"Trẻ Em (Suất)"',
            '"Tổng Người"',
        ]);

        $stt = 1;
        foreach ($report['special_dietary_list'] as $item) {
            $csvLines[] = implode(',', [
                $stt++,
                '"'.str_replace('"', '""', $item['table_name']).'"',
                '"'.str_replace('"', '""', $item['guest_name']).'"',
                '"'.str_replace('"', '""', $item['phone']).'"',
                '"'.str_replace('"', '""', $item['menu_preference']).'"',
                '"'.str_replace('"', '""', $item['requirements']).'"',
                $item['adults'],
                $item['children'],
                $item['total'],
            ]);
        }

        return implode("\r\n", $csvLines);
    }
}
