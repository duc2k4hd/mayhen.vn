<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;

class SeatingCapacityGuardianService
{
    /**
     * Phân tích chuyên sâu sức khỏe phân bàn tiệc cưới và cảnh báo quá tải / thiếu hụt.
     *
     * @return array{
     *     health_score: int,
     *     status: string,
     *     status_label: string,
     *     metrics: array{
     *         total_tables: int,
     *         total_capacity: int,
     *         total_assigned: int,
     *         total_unassigned: int,
     *         overall_occupancy_percent: float,
     *         overfilled_tables_count: int,
     *         underfilled_tables_count: int,
     *         optimal_tables_count: int,
     *         recommended_official_tables: int,
     *         recommended_buffer_tables: int
     *     },
     *     table_analyses: array<int, array<string, mixed>>,
     *     issues: array<int, array{severity: string, table_name: string, message: string, suggestion: string}>
     * }
     */
    public function analyzeSeatingHealth(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $tables = $wedding->tables()->with(['guests.response'])->get();
        $unassignedGuests = $wedding->guests()
            ->with('response')
            ->whereNull('table_id')
            ->get();

        $totalTables = $tables->count();
        $totalCapacity = (int) $tables->sum('capacity');
        $totalAssigned = (int) $tables->sum('assigned_attendees_count');
        $totalUnassigned = (int) $unassignedGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });

        $totalAttending = $totalAssigned + $totalUnassigned;

        $overfilledCount = 0;
        $underfilledCount = 0;
        $optimalCount = 0;

        $tableAnalyses = [];
        $issues = [];

        foreach ($tables as $table) {
            $assigned = $table->assigned_attendees_count;
            $capacity = max(1, $table->capacity);
            $occupancyPercent = round(($assigned / $capacity) * 100, 1);

            $hasSeniorFamily = false;
            $hasYoungFriends = false;

            foreach ($table->guests as $guest) {
                $grp = mb_strtolower((string) $guest->group_name, 'UTF-8');
                if (str_contains($grp, 'gia đình') || str_contains($grp, 'họ hàng') || str_contains($grp, 'người lớn') || str_contains($grp, 'bác') || str_contains($grp, 'chú')) {
                    $hasSeniorFamily = true;
                }
                if (str_contains($grp, 'bạn') || str_contains($grp, 'đồng nghiệp') || str_contains($grp, 'phượt') || str_contains($grp, 'quẩy') || str_contains($grp, 'cấp 3') || str_contains($grp, 'đại học')) {
                    $hasYoungFriends = true;
                }
            }

            $hasMixedGenerations = $hasSeniorFamily && $hasYoungFriends;

            if ($assigned > $capacity) {
                $status = 'overfilled';
                $overfilledCount++;
                $overSeats = $assigned - $capacity;
                $issues[] = [
                    'severity' => 'critical',
                    'table_name' => $table->name,
                    'message' => "Bàn \"{$table->name}\" đang bị quá tải ({$assigned}/{$capacity} ghế, thừa {$overSeats} người).",
                    'suggestion' => 'Chuyển bớt khách sang bàn trống hoặc tăng sức chứa của bàn lên.',
                ];
            } elseif ($assigned < ($capacity * 0.5) && $assigned > 0) {
                $status = 'underfilled';
                $underfilledCount++;
                $issues[] = [
                    'severity' => 'warning',
                    'table_name' => $table->name,
                    'message' => "Bàn \"{$table->name}\" xếp quá thưa (mới có {$assigned}/{$capacity} ghế).",
                    'suggestion' => 'Nên ghép thêm khách hoặc dồn nhóm để tránh lãng phí suất tiệc của nhà hàng.',
                ];
            } else {
                $status = 'optimal';
                $optimalCount++;
            }

            if ($hasMixedGenerations) {
                $issues[] = [
                    'severity' => 'info',
                    'table_name' => $table->name,
                    'message' => "Bàn \"{$table->name}\" đang ghép chung nhóm Người Lớn Tuổi / Họ Hàng với nhóm Bạn Bè Trẻ.",
                    'suggestion' => 'Nên cân nhắc tách riêng để các bác lớn tuổi có không gian trò chuyện ấm cúng, không bị ồn ào.',
                ];
            }

            $tableAnalyses[] = [
                'id' => $table->id,
                'name' => $table->name,
                'shape' => $table->shape,
                'capacity' => $capacity,
                'assigned_attendees' => $assigned,
                'occupancy_percent' => $occupancyPercent,
                'status' => $status,
                'has_mixed_generations' => $hasMixedGenerations,
            ];
        }

        // Dự báo số bàn đặt nhà hàng
        $recommendedOfficial = (int) ceil($totalAttending / 10);
        $recommendedBuffer = (int) max(1, ceil($recommendedOfficial * 0.1));

        // Tính điểm số sức khỏe (Health score: 0 - 100)
        $score = 100;
        $score -= ($overfilledCount * 20); // Mỗi bàn quá tải trừ 20 điểm
        $score -= ($underfilledCount * 10); // Mỗi bàn quá thưa trừ 10 điểm
        if ($totalUnassigned > 0) {
            $score -= min(30, (int) ceil($totalUnassigned / 2)); // Khách chưa xếp bàn bị trừ điểm
        }
        $score = max(0, min(100, $score));

        $statusOverall = match (true) {
            $score >= 85 => 'excellent',
            $score >= 60 => 'good',
            default => 'warning'
        };

        $statusLabel = match ($statusOverall) {
            'excellent' => 'Sơ đồ bàn tiệc hoàn hảo',
            'good' => 'Khá tốt, cần tinh chỉnh nhẹ',
            default => 'Cần xử lý quá tải / thiếu bàn'
        };

        $overallOccupancy = $totalCapacity > 0 ? round(($totalAssigned / $totalCapacity) * 100, 1) : 0;

        return [
            'health_score' => $score,
            'status' => $statusOverall,
            'status_label' => $statusLabel,
            'metrics' => [
                'total_tables' => $totalTables,
                'total_capacity' => $totalCapacity,
                'total_assigned' => $totalAssigned,
                'total_unassigned' => $totalUnassigned,
                'overall_occupancy_percent' => (float) $overallOccupancy,
                'overfilled_tables_count' => $overfilledCount,
                'underfilled_tables_count' => $underfilledCount,
                'optimal_tables_count' => $optimalCount,
                'recommended_official_tables' => $recommendedOfficial,
                'recommended_buffer_tables' => $recommendedBuffer,
            ],
            'table_analyses' => $tableAnalyses,
            'issues' => $issues,
        ];
    }
}
