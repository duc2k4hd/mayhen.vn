<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\Carbon;

class WeddingWeekReadinessService
{
    /**
     * Đánh giá toàn diện mức độ sẵn sàng cho tuần lễ cưới (Wedding Week Support Readiness Radar).
     *
     * @return array{
     *     wedding_date: ?string,
     *     days_until_wedding: ?int,
     *     is_wedding_week: bool,
     *     readiness_score: int,
     *     status: string,
     *     status_label: string,
     *     pillars: array<int, array{key: string, title: string, status: string, score: int, description: string, action_text: string, action_url: string}>,
     *     emergency_support: array{hotline: string, zalo: string, commitment: string}
     * }
     */
    public function assessReadiness(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $weddingDate = $wedding && $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : null;
        $now = Carbon::now();

        $daysUntil = $weddingDate ? (int) $now->diffInDays($weddingDate, false) : null;
        $isWeddingWeek = $daysUntil !== null && $daysUntil >= -1 && $daysUntil <= 7;

        $pillars = [];
        $totalScore = 0;

        // Trụ cột 1: Đường Dẫn & Xuất Bản
        $isPublished = (bool) $invitation->is_published;
        $hasCustomSlug = ! empty($invitation->slug);
        $pillar1Score = $isPublished && $hasCustomSlug ? 20 : 5;
        $pillars[] = [
            'key' => 'link_published',
            'title' => 'Đường Dẫn & Xuất Bản Thiệp Cưới',
            'status' => $pillar1Score === 20 ? 'ready' : 'warning',
            'score' => $pillar1Score,
            'description' => $isPublished
                ? "Thiệp cưới đã xuất bản tại: mayhen.vn/thiep/{$invitation->slug} và sẵn sàng cho khách truy cập."
                : 'Thiệp cưới chưa được xuất bản. Khách mời sẽ không thể mở xem thiệp trực tuyến.',
            'action_text' => 'Xem Thiệp Trực Tiếp',
            'action_url' => route('invitation.show', $invitation->slug),
        ];
        $totalScore += $pillar1Score;

        // Trụ cột 2: Danh Sách Khách & Tỷ Lệ RSVP
        $guests = $wedding ? $wedding->guests()->with('response')->get() : collect();
        $totalGuests = $guests->count();
        $respondedCount = $guests->filter(fn (Guest $g) => $g->response !== null)->count();
        $rsvpRatio = $totalGuests > 0 ? ($respondedCount / $totalGuests) : 0;
        $pillar2Score = match (true) {
            $rsvpRatio >= 0.75 => 20,
            $rsvpRatio >= 0.40 => 15,
            default => 10,
        };
        $pillars[] = [
            'key' => 'guest_rsvp',
            'title' => 'Danh Sách Khách Mời & Phản Hồi RSVP',
            'status' => $pillar2Score >= 15 ? 'ready' : 'warning',
            'score' => $pillar2Score,
            'description' => $totalGuests > 0
                ? "Đã có {$respondedCount}/{$totalGuests} khách gửi phản hồi tham dự (".round($rsvpRatio * 100).'%).'
                : 'Chưa có danh sách khách mời trong hệ thống. Hãy thêm danh bạ để theo dõi điểm danh.',
            'action_text' => 'Kế Hoạch Nhắc Nhở Khách',
            'action_url' => route('guests.reminder_planner', $invitation->id),
        ];
        $totalScore += $pillar2Score;

        // Trụ cột 3: Sắp Xếp Bàn Tiệc & Sức Chứa
        $tables = $wedding ? $wedding->tables()->get() : collect();
        $totalCapacity = (int) $tables->sum('capacity');
        $hasTables = $tables->count() > 0;
        $pillar3Score = $hasTables && $totalCapacity > 0 ? 20 : 5;
        $pillars[] = [
            'key' => 'seating_capacity',
            'title' => 'Sơ Đồ Xếp Bàn Tiệc & Sức Chứa Sảnh',
            'status' => $pillar3Score === 20 ? 'ready' : 'warning',
            'score' => $pillar3Score,
            'description' => $hasTables
                ? "Đã bố trí {$tables->count()} bàn tiệc với tổng sức chứa {$totalCapacity} chỗ ngồi."
                : 'Chưa tạo sơ đồ bàn tiệc. Ban lễ tân sẽ không biết khách ngồi bàn nào khi đón tiếp.',
            'action_text' => 'Studio Sơ Đồ Bàn Tiệc',
            'action_url' => route('seating.index', $invitation->id),
        ];
        $totalScore += $pillar3Score;

        // Trụ cột 4: Đội Ngũ Lễ Tân & Tiếp Đón
        $shifts = $invitation->custom_config['reception_shifts'] ?? [];
        $hasPin = ! empty($invitation->custom_config['checkin_pin']);
        $hasShifts = count($shifts) > 0;
        $pillar4Score = ($hasPin || $hasShifts) ? 20 : 10;
        $pillars[] = [
            'key' => 'reception_staff',
            'title' => 'Cổng Tiếp Tân & Mã PIN Điểm Danh',
            'status' => $pillar4Score === 20 ? 'ready' : 'warning',
            'score' => $pillar4Score,
            'description' => $hasShifts
                ? 'Đã cấu hình '.count($shifts).' ca trực lễ tân có mã PIN riêng biệt cho người thân đón khách.'
                : 'Đã sẵn sàng mã PIN tiếp tân chung. Nên phân ca nếu có nhiều cổng đón khách.',
            'action_text' => 'Mở Cổng Điểm Danh',
            'action_url' => route('checkin.portal', $invitation->slug),
        ];
        $totalScore += $pillar4Score;

        // Trụ cột 5: Phương Án Dự Phòng Mất Mạng (Offline Resilience Kit)
        $pillar5Score = 20;
        $pillars[] = [
            'key' => 'resilience_backup',
            'title' => 'Bộ Công Cụ Dự Phòng Ngày Cưới (Resilience Kit)',
            'status' => 'ready',
            'score' => $pillar5Score,
            'description' => 'Sẵn sàng danh sách khách in ra giấy A4, bảng tra cứu bàn tiệc và kịch bản đón khách khi sảnh tiệc mất mạng.',
            'action_text' => 'Tải Bộ Kỷ Yếu & Dự Phòng',
            'action_url' => route('memory.keepsake_book', $invitation->id),
        ];
        $totalScore += $pillar5Score;

        $status = match (true) {
            $totalScore >= 85 => 'excellent',
            $totalScore >= 70 => 'good',
            default => 'warning'
        };

        $statusLabel = match ($status) {
            'excellent' => 'Sẵn Sàng 100% Cho Ngày Trọng Đại',
            'good' => 'Khá Tốt, Cần Hoàn Thiện Vài Mục',
            default => 'Cần Chuẩn Bị Thêm Trước Ngày Cưới'
        };

        return [
            'wedding_date' => $weddingDate ? $weddingDate->format('d/m/Y') : null,
            'days_until_wedding' => $daysUntil,
            'is_wedding_week' => $isWeddingWeek,
            'readiness_score' => $totalScore,
            'status' => $status,
            'status_label' => $statusLabel,
            'pillars' => $pillars,
            'emergency_support' => [
                'hotline' => '1900 88xx (Ưu tiên đám cưới trong tuần 24/7)',
                'zalo' => 'https://zalo.me/mayhenvn',
                'commitment' => 'Kỹ thuật viên Mây Hẹn trực ban cam kết hỗ trợ trong vòng 15 phút cho mọi sự cố trong ngày cưới.',
            ],
        ];
    }
}
