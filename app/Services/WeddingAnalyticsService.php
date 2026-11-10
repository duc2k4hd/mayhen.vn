<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeddingAnalyticsService
{
    /**
     * Ghi nhận lượt truy cập thiệp cưới thực tế và bảo vệ quyền riêng tư.
     */
    public function recordVisit(Invitation $invitation, Request $request, ?Guest $guest = null): ?InvitationView
    {
        $userAgent = (string) $request->userAgent();

        // 1. Không ghi nhận lượt xem của chủ sở hữu thiệp cưới
        if ($invitation->isOwner()) {
            return null;
        }

        // 2. Loại bỏ các bot / crawler tìm kiếm tự động
        if ($this->isCrawler($userAgent)) {
            return null;
        }

        // 3. Tăng biến đếm tổng lượt xem tức thì (Atomic Increment)
        $invitation->increment('view_count');

        // 4. Phân tích thiết bị, nền tảng và trình duyệt
        $deviceType = $this->detectDeviceType($userAgent);
        $platform = $this->detectPlatform($userAgent);
        $browser = $this->detectBrowser($userAgent);

        // 5. Nguồn giới thiệu (Referer)
        $refererHeader = $request->header('referer');
        $refererHost = null;
        if ($refererHeader) {
            $parsedHost = parse_url($refererHeader, PHP_URL_HOST);
            $refererHost = $parsedHost ? substr($parsedHost, 0, 100) : null;
        }

        // 6. Mã băm ẩn danh bảo vệ PII (Luật 91/2025/QH15 & Section 41.1 PRD V3)
        // Tuyệt đối không lưu IP thô vào database
        $salt = (string) config('app.key', 'mayhen_analytics_salt');
        $ip = $request->ip() ?? '0.0.0.0';
        $dayStamp = date('Y-m-d');
        $visitorHash = hash('sha256', "{$salt}_{$ip}_{$userAgent}_{$dayStamp}");

        // 7. Chống spam ghi nhận liên tục (De-duplication trong 15 phút)
        $recentView = InvitationView::where('invitation_id', $invitation->id)
            ->where('visitor_hash', $visitorHash)
            ->where('viewed_at', '>=', Carbon::now()->subMinutes(15))
            ->first();

        if ($recentView) {
            // Đã ghi nhận trong 15 phút gần nhất, không tạo thêm bản ghi chi tiết
            return $recentView;
        }

        return InvitationView::create([
            'invitation_id' => $invitation->id,
            'wedding_id' => $invitation->wedding_id,
            'guest_id' => $guest?->id,
            'visitor_hash' => $visitorHash,
            'device_type' => $deviceType,
            'platform' => $platform,
            'browser' => $browser,
            'referer_host' => $refererHost,
            'viewed_at' => Carbon::now(),
        ]);
    }

    /**
     * Tổng hợp báo cáo phân tích hiệu suất tương tác hôn lễ (100% dữ liệu thật).
     *
     * @return array<string, mixed>
     */
    public function getInvitationDashboardStats(Invitation $invitation, int $days = 30): array
    {
        $since = Carbon::now()->subDays($days)->startOfDay();
        $wedding = $invitation->wedding;

        // 1. Chỉ số lượt xem & khách truy cập
        $totalViews = (int) $invitation->view_count;
        $periodViews = $invitation->views()->where('viewed_at', '>=', $since)->count();
        $uniqueVisitors = $invitation->views()
            ->where('viewed_at', '>=', $since)
            ->distinct('visitor_hash')
            ->count('visitor_hash');

        // 2. Chỉ số khách mời & chiến dịch
        $totalGuests = 0;
        $sentGuests = 0;
        $openedGuests = 0;
        $totalResponses = 0;
        $attendingResponses = 0;
        $declinedResponses = 0;
        $attendingHeadcount = 0;
        $totalWishes = 0;
        $approvedWishes = 0;

        if ($wedding) {
            $totalGuests = $wedding->guests()->count();
            $sentGuests = $wedding->guests()->where('is_sent', true)->count();
            $openedGuests = $wedding->guests()->whereNotNull('opened_at')->count();

            $responses = GuestResponse::where('wedding_id', $wedding->id)->get();
            $totalResponses = $responses->count();
            $attendingResponses = $responses->where('attending_status', 'attending')->count();
            $declinedResponses = $responses->where('attending_status', 'declined')->count();

            $attendingHeadcount = (int) $responses->where('attending_status', 'attending')
                ->sum(function ($r) {
                    return (int) $r->adults_count + (int) $r->children_count;
                });

            $totalWishes = $wedding->wishes()->count();
            $approvedWishes = $wedding->wishes()->where('is_approved', true)->count();
        }

        // 3. Tỷ lệ chuyển đổi phễu (Funnel Conversion Rates)
        $openRate = $sentGuests > 0 ? round(($openedGuests / $sentGuests) * 100, 1) : 0.0;
        $rsvpConversionRate = $openedGuests > 0 ? round(($totalResponses / $openedGuests) * 100, 1) : 0.0;
        $attendanceRate = $totalResponses > 0 ? round(($attendingResponses / $totalResponses) * 100, 1) : 0.0;
        $wishRate = $openedGuests > 0 ? round(($totalWishes / $openedGuests) * 100, 1) : 0.0;

        // 4. Phân bố thiết bị (Device Breakdown)
        $deviceQuery = $invitation->views()
            ->where('viewed_at', '>=', $since)
            ->select('device_type', DB::raw('count(*) as count'))
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->all();

        $totalDeviceCount = array_sum($deviceQuery);
        $deviceBreakdown = [
            'mobile' => [
                'count' => $deviceQuery['mobile'] ?? 0,
                'pct' => $totalDeviceCount > 0 ? round((($deviceQuery['mobile'] ?? 0) / $totalDeviceCount) * 100, 1) : 0.0,
            ],
            'tablet' => [
                'count' => $deviceQuery['tablet'] ?? 0,
                'pct' => $totalDeviceCount > 0 ? round((($deviceQuery['tablet'] ?? 0) / $totalDeviceCount) * 100, 1) : 0.0,
            ],
            'desktop' => [
                'count' => $deviceQuery['desktop'] ?? 0,
                'pct' => $totalDeviceCount > 0 ? round((($deviceQuery['desktop'] ?? 0) / $totalDeviceCount) * 100, 1) : 0.0,
            ],
        ];

        // 5. Phân bố hệ điều hành (Platform Breakdown)
        $platformQuery = $invitation->views()
            ->where('viewed_at', '>=', $since)
            ->select('platform', DB::raw('count(*) as count'))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->all();

        // 6. Phân bố ứng dụng / trình duyệt (In-app Browser Breakdown)
        $browserQuery = $invitation->views()
            ->where('viewed_at', '>=', $since)
            ->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->pluck('count', 'browser')
            ->all();

        // 7. Dữ liệu dòng thời gian 7 ngày gần nhất (Timeline Chart)
        $timelineData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $dayViews = $invitation->views()
                ->whereBetween('viewed_at', [$dayStart, $dayEnd])
                ->count();

            $dayUnique = $invitation->views()
                ->whereBetween('viewed_at', [$dayStart, $dayEnd])
                ->distinct('visitor_hash')
                ->count('visitor_hash');

            $timelineData[] = [
                'date' => $day->format('d/m'),
                'day_name' => $day->format('D'),
                'views' => $dayViews,
                'unique' => $dayUnique,
            ];
        }

        // Tìm giá trị max để vẽ biểu đồ Pure SVG/CSS scale chuẩn
        $maxTimelineValue = max(1, ...array_column($timelineData, 'views'));

        return [
            'summary' => [
                'total_views' => $totalViews,
                'period_views' => $periodViews,
                'unique_visitors' => $uniqueVisitors,
                'total_guests' => $totalGuests,
                'sent_guests' => $sentGuests,
                'opened_guests' => $openedGuests,
                'total_responses' => $totalResponses,
                'attending_responses' => $attendingResponses,
                'declined_responses' => $declinedResponses,
                'attending_headcount' => $attendingHeadcount,
                'total_wishes' => $totalWishes,
                'approved_wishes' => $approvedWishes,
                'open_rate' => $openRate,
                'rsvp_conversion_rate' => $rsvpConversionRate,
                'attendance_rate' => $attendanceRate,
                'wish_rate' => $wishRate,
            ],
            'funnel' => [
                [
                    'step' => 1,
                    'name' => 'Khách Đã Gửi Link',
                    'count' => $sentGuests,
                    'total' => $totalGuests,
                    'rate' => $totalGuests > 0 ? round(($sentGuests / $totalGuests) * 100, 1) : 0.0,
                    'unit' => 'khách',
                ],
                [
                    'step' => 2,
                    'name' => 'Khách Đã Mở Thiệp',
                    'count' => $openedGuests,
                    'total' => $sentGuests,
                    'rate' => $openRate,
                    'unit' => 'khách',
                ],
                [
                    'step' => 3,
                    'name' => 'Xác Nhận Tham Dự (RSVP)',
                    'count' => $totalResponses,
                    'total' => $openedGuests,
                    'rate' => $rsvpConversionRate,
                    'unit' => 'phản hồi',
                ],
                [
                    'step' => 4,
                    'name' => 'Khách Sẽ Đến Tiệc',
                    'count' => $attendingResponses,
                    'total' => $totalResponses,
                    'rate' => $attendanceRate,
                    'unit' => 'khách mời',
                ],
                [
                    'step' => 5,
                    'name' => 'Gửi Lời Chúc Mừng',
                    'count' => $totalWishes,
                    'total' => $openedGuests,
                    'rate' => $wishRate,
                    'unit' => 'lời chúc',
                ],
            ],
            'devices' => $deviceBreakdown,
            'platforms' => $platformQuery,
            'browsers' => $browserQuery,
            'timeline' => $timelineData,
            'max_timeline_value' => $maxTimelineValue,
            'period_days' => $days,
        ];
    }

    /**
     * Xuất dữ liệu nhật ký phân tích ra file CSV có gắn UTF-8 BOM và chống Excel Injection.
     */
    public function exportAnalyticsCsv(Invitation $invitation, int $days = 30): string
    {
        $since = Carbon::now()->subDays($days)->startOfDay();
        $views = $invitation->views()
            ->with('guest')
            ->where('viewed_at', '>=', $since)
            ->latest('viewed_at')
            ->limit(2000)
            ->get();

        $rows = [];
        $rows[] = ['Thời Điểm', 'Loại Thiết Bị', 'Nền Tảng HĐH', 'Ứng Dụng / Trình Duyệt', 'Nguồn Giới Thiệu (Referer)', 'Khách Mời Cá Nhân'];

        foreach ($views as $view) {
            $guestName = $view->guest ? $view->guest->name : 'Khách Vãng Lai / Link Chung';
            $rows[] = [
                $view->viewed_at ? $view->viewed_at->format('Y-m-d H:i:s') : '',
                $view->device_type,
                $view->platform,
                $view->browser,
                $view->referer_host ?? 'Trực Tiếp',
                $guestName,
            ];
        }

        $fp = fopen('php://temp', 'r+');
        // Gắn UTF-8 BOM
        fwrite($fp, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            $sanitized = array_map(function ($val) {
                $str = (string) $val;
                // Phòng vệ tấn công CSV / Excel formula injection
                if (isset($str[0]) && in_array($str[0], ['=', '+', '-', '@'], true)) {
                    return "'".$str;
                }

                return $str;
            }, $row);
            fputcsv($fp, $sanitized);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return (string) $csv;
    }

    /**
     * Nhận diện thiết bị (Mobile, Tablet, Desktop).
     */
    protected function detectDeviceType(string $ua): string
    {
        if (preg_match('/(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk)/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/(mobi|iphone|ipod|phone|blackberry|opera mini|iemobile)/i', $ua)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Nhận diện hệ điều hành.
     */
    protected function detectPlatform(string $ua): string
    {
        if (preg_match('/(iphone|ipad|ipod)/i', $ua)) {
            return 'ios';
        }
        if (preg_match('/android/i', $ua)) {
            return 'android';
        }
        if (preg_match('/windows nt/i', $ua)) {
            return 'windows';
        }
        if (preg_match('/macintosh|mac os x/i', $ua)) {
            return 'macos';
        }
        if (preg_match('/linux/i', $ua)) {
            return 'linux';
        }

        return 'other';
    }

    /**
     * Nhận diện ứng dụng / trình duyệt (đặc biệt Zalo, Facebook In-app).
     */
    protected function detectBrowser(string $ua): string
    {
        if (stripos($ua, 'zalo') !== false) {
            return 'zalo';
        }
        if (preg_match('/(fbav|fban|fb_iab|fb4a)/i', $ua)) {
            return 'facebook';
        }
        if (preg_match('/edg/i', $ua)) {
            return 'edge';
        }
        if (preg_match('/(firefox|fxios)/i', $ua)) {
            return 'firefox';
        }
        if (preg_match('/(chrome|crios)/i', $ua)) {
            return 'chrome';
        }
        if (preg_match('/safari/i', $ua)) {
            return 'safari';
        }

        return 'other';
    }

    /**
     * Kiểm tra bot / web crawler.
     */
    protected function isCrawler(string $ua): bool
    {
        if (empty($ua)) {
            return true;
        }

        return (bool) preg_match('/(bot|crawler|spider|crawling|googlebot|bingbot|yandex|slurp|duckduckbot|baiduspider|facebookexternalhit|whatsapp)/i', $ua);
    }
}
