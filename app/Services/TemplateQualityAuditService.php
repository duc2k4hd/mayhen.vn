<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TemplateQualityAuditService
{
    /**
     * Comprehensive QA Audit of an Invitation Template (Wave 04 - Atelier Governance).
     * Inspects 10 Canonical Sections, Contract Live Sync data-sync-target, Local Assets Defense, CSS BEM Prefix, and AI Curated Parity.
     *
     * @return array<string, mixed>
     */
    public static function inspectTemplateIntegrity(string $slug): array
    {
        $templateDir = resource_path("views/client/invitations/{$slug}");
        $schemaPath = "{$templateDir}/schema.json";
        $bladePath = "{$templateDir}/template.blade.php";
        $cssPath = "{$templateDir}/styles.css";
        $jsPath = "{$templateDir}/script.js";
        $assetsDir = "{$templateDir}/assets";

        $schemaExists = File::exists($schemaPath);
        $bladeExists = File::exists($bladePath);
        $cssExists = File::exists($cssPath);
        $jsExists = File::exists($jsPath);
        $assetsExists = File::isDirectory($assetsDir);

        $filesReport = [
            'schema_json' => ['exists' => $schemaExists, 'path' => "resources/views/client/invitations/{$slug}/schema.json"],
            'template_blade' => ['exists' => $bladeExists, 'path' => "resources/views/client/invitations/{$slug}/template.blade.php"],
            'styles_css' => ['exists' => $cssExists, 'path' => "resources/views/client/invitations/{$slug}/styles.css"],
            'script_js' => ['exists' => $jsExists, 'path' => "resources/views/client/invitations/{$slug}/script.js"],
            'assets_dir' => ['exists' => $assetsExists, 'path' => "resources/views/client/invitations/{$slug}/assets"],
        ];

        $rawSchema = [];
        $sectionsReport = [];
        $galleryValid = false;

        if ($schemaExists) {
            $rawSchema = json_decode(File::get($schemaPath), true) ?? [];
            $presentSectionIds = collect($rawSchema['sections'] ?? [])->pluck('id')->filter()->all();

            $canonicalSections = InvitationTemplateService::CANONICAL_SECTION_ORDER;
            $canonicalDefinitions = InvitationTemplateService::getCanonicalSections();

            foreach ($canonicalSections as $canonId) {
                $isPassed = in_array($canonId, $presentSectionIds, true);
                $title = $canonicalDefinitions[$canonId]['title'] ?? ucfirst(str_replace('_', ' ', $canonId));

                $sectionsReport[] = [
                    'id' => $canonId,
                    'title' => $title,
                    'passed' => $isPassed,
                ];
            }

            foreach ($rawSchema['sections'] ?? [] as $sec) {
                foreach ($sec['fields'] ?? [] as $f) {
                    if (($f['key'] ?? '') === 'wedding.extra_data.gallery') {
                        $galleryValid = true;
                        break 2;
                    }
                }
            }
        }

        $canonicalCount = collect($sectionsReport)->where('passed', true)->count();
        $isCanonicalValid = ($canonicalCount >= 10);

        $bladeContent = $bladeExists ? File::get($bladePath) : '';
        $cssContent = $cssExists ? File::get($cssPath) : '';

        preg_match_all('/data-sync-target=["\']([^"\']+)["\']/', $bladeContent, $syncMatches);
        $syncTargets = array_values(array_unique($syncMatches[1] ?? []));
        $syncCount = count($syncTargets);
        $hasEssentialSync = in_array('groom_name', $syncTargets, true) && in_array('bride_name', $syncTargets, true);

        $extendsInvitation = str_contains($bladeContent, "@extends('client.layouts.invitation')")
            || str_contains($bladeContent, '@extends("client.layouts.invitation")');
        $hasEmbedGuard = str_contains($bladeContent, '$isEmbed')
            || str_contains($cssContent, '.card-wrapper')
            || $extendsInvitation;

        $usesBEMPrefix = str_contains($cssContent, 'mayhen_invitation_')
            || str_contains($bladeContent, 'mayhen_invitation_');

        $externalDomains = ['ladicdn.com', 'builder.27biggroup.com', 'unsplash.com', 'iili.io', 'imgbb.com', 'cloudinary.com'];
        $externalViolations = [];

        foreach ([$bladeContent, $cssContent, json_encode($rawSchema)] as $corpus) {
            foreach ($externalDomains as $domain) {
                if (str_contains((string) $corpus, $domain)) {
                    $externalViolations[] = $domain;
                }
            }
        }
        $externalViolations = array_values(array_unique($externalViolations));
        $isLocalAssetsOnly = empty($externalViolations);

        $localAssetFiles = $assetsExists ? File::allFiles($assetsDir) : [];
        $localAssetCount = count($localAssetFiles);

        $curatedCanonical = ['hero', 'story', 'parents', 'countdown_calendar', 'events', 'timeline', 'album'];
        $curatedPassedCount = collect($sectionsReport)
            ->whereIn('id', $curatedCanonical)
            ->where('passed', true)
            ->count();
        $curatedCompatible = ($curatedPassedCount >= 7);

        $checksPassed = 0;
        $totalChecks = 6;

        if ($schemaExists && $bladeExists && $cssExists) {
            $checksPassed++;
        }
        if ($isCanonicalValid) {
            $checksPassed++;
        }
        if ($hasEssentialSync) {
            $checksPassed++;
        }
        if ($extendsInvitation) {
            $checksPassed++;
        }
        if ($isLocalAssetsOnly) {
            $checksPassed++;
        }
        if ($curatedCompatible) {
            $checksPassed++;
        }

        $score = (int) round(($checksPassed / $totalChecks) * 100);

        return [
            'slug' => $slug,
            'score' => $score,
            'files' => $filesReport,
            'raw_schema' => $rawSchema,
            'sections_report' => $sectionsReport,
            'canonical_passed_count' => $canonicalCount,
            'is_canonical_valid' => $isCanonicalValid,
            'gallery_valid' => $galleryValid,
            'sync_targets' => $syncTargets,
            'sync_count' => $syncCount,
            'has_essential_sync' => $hasEssentialSync,
            'extends_invitation' => $extendsInvitation,
            'has_embed_guard' => $hasEmbedGuard,
            'uses_bem_prefix' => $usesBEMPrefix,
            'is_local_assets_only' => $isLocalAssetsOnly,
            'external_violations' => $externalViolations,
            'local_asset_count' => $localAssetCount,
            'curated_compatible' => $curatedCompatible,
        ];
    }

    /**
     * Extract Template Visual DNA (Innovation #10).
     *
     * @return array<string, mixed>
     */
    public static function extractVisualDna(string $slug): array
    {
        $templateDir = resource_path("views/client/invitations/{$slug}");
        $cssPath = "{$templateDir}/styles.css";
        $schemaPath = "{$templateDir}/schema.json";

        $cssContent = File::exists($cssPath) ? File::get($cssPath) : '';
        $schemaData = File::exists($schemaPath) ? (json_decode(File::get($schemaPath), true) ?? []) : [];

        $fonts = [];
        if (preg_match_all('/font-family:\s*([^;\}]+)/i', $cssContent, $fontMatches)) {
            foreach ($fontMatches[1] as $fm) {
                $clean = trim(str_replace(['"', "'"], '', explode(',', $fm)[0]));
                if ($clean && ! in_array($clean, $fonts, true) && ! in_array(strtolower($clean), ['inherit', 'sans-serif', 'serif', 'system-ui'])) {
                    $fonts[] = $clean;
                }
            }
        }
        $headingFont = $fonts[0] ?? 'Playfair Display';
        $bodyFont = $fonts[1] ?? 'Inter';

        $colors = [];
        if (preg_match_all('/#([a-f0-9]{6}|[a-f0-9]{3})\b/i', $cssContent, $colorMatches)) {
            $hexCounts = array_count_values(array_map('strtoupper', $colorMatches[0]));
            arsort($hexCounts);
            $colors = array_slice(array_keys($hexCounts), 0, 6);
        }

        if (empty($colors)) {
            $colors = ['#FAF8F5', '#1F1D1A', '#C5A880', '#EFE8DD'];
        }

        $category = $schemaData['category'] ?? '';
        if (str_contains(strtolower($cssContent), 'gold') || str_contains(strtolower($slug), 'gold') || str_contains(strtolower($category), 'luxury')) {
            $theme = 'Muted Gold / Hoàng Gia Sang Trọng';
        } elseif (str_contains(strtolower($cssContent), 'rose') || str_contains(strtolower($slug), 'rose') || str_contains(strtolower($category), 'romantic')) {
            $theme = 'Dusty Rose / Lãng Mạn Thơ Mộng';
        } elseif (str_contains(strtolower($cssContent), 'sage') || str_contains(strtolower($slug), 'sage') || str_contains(strtolower($category), 'minimal')) {
            $theme = 'Sage Green / Tối Giản Thiên Nhiên';
        } else {
            $theme = 'Warm Cream & Ivory / Quiet Luxury Cổ Điển';
        }

        $hasBorder = str_contains($cssContent, 'border:') || str_contains($cssContent, 'border-radius');
        $hasPaperTexture = str_contains($cssContent, 'texture') || str_contains($cssContent, 'paper') || str_contains($cssContent, 'noise');

        $brandHarmony = 100;
        $violations = [];
        foreach ($colors as $c) {
            if (in_array(strtoupper($c), ['#FF00FF', '#00FFFF', '#00FF00', '#FF0000'], true)) {
                $brandHarmony -= 20;
                $violations[] = "Màu {$c} có độ chói cao, không phù hợp với chuẩn Quiet Luxury.";
            }
        }

        return [
            'slug' => $slug,
            'heading_font' => $headingFont,
            'body_font' => $bodyFont,
            'fonts' => $fonts,
            'palette' => $colors,
            'theme' => $theme,
            'has_border' => $hasBorder,
            'has_paper_texture' => $hasPaperTexture,
            'brand_harmony' => max(0, $brandHarmony),
            'violations' => $violations,
        ];
    }

    /**
     * Chạy kiểm định toàn diện Preflight Quality Gate trước khi phát hành phiên bản mẫu thiệp (NXT-04).
     *
     * @return array{
     *     slug: string,
     *     is_ready_for_release: bool,
     *     readiness_score: int,
     *     blockers: array<int, string>,
     *     warnings: array<int, string>,
     *     metrics: array<string, mixed>,
     *     viewports: array<int, int>
     * }
     */
    public static function runPreflightAudit(string $slug): array
    {
        $integrity = self::inspectTemplateIntegrity($slug);
        $visualDna = self::extractVisualDna($slug);

        $blockers = [];
        $warnings = [];

        if (! ($integrity['files']['schema_json']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp schema.json khai báo cấu trúc dữ liệu.';
        }
        if (! ($integrity['files']['template_blade']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp template.blade.php giao diện chính.';
        }
        if (! ($integrity['files']['styles_css']['exists'] ?? false)) {
            $blockers[] = 'Thiếu tệp styles.css định kiểu mỹ thuật.';
        }

        if (! ($integrity['is_canonical_valid'] ?? false)) {
            $missingCount = 10 - ($integrity['canonical_passed_count'] ?? 0);
            $blockers[] = "Mẫu thiệp chưa đủ 10 Section tiêu chuẩn (hiện có {$integrity['canonical_passed_count']}/10, thiếu {$missingCount} section).";
        }

        if (($integrity['sync_count'] ?? 0) < 2 || ! ($integrity['has_essential_sync'] ?? false)) {
            $blockers[] = 'Thiếu hợp đồng Live Sync tối thiểu (bắt buộc data-sync-target="groom_name" và "bride_name").';
        }

        if (! ($integrity['is_local_assets_only'] ?? true)) {
            $violationsList = implode(', ', $integrity['external_violations'] ?? []);
            $blockers[] = "Phát hiện URL liên kết server bên thứ ba vi phạm quy chuẩn 100% Local Assets: {$violationsList}.";
        }

        if (! ($integrity['extends_invitation'] ?? false)) {
            $warnings[] = 'Template chưa kế thừa layout chuẩn @extends("client.layouts.invitation").';
        }

        if (($visualDna['brand_harmony'] ?? 100) < 80) {
            $warnings[] = 'Bảng màu có một số sắc độ chói cần tinh chỉnh thêm theo phong cách Quiet Luxury.';
        }

        $isReady = empty($blockers);
        $score = $integrity['score'] ?? 0;
        if (! $isReady) {
            $score = min($score, 65);
        }

        return [
            'slug' => $slug,
            'is_ready_for_release' => $isReady,
            'readiness_score' => $score,
            'blockers' => $blockers,
            'warnings' => $warnings,
            'metrics' => [
                'canonical_sections' => ($integrity['canonical_passed_count'] ?? 0).'/10',
                'sync_targets_count' => $integrity['sync_count'] ?? 0,
                'local_assets_count' => $integrity['local_asset_count'] ?? 0,
                'brand_harmony_score' => $visualDna['brand_harmony'] ?? 100,
                'theme' => $visualDna['theme'] ?? 'Quiet Luxury',
            ],
            'viewports' => [375, 430, 768, 1440],
        ];
    }
}
