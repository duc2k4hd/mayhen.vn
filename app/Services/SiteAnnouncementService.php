<?php

namespace App\Services;

use App\Models\SiteAnnouncement;
use App\Models\User;

class SiteAnnouncementService
{
    /**
     * Lấy thông báo hiện hành đang kích hoạt cho vị trí cụ thể.
     */
    public function getActiveAnnouncement(string $placement = SiteAnnouncement::PLACEMENT_TOP_BAR): ?SiteAnnouncement
    {
        return SiteAnnouncement::active()
            ->forPlacement($placement)
            ->latest('id')
            ->first();
    }

    /**
     * Tạo thông báo mới kèm kiểm toán.
     */
    public function createAnnouncement(array $data, ?User $admin = null): SiteAnnouncement
    {
        $announcement = SiteAnnouncement::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'type' => $data['type'] ?? SiteAnnouncement::TYPE_INFO,
            'placement' => $data['placement'] ?? SiteAnnouncement::PLACEMENT_TOP_BAR,
            'cta_text' => $data['cta_text'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_dismissible' => (bool) ($data['is_dismissible'] ?? true),
            'starts_at' => ! empty($data['starts_at']) ? $data['starts_at'] : null,
            'ends_at' => ! empty($data['ends_at']) ? $data['ends_at'] : null,
            'created_by' => $admin?->id ?? auth()->id(),
        ]);

        AuditLogger::log(
            'ANNOUNCEMENT_CREATED',
            SiteAnnouncement::class,
            $announcement->id,
            "Tạo thông báo công khai #{$announcement->id}: {$announcement->title} ({$announcement->placement_label})",
            [
                'title' => $announcement->title,
                'type' => $announcement->type,
                'placement' => $announcement->placement,
                'is_active' => $announcement->is_active,
                'starts_at' => $announcement->starts_at?->toIso8601String(),
                'ends_at' => $announcement->ends_at?->toIso8601String(),
            ]
        );

        return $announcement;
    }

    /**
     * Cập nhật thông báo công khai.
     */
    public function updateAnnouncement(SiteAnnouncement $announcement, array $data, ?User $admin = null): SiteAnnouncement
    {
        $announcement->update([
            'title' => $data['title'] ?? $announcement->title,
            'content' => $data['content'] ?? $announcement->content,
            'type' => $data['type'] ?? $announcement->type,
            'placement' => $data['placement'] ?? $announcement->placement,
            'cta_text' => array_key_exists('cta_text', $data) ? $data['cta_text'] : $announcement->cta_text,
            'cta_url' => array_key_exists('cta_url', $data) ? $data['cta_url'] : $announcement->cta_url,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $announcement->is_active,
            'is_dismissible' => isset($data['is_dismissible']) ? (bool) $data['is_dismissible'] : $announcement->is_dismissible,
            'starts_at' => array_key_exists('starts_at', $data) ? (! empty($data['starts_at']) ? $data['starts_at'] : null) : $announcement->starts_at,
            'ends_at' => array_key_exists('ends_at', $data) ? (! empty($data['ends_at']) ? $data['ends_at'] : null) : $announcement->ends_at,
        ]);

        AuditLogger::log(
            'ANNOUNCEMENT_UPDATED',
            SiteAnnouncement::class,
            $announcement->id,
            "Cập nhật thông báo công khai #{$announcement->id}: {$announcement->title}",
            [
                'title' => $announcement->title,
                'type' => $announcement->type,
                'placement' => $announcement->placement,
                'is_active' => $announcement->is_active,
            ]
        );

        return $announcement;
    }

    /**
     * Bật / tắt nhanh trạng thái thông báo.
     */
    public function toggleStatus(SiteAnnouncement $announcement, ?User $admin = null): bool
    {
        $newStatus = ! $announcement->is_active;
        $announcement->update(['is_active' => $newStatus]);

        AuditLogger::log(
            'ANNOUNCEMENT_TOGGLED',
            SiteAnnouncement::class,
            $announcement->id,
            ($newStatus ? 'Bật' : 'Tắt')." thông báo #{$announcement->id} ({$announcement->title})",
            ['is_active' => $newStatus]
        );

        return $newStatus;
    }

    /**
     * Xóa thông báo công khai.
     */
    public function deleteAnnouncement(SiteAnnouncement $announcement, ?User $admin = null): void
    {
        $title = $announcement->title;
        $id = $announcement->id;

        $announcement->delete();

        AuditLogger::log(
            'ANNOUNCEMENT_DELETED',
            SiteAnnouncement::class,
            $id,
            "Xóa thông báo công khai #{$id}: {$title}",
            ['title' => $title]
        );
    }

    /**
     * Thống kê KPI thông báo.
     *
     * @return array{
     *     active_count: int,
     *     scheduled_count: int,
     *     expired_count: int,
     *     total_views: int,
     *     total_clicks: int
     * }
     */
    public function getStatistics(): array
    {
        $now = now();
        $active = SiteAnnouncement::active()->count();

        $scheduled = SiteAnnouncement::where('is_active', true)
            ->whereNotNull('starts_at')
            ->where('starts_at', '>', $now)
            ->count();

        $expired = SiteAnnouncement::whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->count();

        $totalViews = (int) SiteAnnouncement::sum('views_count');
        $totalClicks = (int) SiteAnnouncement::sum('clicks_count');

        return [
            'active_count' => $active,
            'scheduled_count' => $scheduled,
            'expired_count' => $expired,
            'total_views' => $totalViews,
            'total_clicks' => $totalClicks,
        ];
    }
}
