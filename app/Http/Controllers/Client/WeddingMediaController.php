<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingStorageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WeddingMediaController extends Controller
{
    public function __construct(
        protected WeddingStorageService $storageService
    ) {}

    /**
     * Màn hình Kho Tệp Media & Quản Lý Dung Lượng Lưu Trữ Cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.user', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $usage = $this->storageService->getStorageUsage($wedding, $invitation);

        return view('client.pages.account.media.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'usage' => $usage,
        ]);
    }

    /**
     * Xóa tệp media để giải phóng dung lượng lưu trữ.
     */
    public function destroy(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'storage_path' => 'required|string',
        ]);

        $storagePath = (string) $request->input('storage_path');
        $success = $this->storageService->deleteMediaFile($invitation->wedding, $invitation, $storagePath);

        if (! $success) {
            return back()->with('error', 'Không thể xóa tệp media này hoặc đường dẫn không hợp lệ.');
        }

        return back()->with('success', 'Đã xóa tệp media và giải phóng dung lượng lưu trữ thành công.');
    }

    /**
     * API kiểm tra tình trạng hạn mức lưu trữ (phục vụ Studio và Uploader realtime).
     */
    public function quota(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $usage = $this->storageService->getStorageUsage($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => [
                'used_bytes' => $usage['used_bytes'],
                'used_mb' => $usage['used_mb'],
                'limit_mb' => $usage['limit_mb'],
                'remaining_mb' => $usage['remaining_mb'],
                'percent' => $usage['percent'],
                'is_exceeded' => $usage['is_exceeded'],
                'total_files_count' => $usage['total_files_count'],
            ],
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        if ($invitation->user_id !== auth()->id() && $invitation->wedding?->user_id !== auth()->id()) {
            abort(404);
        }
    }
}

