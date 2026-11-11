<?php

namespace App\Services;

use App\Models\ContentReport;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Database\Eloquent\Model;

class AbuseReportService
{
    /**
     * Ngưỡng số lượng báo cáo tự động tạm ẩn nội dung chờ duyệt.
     */
    public const AUTO_HIDE_THRESHOLD = 3;

    /**
     * Gửi báo cáo vi phạm cho một nội dung (Lời chúc hoặc Ảnh).
     *
     * @param  array{
     *     reason: string,
     *     details?: ?string,
     *     reporter_name?: ?string,
     *     reporter_phone?: ?string,
     *     reporter_ip?: ?string
     * }  $data
     */
    public function createReport(Model $model, array $data, ?User $user = null): ContentReport
    {
        $weddingId = $model->wedding_id ?? ($model instanceof Wedding ? $model->id : null);
        if (! $weddingId) {
            throw new \InvalidArgumentException('Nội dung báo cáo không thuộc về bất kỳ đám cưới nào.');
        }

        $wedding = Wedding::find($weddingId);
        $isOwner = $user && $wedding && (int) $user->id === (int) $wedding->user_id;

        $report = ContentReport::create([
            'reportable_type' => get_class($model),
            'reportable_id' => $model->id,
            'wedding_id' => $weddingId,
            'reporter_name' => $data['reporter_name'] ?? ($user?->name ?? 'Quan khách'),
            'reporter_phone' => $data['reporter_phone'] ?? ($user?->phone ?? null),
            'reporter_ip' => $data['reporter_ip'] ?? request()->ip(),
            'reason' => $data['reason'] ?? ContentReport::REASON_INAPPROPRIATE,
            'details' => $data['details'] ?? null,
            'status' => ContentReport::STATUS_PENDING,
        ]);

        // Nếu là chủ thiệp báo cáo: Tự động ẩn ngay lập tức theo Mục 17.3 PRD V3
        if ($isOwner) {
            if (isset($model->is_approved)) {
                $model->update(['is_approved' => false]);
            }
        } else {
            // Kiểm tra số lượt báo cáo chờ duyệt của nội dung này
            $pendingCount = ContentReport::where('reportable_type', get_class($model))
                ->where('reportable_id', $model->id)
                ->where('status', ContentReport::STATUS_PENDING)
                ->count();

            if ($pendingCount >= self::AUTO_HIDE_THRESHOLD) {
                if (isset($model->is_approved)) {
                    $model->update(['is_approved' => false]);
                }
            }
        }

        return $report;
    }

    /**
     * Xử lý giải quyết một hồ sơ báo cáo vi phạm (Mục 17.3 PRD V3).
     *
     * @param  string  $action  'hide' | 'delete' | 'dismiss'
     */
    public function resolveReport(ContentReport $report, string $action, ?User $admin = null, ?string $notes = null): ContentReport
    {
        $target = $report->reportable;
        $adminId = $admin?->id ?? auth()->id();

        switch ($action) {
            case 'hide':
                if ($target && isset($target->is_approved)) {
                    $target->update(['is_approved' => false]);
                }
                $report->update([
                    'status' => ContentReport::STATUS_HIDDEN,
                    'action_taken' => 'hidden',
                    'resolved_by' => $adminId,
                    'resolved_at' => now(),
                    'resolution_notes' => $notes ?: 'Quản trị viên đã ẩn nội dung khỏi thiệp cưới.',
                ]);
                break;

            case 'delete':
                if ($target) {
                    $target->delete();
                }
                $report->update([
                    'status' => ContentReport::STATUS_DELETED,
                    'action_taken' => 'deleted',
                    'resolved_by' => $adminId,
                    'resolved_at' => now(),
                    'resolution_notes' => $notes ?: 'Quản trị viên đã xóa vĩnh viễn nội dung vi phạm.',
                ]);
                break;

            case 'dismiss':
                if ($target && isset($target->is_approved)) {
                    $target->update(['is_approved' => true]);
                }
                $report->update([
                    'status' => ContentReport::STATUS_DISMISSED,
                    'action_taken' => 'dismissed',
                    'resolved_by' => $adminId,
                    'resolved_at' => now(),
                    'resolution_notes' => $notes ?: 'Nội dung đạt chuẩn an toàn, bác bỏ khiếu nại báo cáo.',
                ]);
                break;

            default:
                throw new \InvalidArgumentException("Hành động xử lý '{$action}' không hợp lệ.");
        }

        // Ghi nhật ký kiểm toán bất biến
        AuditLogger::log(
            'MODERATION_CASE_RESOLVED',
            ContentReport::class,
            $report->id,
            "Quản trị viên xử lý hồ sơ kiểm duyệt #{$report->id} ({$report->reason_label}) -> Hành động: {$action}",
            [
                'report_id' => $report->id,
                'target_type' => $report->reportable_type,
                'target_id' => $report->reportable_id,
                'wedding_id' => $report->wedding_id,
                'action_taken' => $action,
                'admin_id' => $adminId,
                'notes' => $notes,
            ]
        );

        return $report;
    }

    /**
     * Thống kê các chỉ số kiểm duyệt nội dung.
     *
     * @return array{
     *     pending_count: int,
     *     hidden_count: int,
     *     deleted_count: int,
     *     dismissed_count: int,
     *     total_count: int
     * }
     */
    public function getStatistics(): array
    {
        $pending = ContentReport::where('status', ContentReport::STATUS_PENDING)->count();
        $hidden = ContentReport::where('status', ContentReport::STATUS_HIDDEN)->count();
        $deleted = ContentReport::where('status', ContentReport::STATUS_DELETED)->count();
        $dismissed = ContentReport::where('status', ContentReport::STATUS_DISMISSED)->count();

        return [
            'pending_count' => $pending,
            'hidden_count' => $hidden,
            'deleted_count' => $deleted,
            'dismissed_count' => $dismissed,
            'total_count' => $pending + $hidden + $deleted + $dismissed,
        ];
    }
}
