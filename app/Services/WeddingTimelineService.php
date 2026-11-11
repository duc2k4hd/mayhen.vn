<?php

namespace App\Services;

use App\Models\Invitation;
use Carbon\Carbon;

class WeddingTimelineService
{
    /**
     * 10 Biểu Tượng SVG Cho Các Thời Khắc Trọng Đại Ngày Cưới.
     *
     * @return array<string, array{name: string, icon: string, symbol: string}>
     */
    public static function getAvailableIcons(): array
    {
        return [
            'cocktail' => [
                'name' => 'Đón Khách & Tiệc Nhẹ (Welcome Cocktail)',
                'icon' => '🍸',
                'symbol' => 'cocktail',
            ],
            'rings' => [
                'name' => 'Nghi Thức Lễ Thành Hôn (Wedding Ceremony)',
                'icon' => '💍',
                'symbol' => 'rings',
            ],
            'champagne' => [
                'name' => 'Rót Rượu & Nâng Ly Chúc Mừng (Toast & Champagne)',
                'icon' => '🥂',
                'symbol' => 'champagne',
            ],
            'feast' => [
                'name' => 'Khai Tiệc Chiêu Đãi (Banquet Commences)',
                'icon' => '🍽️',
                'symbol' => 'feast',
            ],
            'camera' => [
                'name' => 'Chụp Ảnh Kỷ Niệm (Photo Session)',
                'icon' => '📸',
                'symbol' => 'camera',
            ],
            'music' => [
                'name' => 'Ban Nhạc & Hòa Tấu (Acoustic Live Music)',
                'icon' => '🎻',
                'symbol' => 'music',
            ],
            'sparkles' => [
                'name' => 'Trò Chơi Tương Tác & Quà Tặng (Interactive Games)',
                'icon' => '✨',
                'symbol' => 'sparkles',
            ],
            'fireworks' => [
                'name' => 'Pháo Hoa & Khiêu Vũ (Fireworks & First Dance)',
                'icon' => '🎆',
                'symbol' => 'fireworks',
            ],
            'tea' => [
                'name' => 'Lễ Gia Tiên & Dâng Trà (Traditional Tea Ceremony)',
                'icon' => '🍵',
                'symbol' => 'tea',
            ],
            'carriage' => [
                'name' => 'Rước Dâu & Di Chuyển (Procession & Arrival)',
                'icon' => '🚗',
                'symbol' => 'carriage',
            ],
        ];
    }

