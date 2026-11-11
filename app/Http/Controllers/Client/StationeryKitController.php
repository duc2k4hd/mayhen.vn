<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\StationeryKitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StationeryKitController extends Controller
{
    /**
     * Màn hình Studio Bộ Ấn Phẩm Đồng Bộ (Mục 2.7 DOCX).
     */
    public function index(int $id): View
    {
        $invitation = Invitation::with(['wedding.events', 'template'])->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $stationeryData = StationeryKitService::getStationeryData($wedding, $invitation);
        $presets = StationeryKitService::getPresets();
        $palettes = StationeryKitService::getColorPalettes();

        return view('client.pages.account.stationery.index', compact(
            'invitation',
            'wedding',
            'stationeryData',
            'presets',
            'palettes'
        ));
    }

    /**
     * Ghi nhận lượt tải ấn phẩm vào Audit Logger và thống kê hành vi người dùng.
     */
    public function logDownload(Request $request, int $id): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Không có quyền thao tác'], 403);
        }

        $validated = $request->validate([
            'preset' => ['required', 'string', 'in:story,save_the_date,vertical_card,table_qr,timeline,bao_hy,thank_you'],
            'palette' => ['required', 'string', 'max:50'],
            'format' => ['required', 'string', 'in:png,jpeg,pdf,print'],
        ]);

        AuditLogger::log(
            'STATIONERY_DOWNLOADED',
            'Invitation',
            $invitation->id,
            "Người dùng tải ấn phẩm đồng bộ [{$validated['preset']}] định dạng [{$validated['format']}] tông màu [{$validated['palette']}]",
            [
                'preset' => $validated['preset'],
                'palette' => $validated['palette'],
                'format' => $validated['format'],
                'wedding_id' => $wedding->id,
                'user_id' => Auth::id(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã ghi nhận lượt tải ấn phẩm thành công.',
        ]);
    }
}

