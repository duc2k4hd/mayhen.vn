<?php

namespace App\Services;

use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PaperInvitationScan;
use App\Models\Review;
use App\Models\Wedding;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class MediaLibraryService
{
    /**
     * Quét 1 lần duy nhất danh sách tệp trong disk `public` kèm size & mtime (tránh lặp `$disk->size`/`lastModified`).
     *
     * @return array<int, array{path: string, size: int, last_modified: int}>
     */
    public static function scanPublicFiles(bool $forceRefresh = false): array
    {
        $cacheKey = 'mayhen_admin_public_files_scan_v1';
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        $scanner = static function (): array {
            $disk = Storage::disk('public');
            $root = rtrim($disk->path(''), DIRECTORY_SEPARATOR);
            if (! is_dir($root)) {
                return [];
            }

            $files = [];
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );
            $prefixLen = strlen($root) + 1;

            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile()) {
                    continue;
                }
                $relPath = str_replace('\\', '/', substr($fileInfo->getPathname(), $prefixLen));
                $baseName = $fileInfo->getFilename();
                if ($relPath === '' || str_starts_with($baseName, '.')) {
                    continue;
                }

                $files[] = [
                    'path' => $relPath,
                    'size' => (int) $fileInfo->getSize(),
                    'last_modified' => (int) $fileInfo->getMTime(),
                ];
            }

            return $files;
        };

        if (app()->runningUnitTests()) {
            return $scanner();
        }

        return Cache::remember($cacheKey, 20, $scanner);
    }

    public static function forgetScanCache(): void
    {
        Cache::forget('mayhen_admin_public_files_scan_v1');
    }

    /**
     * Định dạng dung lượng byte sang KB/MB/GB dễ đọc.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $pow = min((int) floor($base), count($units) - 1);

        return round(pow(1024, $base - $pow), $precision).' '.$units[$pow];
    }

    /**
     * Thu thập thống kê tổng quan về dung lượng lưu trữ và phân loại tệp tin.
     *
     * @return array{total_files: int, total_bytes: int, formatted_total: string, categories: array<string, array{count: int, bytes: int, formatted: string}>, types: array<string, array{count: int, bytes: int}>}
     */
    public static function getStorageStats(): array
    {
        $allFiles = self::scanPublicFiles();

        $totalBytes = 0;
        $categories = [
            'musics' => ['count' => 0, 'bytes' => 0, 'label' => 'Âm Nhạc Cưới (MP3)'],
            'weddings' => ['count' => 0, 'bytes' => 0, 'label' => 'Ảnh Cưới Khách Hàng'],
            'templates' => ['count' => 0, 'bytes' => 0, 'label' => 'Tài Nguyên Mẫu Thiệp'],
            'assets' => ['count' => 0, 'bytes' => 0, 'label' => 'Biểu Tượng & Hoa Văn'],
            'other' => ['count' => 0, 'bytes' => 0, 'label' => 'Tài Liệu & Khác'],
        ];

        $types = [
            'audio' => ['count' => 0, 'bytes' => 0],
            'image' => ['count' => 0, 'bytes' => 0],
            'document' => ['count' => 0, 'bytes' => 0],
            'other' => ['count' => 0, 'bytes' => 0],
        ];

        foreach ($allFiles as $entry) {
            $file = $entry['path'];
            $size = $entry['size'];
            $totalBytes += $size;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Phân loại thư mục
            if (str_contains($file, 'music') || str_contains($file, 'musics')) {
                $categories['musics']['count']++;
                $categories['musics']['bytes'] += $size;
            } elseif (str_starts_with($file, 'weddings/') || str_starts_with($file, 'paper_scans/') || str_starts_with($file, 'guest_photos/')) {
                $categories['weddings']['count']++;
                $categories['weddings']['bytes'] += $size;
            } elseif (str_contains($file, 'template') || str_contains($file, 'templates')) {
                $categories['templates']['count']++;
                $categories['templates']['bytes'] += $size;
            } elseif (str_contains($file, 'images') || str_contains($file, 'business')) {
                $categories['assets']['count']++;
                $categories['assets']['bytes'] += $size;
            } else {
                $categories['other']['count']++;
                $categories['other']['bytes'] += $size;
            }

            // Phân loại loại file
            if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'])) {
                $types['audio']['count']++;
                $types['audio']['bytes'] += $size;
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                $types['image']['count']++;
                $types['image']['bytes'] += $size;
            } elseif (in_array($ext, ['pdf', 'csv', 'xlsx', 'txt'])) {
                $types['document']['count']++;
                $types['document']['bytes'] += $size;
            } else {
                $types['other']['count']++;
                $types['other']['bytes'] += $size;
            }
        }

        foreach ($categories as $k => $data) {
            $categories[$k]['formatted'] = self::formatBytes($data['bytes']);
        }

        return [
            'total_files' => count($allFiles),
            'total_bytes' => $totalBytes,
            'formatted_total' => self::formatBytes($totalBytes),
            'categories' => $categories,
            'types' => $types,
        ];
    }

    /**
     * Liệt kê danh sách tệp tin có lọc và phân trang thủ công an toàn.
     *
     * @param  array{category?: ?string, type?: ?string, search?: ?string}  $filters
     * @return array<int, array{path: string, name: string, category: string, ext: string, type: string, size: int, formatted_size: string, url: string, last_modified: int, modified_formatted: string}>
     */
    public static function listFiles(array $filters = []): array
    {
        $disk = Storage::disk('public');
        $allFiles = self::scanPublicFiles();
        $results = [];

        $categoryFilter = $filters['category'] ?? null;
        $typeFilter = $filters['type'] ?? null;
        $searchFilter = ! empty($filters['search']) ? mb_strtolower($filters['search'], 'UTF-8') : null;

        foreach ($allFiles as $entry) {
            $file = $entry['path'];
            $name = basename($file);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // Xác định Category
            $cat = 'other';
            if (str_contains($file, 'music') || str_contains($file, 'musics')) {
                $cat = 'musics';
            } elseif (str_starts_with($file, 'weddings/') || str_starts_with($file, 'paper_scans/') || str_starts_with($file, 'guest_photos/')) {
                $cat = 'weddings';
            } elseif (str_contains($file, 'template') || str_contains($file, 'templates')) {
                $cat = 'templates';
            } elseif (str_contains($file, 'images') || str_contains($file, 'business')) {
                $cat = 'assets';
            }

            // Xác định Type
            $type = 'other';
            if (in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac'])) {
                $type = 'audio';
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                $type = 'image';
            } elseif (in_array($ext, ['pdf', 'csv', 'xlsx', 'txt'])) {
                $type = 'document';
            }

            // Lọc
            if ($categoryFilter && $cat !== $categoryFilter) {
                continue;
            }

            if ($typeFilter && $type !== $typeFilter) {
                continue;
            }

            if ($searchFilter && ! str_contains(mb_strtolower($file, 'UTF-8'), $searchFilter)) {
                continue;
            }

            $size = $entry['size'];
            $lastModified = $entry['last_modified'];

            $results[] = [
                'path' => $file,
                'name' => $name,
                'category' => $cat,
                'ext' => $ext,
                'type' => $type,
                'size' => $size,
                'formatted_size' => self::formatBytes($size),
                'url' => $disk->url($file),
                'last_modified' => $lastModified,
                'modified_formatted' => date('d/m/Y H:i', $lastModified),
            ];
        }

        // Sắp xếp tệp mới nhất lên đầu
        usort($results, fn ($a, $b) => $b['last_modified'] <=> $a['last_modified']);

        return $results;
    }

    /**
     * Quét và phát hiện các tệp tin trong Storage không còn được tham chiếu trong bất kỳ bản ghi Database nào (Mục 7.5 DOCX).
     * Dùng cursor() để tránh nạp toàn bộ bảng weddings/invitations vào RAM.
     *
     * @return array{orphans: array<int, array{path: string, name: string, size: int, formatted_size: string, url: string, last_modified: int, modified_formatted: string}>, total_orphan_bytes: int, formatted_orphan_size: string, total_orphans: int}
     */
    public static function detectOrphanedFiles(): array
    {
        $disk = Storage::disk('public');
        $allFiles = self::scanPublicFiles(true);

        // 1. Thu thập tất cả đường dẫn file đang được lưu trong Database
        $referencedPaths = [];

        // Ảnh bìa & dữ liệu extra_data của Wedding (cursor để tiết kiệm RAM)
        foreach (Wedding::select(['id', 'cover_photo_url', 'extra_data'])->cursor() as $w) {
            if ($w->cover_photo_url) {
                $referencedPaths[] = self::normalizePath($w->cover_photo_url);
            }
            if (! empty($w->extra_data)) {
                $extraStr = is_array($w->extra_data) ? json_encode($w->extra_data) : (string) $w->extra_data;
                // Trích xuất các chuỗi đường dẫn tệp trong json
                preg_match_all('/(?:storage\/|weddings\/|paper_scans\/|photos\/)([a-zA-Z0-9_\-\.\/]+)/', $extraStr, $matches);
                if (! empty($matches[1])) {
                    foreach ($matches[1] as $m) {
                        $referencedPaths[] = trim($m, '/');
                    }
                }
            }
        }

        // Ảnh khách chụp GuestPhoto
        foreach (GuestPhoto::select(['id', 'photo_url'])->cursor() as $gp) {
            if ($gp->photo_url) {
                $referencedPaths[] = self::normalizePath($gp->photo_url);
            }
        }

        // Ảnh quét thiệp giấy PaperInvitationScan
        foreach (PaperInvitationScan::select(['id', 'image_path'])->cursor() as $scan) {
            if ($scan->image_path) {
                $referencedPaths[] = self::normalizePath($scan->image_path);
            }
        }

        // Ảnh mẫu thiệp InvitationTemplate
        foreach (InvitationTemplate::select(['id', 'thumbnail_url'])->cursor() as $tmpl) {
            if ($tmpl->thumbnail_url) {
                $referencedPaths[] = self::normalizePath($tmpl->thumbnail_url);
            }
        }

        // Đánh giá khách hàng Review
        foreach (Review::select(['id', 'photo_url'])->cursor() as $rev) {
            if ($rev->photo_url) {
                $referencedPaths[] = self::normalizePath($rev->photo_url);
            }
        }

        // Ảnh trang trí & khung ảnh của Studio Visual Editor V3 trong custom_config / draft_config
        foreach (Invitation::select(['id', 'custom_config', 'draft_config'])->cursor() as $inv) {
            foreach (['custom_config', 'draft_config'] as $cfgCol) {
                $cfgArr = is_array($inv->{$cfgCol}) ? $inv->{$cfgCol} : (json_decode((string) ($inv->{$cfgCol} ?? ''), true) ?: []);
                foreach (VisualEditorSanitizerService::extractReferencedAssetUrls($cfgArr) as $v3Url) {
                    $referencedPaths[] = self::normalizePath($v3Url);
                }
            }
        }

        $referencedLookup = array_flip(array_filter(array_unique($referencedPaths)));

        // 2. Tìm các file không còn nằm trong danh sách tham chiếu
        // Lưu ý: Các file hệ thống cố định trong `client/musics` và `client/images` là tài nguyên tĩnh dùng chung, không tính là mồ côi
        $orphans = [];
        $totalOrphanBytes = 0;

        foreach ($allFiles as $entry) {
            $file = $entry['path'];
            // Không tính các thư mục tĩnh của hệ thống
            if (str_starts_with($file, 'client/musics/') || str_starts_with($file, 'client/images/') || str_starts_with($file, 'admin/')) {
                continue;
            }

            $normalized = self::normalizePath($file);

            if (! isset($referencedLookup[$normalized]) && ! isset($referencedLookup[$file])) {
                $size = $entry['size'];
                $lastModified = $entry['last_modified'];

                $orphans[] = [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => $size,
                    'formatted_size' => self::formatBytes($size),
                    'url' => $disk->url($file),
                    'last_modified' => $lastModified,
                    'modified_formatted' => date('d/m/Y H:i', $lastModified),
                ];
                $totalOrphanBytes += $size;
            }
        }

        usort($orphans, fn ($a, $b) => $b['size'] <=> $a['size']);

        return [
            'orphans' => $orphans,
            'total_orphan_bytes' => $totalOrphanBytes,
            'formatted_orphan_size' => self::formatBytes($totalOrphanBytes),
            'total_orphans' => count($orphans),
        ];
    }

    /**
     * Chuẩn hóa đường dẫn tương đối để so sánh chính xác.
     */
    private static function normalizePath(string $urlOrPath): string
    {
        $path = parse_url($urlOrPath, PHP_URL_PATH) ?? $urlOrPath;
        $path = str_replace(['/storage/', 'storage/'], '', $path);

        return ltrim($path, '/');
    }
}
