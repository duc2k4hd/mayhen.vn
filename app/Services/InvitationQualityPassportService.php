<?php

namespace App\Services;

use App\Models\Invitation;

class InvitationQualityPassportService
{
    /**
     * Inspect and generate an official Quality Passport for an invitation.
     *
     * @return array<string, mixed>
     */
    public static function inspect(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $template = $invitation->template;
        $templateSlug = $template?->slug ?? '';

        // 1. Template Architecture Check
        $templateIntegrity = $templateSlug !== ''
            ? InvitationTemplateService::inspectTemplateIntegrity($templateSlug)
            : ['is_valid' => false, 'sections_report' => [], 'sync_count' => 0];

        $has10Sections = ($templateIntegrity['canonical_sections_count'] ?? 0) >= 10;
        $hasLiveSync = ($templateIntegrity['sync_count'] ?? 0) > 0;
        $hasLocalAssets = ! empty($templateIntegrity['files_report']['assets_dir']['exists']);

        // 2. Wedding Data Completeness
        $hasCoupleNames = ! empty($wedding?->groom_name) && ! empty($wedding?->bride_name);
        $hasWeddingDate = ! empty($wedding?->wedding_date);
        $hasVenueInfo = ! empty($wedding?->venue_name) || ! empty($wedding?->venue_address);
        $hasCoverPhoto = ! empty($wedding?->cover_photo_url);
        $hasMusic = ! empty($wedding?->music_url);

        // 3. Guest & Seating Ratio Check
        $totalGuests = $wedding ? $wedding->guests()->count() : 0;
        $totalTables = $wedding ? $wedding->tables()->count() : 0;
        $tableCapacity = $wedding ? (int) $wedding->tables()->sum('capacity') : 0;
        $seatingValid = ($totalGuests === 0) || ($tableCapacity >= $totalGuests);

        // 4. Scoring (Total 100)
        $score = 0;
        if ($has10Sections) {
            $score += 25;
        }
        if ($hasLiveSync) {
            $score += 15;
        }
        if ($hasLocalAssets) {
            $score += 10;
        }
        if ($hasCoupleNames) {
            $score += 15;
        }
        if ($hasWeddingDate) {
            $score += 10;
        }
        if ($hasVenueInfo) {
            $score += 10;
        }
        if ($hasCoverPhoto) {
            $score += 5;
        }
        if ($hasMusic) {
            $score += 5;
        }
        if ($seatingValid && $totalTables > 0) {
            $score += 5;
        }

        $score = min(100, $score);

        $grade = match (true) {
            $score >= 90 => 'A+',
            $score >= 80 => 'A',
            $score >= 60 => 'B',
            default => 'C',
        };

        $gradeLabel = match ($grade) {
            'A+' => 'Xuất Sắc (Quiet Luxury Certified)',
            'A' => 'Đạt Chuẩn Cao Cấp (Ready to Publish)',
            'B' => 'Cần Bổ Sung Thông Tin',
            default => 'Chưa Hoàn Thiện',
        };

        $isCertified = ($score >= 80) && $hasCoupleNames && $has10Sections;

        $checklist = [
            [
                'title' => 'Kiến Trúc 10 Section Tiêu Chuẩn',
                'description' => ($templateIntegrity['canonical_sections_count'] ?? 0).'/10 section chuẩn hóa',
                'passed' => $has10Sections,
            ],
            [
                'title' => 'Hợp Đồng Live Sync (data-sync-target)',
                'description' => ($templateIntegrity['sync_count'] ?? 0).' điểm đồng bộ thời gian thực',
                'passed' => $hasLiveSync,
            ],
            [
                'title' => 'Tài Nguyên Cục Bộ (Zero External CDN)',
                'description' => $hasLocalAssets ? '100% assets lưu trữ nội bộ' : 'Thiếu thư mục assets cục bộ',
                'passed' => $hasLocalAssets,
            ],
            [
                'title' => 'Thông Tin Đôi Uyên Ương',
                'description' => $hasCoupleNames ? "{$wedding->groom_name} & {$wedding->bride_name}" : 'Chưa có tên cô dâu/chú rể',
                'passed' => $hasCoupleNames,
            ],
            [
                'title' => 'Thời Gian & Địa Điểm Tổ Chức',
                'description' => $hasWeddingDate ? ($wedding->wedding_date->format('d/m/Y').($wedding->venue_name ? " tại {$wedding->venue_name}" : '')) : 'Chưa định ngày cưới',
                'passed' => $hasWeddingDate && $hasVenueInfo,
            ],
            [
                'title' => 'Hình Ảnh Bìa & Giai Điệu Ngày Cưới',
                'description' => ($hasCoverPhoto ? 'Đã có ảnh bìa' : 'Chưa có ảnh bìa').' & '.($hasMusic ? 'Đã có nhạc nền' : 'Chưa chọn nhạc'),
                'passed' => $hasCoverPhoto || $hasMusic,
            ],
        ];

        return [
            'score' => $score,
            'grade' => $grade,
            'grade_label' => $gradeLabel,
            'is_certified' => $isCertified,
            'checklist' => $checklist,
            'template_name' => $template?->name ?? 'Mặc định',
            'inspected_at' => now()->format('d/m/Y H:i'),
        ];
    }
}
