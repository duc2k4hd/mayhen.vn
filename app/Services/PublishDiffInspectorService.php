<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Carbon;

class PublishDiffInspectorService
{
    /**
     * Danh mục các trường dữ liệu và nhãn tiếng Việt phục vụ so sánh Diff.
     *
     * @var array<string, array{label: string, category: string, risk: string}>
     */
    protected const FIELD_DEFINITIONS = [
        // 1. Đôi Uyên Ương & Gia Đình
        'groom_name' => ['label' => 'Họ tên Chú Rể', 'category' => 'couple_family', 'risk' => 'warning'],
        'bride_name' => ['label' => 'Họ tên Cô Dâu', 'category' => 'couple_family', 'risk' => 'warning'],
        'groom_father' => ['label' => 'Họ tên Cha (Nhà Trai)', 'category' => 'couple_family', 'risk' => 'info'],
        'groom_mother' => ['label' => 'Họ tên Mẹ (Nhà Trai)', 'category' => 'couple_family', 'risk' => 'info'],
        'bride_father' => ['label' => 'Họ tên Cha (Nhà Gái)', 'category' => 'couple_family', 'risk' => 'info'],
        'bride_mother' => ['label' => 'Họ tên Mẹ (Nhà Gái)', 'category' => 'couple_family', 'risk' => 'info'],

        // 2. Thời Gian & Địa Điểm Sự Kiện (CRITICAL RISK)
        'wedding_date' => ['label' => 'Ngày Cử Hành Hôn Lễ', 'category' => 'schedule_venue', 'risk' => 'critical'],
        'party_time' => ['label' => 'Giờ Khai Tiệc / Đón Khách', 'category' => 'schedule_venue', 'risk' => 'critical'],
        'event_party_address' => ['label' => 'Địa chỉ Sảnh Tiệc Cưới', 'category' => 'schedule_venue', 'risk' => 'critical'],
        'venue_name' => ['label' => 'Tên Trung Tâm Tiệc Cưới', 'category' => 'schedule_venue', 'risk' => 'critical'],
        'maps_url' => ['label' => 'Liên Kết Bản Đồ Google Maps', 'category' => 'schedule_venue', 'risk' => 'warning'],

        // 3. Mừng Cưới & Tài Khoản Ngân Hàng (WARNING RISK)
        'groom_bank_account' => ['label' => 'Số Tài Khoản (Chú Rể)', 'category' => 'banking_qr', 'risk' => 'warning'],
        'groom_bank_name' => ['label' => 'Ngân Hàng (Chú Rể)', 'category' => 'banking_qr', 'risk' => 'warning'],
        'groom_bank_holder' => ['label' => 'Chủ Tài Khoản (Chú Rể)', 'category' => 'banking_qr', 'risk' => 'warning'],
        'bride_bank_account' => ['label' => 'Số Tài Khoản (Cô Dâu)', 'category' => 'banking_qr', 'risk' => 'warning'],
        'bride_bank_name' => ['label' => 'Ngân Hàng (Cô Dâu)', 'category' => 'banking_qr', 'risk' => 'warning'],
        'bride_bank_holder' => ['label' => 'Chủ Tài Khoản (Cô Dâu)', 'category' => 'banking_qr', 'risk' => 'warning'],

        // 4. Album Ảnh & Media Nghệ Thuật
        'cover_photo_url' => ['label' => 'Ảnh Bìa Thiệp Cưới', 'category' => 'media_album', 'risk' => 'info'],
        'hero_photo_url' => ['label' => 'Ảnh Chân Dung Chính (Hero)', 'category' => 'media_album', 'risk' => 'info'],
        'music_url' => ['label' => 'Nhạc Nền Hôn Lễ', 'category' => 'media_album', 'risk' => 'info'],
        'album_photos_count' => ['label' => 'Số lượng ảnh trong Album', 'category' => 'media_album', 'risk' => 'info'],

        // 5. Thẩm Mỹ, Bảng Màu & Hiệu Ứng
        'accent_palette' => ['label' => 'Tông Màu Chủ Đạo Thiệp', 'category' => 'aesthetic_theme', 'risk' => 'info'],
        'wax_seal_symbol' => ['label' => 'Biểu Tượng Con Dấu Sáp', 'category' => 'aesthetic_theme', 'risk' => 'info'],
        'love_quote' => ['label' => 'Thông Điệp Yêu / Lời Ngỏ', 'category' => 'aesthetic_theme', 'risk' => 'info'],
    ];