    /**
     * 4 Kịch Bản Hôn Lễ Chuẩn Mực Điển Hình Tại Việt Nam.
     *
     * @return array<string, array{name: string, description: string, items: array<int, array{time: string, title: string, location: string, description: string, icon: string}>}>
     */
    public static function getPresetScenarios(): array
    {
        return [
            'evening_banquet' => [
                'name' => 'Tiệc Cưới Tối Khách Sạn / Trung Tâm Hội Nghị',
                'description' => 'Kịch bản kinh điển, trang trọng cho tiệc cưới buổi tối tại sảnh tiệc cao cấp.',
                'items' => [
                    [
                        'time' => '17:30',
                        'title' => 'Đón Tiếp Quan Khách & Chụp Ảnh Kỷ Niệm',
                        'location' => 'Sảnh đón & Photo Booth',
                        'description' => 'Thưởng thức trà bánh nhẹ, ký tên lưu bút và lưu giữ khoảnh khắc cùng cô dâu chú rể.',
                        'icon' => 'cocktail',
                    ],
                    [
                        'time' => '18:30',
                        'title' => 'Nghi Thức Lễ Thành Hôn & Trao Nhẫn Cưới',
                        'location' => 'Lễ đường Grand Ballroom',
                        'description' => 'Khoảnh khắc trao nhẫn thiêng liêng, cắt bánh tình yêu và rót tháp rượu hạnh phúc.',
                        'icon' => 'rings',
                    ],
                    [
                        'time' => '19:00',
                        'title' => 'Khai Tiệc Chiêu Đãi & Ban Nhạc Hòa Tấu',
                        'location' => 'Sảnh tiệc chính',
                        'description' => 'Thưởng thức set menu ẩm thực cao cấp trong không gian âm nhạc acoustic du dương.',
                        'icon' => 'feast',
                    ],
                    [
                        'time' => '20:00',
                        'title' => 'Trò Chơi Tương Tác & Bốc Thăm May Mắn',
                        'location' => 'Sân khấu trung tâm',
                        'description' => 'Những câu chuyện vui vẻ của cặp đôi cùng các phần quà kỷ niệm bất ngờ dành cho khách.',
                        'icon' => 'sparkles',
                    ],
                    [
                        'time' => '21:00',
                        'title' => 'Cảm Ơn Quan Khách & Lời Chào Tạm Biệt',
                        'location' => 'Khu vực sảnh đón',
                        'description' => 'Cô dâu chú rể và hai bên gia đình gửi quà cảm ơn chân thành đến toàn thể quý khách.',
                        'icon' => 'champagne',
                    ],
                ],
            ],
            'sunset_beach' => [
                'name' => 'Lễ Cưới Bãi Biển / Resort Hoàng Hôn (Sunset Beach)',
                'description' => 'Phong cách lãng mạn, phóng khoáng bên bờ biển vào thời khắc hoàng hôn rực rỡ.',
                'items' => [
                    [
                        'time' => '16:00',
                        'title' => 'Đón Khách Tại Bãi Cỏ Ven Biển & Welcome Drink',
                        'location' => 'Bãi cỏ hướng biển',
                        'description' => 'Thưởng thức nước dừa tươi, cocktail nhiệt đới và ngắm gió biển trong lành.',
                        'icon' => 'cocktail',
                    ],
                    [
                        'time' => '17:00',
                        'title' => 'Nghi Thức Trao Lời Thề Nguyện (Sunset Vows)',
                        'location' => 'Vòm hoa bờ cát',
                        'description' => 'Cặp đôi trao nhau lời hẹn ước trọn đời dưới ánh hoàng hôn nhuộm vàng mặt biển.',
                        'icon' => 'rings',
                    ],
                    [
                        'time' => '18:00',
                        'title' => 'Chụp Ảnh Hoàng Hôn & Golden Hour',
                        'location' => 'Bờ biển',
                        'description' => 'Ghi lại những bức ảnh kỷ niệm tuyệt đẹp trong ánh sáng vàng điện ảnh rực rỡ.',
                        'icon' => 'camera',
                    ],
                    [
                        'time' => '18:45',
                        'title' => 'Dạ Tiệc Hải Sản Ngoài Trời Lung Linh Ánh Đèn',
                        'location' => 'Khuôn viên hồ bơi resort',
                        'description' => 'Thưởng thức ẩm thực miền biển, nâng ly chúc mừng dưới bầu trời đêm ngập sao.',
                        'icon' => 'feast',
                    ],
                    [
                        'time' => '20:30',
                        'title' => 'After-Party, Khiêu Vũ & Bắn Pháo Hoa',
                        'location' => 'Beach Lounge',
                        'description' => 'Giai điệu rộn ràng, vũ điệu sôi động và màn pháo hoa chúc phúc lung linh.',
                        'icon' => 'fireworks',
                    ],
                ],
            ],
            'traditional_ancestral' => [
                'name' => 'Lễ Gia Tiên & Rước Dâu Truyền Thống',
                'description' => 'Nghi lễ trang nghiêm, tôn vinh cội nguồn và đạo hiếu của dân tộc Việt Nam.',
                'items' => [
                    [
                        'time' => '07:30',
                        'title' => 'Đoàn Nhà Trai Xuất Phát Rước Dâu',
                        'location' => 'Tư gia nhà trai',
                        'description' => 'Chuẩn bị đội hình bê tráp, sính lễ truyền thống khởi hành sang nhà gái.',
                        'icon' => 'carriage',
                    ],
                    [
                        'time' => '08:30',
                        'title' => 'Lễ Trao Mâm Quả & Nhập Phòng Khách',
                        'location' => 'Tư gia nhà gái',
                        'description' => 'Trao nhận quả sính lễ, đại diện hai họ phát biểu chúc phúc.',
                        'icon' => 'tea',
                    ],
                    [
                        'time' => '09:00',
                        'title' => 'Nghi Thức Gia Tiên & Dâng Trà Đấng Sinh Thành',
                        'location' => 'Bàn thờ gia tiên nhà gái',
                        'description' => 'Thắp hương tưởng nhớ tổ tiên, dâng trà cảm tạ công ơn dưỡng dục sinh thành.',
                        'icon' => 'rings',
                    ],
                    [
                        'time' => '10:15',
                        'title' => 'Lễ Rước Nàng Dâu Về Nhà Chồng',
                        'location' => 'Tư gia nhà trai',
                        'description' => 'Nghi thức mừng dâu mới, ra mắt gia tiên và họ hàng đàng trai.',
                        'icon' => 'carriage',
                    ],
                    [
                        'time' => '11:30',
                        'title' => 'Tiệc Mừng Thân Mật Gia Đình & Họ Hàng',
                        'location' => 'Tư gia hoặc nhà hàng',
                        'description' => 'Bữa cơm sum vầy chúc mừng hai họ nên duyên thông gia bền chặt.',
                        'icon' => 'feast',
                    ],
                ],
            ],
            'intimate_garden' => [
                'name' => 'Tiệc Cưới Sân Vườn Ngoài Trời Thân Mật (Garden Party)',
                'description' => 'Không gian xanh mát, tự nhiên và gắn kết cho tiệc cưới ngoài trời.',
                'items' => [
                    [
                        'time' => '15:30',
                        'title' => 'Đón Khách Check-in & Ký Tên Sổ Lưu Bút',
                        'location' => 'Lối vào khu vườn',
                        'description' => 'Ký tên chúc phúc, thưởng thức nước quả tươi và chụp ảnh kỷ niệm.',
                        'icon' => 'camera',
                    ],
                    [
                        'time' => '16:30',
                        'title' => 'Lễ Thành Hôn Dưới Vòm Hoa Sân Vườn',
                        'location' => 'Khu vườn trung tâm',
                        'description' => 'Nghi thức trao nhẫn giản dị, ấm cúng trong tiếng đàn guitar mộc mạc.',
                        'icon' => 'rings',
                    ],
                    [
                        'time' => '17:30',
                        'title' => 'Tiệc Trà Chiều & Ban Nhạc Acoustic',
                        'location' => 'Hiên nhà gỗ',
                        'description' => 'Giao lưu trò chuyện thong thả cùng bạn bè và người thân yêu.',
                        'icon' => 'music',
                    ],
                    [
                        'time' => '18:30',
                        'title' => 'Tiệc Nướng BBQ & Bữa Tối Thân Mật',
                        'location' => 'Bàn tiệc dài ngoài trời',
                        'description' => 'Bữa tối ấm cúng dưới dây đèn giăng lấp lánh như cổ tích.',
                        'icon' => 'feast',
                    ],
                    [
                        'time' => '20:00',
                        'title' => 'Đốt Lửa Trại & Khiêu Vũ First Dance',
                        'location' => 'Khu lửa trại',
                        'description' => 'Cặp đôi nhảy điệu khiêu vũ đầu tiên trong tiếng reo hò của bạn bè.',
                        'icon' => 'fireworks',
                    ],
                ],
            ],
        ];
    }

