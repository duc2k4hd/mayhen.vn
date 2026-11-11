<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsReportService
{
    /**
     * Phân tích và trích xuất khoảng thời gian lọc và khoảng thời gian đối soát kỳ trước.
     *
     * @return array{start: Carbon, end: Carbon, prev_start: Carbon, prev_end: Carbon, label: string}
     */
    public function parseDateRange(?string $range, ?string $startDate = null, ?string $endDate = null): array
    {
        $now = now();

        switch ($range) {
            case '7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                $diffDays = 7;
                $prevEnd = $start->copy()->subSecond();
                $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();
                $label = '7 ngày qua';
                break;

            case 'this_month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subMonth()->startOfMonth();
                $prevEnd = $now->copy()->subMonth()->endOfMonth();
                $label = 'Tháng này ('.$now->format('m/Y').')';
                break;

            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end = $now->copy()->subMonth()->endOfMonth();
                $prevStart = $now->copy()->subMonths(2)->startOfMonth();
                $prevEnd = $now->copy()->subMonths(2)->endOfMonth();
                $label = 'Tháng trước ('.$start->format('m/Y').')';
                break;

            case '90_days':
                $start = $now->copy()->subDays(89)->startOfDay();
                $end = $now->copy()->endOfDay();
                $diffDays = 90;
                $prevEnd = $start->copy()->subSecond();
                $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();
                $label = '90 ngày qua';
                break;

            case 'custom':
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->subDays(29)->startOfDay();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay();
                $diffDays = max(1, (int) $start->diffInDays($end) + 1);
                $prevEnd = $start->copy()->subSecond();
                $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();
                $label = $start->format('d/m/Y').' - '.$end->format('d/m/Y');
                break;

            case '30_days':
            default:
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                $diffDays = 30;
                $prevEnd = $start->copy()->subSecond();
                $prevStart = $prevEnd->copy()->subDays($diffDays - 1)->startOfDay();
                $label = '30 ngày qua';
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'prev_start' => $prevStart,
            'prev_end' => $prevEnd,
            'label' => $label,
        ];
    }

    /**
     * Báo cáo Doanh thu & Tăng trưởng (Revenue Analytics).
     *
     * @return array<string, mixed>
     */
    public function getRevenueReport(Carbon $start, Carbon $end, Carbon $prevStart, Carbon $prevEnd): array
    {
        // 1. Kỳ hiện tại
        $currentOrders = Order::where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->get();

        $grossRevenue = (float) $currentOrders->sum('amount');
        $refundAmount = (float) $currentOrders->sum('refund_amount');
        $netRevenue = max(0, $grossRevenue - $refundAmount);
        $ordersCount = $currentOrders->count();
        $aov = $ordersCount > 0 ? round($netRevenue / $ordersCount) : 0;

        // 2. Kỳ trước (đối soát tăng trưởng)
        $prevOrders = Order::where('status', 'paid')
            ->whereBetween('paid_at', [$prevStart, $prevEnd])
            ->get();

        $prevGross = (float) $prevOrders->sum('amount');
        $prevRefund = (float) $prevOrders->sum('refund_amount');
        $prevNetRevenue = max(0, $prevGross - $prevRefund);
        $prevOrdersCount = $prevOrders->count();

        $revenueGrowthPct = $prevNetRevenue > 0
            ? round((($netRevenue - $prevNetRevenue) / $prevNetRevenue) * 100, 1)
            : ($netRevenue > 0 ? 100 : 0);

        $ordersGrowthPct = $prevOrdersCount > 0
            ? round((($ordersCount - $prevOrdersCount) / $prevOrdersCount) * 100, 1)
            : ($ordersCount > 0 ? 100 : 0);

        // 3. Xu hướng theo ngày (Daily trend)
        $dailyTrend = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $dayKey = $cursor->format('Y-m-d');
            $dayLabel = $cursor->format('d/m');
            $dayOrders = $currentOrders->filter(function ($order) use ($cursor) {
                return Carbon::parse($order->paid_at)->isSameDay($cursor);
            });

            $dailyTrend[] = [
                'date' => $dayKey,
                'label' => $dayLabel,
                'revenue' => (float) $dayOrders->sum('amount'),
                'orders_count' => $dayOrders->count(),
            ];

            $cursor->addDay();
        }

        return [
            'gross_revenue' => $grossRevenue,
            'discount_amount' => 0,
            'refund_amount' => $refundAmount,
            'net_revenue' => $netRevenue,
            'orders_count' => $ordersCount,
            'aov' => $aov,
            'revenue_growth_pct' => $revenueGrowthPct,
            'orders_growth_pct' => $ordersGrowthPct,
            'daily_trend' => $dailyTrend,
        ];
    }

    /**
     * Báo cáo Phễu chuyển đổi (Acquisition & Conversion Funnel).
     *
     * @return array<string, mixed>
     */
    public function getAcquisitionFunnel(Carbon $start, Carbon $end): array
    {
        // 1. Lượt xem thiệp & Landing (Views)
        $totalViews = (int) DB::table('invitation_views')
            ->whereBetween('viewed_at', [$start, $end])
            ->count();

        // 2. Dự án cưới được tạo (Created)
        $totalCreated = (int) Wedding::whereBetween('created_at', [$start, $end])->count();

        // 3. Thiệp được xuất bản (Published)
        $totalPublished = (int) Invitation::where('is_published', true)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // 4. Đơn hàng trả phí thành công (Paid)
        $totalPaid = (int) Order::where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->count();

        // Tính tỷ lệ chuyển đổi từng chặng
        $viewToCreatePct = $totalViews > 0 ? round(($totalCreated / $totalViews) * 100, 1) : 0;
        $createToPublishPct = $totalCreated > 0 ? round(($totalPublished / $totalCreated) * 100, 1) : 0;
        $publishToPaidPct = $totalPublished > 0 ? round(($totalPaid / $totalPublished) * 100, 1) : 0;
        $overallConversionPct = $totalViews > 0 ? round(($totalPaid / $totalViews) * 100, 2) : 0;

        return [
            'total_views' => $totalViews,
            'total_created' => $totalCreated,
            'total_published' => $totalPublished,
            'total_paid' => $totalPaid,
            'view_to_create_pct' => $viewToCreatePct,
            'create_to_publish_pct' => $createToPublishPct,
            'publish_to_paid_pct' => $publishToPaidPct,
            'overall_conversion_pct' => $overallConversionPct,
        ];
    }

    /**
     * Báo cáo Hiệu suất Mẫu thiệp (Template Performance).
     *
     * @return Collection<int, array>
     */
    public function getTemplatePerformance(Carbon $start, Carbon $end, int $limit = 50): Collection
    {
        $templates = InvitationTemplate::all();

        $performance = $templates->map(function ($template) use ($start, $end) {
            $viewsCount = (int) DB::table('invitation_views')
                ->join('invitations', 'invitation_views.invitation_id', '=', 'invitations.id')
                ->where('invitations.template_id', $template->id)
                ->whereBetween('invitation_views.viewed_at', [$start, $end])
                ->count();

            $createdCount = (int) Invitation::where('template_id', $template->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $paidOrders = Order::where('status', 'paid')
                ->whereHas('invitation', fn ($q) => $q->where('template_id', $template->id))
                ->whereBetween('paid_at', [$start, $end])
                ->get();

            $paidCount = $paidOrders->count();
            $revenue = (float) $paidOrders->sum('amount');
            $conversionRate = $viewsCount > 0 ? round(($createdCount / $viewsCount) * 100, 1) : 0;

            return [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'tier' => $template->tier ?? 'standard',
                'views_count' => $viewsCount,
                'created_count' => $createdCount,
                'paid_count' => $paidCount,
                'revenue' => $revenue,
                'conversion_rate' => $conversionRate,
            ];
        });

        return $performance->sortByDesc('created_count')->take($limit)->values();
    }

    /**
     * Báo cáo Khách mời & RSVP (Guest Engagement & Headcount).
     *
     * @return array<string, mixed>
     */
    public function getRsvpAnalytics(Carbon $start, Carbon $end): array
    {
        $guests = Guest::with('response')->whereBetween('created_at', [$start, $end])->get();
        $totalGuests = $guests->count();

        // Khách đã mở thiệp cá nhân hóa (Open Tracking)
        $openedCount = $guests->where('opened_at', '!=', null)->count();
        $openRatePct = $totalGuests > 0 ? round(($openedCount / $totalGuests) * 100, 1) : 0;

        // Phản hồi RSVP từ quan hệ response
        $attendingCount = 0;
        $declinedCount = 0;
        $pendingCount = 0;
        $totalHeadcount = 0;

        foreach ($guests as $g) {
            $resp = $g->response;
            if ($resp) {
                if ($resp->attending_status === 'attending') {
                    $attendingCount++;
                    $totalHeadcount += max(1, (int) ($resp->adults_count + $resp->children_count));
                } elseif ($resp->attending_status === 'declined') {
                    $declinedCount++;
                } else {
                    $pendingCount++;
                }
            } else {
                $pendingCount++;
            }
        }

        $totalResponded = $attendingCount + $declinedCount;
        $responseRatePct = $totalGuests > 0 ? round(($totalResponded / $totalGuests) * 100, 1) : 0;

        // Phân bổ nhóm khách (group_name)
        $groomSideCount = $guests->filter(fn ($g) => str_contains(mb_strtolower($g->group_name ?? ''), 'trai'))->count();
        $brideSideCount = $guests->filter(fn ($g) => str_contains(mb_strtolower($g->group_name ?? ''), 'gái'))->count();

        return [
            'total_guests' => $totalGuests,
            'opened_count' => $openedCount,
            'open_rate_pct' => $openRatePct,
            'attending_count' => $attendingCount,
            'declined_count' => $declinedCount,
            'pending_count' => $pendingCount,
            'response_rate_pct' => $responseRatePct,
            'total_headcount' => $totalHeadcount,
            'groom_side_count' => $groomSideCount,
            'bride_side_count' => $brideSideCount,
        ];
    }

    /**
     * Báo cáo Vận hành & Chỉ số Dịch vụ (Operations & Service Health).
     *
     * @return array<string, mixed>
     */
    public function getOperationsMetrics(Carbon $start, Carbon $end): array
    {
        // 1. Ticket hỗ trợ khách hàng
        $totalTickets = SupportTicket::whereBetween('created_at', [$start, $end])->count();
        $resolvedTickets = SupportTicket::whereIn('status', ['resolved', 'closed'])
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $ticketResolutionRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 100;

        // 2. Failed jobs phát sinh
        $failedJobsCount = DB::table('failed_jobs')
            ->whereBetween('failed_at', [$start, $end])
            ->count();

        // 3. Batches hoàn thành
        $completedBatches = DB::table('job_batches')
            ->whereNotNull('finished_at')
            ->whereBetween('finished_at', [$start->timestamp, $end->timestamp])
            ->count();

        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'ticket_resolution_rate' => $ticketResolutionRate,
            'failed_jobs_count' => $failedJobsCount,
            'completed_batches' => $completedBatches,
        ];
    }

    /**
     * Xuất dữ liệu báo cáo dạng mảng phục vụ tải CSV.
     *
     * @return array<int, array<string, string>>
     */
    public function getExportData(string $tab, Carbon $start, Carbon $end, ?User $admin = null): array
    {
        $data = [];

        if ($tab === 'revenue') {
            $orders = Order::where('status', 'paid')
                ->whereBetween('paid_at', [$start, $end])
                ->latest('paid_at')
                ->get();

            foreach ($orders as $order) {
                $data[] = [
                    'Mã Đơn' => $order->order_code,
                    'Khách Hàng' => $order->user?->name ?? 'N/A',
                    'Email' => $order->user?->email ?? 'N/A',
                    'Gói Dịch Vụ' => $order->plan?->name ?? 'Gói Tiêu Chuẩn',
                    'Số Tiền Thanh Toán' => number_format($order->amount, 0, ',', '.').' đ',
                    'Tiền Hoàn (Refund)' => number_format($order->refund_amount ?? 0, 0, ',', '.').' đ',
                    'Cổng Thanh Toán' => strtoupper($order->payment_method ?? 'VietQR'),
                    'Thời Điểm Thanh Toán' => Carbon::parse($order->paid_at)->format('d/m/Y H:i'),
                ];
            }
        } elseif ($tab === 'rsvp') {
            $guests = Guest::with('response')->whereBetween('created_at', [$start, $end])->latest('id')->get();

            foreach ($guests as $guest) {
                $resp = $guest->response;
                $statusLabel = 'Chưa phản hồi';
                $headcount = 0;
                if ($resp) {
                    if ($resp->attending_status === 'attending') {
                        $statusLabel = 'Tham dự';
                        $headcount = max(1, (int) ($resp->adults_count + $resp->children_count));
                    } elseif ($resp->attending_status === 'declined') {
                        $statusLabel = 'Báo bận (Không tham dự)';
                    }
                }

                $data[] = [
                    'Họ Tên Khách Mời' => $guest->name,
                    'Số Điện Thoại' => $guest->phone ?? 'N/A',
                    'Nhóm Khách' => $guest->group_name ?? 'Khách Mời',
                    'Trạng Thái RSVP' => $statusLabel,
                    'Số Người Tham Dự' => (string) $headcount,
                    'Đã Mở Thiệp' => $guest->opened_at ? 'Đã xem' : 'Chưa xem',
                    'Thời Điểm Mở' => $guest->opened_at ? Carbon::parse($guest->opened_at)->format('d/m/Y H:i') : '',
                ];
            }
        } elseif ($tab === 'templates') {
            $templates = $this->getTemplatePerformance($start, $end, 50);

            foreach ($templates as $t) {
                $data[] = [
                    'Mã Mẫu' => $t['slug'],
                    'Tên Mẫu Thiệp' => $t['name'],
                    'Phân Khúc (Tier)' => strtoupper($t['tier']),
                    'Lượt Xem' => (string) $t['views_count'],
                    'Số Lượng Tạo' => (string) $t['created_count'],
                    'Số Đơn Trả Phí' => (string) $t['paid_count'],
                    'Tỷ Lệ Chuyển Đổi' => $t['conversion_rate'].'%',
                    'Doanh Thu Mang Lại' => number_format($t['revenue'], 0, ',', '.').' đ',
                ];
            }
        }

        AuditLogger::log(
            'ANALYTICS_REPORT_EXPORTED',
            'AnalyticsReport',
            0,
            "Xuất dữ liệu báo cáo phân hệ [{$tab}] từ {$start->format('d/m/Y')} đến {$end->format('d/m/Y')}",
            ['tab' => $tab, 'count' => count($data), 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $data;
    }
}