    /**
     * Tên danh mục hiển thị tiếng Việt.
     *
     * @var array<string, string>
     */
    public const CATEGORY_LABELS = [
        'schedule_venue' => 'Thời Gian, Sảnh Tiệc & Địa Điểm',
        'couple_family' => 'Đôi Uyên Ương & Gia Đình Hai Bên',
        'banking_qr' => 'Mừng Cưới & Thông Tin Tài Khoản',
        'media_album' => 'Ảnh Cưới, Nhạc Nền & Album',
        'aesthetic_theme' => 'Bảng Màu & Phong Cách Mỹ Thuật',
    ];

    /**
     * Trích xuất snapshot của bản đang phát hành thực tế (Live Snapshot).
     */
    public function getLiveSnapshot(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        // Nếu đã từng lưu snapshot lúc publish thì ưu tiên sử dụng snapshot đó
        if (! empty($customConfig['_published_snapshot']) && is_array($customConfig['_published_snapshot'])) {
            return $customConfig['_published_snapshot'];
        }

        // Nếu chưa từng có snapshot lưu riêng, tạo snapshot từ trạng thái hiện hành của Wedding/Invitation
        return $this->captureCurrentStateSnapshot($invitation);
    }

    /**
     * Trích xuất snapshot của bản nháp đang chỉnh sửa trong Studio (Draft Snapshot).
     */
    public function getDraftSnapshot(Invitation $invitation): array
    {
        $draftConfig = is_array($invitation->draft_config)
            ? $invitation->draft_config
            : (json_decode((string) $invitation->draft_config, true) ?: []);

        if (empty($draftConfig)) {
            // Nếu chưa có draft_config riêng biệt, draft chính là trạng thái hiện hành của thiệp
            return $this->captureCurrentStateSnapshot($invitation);
        }

        return $this->normalizeSnapshotData($draftConfig, $invitation);
    }

