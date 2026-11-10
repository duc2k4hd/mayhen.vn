<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\OperationsReplayService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsReplayController extends Controller
{
    /**
     * Màn hình tra cứu & Bàn phóng Replay Vận Hành (NXT-14).
     */
    public function index(Request $request): View
    {
        $recentWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $recentOrders = Order::with('user')
            ->select('id', 'order_code', 'user_id', 'amount', 'status', 'created_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.pages.intelligence.replay.index', [
            'recentWeddings' => $recentWeddings,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Bảng điều khiển Tái Hiện Chuỗi Thao Tác Bước-Theo-Bước (Step-by-Step Simulation Sandbox).
     */
    public function show(Request $request, string $type, string $id): View
    {
        $user = $request->user();
        $unmaskRequested = $request->has('unmask') && $request->boolean('unmask');

        // Only super admin can unmask PII
        $canUnmask = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
        $maskPii = ! ($canUnmask && $unmaskRequested);

        if ($unmaskRequested && $canUnmask) {
            AuditLogger::log(
                'unmask_replay_pii',
                'OperationsReplay',
                $id,
                "Super Admin {$user->name} mở kính giải mã PII trong buồng Replay chuỗi vận hành {$type} #{$id}",
                ['type' => $type, 'id' => $id]
            );
        }

        $replayData = OperationsReplayService::assembleTimeline($type, $id, $maskPii);

        return view('admin.pages.intelligence.replay.show', [
            'type' => $type,
            'id' => $id,
            'replay' => $replayData,
            'isMasked' => $maskPii,
            'canUnmask' => $canUnmask,
        ]);
    }
}