    /**
     * Cấu hình mặc định cho Timeline ngày cưới.
     *
     * @return array<string, mixed>
     */
    public static function getDefaultConfig(?Invitation $invitation = null): array
    {
        $presets = self::getPresetScenarios();
        $defaultScenario = $presets['evening_banquet'];

        $wedding = $invitation?->wedding;
        $legacyTimeline = $wedding?->extra_data['timeline'] ?? null;

        $items = $defaultScenario['items'];
        if (! empty($legacyTimeline) && is_array($legacyTimeline)) {
            $parsed = [];
            foreach ($legacyTimeline as $idx => $t) {
                if (is_array($t) && ! empty($t['time']) && ! empty($t['title'])) {
                    $parsed[] = [
                        'time' => trim((string) $t['time']),
                        'title' => trim((string) $t['title']),
                        'location' => trim((string) ($t['location'] ?? ($wedding->venue_name ?? 'Sảnh tiệc'))),
                        'description' => trim((string) ($t['description'] ?? '')),
                        'icon' => $t['icon'] ?? ($idx === 0 ? 'cocktail' : ($idx === 1 ? 'rings' : 'feast')),
                    ];
                }
            }
            if (! empty($parsed)) {
                $items = $parsed;
            }
        }

        return [
            'enabled' => true,
            'title' => 'Lịch Trình Hôn Lễ (Wedding Program)',
            'subtitle' => 'Thời gian biểu chi tiết các nghi thức và hoạt động trong ngày trọng đại',
            'scenario_key' => 'evening_banquet',
            'calendar_reminder_hours' => 2,
            'items' => $items,
        ];
    }

