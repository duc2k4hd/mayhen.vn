<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\MediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Thư viện Media tập trung & Quản trị Dung lượng (Mục 7.5 DOCX).
     */
    public function index(Request $request): View
    {
        $stats = MediaLibraryService::getStorageStats();

        $filters = [
            'category' => $request->category,
            'type' => $request->type,
            'search' => $request->search,
        ];

        $allFiles = MediaLibraryService::listFiles($filters);

        // Phân trang thủ công mượt mà
        $perPage = 24;
        $page = (int) $request->input('page', 1);
        $total = count($allFiles);
        $startingPoint = ($page * $perPage) - $perPage;
        $slicedArray = array_slice($allFiles, $startingPoint, $perPage);

        $paginatedFiles = new LengthAwarePaginator(
            $slicedArray,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.pages.media.index', compact('stats', 'paginatedFiles', 'filters'));
    }

    /**
     * Tải lên tài nguyên Media mới phục vụ hệ thống (Mục 7.5 DOCX).
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,svg,gif,mp3,wav,m4a,pdf', 'max:20480'],
            'folder' => ['nullable', 'string', 'in:client/images,client/musics,admin/assets,weddings'],
        ], [
            'file.required' => 'Vui lòng chọn tệp tin cần tải lên.',
            'file.mimes' => 'Định dạng tệp không được hỗ trợ. Chấp nhận: JPG, PNG, WebP, SVG, GIF, MP3, WAV, PDF.',
            'file.max' => 'Dung lượng tệp tối đa cho phép là 20MB.',
        ]);

        $folder = $request->input('folder', 'client/images');
        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();

        $path = $uploadedFile->store($folder, 'public');

        AuditLogger::log(
            'MEDIA_UPLOADED',
            'Storage',
            0,
            "Quản trị viên tải tệp [{$originalName}] vào thư mục [{$folder}]",
            [
                'original_name' => $originalName,
                'path' => $path,
                'size' => $uploadedFile->getSize(),
                'user_id' => Auth::id(),
            ]
        );

        return back()->with('success', "Đã tải lên tệp {$originalName} thành công vào thư mục {$folder}.");
    }

    /**
     * Xóa vĩnh viễn một tệp tin khỏi Storage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = base64_decode($request->path, true);
        if (! $path || ! Storage::disk('public')->exists($path)) {
            // Thử dùng path trực tiếp nếu không phải base64
            $path = $request->path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Tệp tin không tồn tại hoặc đã bị xóa trước đó.');
        }

        $size = Storage::disk('public')->size($path);
        Storage::disk('public')->delete($path);

        AuditLogger::log(
            'MEDIA_DELETED',
            'Storage',
            0,
            "Quản trị viên xóa tệp tin [{$path}]",
            ['path' => $path, 'size' => $size, 'user_id' => Auth::id()]
        );

        return back()->with('success', "Đã xóa tệp tin {$path} thành công.");
    }

    /**
     * Màn hình Quét & Dọn Dẹp File Không Còn Được Tham Chiếu (Orphaned Assets Cleaner — Mục 7.5 DOCX).
     */
    public function orphans(Request $request): View
    {
        $orphanData = MediaLibraryService::detectOrphanedFiles();

        return view('admin.pages.media.orphans', compact('orphanData'));
    }

    /**
     * Dọn dẹp hàng loạt các tệp mồ côi đã chọn để giải phóng dung lượng ổ đĩa.
     */
    public function purgeOrphans(Request $request): RedirectResponse
    {
        $request->validate([
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string'],
        ], [
            'paths.required' => 'Vui lòng chọn ít nhất một tệp mồ côi cần dọn dẹp.',
        ]);

        $disk = Storage::disk('public');
        $deletedCount = 0;
        $deletedBytes = 0;

        foreach ($request->paths as $encodedPath) {
            $path = base64_decode($encodedPath, true) ?: $encodedPath;

            if ($disk->exists($path)) {
                $deletedBytes += $disk->size($path);
                $disk->delete($path);
                $deletedCount++;
            }
        }

        $formattedBytes = MediaLibraryService::formatBytes($deletedBytes);

        AuditLogger::log(
            'ORPHAN_MEDIA_PURGED',
            'Storage',
            0,
            "Quản trị viên đã dọn dẹp {$deletedCount} tệp rác mồ côi (Giải phóng {$formattedBytes})",
            ['count' => $deletedCount, 'bytes' => $deletedBytes, 'user_id' => Auth::id()]
        );

        return redirect()->route('admin.media.orphans')
            ->with('success', "Đã dọn dẹp thành công {$deletedCount} tệp mồ côi, giải phóng {$formattedBytes} dung lượng ổ đĩa!");
    }
}

