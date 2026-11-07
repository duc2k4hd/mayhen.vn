<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PartnerDashboardController extends Controller
{
    /**
     * Bảng điều khiển trung tâm B2B dành cho Studio & Wedding Planner.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $partner = $user->partner;

        // Nếu admin truy cập nhưng chưa có partner, hỗ trợ tạo partner mẫu hoặc lấy partner đầu tiên
        if (! $partner && $user->isAdmin()) {
            $partner = Partner::first();
            if (! $partner) {
                return redirect()->route('partner.register')->with('info', 'Chưa có dữ liệu đối tác nào. Vui lòng tạo tài khoản đối tác mẫu.');
            }
        }

        abort_unless($partner, 404, 'Không tìm thấy thông tin đối tác.');

        // Thống kê tổng quan B2B
        $totalWeddingsCount = $partner->weddings()->count();
        $totalPublishedCount = $partner->invitations()->where('is_published', true)->count();

        $weddings = $partner->weddings()->with(['invitations.template'])->get();
        $totalGuestsAttended = $weddings->sum(function ($w) {
            return $w->guestResponses()->where('attending_status', 'attending')->count();
        });

        // 6 Đám cưới khách hàng mới nhất của Studio
        $recentWeddings = $partner->weddings()
            ->with(['invitations.template'])
            ->latest()
            ->limit(6)
            ->get();

        // 5 Lịch sử hoa hồng mới nhất
        $recentCommissions = $partner->commissions()
            ->latest()
            ->limit(5)
            ->get();

        return view('partner.dashboard', [
            'partner' => $partner,
            'totalWeddingsCount' => $totalWeddingsCount,
            'totalPublishedCount' => $totalPublishedCount,
            'totalGuestsAttended' => $totalGuestsAttended,
            'recentWeddings' => $recentWeddings,
            'recentCommissions' => $recentCommissions,
        ]);
    }
}
