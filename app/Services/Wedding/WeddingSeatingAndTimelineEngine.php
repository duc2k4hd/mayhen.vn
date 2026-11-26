<?php

declare(strict_types=1);

namespace App\Services\Wedding;

/**
 * Wedding Floor Plan / Seating Validator & Wedding Day Timeline Conflict Detector.
 *
 * - Kiểm tra sơ đồ bàn tiệc 2D: vượt biên sảnh, chồng lấn bàn, vượt sức chứa ghế, và trùng khách ngồi nhiều bàn.
 * - Kiểm tra lịch trình ngày cưới: thời lượng âm/không hợp lệ, trùng khung giờ của cùng người phụ trách, thiếu khoảng đệm di chuyển giữa 2 địa điểm khác nhau.
 */
class WeddingSeatingAndTimelineEngine
{
    /**
     * Kiểm tra tính hợp lệ của sơ đồ mặt bằng & xếp chỗ bàn tiệc 2D.
     *
     * @param  array<string, mixed>  $floorplan  {room_width: int, room_height: int, tables: list<array>}
     * @return array{valid: bool, total_tables: int, total_capacity: int, total_assigned: int, issues: list<array{type: string, severity: 'error'|'warning', message: string}>}
     */
    public function validateFloorPlan(array $floorplan): array
    {
        $roomW = max(200, (int) ($floorplan['room_width'] ?? 800));
        $roomH = max(200, (int) ($floorplan['room_height'] ?? 520));
        $tables = (array) ($floorplan['tables'] ?? []);

        $issues = [];
        $totalCapacity = 0;
        $totalAssigned = 0;
        $guestPlacement = []; // guest_name_normalized => table_label

        $count = count($tables);
        for ($i = 0; $i < $count; $i++) {
            $t = $tables[$i];
            $label = (string) ($t['label'] ?? ('Bàn '.($i + 1)));
            $x = (float) ($t['x'] ?? 100);
            $y = (float) ($t['y'] ?? 100);
            $radius = max(20.0, (float) ($t['radius'] ?? 36));
            $capacity = max(1, (int) ($t['capacity'] ?? 10));
            $guests = array_values(array_filter(array_map(
                static fn ($g) => trim((string) $g),
                (array) ($t['guests'] ?? [])
            )));

            $totalCapacity += $capacity;
            $totalAssigned += count($guests);

            // 1. Vượt biên sảnh
            if ($x - $radius < 0 || $y - $radius < 0 || $x + $radius > $roomW || $y + $radius > $roomH) {
                $issues[] = [
                    'type' => 'out_of_bounds',
                    'severity' => 'error',
                    'message' => "{$label} đang nằm vượt ra ngoài phạm vi sảnh tiệc ({$roomW}×{$roomH}).",
                ];
            }

            // 2. Vượt sức chứa bàn
            if (count($guests) > $capacity) {
                $issues[] = [
                    'type' => 'capacity_exceeded',
                    'severity' => 'error',
                    'message' => "{$label} đang xếp ".count($guests)." khách nhưng sức chứa tối đa chỉ có {$capacity} ghế.",
                ];
            }

            // 3. Kiểm tra trùng khách ngồi 2 bàn khác nhau
            foreach ($guests as $guestName) {
                $key = mb_strtolower($guestName, 'UTF-8');
                if (isset($guestPlacement[$key])) {
                    $issues[] = [
                        'type' => 'duplicate_guest',
                        'severity' => 'error',
                        'message' => "Khách mời \"{$guestName}\" đang bị xếp trùng ở cả {$guestPlacement[$key]} và {$label}.",
                    ];
                } else {
                    $guestPlacement[$key] = $label;
                }
            }

            // 4. Kiểm tra chồng lấn hình học giữa 2 bàn (khoảng cách tâm < r1 + r2 + 8px lối đi)
            for ($j = $i + 1; $j < $count; $j++) {
                $t2 = $tables[$j];
                $label2 = (string) ($t2['label'] ?? ('Bàn '.($j + 1)));
                $x2 = (float) ($t2['x'] ?? 100);
                $y2 = (float) ($t2['y'] ?? 100);
                $r2 = max(20.0, (float) ($t2['radius'] ?? 36));
                $dist = hypot($x - $x2, $y - $y2);

                if ($dist < ($radius + $r2)) {
                    $issues[] = [
                        'type' => 'table_overlap',
                        'severity' => 'error',
                        'message' => "{$label} và {$label2} đang bị chồng lấn vị trí trên mặt bằng.",
                    ];
                } elseif ($dist < ($radius + $r2 + 12)) {
                    $issues[] = [
                        'type' => 'tight_aisle',
                        'severity' => 'warning',
                        'message' => "Lối đi giữa {$label} và {$label2} khá hẹp, nên kéo giãn thêm để phục vụ thuận tiện.",
                    ];
                }
            }
        }

        $hasError = count(array_filter($issues, static fn ($it) => $it['severity'] === 'error')) > 0;

        return [
            'valid' => ! $hasError,
            'total_tables' => $count,
            'total_capacity' => $totalCapacity,
            'total_assigned' => $totalAssigned,
            'issues' => $issues,
        ];
    }