    /**
     * Phân giải cấu hình Timeline ngày cưới hoàn chỉnh.
     *
     * @return array<string, mixed>
     */
    public static function resolveTimelineConfig(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $default = self::getDefaultConfig($invitation);
        $saved = $customConfig['timeline'] ?? [];

        $enabled = isset($saved['enabled']) ? (bool) $saved['enabled'] : $default['enabled'];
        $title = ! empty($saved['title']) ? (string) $saved['title'] : $default['title'];
        $subtitle = ! empty($saved['subtitle']) ? (string) $saved['subtitle'] : $default['subtitle'];
        $scenarioKey = ! empty($saved['scenario_key']) ? (string) $saved['scenario_key'] : $default['scenario_key'];
        $calendarReminder = isset($saved['calendar_reminder_hours']) ? (int) $saved['calendar_reminder_hours'] : 2;

        $items = ! empty($saved['items']) && is_array($saved['items']) ? $saved['items'] : $default['items'];

        $icons = self::getAvailableIcons();
        foreach ($items as &$item) {
            $iconKey = $item['icon'] ?? 'rings';
            $item['icon_symbol'] = $icons[$iconKey]['icon'] ?? '💍';
        }

        return [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'scenario_key' => $scenarioKey,
            'calendar_reminder_hours' => $calendarReminder,
            'items' => $items,
        ];
    }

    /**
     * Cập nhật danh sách mốc thời gian ngày cưới vào thiệp cưới.
     *
     * @param  array<string, mixed>  $data
     */
    public static function updateTimeline(Invitation $invitation, array $data): Invitation
    {
        $current = self::resolveTimelineConfig($invitation);

        $enabled = isset($data['enabled']) ? (bool) $data['enabled'] : true;
        $title = ! empty($data['title']) ? trim((string) $data['title']) : $current['title'];
        $subtitle = ! empty($data['subtitle']) ? trim((string) $data['subtitle']) : $current['subtitle'];
        $scenarioKey = ! empty($data['scenario_key']) ? trim((string) $data['scenario_key']) : $current['scenario_key'];
        $calendarReminder = isset($data['calendar_reminder_hours']) ? max(1, (int) $data['calendar_reminder_hours']) : 2;

        $items = [];
        if (! empty($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                if (! empty($item['time']) && ! empty($item['title'])) {
                    $items[] = [
                        'time' => trim((string) $item['time']),
                        'title' => trim((string) $item['title']),
                        'location' => trim((string) ($item['location'] ?? '')),
                        'description' => trim((string) ($item['description'] ?? '')),
                        'icon' => ! empty($item['icon']) ? trim((string) $item['icon']) : 'rings',
                    ];
                }
            }
        }
        if (empty($items)) {
            $items = $current['items'];
        }

        $timelineConfig = [
            'enabled' => $enabled,
            'title' => $title,
            'subtitle' => $subtitle,
            'scenario_key' => $scenarioKey,
            'calendar_reminder_hours' => $calendarReminder,
            'items' => $items,
        ];

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $customConfig['timeline'] = $timelineConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // Đồng bộ ngược vào wedding->extra_data['timeline']
        $wedding = $invitation->wedding;
        if ($wedding) {
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : [];
            $extra['timeline'] = array_map(function ($it) {
                return [
                    'time' => $it['time'],
                    'title' => $it['title'],
                    'location' => $it['location'] ?? '',
                ];
            }, $items);
            $wedding->extra_data = $extra;
            $wedding->save();
        }

        AuditLogger::log(
            'TIMELINE_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật lịch trình ngày cưới cho thiệp #{$invitation->id}",
            [
                'invitation_id' => $invitation->id,
                'items_count' => count($items),
                'scenario_key' => $scenarioKey,
            ]
        );

        return $invitation;
    }

