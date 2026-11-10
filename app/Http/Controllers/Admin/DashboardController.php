<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\AuditLogger;
use App\Services\WeddingReadinessRadarService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __construct(
        protected WeddingReadinessRadarService $radarService
    ) {}

    /**
     * Display the Admin KPI & Metrics Dashboard.
     */
    public function index(): View
    {
        $totalRevenue = Order::where('status', 'paid')->sum('amount');
        $paidOrdersCount = Order::where('status', 'paid')->count();
        $totalInvitations = Invitation::count();
        $publishedInvitations = Invitation::where('is_published', true)->count();
        $totalUsers = User::count();
        $totalGuests = Guest::count();

        // 7-Day Revenue Trend (Pure Native Fast Calculation)
        $revenueLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayRevenue = Order::where('status', 'paid')
                ->whereDate('paid_at', $date)
                ->sum('amount');
            $revenueLast7Days[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('d/m'),
                'day_name' => $i === 0 ? 'Hôm nay' : 'T'.($date->dayOfWeek + 1 === 1 ? 'CN' : $date->dayOfWeek + 1),
                'amount' => (int) $dayRevenue,
            ];
        }
        $maxDailyRevenue = max(array_column($revenueLast7Days, 'amount')) ?: 1;

        // RSVP Metrics
        $totalRsvps = GuestResponse::count();
        $attendingRsvps = GuestResponse::where('attending_status', 'attending')->count();
        $attendanceRate = $totalRsvps > 0 ? round(($attendingRsvps / $totalRsvps) * 100) : 0;

        $recentOrders = Order::with(['user', 'plan'])->latest('id')->take(5)->get();
        $templates = InvitationTemplate::withCount('invitations')->orderByDesc('invitations_count')->take(5)->get();
        $recentAuditLogs = AuditLogger::getRecentLogs(5);

        // Operational Actionable Alerts
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $pendingWishesCount = WeddingWish::where('is_approved', false)->count();
        $lockedInvitationsCount = Invitation::where('custom_config->is_locked', true)->count();

        // Wedding Readiness Radar Summary (14-day window)
        $radarSummary = $this->radarService->scan(14);
        $radarKpis = $radarSummary['kpis'];
        $highRiskWeddings = $radarSummary['items']->where('risk_level', 'red')->take(3);

        // Role-Aware Morning Briefing (Innovation #18)
        $user = Auth::user();
        $currentHour = (int) now()->format('H');
        $greetingTime = match (true) {
            $currentHour >= 5 && $currentHour < 12 => 'Chào buổi sáng',
            $currentHour >= 12 && $currentHour < 18 => 'Chào buổi chiều',
            default => 'Chào buổi tối',
        };

        $revenue24h = (int) Order::where('status', 'paid')
            ->where('paid_at', '>=', now()->subHours(24))
            ->sum('amount');
        $orders24h = Order::where('status', 'paid')
            ->where('paid_at', '>=', now()->subHours(24))
            ->count();

        $todayWeddingsCount = Wedding::whereDate('wedding_date', Carbon::today())->count();
        $todayWeddings = Wedding::whereDate('wedding_date', Carbon::today())
            ->with(['user', 'invitations:id,wedding_id,slug,is_published'])
            ->take(3)
            ->get();

        $openTicketsCount = Schema::hasTable('support_tickets')
            ? SupportTicket::whereIn('status', ['open', 'in_progress'])->count()
            : 0;

        $morningBrief = [
            'greeting' => "{$greetingTime}, ".($user ? $user->name : 'Quản trị viên').'!',
            'role' => $user?->role ?? 'admin',
            'role_title' => match ($user?->role) {
                'admin' => 'Tổng Chỉ Huy Vận Hành (Super Admin)',
                'manager' => 'Quản Lý Nghiệp Vụ (Operations Manager)',
                'staff' => 'Chuyên Viên Hỗ Trợ & Ngày Cưới (Staff)',
                'partner' => 'Đối Tác / Studio (Partner)',
                default => 'Thành Viên Atelier',
            },
            'date_str' => Carbon::now()->locale('vi')->translatedFormat('l, d/m/Y'),
            'revenue_24h' => $revenue24h,
            'orders_24h' => $orders24h,
            'today_weddings_count' => $todayWeddingsCount,
            'today_weddings' => $todayWeddings,
            'open_tickets_count' => $openTicketsCount,
            'pending_orders_count' => $pendingOrdersCount,
            'pending_wishes_count' => $pendingWishesCount,
            'radar_high_risk_count' => $radarKpis['high_risk'] ?? 0,
        ];

        return view('admin.pages.dashboard', [
            'morningBrief' => $morningBrief,
            'totalRevenue' => $totalRevenue,
            'paidOrdersCount' => $paidOrdersCount,
            'totalInvitations' => $totalInvitations,
            'publishedInvitations' => $publishedInvitations,
            'totalUsers' => $totalUsers,
            'totalGuests' => $totalGuests,
            'totalRsvps' => $totalRsvps,
            'attendingRsvps' => $attendingRsvps,
            'attendanceRate' => $attendanceRate,
            'revenueLast7Days' => $revenueLast7Days,
            'maxDailyRevenue' => $maxDailyRevenue,
            'recentOrders' => $recentOrders,
            'templates' => $templates,
            'recentAuditLogs' => $recentAuditLogs,
            'pendingOrdersCount' => $pendingOrdersCount,
            'pendingWishesCount' => $pendingWishesCount,
            'lockedInvitationsCount' => $lockedInvitationsCount,
            'radarKpis' => $radarKpis,
            'highRiskWeddings' => $highRiskWeddings,
        ]);
    }
}