    /**
     * Kiểm tra xung đột lịch trình ngày cưới (Wedding Day Timeline Conflict Detector).
     *
     * @param  list<array<string, mixed>>  $items  [{title, start_time: 'HH:MM', end_time: 'HH:MM', location, assignee}]
     * @return array{valid: bool, conflicts: list<array{type: string, severity: 'error'|'warning', message: string}>, sorted_items: list<array<string, mixed>>}
     */
    public function detectTimelineConflicts(array $items): array
    {
        $parsed = [];
        $conflicts = [];

        foreach ($items as $idx => $item) {
            $title = trim((string) ($item['title'] ?? ('Mục #'.($idx + 1))));
            $startMin = $this->parseTimeToMinutes((string) ($item['start_time'] ?? ''));
            $endMin = $this->parseTimeToMinutes((string) ($item['end_time'] ?? ''));

            if ($startMin === null || $endMin === null) {
                $conflicts[] = [
                    'type' => 'invalid_time',
                    'severity' => 'error',
                    'message' => "Mục \"{$title}\" có định dạng giờ không hợp lệ (cần HH:MM).",
                ];

                continue;
            }

            if ($endMin <= $startMin) {
                $conflicts[] = [
                    'type' => 'negative_duration',
                    'severity' => 'error',
                    'message' => "Mục \"{$title}\" có giờ kết thúc ({$item['end_time']}) nhỏ hơn hoặc bằng giờ bắt đầu ({$item['start_time']}).",
                ];
            }

            $parsed[] = array_merge($item, [
                'title' => $title,
                '_start_min' => $startMin,
                '_end_min' => $endMin,
                'duration_min' => max(0, $endMin - $startMin),
            ]);
        }

        usort($parsed, static fn ($a, $b) => $a['_start_min'] <=> $b['_start_min']);

        $n = count($parsed);
        for ($i = 0; $i < $n; $i++) {
            $a = $parsed[$i];
            $assigneeA = mb_strtolower(trim((string) ($a['assignee'] ?? '')), 'UTF-8');
            $locA = mb_strtolower(trim((string) ($a['location'] ?? '')), 'UTF-8');

            // Kiểm tra khoảng đệm di chuyển giữa 2 mục liên tiếp khác địa điểm
            if ($i + 1 < $n) {
                $next = $parsed[$i + 1];
                $locNext = mb_strtolower(trim((string) ($next['location'] ?? '')), 'UTF-8');
                $gap = $next['_start_min'] - $a['_end_min'];
                if ($locA !== '' && $locNext !== '' && $locA !== $locNext && $gap < 15) {
                    $conflicts[] = [
                        'type' => 'missing_travel_buffer',
                        'severity' => 'warning',
                        'message' => "Chuyển địa điểm từ \"{$a['location']}\" ({$a['title']}) sang \"{$next['location']}\" ({$next['title']}) chỉ có {$gap} phút đệm; nên bố trí tối thiểu 15–30 phút di chuyển.",
                    ];
                }
            }

            // Kiểm tra trùng lịch của cùng người phụ trách / nhà cung cấp
            for ($j = $i + 1; $j < $n; $j++) {
                $b = $parsed[$j];
                if ($b['_start_min'] >= $a['_end_min']) {
                    break;
                }
                $assigneeB = mb_strtolower(trim((string) ($b['assignee'] ?? '')), 'UTF-8');
                if ($assigneeA !== '' && $assigneeA === $assigneeB) {
                    $conflicts[] = [
                        'type' => 'assignee_overlap',
                        'severity' => 'error',
                        'message' => "\"{$a['assignee']}\" đang bị trùng lịch giữa \"{$a['title']}\" ({$a['start_time']}–{$a['end_time']}) và \"{$b['title']}\" ({$b['start_time']}–{$b['end_time']}).",
                    ];
                }
            }
        }

        $hasError = count(array_filter($conflicts, static fn ($c) => $c['severity'] === 'error')) > 0;

        return [
            'valid' => ! $hasError,
            'conflicts' => $conflicts,
            'sorted_items' => array_map(static function (array $row): array {
                unset($row['_start_min'], $row['_end_min']);

                return $row;
            }, $parsed),
        ];
    }

    /**
     * Sinh sơ đồ mặt bằng mẫu 8 bàn tròn cho dự án mới.
     *
     * @return array<string, mixed>
     */
    public function createDefaultFloorPlan(): array
    {
        $tables = [];
        $positions = [
            [180, 170], [340, 170], [500, 170], [660, 170],
            [180, 340], [340, 340], [500, 340], [660, 340],
        ];
        foreach ($positions as $idx => [$x, $y]) {
            $tables[] = [
                'id' => 'T'.($idx + 1),
                'label' => $idx < 2 ? 'Bàn VIP '.($idx + 1) : 'Bàn '.($idx + 1),
                'x' => $x,
                'y' => $y,
                'radius' => 38,
                'capacity' => 10,
                'zone' => $idx < 2 ? 'Gia đình hai họ' : 'Khách mời',
                'guests' => [],
            ];
        }

        return [
            'room_width' => 840,
            'room_height' => 480,
            'stage' => ['x' => 270, 'y' => 20, 'width' => 300, 'height' => 64, 'label' => 'Sân khấu chính'],
            'tables' => $tables,
        ];
    }

    protected function parseTimeToMinutes(string $hhmm): ?int
    {
        if (! preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', trim($hhmm), $m)) {
            return null;
        }

        return ((int) $m[1]) * 60 + (int) $m[2];
    }
}