    /**
     * Tạo đường dẫn thêm vào các ứng dụng lịch phổ biến (Google, Outlook, Yahoo, Apple iCal).
     *
     * @return array<string, string>
     */
    public static function generateCalendarLinks(Invitation $invitation, ?array $event = null): array
    {
        $wedding = $invitation->wedding;
        $coupleName = ($wedding->groom_name ?? 'Chú rể').' & '.($wedding->bride_name ?? 'Cô dâu');
        $weddingDate = $wedding?->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::now()->addDays(7);

        $eventTitle = ! empty($event['title'])
            ? "Lễ Cưới {$coupleName} — {$event['title']}"
            : "Lễ Cưới Trọng Đại: {$coupleName}";

        // Phân tích giờ bắt đầu
        $timeStr = ! empty($event['time']) ? $event['time'] : '17:30';
        $timeParts = explode(':', str_replace(['h', 'H'], ':', $timeStr));
        $hour = isset($timeParts[0]) ? (int) $timeParts[0] : 17;
        $minute = isset($timeParts[1]) ? (int) $timeParts[1] : 30;

        $startDateTime = $weddingDate->copy()->setTime($hour, $minute, 0);
        $endDateTime = $startDateTime->copy()->addHours(4);

        $location = trim(($wedding->venue_name ? $wedding->venue_name.' — ' : '').($wedding->venue_address ?: 'Việt Nam'));
        $description = "Trân trọng kính mời quý khách tham dự hôn lễ của đôi uyên ương {$coupleName}.\n"
            .(! empty($event['description']) ? "Nội dung: {$event['description']}\n" : '')
            .'Xem thiệp cưới trực tuyến tại: '.url('/thiep/'.$invitation->slug);

        // Định dạng thời gian UTC cho Google Calendar
        $utcStart = $startDateTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $utcEnd = $endDateTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');

        // 1. Google Calendar URL
        $googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            .'&text='.urlencode($eventTitle)
            .'&dates='.$utcStart.'/'.$utcEnd
            .'&details='.urlencode($description)
            .'&location='.urlencode($location);

        // 2. Outlook Web URL
        $outlookUrl = 'https://outlook.live.com/calendar/0/action/compose?allday=false'
            .'&subject='.urlencode($eventTitle)
            .'&startdt='.urlencode($startDateTime->toIso8601String())
            .'&enddt='.urlencode($endDateTime->toIso8601String())
            .'&body='.urlencode($description)
            .'&location='.urlencode($location);

        // 3. Office 365 URL
        $office365Url = 'https://outlook.office.com/calendar/0/action/compose?allday=false'
            .'&subject='.urlencode($eventTitle)
            .'&startdt='.urlencode($startDateTime->toIso8601String())
            .'&enddt='.urlencode($endDateTime->toIso8601String())
            .'&body='.urlencode($description)
            .'&location='.urlencode($location);

        // 4. Yahoo Calendar URL
        $yahooUrl = 'https://calendar.yahoo.com/?v=60'
            .'&title='.urlencode($eventTitle)
            .'&st='.$utcStart
            .'&et='.$utcEnd
            .'&desc='.urlencode($description)
            .'&in_loc='.urlencode($location);

        // 5. Đường dẫn tải iCal (.ics) cho Apple Calendar
        $icalRoute = route('invitation.timeline.download_ics', $invitation->slug);

        return [
            'google' => $googleUrl,
            'outlook' => $outlookUrl,
            'office365' => $office365Url,
            'yahoo' => $yahooUrl,
            'ical' => $icalRoute,
        ];
    }

    /**
     * Xuất tệp iCalendar chuẩn RFC 5545 (.ics) cho thiết bị Apple và lịch cá nhân.
     */
    public static function generateIcsContent(Invitation $invitation): string
    {
        $wedding = $invitation->wedding;
        $coupleName = ($wedding->groom_name ?? 'Chú rể').' & '.($wedding->bride_name ?? 'Cô dâu');
        $weddingDate = $wedding?->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::now()->addDays(7);

        $startDateTime = $weddingDate->copy()->setTime(17, 30, 0);
        $endDateTime = $startDateTime->copy()->addHours(4);

        $utcStart = $startDateTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $utcEnd = $endDateTime->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $utcStamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $uid = 'wedding-'.($invitation->slug ?: $invitation->id).'@mayhen.vn';

        $summary = "Lễ Cưới: {$coupleName}";
        $location = trim(($wedding->venue_name ? $wedding->venue_name.' — ' : '').($wedding->venue_address ?: 'Sảnh tiệc cưới'));
        $description = "Trân trọng kính mời quý khách tham dự hôn lễ của {$coupleName}. Chi tiết thiệp mời: ".url('/thiep/'.$invitation->slug);

        $ics = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//May Hen Wedding Atelier//Wedding Day Timeline//VI',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$utcStamp,
            'DTSTART:'.$utcStart,
            'DTEND:'.$utcEnd,
            'SUMMARY:'.addcslashes($summary, ',;'),
            'LOCATION:'.addcslashes($location, ',;'),
            'DESCRIPTION:'.addcslashes($description, ',;'),
            'URL:'.url('/thiep/'.$invitation->slug),
            'STATUS:CONFIRMED',
            // Báo thức nhắc nhở trước 1 ngày (1440 phút)
            'BEGIN:VALARM',
            'TRIGGER:-P1D',
            'ACTION:DISPLAY',
            'DESCRIPTION:Nhắc nhở ngày mai tham dự lễ cưới '.$coupleName,
            'END:VALARM',
            // Báo thức nhắc nhở trước 2 giờ
            'BEGIN:VALARM',
            'TRIGGER:-PT2H',
            'ACTION:DISPLAY',
            'DESCRIPTION:Chuẩn bị trang phục tham dự lễ cưới '.$coupleName,
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $ics);
    }
}
