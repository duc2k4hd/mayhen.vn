<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PartnerCommissionController extends Controller
{
    /**
     * Bảng theo dõi hoa hồng & chiết khấu đối tác Studio.
     */
    public function index(Request $request): View
    {
        $partner = $this->resolvePartner();

        $query = $partner->commissions()->with(['wedding', 'order']);

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $commissions = $query->latest()->paginate(15)->withQueryString();

        // Thống kê tài chính B2B
        $totalEarned = (float) $partner->commissions()->whereIn('status', ['approved', 'paid'])->sum('amount');
        $totalPending = (float) $partner->commissions()->where('status', 'pending')->sum('amount');
        $totalPaidOut = (float) $partner->commissions()->where('type', 'payout')->sum('amount');

        return view('partner.commissions.index', [
            'partner' => $partner,
            'commissions' => $commissions,
            'totalEarned' => $totalEarned,
            'totalPending' => $totalPending,
            'totalPaidOut' => $totalPaidOut,
            'type' => $type,
            'status' => $status,
        ]);
    }

    private function resolvePartner(): Partner
    {
        $user = auth()->user();
        if ($user->partner) {
            return $user->partner;
        }

        if ($user->isAdmin()) {
            $partner = Partner::first();
            if ($partner) {
                return $partner;
            }
        }

        abort(403, 'Bạn chưa có thông tin đối tác Studio.');
    }
}
