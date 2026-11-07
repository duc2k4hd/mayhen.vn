<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\User;
use App\Models\WeddingWish;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
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

        return view('admin.pages.dashboard', [
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
        ]);
    }
}
