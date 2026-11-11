<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Services\SystemBackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminBackupController extends Controller
{
    public function __construct(
        protected SystemBackupService $backupService
    ) {}

    /**
     * Danh sách các bản snapshot & bảng điều khiển thảm họa (Disaster Recovery Cockpit).
     */
    public function index(): View
    {
        $backups = SystemBackup::with('creator')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $totalBackups = SystemBackup::count();
        $totalBytes = SystemBackup::sum('file_size_bytes');
        $latestBackup = SystemBackup::latest('id')->first();
        $verifiedCount = SystemBackup::whereNotNull('verified_at')->count();
        $verifiedPct = $totalBackups > 0 ? round(($verifiedCount / $totalBackups) * 100, 1) : 0;

        $formattedTotalSize = $totalBytes < 1048576
            ? round($totalBytes / 1024, 2).' KB'
            : round($totalBytes / 1048576, 2).' MB';

        return view('admin.pages.settings.backups.index', compact(
            'backups',
            'totalBackups',
            'formattedTotalSize',
            'latestBackup',
            'verifiedPct',
            'verifiedCount'
        ));
    }

    /**
     * Tạo bản snapshot cơ sở dữ liệu mới tức thời.
     */
    public function create(Request $request): RedirectResponse
    {
        $note = (string) $request->input('note', '');

        try {
            $backup = $this->backupService->createDatabaseBackup($request->user(), $note);

            return back()->with('success', "Đã tạo thành công bản snapshot cơ sở dữ liệu [{$backup->filename}] ({$backup->formatted_size}).");
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi tạo bản sao lưu: '.$e->getMessage());
        }
    }

    /**
     * Kiểm thử phục hồi bản sao lưu độc lập (OPS-01 Verification).
     */
    public function verify(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $result = $this->backupService->testRestoreVerification($id, $request->user());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Tải về bản sao lưu an toàn.
     */
    public function download(Request $request, int $id): BinaryFileResponse
    {
        return $this->backupService->downloadBackup($id, $request->user());
    }

    /**
     * Xóa một bản sao lưu.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $deleted = $this->backupService->deleteBackup($id, $request->user());

        if ($deleted) {
            return back()->with('success', 'Đã xóa bản sao lưu thành công khỏi kho lưu trữ.');
        }

        return back()->with('error', 'Không tìm thấy bản sao lưu cần xóa.');
    }

    /**
     * Dọn dẹp các bản sao lưu cũ theo chính sách lưu trữ (Retention Policy).
     */
    public function cleanOld(Request $request): RedirectResponse
    {
        $keep = (int) $request->input('keep_count', 7);
        $deletedCount = $this->backupService->cleanOldBackups($keep, $request->user());

        if ($deletedCount === 0) {
            return back()->with('info', "Kho lưu trữ hiện tại có ít hơn hoặc bằng {$keep} bản snapshot. Không có bản nào cần dọn dẹp.");
        }

        return back()->with('success', "Đã dọn dẹp {$deletedCount} bản snapshot cũ hơn {$keep} bản gần nhất.");
    }
}
