<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\WeddingArchiveExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WeddingArchiveExportController extends Controller
{
    public function __construct(
        protected WeddingArchiveExportService $archiveExportService
    ) {}

    /**
     * Tải trọn bộ gói dữ liệu kỷ niệm hôn lễ (.ZIP).
     */
    public function download(Invitation $invitation): BinaryFileResponse
    {
        $userId = auth()->id();
        $weddingUserId = $invitation->wedding?->user_id;
        $sessionOwnedSlugs = session('owned_invitation_slugs', []);

        $isOwner = ($userId && $weddingUserId && (int) $userId === (int) $weddingUserId)
            || in_array($invitation->slug, (array) $sessionOwnedSlugs, true);

        if (! $isOwner) {
            abort(403, 'Bạn không có quyền tải gói dữ liệu kỷ niệm của thiệp cưới này.');
        }

        $archive = $this->archiveExportService->createArchive($invitation);

        AuditLogger::log(
            'WEDDING_ARCHIVE_EXPORTED',
            Invitation::class,
            $invitation->id,
            "Tải trọn bộ kho lưu trữ kỷ niệm cưới .ZIP cho thiệp #{$invitation->slug}",
            [
                'user_id' => $userId,
                'filename' => $archive['filename'],
                'files_count' => $archive['files_count'],
                'size_bytes' => $archive['size_bytes'],
            ]
        );

        return response()->download($archive['zip_path'], $archive['filename'], [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