    /**
     * So sánh chi tiết sự khác biệt giữa Bản Đang Phát Hành và Bản Nháp Chỉnh Sửa.
     *
     * @return array{
     *     has_changes: bool,
     *     total_changes: int,
     *     critical_count: int,
     *     warning_count: int,
     *     info_count: int,
     *     has_critical_schedule_change: bool,
     *     grouped_diffs: array<string, array<int, array{
     *         field: string,
     *         label: string,
     *         risk: string,
     *         old_value: mixed,
     *         new_value: mixed,
     *         old_display: string,
     *         new_display: string,
     *         is_empty_to_filled: bool,
     *         is_cleared: bool
     *     }>>,
     *     flat_diffs: array<int, array>
     * }
     */
    public function computeDiff(Invitation $invitation): array
    {
        $live = $this->getLiveSnapshot($invitation);
        $draft = $this->getDraftSnapshot($invitation);

        $flatDiffs = [];
        $groupedDiffs = [
            'schedule_venue' => [],
            'couple_family' => [],
            'banking_qr' => [],
            'media_album' => [],
            'aesthetic_theme' => [],
        ];

        $criticalCount = 0;
        $warningCount = 0;
        $infoCount = 0;
        $hasCriticalScheduleChange = false;

        foreach (self::FIELD_DEFINITIONS as $field => $meta) {
            $oldVal = $live[$field] ?? null;
            $newVal = $draft[$field] ?? null;

            // Chuẩn hóa so sánh để tránh lệch do khoảng trắng hoặc kiểu dữ liệu
            $oldNorm = is_string($oldVal) ? trim($oldVal) : $oldVal;
            $newNorm = is_string($newVal) ? trim($newVal) : $newVal;

            if ($oldNorm !== $newNorm) {
                $risk = $meta['risk'];
                if ($risk === 'critical') {
                    $criticalCount++;
                    if ($meta['category'] === 'schedule_venue') {
                        $hasCriticalScheduleChange = true;
                    }
                } elseif ($risk === 'warning') {
                    $warningCount++;
                } else {
                    $infoCount++;
                }

                $diffItem = [
                    'field' => $field,
                    'label' => $meta['label'],
                    'category' => $meta['category'],
                    'risk' => $risk,
                    'old_value' => $oldVal,
                    'new_value' => $newVal,
                    'old_display' => $this->formatDisplayValue($field, $oldVal),
                    'new_display' => $this->formatDisplayValue($field, $newVal),
                    'is_empty_to_filled' => empty($oldVal) && ! empty($newVal),
                    'is_cleared' => ! empty($oldVal) && empty($newVal),
                ];

                $flatDiffs[] = $diffItem;
                $category = $meta['category'];
                if (isset($groupedDiffs[$category])) {
                    $groupedDiffs[$category][] = $diffItem;
                }
            }
        }

        return [
            'has_changes' => count($flatDiffs) > 0,
            'total_changes' => count($flatDiffs),
            'critical_count' => $criticalCount,
            'warning_count' => $warningCount,
            'info_count' => $infoCount,
            'has_critical_schedule_change' => $hasCriticalScheduleChange,
            'grouped_diffs' => $groupedDiffs,
            'flat_diffs' => $flatDiffs,
        ];
    }

    /**
     * Xuất bản chính thức bản nháp (Commit Publish).
     *
     * @return array{
     *     success: bool,
     *     revision_id: int,
     *     changes_count: int,
     *     published_at: string
     * }
     */
    public function commitPublish(Invitation $invitation, ?string $changeNote = null, ?User $user = null): array
    {
        $diff = $this->computeDiff($invitation);
        $draftSnapshot = $this->getDraftSnapshot($invitation);

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $newRevision = ((int) ($invitation->revision_id ?? 0)) + 1;
        $now = now();

        // 1. Áp dụng các thay đổi từ draft vào wedding nếu cần
        $wedding = $invitation->wedding;
        if ($wedding) {
            $weddingUpdates = [];
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?: []);

            if (isset($draftSnapshot['groom_name'])) {
                $weddingUpdates['groom_name'] = $draftSnapshot['groom_name'];
            }
            if (isset($draftSnapshot['bride_name'])) {
                $weddingUpdates['bride_name'] = $draftSnapshot['bride_name'];
            }
            if (isset($draftSnapshot['wedding_date'])) {
                $weddingUpdates['wedding_date'] = $draftSnapshot['wedding_date'];
            }
            if (isset($draftSnapshot['venue_name'])) {
                $weddingUpdates['venue_name'] = $draftSnapshot['venue_name'];
            }
            if (isset($draftSnapshot['cover_photo_url'])) {
                $weddingUpdates['cover_photo_url'] = $draftSnapshot['cover_photo_url'];
            }
            if (isset($draftSnapshot['music_url'])) {
                $weddingUpdates['music_url'] = $draftSnapshot['music_url'];
            }

            // Đồng bộ phụ mẫu và tài khoản ngân hàng vào extra_data
            foreach (['groom_father', 'groom_mother', 'bride_father', 'bride_mother', 'event_party_address', 'maps_url', 'party_time', 'groom_bank_account', 'groom_bank_name', 'groom_bank_holder', 'bride_bank_account', 'bride_bank_name', 'bride_bank_holder', 'love_quote'] as $extraKey) {
                if (isset($draftSnapshot[$extraKey])) {
                    $extra[$extraKey] = $draftSnapshot[$extraKey];
                }
            }

            $weddingUpdates['extra_data'] = $extra;
            $wedding->update($weddingUpdates);
        }

