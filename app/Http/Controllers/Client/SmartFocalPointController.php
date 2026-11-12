<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\SmartFocalPointService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartFocalPointController extends Controller
{
    public function __construct(
        protected SmartFocalPointService $focalService
    ) {}

    /**
     * Màn hình Studio Tối Ưu Bố Cục Ảnh Cưới & Điểm Tiêu Cự.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $summary = $this->focalService->getFocalSummary($invitation);

        AuditLogger::log(
            'FOCAL_POINT_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            'Truy cập Studio căn chỉnh tiêu cự ảnh cưới: '.count($summary['images']).' ảnh'
        );

        return view('client.pages.account.focal_point.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'images' => $summary['images'],
            'presets' => $summary['presets'],
        ]);
    }

    /**
     * Cập nhật điểm tiêu cự (x, y) cho một ảnh cưới qua AJAX.
     */
    public function update(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'image_key' => 'required|string|max:64',
            'x' => 'required|numeric|min:0|max:100',
            'y' => 'required|numeric|min:0|max:100',
        ]);

        $this->focalService->updateFocalPoint(
            $invitation,
            $validated['image_key'],
            $validated['x'],
            $validated['y']
        );

        AuditLogger::log(
            'FOCAL_POINT_UPDATED',
            'Invitation',
            $invitation->id,
            "Đã cập nhật tiêu cự ảnh '{$validated['image_key']}' thành ({$validated['x']}%, {$validated['y']}%)"
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu điểm tiêu cự ảnh thành công!',
            'focal_point' => [
                'x' => (int) round($validated['x']),
                'y' => (int) round($validated['y']),
            ],
        ]);
    }

    /**
     * Đưa tiêu cự ảnh về vị trí trung tâm mặc định (50%, 50%).
     */
    public function reset(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'image_key' => 'required|string|max:64',
        ]);

        $this->focalService->resetFocalPoint($invitation, $validated['image_key']);

        AuditLogger::log(
            'FOCAL_POINT_RESET',
            'Invitation',
            $invitation->id,
            "Đã đặt lại tiêu cự ảnh '{$validated['image_key']}' về mặc định (50%, 50%)"
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã đưa tiêu cự về tâm điểm mặc định.',
            'focal_point' => ['x' => 50, 'y' => 50],
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới.
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $invitationId): Invitation
    {
        $user = $request->user();
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        if (! $invitation->wedding || ($invitation->wedding->user_id !== $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập thiệp cưới này.');
        }

        return $invitation;
    }
}

