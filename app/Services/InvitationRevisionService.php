<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invitation;
use App\Models\InvitationRevision;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class InvitationRevisionService
{
    protected const MAX_AUTOSAVES_PER_INVITATION = 30;

    /**
     * Tạo một bản snapshot lịch sử phiên bản mới cho thiệp cưới
     *
     * @param  array<string, mixed>  $configData
     */
    public function createRevision(
        Invitation $invitation,
        array $configData,
        string $type = 'manual_save',
        ?string $title = null,
        ?int $userId = null
    ): InvitationRevision {
        $normalizedJson = json_encode($configData, JSON_UNESCAPED_UNICODE);
        $checksum = hash('sha256', (string) $normalizedJson);

        $latest = $invitation->revisions()->first();

        // Chống lưu trùng lặp (Deduplication): Nếu nội dung snapshot giống hệt bản gần nhất
        if ($latest && $latest->checksum === $checksum) {
            return $latest;
        }

        return DB::transaction(function () use ($invitation, $configData, $type, $title, $userId, $checksum, $latest) {
            $nextVersionNumber = $latest ? ($latest->version_number + 1) : 1;

            // Sinh tóm tắt các trường thay đổi so với bản trước
            $diffSummary = $latest ? $this->computeDiffSummary($latest->config_data, $configData) : ['all_sections_initialized'];

            $defaultTitle = match ($type) {
                'published' => "Xuất bản chính thức v{$nextVersionNumber}",
                'autosave' => "Tự động lưu nháp v{$nextVersionNumber}",
                'restored' => $title ?: 'Phục hồi từ phiên bản trước',
                'template_switched' => "Chuyển đổi giao diện mẫu v{$nextVersionNumber}",
                default => "Lưu thủ công v{$nextVersionNumber}",
            };

            $revision = InvitationRevision::create([
                'invitation_id' => $invitation->id,
                'version_number' => $nextVersionNumber,
                'revision_type' => $type,
                'title' => $title ?: $defaultTitle,
                'config_data' => $configData,
                'diff_summary' => $diffSummary,
                'checksum' => $checksum,
                'created_by' => $userId ?: Auth::id(),
                'ip_address' => Request::ip() ?? '127.0.0.1',
                'user_agent' => substr(Request::userAgent() ?? 'System/Studio', 0, 255),
                'created_at' => now(),
            ]);

            // Cập nhật con trỏ revision_id trên thiệp cưới
            $invitation->updateQuietly([
                'revision_id' => $revision->id,
                'last_autosaved_at' => now(),
            ]);

            // Cắt tỉa các bản nháp autosave quá cũ để tối ưu tài nguyên lưu trữ
            if ($type === 'autosave') {
                $this->pruneOldAutosaves($invitation->id);
            }

            return $revision;
        });
    }

    /**
     * Lấy danh sách lịch sử phiên bản của một thiệp cưới
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, InvitationRevision>
     */
    public function getRevisions(int $invitationId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InvitationRevision::with('creator')
            ->where('invitation_id', $invitationId)
            ->orderByDesc('version_number');

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('revision_type', $filters['type']);
        }

        return $query->paginate($perPage);
    }

    /**
     * So sánh chi tiết sự khác biệt giữa hai phiên bản
     *
     * @return array<string, mixed>
     */
    public function getRevisionDiff(int $invitationId, int $revisionId, ?int $compareWithRevisionId = null): array
    {
        $currentRevision = InvitationRevision::where('invitation_id', $invitationId)
            ->where('id', $revisionId)
            ->firstOrFail();

        if ($compareWithRevisionId) {
            $baseRevision = InvitationRevision::where('invitation_id', $invitationId)
                ->where('id', $compareWithRevisionId)
                ->firstOrFail();
        } else {
            // Mặc định so sánh với phiên bản liền trước đó
            $baseRevision = InvitationRevision::where('invitation_id', $invitationId)
                ->where('version_number', '<', $currentRevision->version_number)
                ->orderByDesc('version_number')
                ->first();
        }

        $baseData = $baseRevision ? ($baseRevision->config_data ?? []) : [];
        $currentData = $currentRevision->config_data ?? [];

        $detailedDiff = $this->buildDetailedDiff($baseData, $currentData);

        return [
            'current' => [
                'id' => $currentRevision->id,
                'version' => $currentRevision->version_number,
                'title' => $currentRevision->title,
                'type' => $currentRevision->revision_type_label,
                'created_at' => $currentRevision->created_at->format('d/m/Y H:i:s'),
                'creator' => $currentRevision->creator?->name ?? 'Hệ thống',
            ],
            'base' => $baseRevision ? [
                'id' => $baseRevision->id,
                'version' => $baseRevision->version_number,
                'title' => $baseRevision->title,
                'type' => $baseRevision->revision_type_label,
                'created_at' => $baseRevision->created_at->format('d/m/Y H:i:s'),
                'creator' => $baseRevision->creator?->name ?? 'Hệ thống',
            ] : null,
            'changes_count' => count($detailedDiff),
            'changes' => $detailedDiff,
        ];
    }

    /**
     * Phục hồi an toàn thiệp cưới về một phiên bản lịch sử
     */
    public function restoreRevision(int $invitationId, int $revisionId, ?int $userId = null): InvitationRevision
    {
        $invitation = Invitation::findOrFail($invitationId);
        $targetRevision = InvitationRevision::where('invitation_id', $invitationId)
            ->where('id', $revisionId)
            ->firstOrFail();

        $targetConfig = $targetRevision->config_data;

        // Cập nhật dữ liệu vào bảng thiệp cưới
        $invitation->custom_config = $targetConfig;
        $invitation->draft_config = $targetConfig;
        $invitation->save();

        // Tạo một bản revision mới đánh dấu phục hồi (Không bao giờ xóa lịch sử cũ)
        $restoreTitle = "Phục hồi từ phiên bản v{$targetRevision->version_number} (".$targetRevision->created_at->format('d/m/Y H:i').')';
        $newRevision = $this->createRevision($invitation, $targetConfig, 'restored', $restoreTitle, $userId);

        // Xóa cache public thiệp nếu đã xuất bản
        Cache::forget('invitation_public_'.$invitation->slug);
        Cache::forget('invitation_embed_'.$invitation->id);

        // Ghi nhận nhật ký kiểm toán
        if (class_exists(AuditLog::class)) {
            try {
                AuditLog::create([
                    'actor_id' => $userId ?: Auth::id(),
                    'actor_type' => User::class,
                    'action' => 'restore_invitation_revision',
                    'auditable_type' => Invitation::class,
                    'auditable_id' => $invitation->id,
                    'new_values' => [
                        'restored_from_version' => $targetRevision->version_number,
                        'new_version' => $newRevision->version_number,
                    ],
                    'ip_address' => Request::ip() ?? '127.0.0.1',
                    'user_agent' => Request::userAgent() ?? 'System',
                ]);
            } catch (\Throwable $e) {
                // Tiếp tục luồng nếu ghi log audit gặp ngoại lệ
            }
        }

        return $newRevision;
    }

    /**
     * Phát hiện xung đột phiên bản khi nhiều người cùng thao tác (STU-03: 409 Conflict Prevention)
     *
     * @return array{has_conflict: bool, latest_version?: int, last_modified_by?: string, last_modified_at?: string}
     */
    public function detectConflict(Invitation $invitation, int $clientBaseRevisionId): array
    {
        $latest = $invitation->revisions()->first();

        if (! $latest) {
            return ['has_conflict' => false];
        }

        if ($latest->id > $clientBaseRevisionId) {
            return [
                'has_conflict' => true,
                'latest_version' => $latest->version_number,
                'last_modified_by' => $latest->creator?->name ?? 'Người dùng khác',
                'last_modified_at' => $latest->created_at->toIso8601String(),
            ];
        }

        return ['has_conflict' => false];
    }

    /**
     * So sánh danh sách các mục/section bị thay đổi
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     * @return array<string>
     */
    protected function computeDiffSummary(array $old, array $new): array
    {
        $changedSections = [];
        $allKeys = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($allKeys as $key) {
            $oldVal = $old[$key] ?? null;
            $newVal = $new[$key] ?? null;

            if ($oldVal !== $newVal) {
                $changedSections[] = (string) $key;
            }
        }

        return ! empty($changedSections) ? $changedSections : ['minor_adjustments'];
    }

    /**
     * Xây dựng cấu trúc so sánh chi tiết giữa 2 bản JSON
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $current
     * @return array<int, array<string, mixed>>
     */
    protected function buildDetailedDiff(array $base, array $current): array
    {
        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($base), array_keys($current)));

        foreach ($allKeys as $key) {
            $hasBase = array_key_exists($key, $base);
            $hasCurrent = array_key_exists($key, $current);

            if (! $hasBase && $hasCurrent) {
                $changes[] = [
                    'key' => $key,
                    'action' => 'added',
                    'old_value' => null,
                    'new_value' => is_array($current[$key]) ? json_encode($current[$key], JSON_UNESCAPED_UNICODE) : (string) $current[$key],
                ];
            } elseif ($hasBase && ! $hasCurrent) {
                $changes[] = [
                    'key' => $key,
                    'action' => 'deleted',
                    'old_value' => is_array($base[$key]) ? json_encode($base[$key], JSON_UNESCAPED_UNICODE) : (string) $base[$key],
                    'new_value' => null,
                ];
            } elseif ($base[$key] !== $current[$key]) {
                $changes[] = [
                    'key' => $key,
                    'action' => 'modified',
                    'old_value' => is_array($base[$key]) ? json_encode($base[$key], JSON_UNESCAPED_UNICODE) : (string) $base[$key],
                    'new_value' => is_array($current[$key]) ? json_encode($current[$key], JSON_UNESCAPED_UNICODE) : (string) $current[$key],
                ];
            }
        }

        return $changes;
    }

    /**
     * Cắt tỉa các bản nháp autosave quá cũ để giải phóng dung lượng database
     */
    protected function pruneOldAutosaves(int $invitationId): void
    {
        $autosaves = InvitationRevision::where('invitation_id', $invitationId)
            ->where('revision_type', 'autosave')
            ->orderByDesc('version_number')
            ->pluck('id');

        if ($autosaves->count() > self::MAX_AUTOSAVES_PER_INVITATION) {
            $idsToDelete = $autosaves->slice(self::MAX_AUTOSAVES_PER_INVITATION);
            InvitationRevision::whereIn('id', $idsToDelete)->delete();
        }
    }
}
