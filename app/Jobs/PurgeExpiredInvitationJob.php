<?php

namespace App\Jobs;

use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\CloudflareR2StorageService;
use App\Services\VisualEditorSanitizerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Hàng đợi dọn dẹp vĩnh viễn tệp media nặng của thiệp đã hết hạn / đã bị khách xóa
 * để giải phóng dung lượng máy chủ nhưng vẫn lưu trữ bản ghi & snapshot nhẹ cho Admin xem trọn đời.
 */
class PurgeExpiredInvitationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $invitationId,
        public string $reason = 'expired'
    ) {}

    public function handle(CloudflareR2StorageService $r2Storage): void
    {
        $invitation = Invitation::withTrashed()
            ->with(['wedding.user', 'template'])
            ->find($this->invitationId);

        if (! $invitation || $invitation->purged_at !== null) {
            return;
        }

        // Phòng vệ an toàn: Nếu khách vừa gia hạn thiệp kịp lúc trước khi job chạy -> hủy dọn dẹp!
        if ($this->reason === 'expired' && $invitation->deleted_by_user_at === null) {
            if ($invitation->expires_at === null || $invitation->expires_at->isFuture()) {
                $invitation->forceFill(['purge_queued_at' => null])->saveQuietly();

                return;
            }
        }

        $wedding = $invitation->wedding;
        $owner = $invitation->owner ?? $wedding?->user;

        // 1. Thu thập và xóa vĩnh viễn các tệp media nặng không còn được thiệp hoạt động nào khác dùng chung
        $mediaResult = $this->purgeUnsharedMediaFiles($invitation, $r2Storage);

        // 2. Xây dựng bản chụp hồ sơ nhẹ (Snapshot) lưu vĩnh viễn cho Quản trị viên xem trọn đời
        $snapshot = [
            'archived_at' => now()->toIso8601String(),
            'archive_reason' => $this->reason,
            'plan_slug' => $invitation->plan_slug ?: 'free',
            'slug' => $invitation->slug,
            'title' => $invitation->title,
            'couple' => [
                'groom_name' => $wedding?->groom_name,
                'bride_name' => $wedding?->bride_name,
                'groom_parents' => $wedding?->groom_parents,
                'bride_parents' => $wedding?->bride_parents,
                'wedding_date' => $wedding?->wedding_date?->format('Y-m-d'),
                'venue_name' => $wedding?->venue_name,
                'venue_address' => $wedding?->venue_address,
            ],
            'owner' => [
                'user_id' => $owner?->id ?? $invitation->owner_user_id,
                'name' => $owner?->name,
                'email' => $owner?->email,
                'phone' => $owner?->phone,
            ],
            'template' => [
                'id' => $invitation->template_id,
                'name' => $invitation->template?->name,
                'slug' => $invitation->template?->slug,
            ],
            'stats' => [
                'view_count' => (int) $invitation->view_count,
                'guests_count' => $wedding ? $wedding->guests()->count() : 0,
                'wishes_count' => $wedding ? $wedding->wishes()->count() : 0,
                'gifts_count' => $wedding ? $wedding->gifts()->count() : 0,
            ],
            'timeline' => [
                'created_at' => $invitation->created_at?->toIso8601String(),
                'published_at' => $invitation->published_at?->toIso8601String(),
                'expires_at' => $invitation->expires_at?->toIso8601String(),
                'deleted_by_user_at' => $invitation->deleted_by_user_at?->toIso8601String(),
            ],
            'purged_media' => $mediaResult,
        ];

        // 3. Dọn bảng lịch sử phiên bản & bản nháp nặng để tiết kiệm dung lượng CSDL
        try {
            $invitation->revisions()->delete();
        } catch (\Throwable $e) {
            Log::warning("Không thể dọn revisions khi purge thiệp #{$invitation->id}: ".$e->getMessage());
        }

        // Giữ lại metadata quản trị tối thiểu trong custom_config, gỡ dữ liệu rác lớn
        $existingConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $compactConfig = [
            '_archived_for_admin' => true,
            'archive_reason' => $this->reason,
            'purged_at' => now()->toIso8601String(),
            'admin_notes' => $existingConfig['admin_notes'] ?? null,
        ];

        // Nếu toàn bộ thiệp thuộc wedding này đều đã purged/deleted thì gỡ đường dẫn ảnh chết trên wedding
        if ($wedding && $mediaResult['deleted_files_count'] > 0) {
            $hasActiveSibling = Invitation::query()
                ->where('wedding_id', $wedding->id)
                ->whereKeyNot($invitation->id)
                ->whereNull('purged_at')
                ->exists();

            if (! $hasActiveSibling) {
                $weddingExtra = is_array($wedding->extra_data) ? $wedding->extra_data : [];
                $weddingExtra['gallery'] = [];
                unset($weddingExtra['hero_photo_url']);

                $wedding->update([
                    'cover_photo_url' => null,
                    'extra_data' => $weddingExtra,
                ]);
            }
        }

        $invitation->forceFill([
            'is_published' => false,
            'draft_config' => null,
            'custom_config' => $compactConfig,
            'purged_at' => now(),
            'admin_archive_snapshot' => $snapshot,
        ])->save();

        // Soft-delete để ẩn hoàn toàn khỏi phía Client & Public, nhưng Admin vẫn xem được trọn đời qua withTrashed()
        if (! $invitation->trashed()) {
            $invitation->delete();
        }

        Cache::forget("invitation_{$invitation->slug}");

        AuditLogger::log(
            'INVITATION_AUTO_PURGED',
            'Invitation',
            $invitation->id,
            "Hàng đợi đã dọn vĩnh viễn {$mediaResult['deleted_files_count']} tệp media ({$mediaResult['freed_mb']} MB) của thiệp [{$invitation->slug}] (Lý do: {$this->reason}) và lưu hồ sơ trọn đời cho Admin.",
            [
                'invitation_id' => $invitation->id,
                'slug' => $invitation->slug,
                'plan_slug' => $invitation->plan_slug,
                'reason' => $this->reason,
                'deleted_files_count' => $mediaResult['deleted_files_count'],
                'freed_bytes' => $mediaResult['freed_bytes'],
            ]
        );
    }

    /**
     * Xóa vĩnh viễn tệp media của thiệp trên đĩa Local/R2 nhưng tuyệt đối không xóa tệp đang được thiệp khác dùng chung.
     *
     * @return array{deleted_files_count: int, freed_bytes: int, freed_mb: float, deleted_paths: array<int, string>}
     */
    protected function purgeUnsharedMediaFiles(Invitation $invitation, CloudflareR2StorageService $r2Storage): array
    {
        $candidatePaths = $this->extractStoragePathsFromInvitation($invitation, $r2Storage);
        $wedding = $invitation->wedding;

        // Xác định các đường dẫn còn đang được thiệp sống khác cùng wedding tham chiếu (VD: thiệp nhân bản Nhà Trai / Nhà Gái)
        $sharedPaths = [];
        if ($wedding) {
            $activeSiblings = Invitation::query()
                ->where('wedding_id', $wedding->id)
                ->whereKeyNot($invitation->id)
                ->whereNull('purged_at')
                ->get();

            foreach ($activeSiblings as $sibling) {
                foreach (array_keys($this->extractStoragePathsFromInvitation($sibling, $r2Storage)) as $relPath) {
                    $sharedPaths[$relPath] = true;
                }
            }
        }

        $disk = Storage::disk('public');
        $deletedCount = 0;
        $freedBytes = 0;
        $deletedPaths = [];

        foreach (array_keys($candidatePaths) as $relPath) {
            if (isset($sharedPaths[$relPath])) {
                continue;
            }

            if (! $this->isSafeDeletablePath($relPath, $wedding?->id)) {
                continue;
            }

            $fileDeleted = false;
            if ($disk->exists($relPath)) {
                $size = (int) $disk->size($relPath);
                if ($disk->delete($relPath)) {
                    $freedBytes += $size;
                    $fileDeleted = true;
                }
            }

            if ($r2Storage->isConfigured()) {
                try {
                    if ($r2Storage->deleteObject($relPath)) {
                        $fileDeleted = true;
                    }
                } catch (\Throwable $e) {
                    Log::warning("Không thể xóa tệp R2 [{$relPath}] khi purge thiệp #{$invitation->id}: ".$e->getMessage());
                }
            }

            if ($fileDeleted) {
                $deletedCount++;
                $deletedPaths[] = $relPath;
            }
        }

        // Xóa thư mục guest_photos/{wedding_id} nếu không còn thiệp sống nào thuộc wedding này
        if ($wedding && empty($sharedPaths)) {
            $hasActiveSibling = Invitation::query()
                ->where('wedding_id', $wedding->id)
                ->whereKeyNot($invitation->id)
                ->whereNull('purged_at')
                ->exists();

            $guestDir = "guest_photos/{$wedding->id}";
            if (! $hasActiveSibling && $disk->exists($guestDir)) {
                foreach ($disk->files($guestDir) as $gPath) {
                    $size = (int) $disk->size($gPath);
                    if ($disk->delete($gPath)) {
                        $freedBytes += $size;
                        $deletedCount++;
                        $deletedPaths[] = $gPath;
                    }
                }
            }
        }

        return [
            'deleted_files_count' => $deletedCount,
            'freed_bytes' => $freedBytes,
            'freed_mb' => round($freedBytes / (1024 * 1024), 2),
            'deleted_paths' => array_slice($deletedPaths, 0, 50),
        ];
    }

    /**
     * @return array<string, bool>
     */
    protected function extractStoragePathsFromInvitation(Invitation $invitation, CloudflareR2StorageService $r2Storage): array
    {
        $urls = [];
        $wedding = $invitation->wedding;

        if ($wedding) {
            if (! empty($wedding->cover_photo_url)) {
                $urls[] = (string) $wedding->cover_photo_url;
            }
            if (! empty($wedding->music_url)) {
                $urls[] = (string) $wedding->music_url;
            }

            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : [];
            if (! empty($extra['hero_photo_url'])) {
                $urls[] = (string) $extra['hero_photo_url'];
            }
            if (! empty($extra['gallery']) && is_array($extra['gallery'])) {
                foreach ($extra['gallery'] as $img) {
                    if (is_string($img)) {
                        $urls[] = $img;
                    }
                }
            }
            foreach ($extra as $val) {
                if (is_string($val) && (str_contains($val, '/storage/') || str_contains($val, 'media.mayhen.vn') || str_contains($val, 'r2.cloudflarestorage.com') || str_contains($val, '.r2.dev'))) {
                    $urls[] = $val;
                }
            }
        }

        $customConfig = is_array($invitation->custom_config) ? $invitation->custom_config : [];
        $draftConfig = is_array($invitation->draft_config) ? $invitation->draft_config : [];

        $customUrls = array_merge(
            VisualEditorSanitizerService::extractReferencedAssetUrls($customConfig),
            $this->collectAssetUrlsRecursive($customConfig)
        );
        $draftUrls = array_merge(
            VisualEditorSanitizerService::extractReferencedAssetUrls($draftConfig),
            $this->collectAssetUrlsRecursive($draftConfig)
        );

        $paths = [];
        foreach (array_merge($urls, $customUrls, $draftUrls) as $url) {
            if (! is_string($url) || trim($url) === '') {
                continue;
            }
            if (str_contains($url, '/storage/')) {
                $rel = ltrim(substr($url, strpos($url, '/storage/') + 9), '/');
                if ($rel !== '') {
                    $paths[$rel] = true;
                }
            } elseif (str_contains($url, 'media.mayhen.vn') || str_contains($url, 'r2.cloudflarestorage.com') || str_contains($url, '.r2.dev')) {
                $rel = ltrim($r2Storage->extractStoragePath($url), '/');
                if ($rel !== '') {
                    $paths[$rel] = true;
                }
            }
        }

        return $paths;
    }

    /**
     * Quét đệ quy mọi chuỗi đường dẫn tài nguyên (/storage/ hoặc R2) bên trong mảng cấu hình.
     *
     * @param  array<mixed>  $data
     * @return list<string>
     */
    protected function collectAssetUrlsRecursive(array $data): array
    {
        $found = [];

        foreach ($data as $value) {
            if (is_array($value)) {
                $found = array_merge($found, $this->collectAssetUrlsRecursive($value));
            } elseif (is_string($value) && (str_contains($value, '/storage/') || str_contains($value, 'media.mayhen.vn') || str_contains($value, 'r2.cloudflarestorage.com') || str_contains($value, '.r2.dev'))) {
                $found[] = $value;
            }
        }

        return $found;
    }

    protected function isSafeDeletablePath(string $relPath, ?int $weddingId): bool
    {
        if (str_contains($relPath, '..') || str_starts_with($relPath, '/') || str_starts_with($relPath, '\\')) {
            return false;
        }

        $allowedPrefixes = [
            'weddings/photos/',
            'weddings/audio/',
            'weddings/music/',
        ];

        if ($weddingId !== null) {
            $allowedPrefixes[] = "guest_photos/{$weddingId}/";
        }

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($relPath, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
