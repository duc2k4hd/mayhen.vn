<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class WeddingStorageService
{
    /**
     * Hạn mức dung lượng lưu trữ mặc định theo từng phân tầng gói (tính bằng MB).
     */
    public const QUOTA_LIMITS_MB = [
        'free' => 50,
        'trial' => 50,
        'premium' => 250,
        'plus' => 250,
        'pro' => 1024,
    ];

    /**
     * Xác định hạn mức dung lượng tối đa (bytes) cho thiệp cưới.
     */
    public function getQuotaLimitBytes(Invitation $invitation): int
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        // Xác định phân tầng gói cước
        $tier = strtolower((string) ($config['tier'] ?? ($invitation->template?->default_config['tier'] ?? 'free')));

        // Kiểm tra đơn hàng active gần nhất của user nếu có
        $user = $invitation->wedding?->user;
        if ($user) {
            $latestOrder = $user->orders()
                ->where('status', 'paid')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->with('plan')
                ->latest()
                ->first();

            if ($latestOrder && $latestOrder->plan) {
                $planSlug = strtolower((string) $latestOrder->plan->slug);
                if (isset(self::QUOTA_LIMITS_MB[$planSlug])) {
                    $tier = $planSlug;
                }
            }
        }

        $limitMb = self::QUOTA_LIMITS_MB[$tier] ?? self::QUOTA_LIMITS_MB['free'];

        return $limitMb * 1024 * 1024;
    }

    /**
     * Tổng hợp tình trạng sử dụng dung lượng lưu trữ của thiệp cưới.
     *
     * @return array<string, mixed>
     */
    public function getStorageUsage(Wedding $wedding, Invitation $invitation): array
    {
        $files = $this->getMediaFiles($wedding, $invitation);

        $usedBytes = 0;
        $weddingPhotosBytes = 0;
        $guestPhotosBytes = 0;

        foreach ($files as $file) {
            $size = (int) ($file['size_bytes'] ?? 0);
            $usedBytes += $size;
            if ($file['type'] === 'guest_photo') {
                $guestPhotosBytes += $size;
            } else {
                $weddingPhotosBytes += $size;
            }
        }

        $limitBytes = $this->getQuotaLimitBytes($invitation);
        $percent = $limitBytes > 0 ? round(($usedBytes / $limitBytes) * 100, 1) : 0;
        $isExceeded = $usedBytes >= $limitBytes;

        return [
            'used_bytes' => $usedBytes,
            'used_mb' => round($usedBytes / (1024 * 1024), 2),
            'limit_bytes' => $limitBytes,
            'limit_mb' => round($limitBytes / (1024 * 1024), 0),
            'remaining_bytes' => max(0, $limitBytes - $usedBytes),
            'remaining_mb' => round(max(0, $limitBytes - $usedBytes) / (1024 * 1024), 2),
            'percent' => min(100, $percent),
            'is_exceeded' => $isExceeded,
            'total_files_count' => count($files),
            'breakdown' => [
                'wedding_photos' => [
                    'bytes' => $weddingPhotosBytes,
                    'mb' => round($weddingPhotosBytes / (1024 * 1024), 2),
                    'count' => count(array_filter($files, fn ($f) => $f['type'] !== 'guest_photo')),
                ],
                'guest_photos' => [
                    'bytes' => $guestPhotosBytes,
                    'mb' => round($guestPhotosBytes / (1024 * 1024), 2),
                    'count' => count(array_filter($files, fn ($f) => $f['type'] === 'guest_photo')),
                ],
            ],
            'files' => $files,
        ];
    }

    /**
     * Kiểm tra xem việc tải thêm một tệp có kích thước $additionalBytes có vượt quá hạn mức không.
     */
    public function canUpload(Wedding $wedding, Invitation $invitation, int $additionalBytes): bool
    {
        $usage = $this->getStorageUsage($wedding, $invitation);

        return ($usage['used_bytes'] + $additionalBytes) <= $usage['limit_bytes'];
    }

    /**
     * Liệt kê danh sách tất cả các tệp media thuộc về đám cưới này.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMediaFiles(Wedding $wedding, Invitation $invitation): array
    {
        $disk = Storage::disk('public');
        $files = [];

        // 1. Quét ảnh trong gallery và cover của Wedding
        $coverUrl = (string) ($wedding->cover_photo_url ?? '');
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $gallery = (array) ($extra['gallery'] ?? []);
        $heroUrl = (string) ($extra['hero_photo_url'] ?? '');

        // Quét thư mục weddings/photos
        $allWeddingPhotos = $disk->exists('weddings/photos') ? $disk->files('weddings/photos') : [];

        // Tìm các ảnh được tham chiếu trực tiếp bởi wedding này
        $activeUrls = array_filter(array_merge([$coverUrl, $heroUrl], $gallery));
        $activeStoragePaths = [];
        foreach ($activeUrls as $url) {
            if (is_string($url) && str_contains($url, '/storage/')) {
                $rel = ltrim(substr($url, strpos($url, '/storage/') + 9), '/');
                $activeStoragePaths[$rel] = true;
            }
        }

        foreach ($allWeddingPhotos as $path) {
            // Chỉ tính những file có trong danh sách active của thiệp này (tránh tính nhầm ảnh của thiệp khác cùng thư mục)
            if (! isset($activeStoragePaths[$path])) {
                continue;
            }

            $size = $disk->exists($path) ? $disk->size($path) : 0;
            $fileName = basename($path);
            $mime = File::mimeType($disk->path($path)) ?: 'image/jpeg';

            $fileType = 'gallery';
            if ($coverUrl && str_contains($coverUrl, $fileName)) {
                $fileType = 'cover';
            } elseif ($heroUrl && str_contains($heroUrl, $fileName)) {
                $fileType = 'hero';
            }

            $files[] = [
                'storage_path' => $path,
                'file_name' => $fileName,
                'url' => asset('storage/'.$path),
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
                'mime' => $mime,
                'type' => $fileType,
                'type_label' => match ($fileType) {
                    'cover' => 'Ảnh Bìa Thiệp',
                    'hero' => 'Ảnh Chính (Hero)',
                    default => 'Ảnh Album Cưới',
                },
                'is_in_use' => true,
                'created_at' => $disk->exists($path) ? date('Y-m-d H:i:s', $disk->lastModified($path)) : null,
            ];
        }

        // 2. Quét ảnh PhotoDrop của khách chụp
        $guestPhotoDir = "guest_photos/{$wedding->id}";
        if ($disk->exists($guestPhotoDir)) {
            $guestFiles = $disk->files($guestPhotoDir);
            foreach ($guestFiles as $gPath) {
                $size = $disk->size($gPath);
                $fileName = basename($gPath);
                $mime = File::mimeType($disk->path($gPath)) ?: 'image/jpeg';

                $files[] = [
                    'storage_path' => $gPath,
                    'file_name' => $fileName,
                    'url' => asset('storage/'.$gPath),
                    'size_bytes' => $size,
                    'size_formatted' => $this->formatBytes($size),
                    'mime' => $mime,
                    'type' => 'guest_photo',
                    'type_label' => 'Ảnh Khách Chụp',
                    'is_in_use' => false,
                    'created_at' => date('Y-m-d H:i:s', $disk->lastModified($gPath)),
                ];
            }
        }

        // Sắp xếp theo ngày tạo mới nhất
        usort($files, function ($a, $b) {
            return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
        });

        return $files;
    }

    /**
     * Xóa an toàn một tệp media khỏi ổ đĩa lưu trữ, chống path traversal.
     */
    public function deleteMediaFile(Wedding $wedding, Invitation $invitation, string $storagePath): bool
    {
        // Kiểm tra chống Path Traversal attack
        if (str_contains($storagePath, '..') || str_starts_with($storagePath, '/') || str_starts_with($storagePath, '\\')) {
            return false;
        }

        // Chỉ cho phép xóa trong 2 thư mục được cấp quyền của wedding
        $allowedPrefixes = [
            'weddings/photos/',
            "guest_photos/{$wedding->id}/",
        ];

        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($storagePath, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        if (! $isAllowed) {
            return false;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($storagePath)) {
            return false;
        }

        // Xóa file vật lý
        $deleted = $disk->delete($storagePath);

        if ($deleted) {
            // Tự động gỡ URL khỏi wedding extra_data.gallery nếu có
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
            if (! empty($extra['gallery']) && is_array($extra['gallery'])) {
                $fileName = basename($storagePath);
                $extra['gallery'] = array_values(array_filter($extra['gallery'], function ($url) use ($fileName) {
                    return ! str_contains((string) $url, $fileName);
                }));
                $wedding->update(['extra_data' => $extra]);
            }

            // Ghi Audit Trail
            AuditLogger::log(
                'MEDIA_FILE_DELETED',
                'Wedding',
                $wedding->id,
                "Đã xóa tệp media [{$storagePath}] để giải phóng dung lượng cho thiệp [{$invitation->slug}].",
                [
                    'invitation_id' => $invitation->id,
                    'storage_path' => $storagePath,
                ]
            );
        }

        return $deleted;
    }

    /**
     * Chuyển đổi số bytes sang định dạng dung lượng dễ đọc (KB, MB).
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