        // 2. Cập nhật custom_config: ghi nhận snapshot mới và lưu vào lịch sử phát hành
        $historyItem = [
            'revision_id' => $newRevision,
            'published_at' => $now->toIso8601String(),
            'published_by' => $user?->id ?? $invitation->wedding?->user_id,
            'change_note' => $changeNote ?: 'Xuất bản bản cập nhật v'.$newRevision,
            'changes_count' => $diff['total_changes'],
            'critical_count' => $diff['critical_count'],
            'diff_summary' => array_map(function ($d) {
                return [
                    'field' => $d['field'],
                    'label' => $d['label'],
                    'risk' => $d['risk'],
                ];
            }, $diff['flat_diffs']),
            'snapshot' => $draftSnapshot,
        ];

        $publishHistory = $customConfig['publish_history'] ?? [];
        if (! is_array($publishHistory)) {
            $publishHistory = [];
        }
        array_unshift($publishHistory, $historyItem);
        // Giới hạn lưu trữ 15 bản gần nhất để tối ưu dung lượng DB
        $customConfig['publish_history'] = array_slice($publishHistory, 0, 15);

        // Lưu snapshot hiện tại làm live snapshot
        $customConfig['_published_snapshot'] = $draftSnapshot;

        // Xóa hoặc làm sạch draft_config sau khi đã xuất bản thành công
        $invitation->custom_config = $customConfig;
        $invitation->draft_config = null;
        $invitation->revision_id = $newRevision;
        $invitation->is_published = true;
        $invitation->published_at = $now;
        $invitation->save();

