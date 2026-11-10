<?php

namespace App\Services;

use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeddingReadinessRadarService
{
    /**
     * Quét toàn bộ đám cưới sắp diễn ra và tính toán điểm sẵn sàng (0 - 100).
     *
     * @param  int  $daysAhead  Số ngày tới cần quét (mặc định 30 ngày)
     * @return array{
     *     kpis: array{
     *         total_upcoming: int,
     *         high_risk_count: int,
     *         medium_risk_count: int,
     *         ready_count: int,
     *         avg_score: float
     *     },
     *     items: Collection
     * }
     */
    public function scan(int $daysAhead = 30): array
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays($daysAhead);

        $weddings = Wedding::with([
            'user',
            'invitations',
            'guests',
            'guestResponses',
            'tables',
        ])
            ->whereNotNull('wedding_date')
            ->where('wedding_date', '>=', $startDate)
            ->where('wedding_date', '<=', $endDate)
            ->orderBy('wedding_date')
            ->get();

        $items = $weddings->map(function (Wedding $wedding) {
            return $this->evaluateWedding($wedding);
        });

        $totalUpcoming = $items->count();
        $highRiskCount = $items->where('risk_level', 'red')->count();
        $mediumRiskCount = $items->where('risk_level', 'yellow')->count();
        $readyCount = $items->where('risk_level', 'green')->count();
        $avgScore = $totalUpcoming > 0 ? round($items->avg('score'), 1) : 100;

        return [
            'kpis' => [
                'total_upcoming' => $totalUpcoming,
                'high_risk_count' => $highRiskCount,
                'medium_risk_count' => $mediumRiskCount,
                'ready_count' => $readyCount,
                'avg_score' => $avgScore,
            ],
            'items' => $items,
        ];
    }

    /**
     * Đánh giá chi tiết điểm sẵn sàng của một đám cưới.
     *
     * @return array<string, mixed>
     */
    public function evaluateWedding(Wedding $wedding): array
    {
        $score = 0;
        $issues = [];
        $checklist = [];

        // 1. Địa điểm tổ chức & Bản đồ (Tối đa 25 điểm)
        $hasVenue = ! empty($wedding->venue_name) && ! empty($wedding->venue_address);
        if ($hasVenue) {
            $score += 25;
            $checklist['venue'] = true;
        } else {
            $issues[] = [
                'code' => 'missing_venue',
                'severity' => 'critical',
                'title' => 'Chưa có địa điểm tiệc cưới',
                'description' => 'Chưa điền tên sảnh tiệc hoặc địa chỉ tổ chức đón khách.',
            ];
            $checklist['venue'] = false;
        }

        // 2. Hình ảnh đại diện & bìa thiệp (Tối đa 25 điểm)
        $hasCoverPhoto = ! empty($wedding->cover_photo_url);
        if ($hasCoverPhoto) {
            $score += 25;
            $checklist['cover_photo'] = true;
        } else {
            $issues[] = [
                'code' => 'missing_cover_photo',
                'severity' => 'critical',
                'title' => 'Chưa có ảnh bìa đám cưới',
                'description' => 'Thiếu ảnh chân dung/ảnh cưới chính của cô dâu chú rể.',
            ];
            $checklist['cover_photo'] = false;
        }

        // 3. Nhạc nền thiệp cưới (Tối đa 15 điểm)
        $hasMusic = ! empty($wedding->music_url);
        if ($hasMusic) {
            $score += 15;
            $checklist['music'] = true;
        } else {
            $issues[] = [
                'code' => 'missing_music',
                'severity' => 'warning',
                'title' => 'Chưa chọn nhạc nền',
                'description' => 'Thiệp chưa được cài đặt giai điệu nền ngày cưới.',
            ];
            $checklist['music'] = false;
        }

        // 4. Khách mời & Sức chứa bàn tiệc (Tối đa 20 điểm)
        $totalGuests = $wedding->guests->count();
        $attendingGuests = $wedding->guestResponses->where('attending_status', 'attending')->count();
        $totalTableCapacity = $wedding->tables->sum('guest_count');

        if ($totalGuests > 0) {
            if ($totalTableCapacity > 0 && $attendingGuests > $totalTableCapacity) {
                // Khách xác nhận vượt quá chỗ ngồi
                $score += 10;
                $issues[] = [
                    'code' => 'capacity_overflow',
                    'severity' => 'warning',
                    'title' => 'Quá tải sức chứa bàn tiệc',
                    'description' => "Có {$attendingGuests} khách xác nhận tham dự nhưng tổng sức chứa bàn tiệc chỉ có {$totalTableCapacity} chỗ.",
                ];
                $checklist['capacity'] = false;
            } else {
                $score += 20;
                $checklist['capacity'] = true;
            }
        } else {
            $issues[] = [
                'code' => 'empty_guest_list',
                'severity' => 'info',
                'title' => 'Chưa nhập danh bạ khách mời',
                'description' => 'Chưa có khách mời nào được thêm vào hệ thống.',
            ];
            $checklist['capacity'] = false;
        }

        // 5. Trạng thái xuất bản thiệp (Tối đa 15 điểm)
        $publishedInvitations = $wedding->invitations->where('is_published', true);
        if ($publishedInvitations->isNotEmpty()) {
            $score += 15;
            $checklist['published'] = true;
        } else {
            $issues[] = [
                'code' => 'no_published_invitation',
                'severity' => 'critical',
                'title' => 'Chưa công bố thiệp mời',
                'description' => 'Chưa có bản thiệp cưới nào được bấm xuất bản trực tuyến.',
            ];
            $checklist['published'] = false;
        }

        // Phân cấp rủi ro
        $riskLevel = match (true) {
            $score >= 80 => 'green',
            $score >= 60 => 'yellow',
            default => 'red',
        };

        $daysRemaining = Carbon::today()->diffInDays($wedding->wedding_date, false);

        return [
            'wedding' => $wedding,
            'couple' => "{$wedding->groom_name} & {$wedding->bride_name}",
            'wedding_date' => $wedding->wedding_date,
            'days_remaining' => (int) $daysRemaining,
            'score' => $score,
            'risk_level' => $riskLevel,
            'issues' => $issues,
            'checklist' => $checklist,
            'primary_invitation' => $wedding->invitations->first(),
        ];
    }
}
