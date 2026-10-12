<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\User;
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

        $recentOrders = Order::with(['user', 'plan'])->latest()->take(6)->get();
        $templates = InvitationTemplate::withCount('invitations')->get();

        return view('admin.pages.dashboard', [
            'totalRevenue' => $totalRevenue,
            'paidOrdersCount' => $paidOrdersCount,
            'totalInvitations' => $totalInvitations,
            'publishedInvitations' => $publishedInvitations,
            'totalUsers' => $totalUsers,
            'totalGuests' => $totalGuests,
            'recentOrders' => $recentOrders,
            'templates' => $templates,
        ]);
    }
}
