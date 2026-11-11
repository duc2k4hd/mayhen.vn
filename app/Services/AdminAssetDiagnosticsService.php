<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAssetDiagnosticsService
{
    /**
     * Danh sách các domain bên thứ ba bị cấm theo Quy tắc 6 (100% Local Assets).
     */
    protected array $untrustedDomains = [
        'builder.27biggroup.com',
        'ladicdn.com',
        'unsplash.com',
        'iili.io',
        'images.unsplash.com',
        'cdn.jsdelivr.net',
    ];

    /**
     * Lấy tổng quan chẩn đoán sức khỏe tài nguyên hình ảnh thiệp cưới.
     */
    public function getDiagnosticsOverview(array $filters = []): array
    {
        $query = Invitation::with(['wedding.user', 'template'])->latest();

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (! empty($filters['template_id'])) {
            $query->where('template_id', $filters['template_id']);
        }

        $invitations = $query->paginate(12)->withQueryString();

        // Chẩn đoán từng thiệp trong trang hiện tại
        $inspectedInvitations = [];
        $totalIssuesCount = 0;
        $totalCriticalCount = 0;
        $totalExternalCount = 0;

        foreach ($invitations as $invitation) {
            $report = $this->inspectInvitationAssets($invitation);
            $inspectedInvitations[] = [
                'invitation' => $invitation,
                'report' => $report,
            ];

            if ($report['has_issues']) {
                $totalIssuesCount += count($report['issues']);
                $totalCriticalCount += $report['critical_count'];
                $totalExternalCount += $report['external_count'];
            }
        }

        $stats = [
            'total_scanned_invitations' => Invitation::count(),
            'inspected_in_page_count' => count($inspectedInvitations),
            'page_issues_count' => $totalIssuesCount,
            'page_critical_count' => $totalCriticalCount,
            'page_external_count' => $totalExternalCount,
        ];

        return [
            'stats' => $stats,
            'paginator' => $invitations,
            'inspected' => $inspectedInvitations,
        ];
    }

    /**
     * Kiểm tra toàn bộ tài nguyên hình ảnh, âm thanh của một thiệp cưới.
     */
    public function inspectInvitationAssets(Invitation $invitation): array
    {
        $assets = $this->extractMediaUrlsFromInvitation($invitation);
        $issues = [];
        $criticalCount = 0;
        $externalCount = 0;

        foreach ($assets as $item) {
            $url = $item['url'];
            $field = $item['field'];
            $label = $item['label'];
            $isCritical = $item['is_critical'];

            $issueType = null;
            $issueMessage = null;

            // 1. Kiểm tra URL rỗng
            if (empty($url)) {
                if ($isCritical) {
                    $issueType = 'missing_required';
                    $issueMessage = "Thiếu trường dữ liệu bắt buộc: {$label}.";
                }
            }
            // 2. Kiểm tra domain ngoài bị cấm
            elseif ($this->isExternalUntrustedUrl($url)) {
                $issueType = 'external_untrusted';
                $domain = parse_url($url, PHP_URL_HOST);
                $issueMessage = "Đang dùng ảnh từ máy chủ ngoại vi ({$domain}) vi phạm quy chuẩn 100% Local Assets.";
                $externalCount++;
            }
            // 3. Kiểm tra file nội bộ /storage/ hoặc /assets/
            elseif (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
                $relativeStoragePath = ltrim(Str::after($url, 'storage/'), '/');
                if (! Storage::disk('public')->exists($relativeStoragePath)) {
                    $issueType = 'missing_local_file';
                    $issueMessage = "File vật lý không tồn tại trên ổ lưu trữ máy chủ: storage/{$relativeStoragePath}.";
                }
            } elseif (str_starts_with($url, '/assets/') || str_starts_with($url, 'assets/')) {
                $publicPath = public_path(ltrim($url, '/'));
                if (! File::exists($publicPath)) {
                    $issueType = 'missing_local_file';
                    $issueMessage = "Tệp giao diện không tồn tại trong thư mục public: {$url}.";
                }
            }

            if ($issueType) {
                if ($isCritical) {
                    $criticalCount++;
                }

                $issues[] = [
                    'field' => $field,
                    'label' => $label,
                    'url' => $url,
                    'type' => $issueType,
                    'severity' => $isCritical ? 'critical' : 'warning',
                    'message' => $issueMessage,
                    'suggested_fallback' => $this->getSuggestedFallback($invitation, $field),
                ];
            }
        }

        return [
            'invitation_id' => $invitation->id,
            'title' => $invitation->title,
            'total_assets_checked' => count($assets),
            'has_issues' => ! empty($issues),
            'issues' => $issues,
            'critical_count' => $criticalCount,
            'external_count' => $externalCount,
            'health_score' => count($assets) > 0 ? max(0, round((1 - (count($issues) / count($assets))) * 100)) : 100,
        ];
    }

    /**
     * Trích xuất toàn bộ URL media được khai báo trong thiệp.
     */
    protected function extractMediaUrlsFromInvitation(Invitation $invitation): array
    {
        $assets = [];
        $config = $invitation->custom_config ?? [];
        $wedding = $invitation->wedding;

        // 1. Ảnh Bìa Hero (Critical)
        $heroPhoto = $config['hero_photo_url'] ?? ($wedding?->cover_photo_url ?? null);
        $assets[] = [
            'field' => 'custom_config.hero_photo_url',
            'label' => 'Ảnh Bìa Hero Chính',
            'url' => $heroPhoto,
            'is_critical' => true,
        ];

        // 2. Ảnh Chú Rể (Critical)
        $groomPhoto = $config['groom_photo_url'] ?? null;
        $assets[] = [
            'field' => 'custom_config.groom_photo_url',
            'label' => 'Ảnh Chân Dung Chú Rể',
            'url' => $groomPhoto,
            'is_critical' => true,
        ];

        // 3. Ảnh Cô Dâu (Critical)
        $bridePhoto = $config['bride_photo_url'] ?? null;
        $assets[] = [
            'field' => 'custom_config.bride_photo_url',
            'label' => 'Ảnh Chân Dung Cô Dâu',
            'url' => $bridePhoto,
            'is_critical' => true,
        ];

        // 4. Nhạc Nền Thiệp
        $musicUrl = $config['music_url'] ?? null;
        if (! empty($musicUrl)) {
            $assets[] = [
                'field' => 'custom_config.music_url',
                'label' => 'Nhạc Nền Đám Cưới',
                'url' => $musicUrl,
                'is_critical' => false,
            ];
        }

        // 5. Ảnh Mã QR Mừng Cưới
        $qrUrl = $config['groom_qr_url'] ?? ($config['bride_qr_url'] ?? null);
        if (! empty($qrUrl)) {
            $assets[] = [
                'field' => 'custom_config.groom_qr_url',
                'label' => 'Mã VietQR Mừng Cưới',
                'url' => $qrUrl,
                'is_critical' => false,
            ];
        }

        // 6. Album Gallery 10 Ảnh
        if (! empty($config['gallery']) && is_array($config['gallery'])) {
            foreach ($config['gallery'] as $idx => $photoUrl) {
                if (is_string($photoUrl)) {
                    $assets[] = [
                        'field' => "custom_config.gallery.{$idx}",
                        'label' => 'Ảnh Album Khoảnh Khắc #'.($idx + 1),
                        'url' => $photoUrl,
                        'is_critical' => false,
                    ];
                }
            }
        }

        return $assets;
    }

    /**
     * Kiểm tra URL có thuộc domain bên thứ ba bị cấm không.
     */
    public function isExternalUntrustedUrl(?string $url): bool
    {
        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        foreach ($this->untrustedDomains as $untrusted) {
            if ($host === $untrusted || str_ends_with($host, '.'.$untrusted)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Đề xuất ảnh fallback an toàn từ thư mục assets của template.
     */
    protected function getSuggestedFallback(Invitation $invitation, string $field): string
    {
        $templateSlug = $invitation->template?->slug ?: 'celestial-luxury';

        if (str_contains($field, 'groom')) {
            return "/mau-thiep/{$templateSlug}/assets/images/groom.webp";
        }
        if (str_contains($field, 'bride')) {
            return "/mau-thiep/{$templateSlug}/assets/images/bride.webp";
        }

        return "/mau-thiep/{$templateSlug}/assets/images/cover.webp";
    }

    /**
     * Áp dụng ảnh fallback an toàn cho một trường bị lỗi của thiệp cưới.
     */
    public function applyFallbackAsset(Invitation $invitation, string $field, ?string $fallbackUrl = null): bool
    {
        $url = $fallbackUrl ?: $this->getSuggestedFallback($invitation, $field);
        $config = $invitation->custom_config ?? [];

        if ($field === 'custom_config.hero_photo_url') {
            $config['hero_photo_url'] = $url;
        } elseif ($field === 'custom_config.groom_photo_url') {
            $config['groom_photo_url'] = $url;
        } elseif ($field === 'custom_config.bride_photo_url') {
            $config['bride_photo_url'] = $url;
        } elseif (str_starts_with($field, 'custom_config.gallery.')) {
            $idx = (int) Str::after($field, 'custom_config.gallery.');
            if (isset($config['gallery']) && is_array($config['gallery'])) {
                $config['gallery'][$idx] = $url;
            }
        } else {
            data_set($config, Str::after($field, 'custom_config.'), $url);
        }

        $invitation->custom_config = $config;

        return (bool) $invitation->save();
    }

    /**
     * Tự động tải ảnh từ URL ngoại vi về lưu cục bộ (Localize Asset).
     */
    public function localizeExternalAsset(Invitation $invitation, string $field, string $externalUrl): array
    {
        try {
            // Tải ảnh qua HTTP
            $response = Http::timeout(10)->get($externalUrl);
            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Không thể tải ảnh từ URL máy chủ ngoại vi (Mã phản hồi: '.$response->status().').',
                ];
            }

            $body = $response->body();
            $extension = pathinfo((string) parse_url($externalUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                $extension = 'webp';
            }

            $fileName = 'localized_'.md5($externalUrl).'.'.$extension;
            $storageRelativePath = "invitations/{$invitation->id}/{$fileName}";

            Storage::disk('public')->put($storageRelativePath, $body);

            $localUrl = "/storage/{$storageRelativePath}";

            // Cập nhật lại config thiệp
            $this->applyFallbackAsset($invitation, $field, $localUrl);

            return [
                'success' => true,
                'local_url' => $localUrl,
                'message' => 'Đã tải về và nội địa hóa tài nguyên thành công (100% Local Asset).',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Lỗi xử lý nội địa hóa tài nguyên: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Sửa nhanh toàn bộ các lỗi asset của một thiệp cưới bằng fallback mẫu.
     */
    public function batchRepairInvitation(Invitation $invitation): int
    {
        $report = $this->inspectInvitationAssets($invitation);
        $repairedCount = 0;

        foreach ($report['issues'] as $issue) {
            if (! empty($issue['suggested_fallback'])) {
                $this->applyFallbackAsset($invitation, $issue['field'], $issue['suggested_fallback']);
                $repairedCount++;
            }
        }

        return $repairedCount;
    }

    /**
     * Xuất báo cáo chẩn đoán lỗi tài nguyên thiệp cưới định dạng CSV UTF-8 BOM.
     */
    public function exportDiagnosticsCsv(): Response
    {
        $invitations = Invitation::with(['wedding.user', 'template'])->get();

        $headers = [
            'ID Thiệp',
            'Tên Thiệp',
            'Đường Dẫn Slug',
            'Mẫu Template',
            'Chủ Tài Khoản',
            'Trường Dữ Liệu Lỗi',
            'Tên Tài Nguyên',
            'Đường Dẫn Bị Lỗi',
            'Mức Độ Nghiêm Trọng',
            'Mô Tả Lỗi',
            'Đề Xuất Phục Hồi',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($invitations as $invitation) {
            $report = $this->inspectInvitationAssets($invitation);
            foreach ($report['issues'] as $issue) {
                fputcsv($handle, [
                    $invitation->id,
                    $invitation->title,
                    $invitation->slug,
                    $invitation->template?->name ?: '—',
                    $invitation->wedding?->user?->name ?: '—',
                    $issue['field'],
                    $issue['label'],
                    $issue['url'] ?: 'Chưa thiết lập',
                    strtoupper($issue['severity']),
                    $issue['message'],
                    $issue['suggested_fallback'],
                ]);
            }
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'mayhen_invitation_asset_diagnostics_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
