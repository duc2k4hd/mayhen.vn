<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvitationAnalyticsController extends Controller
{
    /**
     * Báo cáo phân tích hiệu suất tương tác & phễu chuyển đổi thiệp cưới.
     */
    public function index(Request $request, int $id, WeddingAnalyticsService $analyticsService): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($id);
        abort_unless($invitation->isOwner(), 404);

        $days = (int) $request->query('days', 30);
        if (! in_array($days, [7, 14, 30, 90], true)) {
            $days = 30;
        }

        $stats = $analyticsService->getInvitationDashboardStats($invitation, $days);

        return view('client.pages.account.analytics', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'stats' => $stats,
            'days' => $days,
        ]);
    }

    /**
     * Xuất dữ liệu nhật ký phân tích ra file CSV.
     */
    public function exportCsv(Request $request, int $id, WeddingAnalyticsService $analyticsService): Response
    {
        $invitation = Invitation::findOrFail($id);
        abort_unless($invitation->isOwner(), 404);

        $days = (int) $request->query('days', 30);
        if (! in_array($days, [7, 14, 30, 90], true)) {
            $days = 30;
        }

        $csv = $analyticsService->exportAnalyticsCsv($invitation, $days);
        $filename = 'thong_ke_thiep_cuoi_'.($invitation->slug ?: $invitation->id)."_{$days}_ngay.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