        return [
            'success' => true,
            'revision_id' => $newRevision,
            'changes_count' => $diff['total_changes'],
            'published_at' => $now->format('d/m/Y H:i'),
        ];
    }

    /**
     * Hủy bỏ toàn bộ các thay đổi nháp, hoàn tác về đúng bản Live đang phát hành.
     */
    public function discardDraftChanges(Invitation $invitation): bool
    {
        $invitation->draft_config = null;
        $invitation->save();

        return true;
    }

    /**
     * Khôi phục (Rollback) dữ liệu về một phiên bản lịch sử cụ thể.
     */
    public function rollbackToRevision(Invitation $invitation, int $targetRevisionId, ?User $user = null): bool
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $history = $customConfig['publish_history'] ?? [];
        $targetRecord = null;

        foreach ($history as $item) {
            if ((int) ($item['revision_id'] ?? 0) === $targetRevisionId) {
                $targetRecord = $item;
                break;
            }
        }

        if (! $targetRecord || empty($targetRecord['snapshot'])) {
            return false;
        }

        // Lưu snapshot mục tiêu thành draft để người dùng xem lại và xuất bản
        $invitation->draft_config = $targetRecord['snapshot'];
        $invitation->save();

        return true;
    }

    /**
     * Lưu snapshot vào draft_config phục vụ thử nghiệm hoặc lưu tạm trong Studio.
     */
    public function saveDraftSnapshot(Invitation $invitation, array $draftData): array
    {
        $currentDraft = $this->getDraftSnapshot($invitation);
        $merged = array_merge($currentDraft, $draftData);

        $invitation->draft_config = $merged;
        $invitation->last_autosaved_at = now();
        $invitation->save();

        return $merged;
    }

    /**
     * Lấy danh sách lịch sử các phiên bản đã xuất bản.
     */
    public function getPublishHistory(Invitation $invitation): array
    {
        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        return $customConfig['publish_history'] ?? [];
    }

    /**
     * Trích xuất snapshot từ cơ sở dữ liệu hiện tại của Wedding và Invitation.
     */
    protected function captureCurrentStateSnapshot(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $extra = [];
        if ($wedding) {
            $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?: []);
        }

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $weddingDate = null;
        if ($wedding?->wedding_date) {
            $weddingDate = Carbon::parse($wedding->wedding_date)->format('Y-m-d');
        }

        $albumPhotosCount = 0;
        if (! empty($customConfig['gallery']['photos']) && is_array($customConfig['gallery']['photos'])) {
            $albumPhotosCount = count($customConfig['gallery']['photos']);
        } elseif (! empty($extra['album_photos']) && is_array($extra['album_photos'])) {
            $albumPhotosCount = count($extra['album_photos']);
        }

        return [
            'groom_name' => (string) ($wedding?->groom_name ?? ''),
            'bride_name' => (string) ($wedding?->bride_name ?? ''),
            'groom_father' => (string) ($extra['groom_father'] ?? ''),
            'groom_mother' => (string) ($extra['groom_mother'] ?? ''),
            'bride_father' => (string) ($extra['bride_father'] ?? ''),
            'bride_mother' => (string) ($extra['bride_mother'] ?? ''),

            'wedding_date' => (string) ($weddingDate ?? ''),
            'party_time' => (string) ($extra['party_time'] ?? '17:30'),
            'event_party_address' => (string) ($extra['event_party_address'] ?? ($wedding?->venue_address ?? '')),
            'venue_name' => (string) ($wedding?->venue_name ?? ''),
            'maps_url' => (string) ($extra['maps_url'] ?? ''),

            'groom_bank_account' => (string) ($extra['groom_bank_account'] ?? ($wedding?->bank_account_number ?? '')),
            'groom_bank_name' => (string) ($extra['groom_bank_name'] ?? ($wedding?->bank_name ?? '')),
            'groom_bank_holder' => (string) ($extra['groom_bank_holder'] ?? ($wedding?->bank_account_name ?? '')),
            'bride_bank_account' => (string) ($extra['bride_bank_account'] ?? ''),
            'bride_bank_name' => (string) ($extra['bride_bank_name'] ?? ''),
            'bride_bank_holder' => (string) ($extra['bride_bank_holder'] ?? ''),

            'cover_photo_url' => (string) ($wedding?->cover_photo_url ?? ''),
            'hero_photo_url' => (string) ($extra['hero_photo_url'] ?? ''),
            'music_url' => (string) ($wedding?->music_url ?? ($customConfig['music']['url'] ?? '')),
            'album_photos_count' => (int) $albumPhotosCount,

            'accent_palette' => (string) ($customConfig['theme']['palette'] ?? 'velvet_ruby'),
            'wax_seal_symbol' => (string) ($customConfig['wax_seal']['symbol'] ?? 'monogram'),
            'love_quote' => (string) ($extra['love_quote'] ?? ($wedding?->story ?? '')),
        ];
    }

    /**
     * Chuẩn hóa dữ liệu snapshot từ input thô.
     */
    protected function normalizeSnapshotData(array $raw, Invitation $invitation): array
    {
        $fallback = $this->captureCurrentStateSnapshot($invitation);

        $normalized = [];
        foreach (self::FIELD_DEFINITIONS as $field => $meta) {
            if (array_key_exists($field, $raw)) {
                $val = $raw[$field];
                $normalized[$field] = is_string($val) ? trim($val) : $val;
            } else {
                $normalized[$field] = $fallback[$field] ?? '';
            }
        }

        return $normalized;
    }

    /**
     * Định dạng giá trị hiển thị thân thiện trên UI Diff.
     */
    protected function formatDisplayValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '(Trống / Chưa thiết lập)';
        }

        if ($field === 'wedding_date' && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::parse($value)->format('d/m/Y');
            } catch (\Exception $e) {
                return (string) $value;
            }
        }

        if ($field === 'album_photos_count') {
            return ((int) $value).' ảnh cưới';
        }

        if (is_bool($value)) {
            return $value ? 'Bật / Có' : 'Tắt / Không';
        }

        return (string) $value;
    }
}
