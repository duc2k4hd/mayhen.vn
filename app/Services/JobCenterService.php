<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class JobCenterService
{
    /**
     * Danh sách từ khóa nhạy cảm cần lọc sạch khỏi exception stack trace.
     */
    protected const SENSITIVE_PATTERNS = [
        '/password[=:\'"]+([^\s,;\'"&]+)/i' => 'password=********',
        '/secret[=:\'"]+([^\s,;\'"&]+)/i' => 'secret=********',
        '/api_key[=:\'"]+([^\s,;\'"&]+)/i' => 'api_key=********',
        '/bearer\s+([A-Za-z0-9\-_\.=]+)/i' => 'Bearer ********',
        '/token[=:\'"]+([^\s,;\'"&]+)/i' => 'token=********',
        '/vietqr_key[=:\'"]+([^\s,;\'"&]+)/i' => 'vietqr_key=********',
        '/sepay_token[=:\'"]+([^\s,;\'"&]+)/i' => 'sepay_token=********',
        '/zalo_token[=:\'"]+([^\s,;\'"&]+)/i' => 'zalo_token=********',
    ];

    /**
     * Thống kê KPI tổng thể cho Job Center.
     *
     * @return array<string, mixed>
     */
    public function getQueueStatistics(): array
    {
        $totalFailed = DB::table('failed_jobs')->count();
        $totalPending = DB::table('jobs')->count();

        $batchesQuery = DB::table('job_batches');
        $activeBatches = (clone $batchesQuery)
            ->whereNull('finished_at')
            ->whereNull('cancelled_at')
            ->count();

        $completedBatches24h = (clone $batchesQuery)
            ->whereNotNull('finished_at')
            ->where('finished_at', '>=', now()->subDay()->timestamp)
            ->count();

        $failedBatches24h = (clone $batchesQuery)
            ->where('failed_jobs', '>', 0)
            ->where('created_at', '>=', now()->subDay()->timestamp)
            ->count();

        $queuesWithFailed = DB::table('failed_jobs')
            ->distinct()
            ->pluck('queue')
            ->toArray();

        return [
            'total_failed' => $totalFailed,
            'total_pending' => $totalPending,
            'active_batches' => $activeBatches,
            'completed_batches_24h' => $completedBatches24h,
            'failed_batches_24h' => $failedBatches24h,
            'queues_with_failed' => $queuesWithFailed,
        ];
    }

    /**
     * Lấy danh sách các Lô công việc (Job Batches) kèm tiến độ.
     */
    public function getBatches(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('job_batches')->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereNull('finished_at')->whereNull('cancelled_at');
            } elseif ($filters['status'] === 'completed') {
                $query->whereNotNull('finished_at')->where('failed_jobs', 0);
            } elseif ($filters['status'] === 'failed') {
                $query->where('failed_jobs', '>', 0);
            } elseif ($filters['status'] === 'cancelled') {
                $query->whereNotNull('cancelled_at');
            }
        }

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', $search)
                    ->orWhere('name', 'like', $search);
            });
        }

        $batches = $query->paginate($perPage, ['*'], 'batches_page')->withQueryString();

        $batches->through(function ($batch) {
            $total = (int) $batch->total_jobs;
            $pending = (int) $batch->pending_jobs;
            $failed = (int) $batch->failed_jobs;
            $processed = $total - $pending;
            $progress = $total > 0 ? (int) round(($processed / $total) * 100) : 100;

            $status = 'running';
            $statusLabel = 'Đang Xử Lý';
            $statusBadge = 'mayhen_admin_badge--info';

            if ($batch->cancelled_at) {
                $status = 'cancelled';
                $statusLabel = 'Đã Hủy';
                $statusBadge = 'mayhen_admin_badge--secondary';
            } elseif ($batch->finished_at) {
                if ($failed > 0) {
                    $status = 'failed';
                    $statusLabel = 'Hoàn Tất Kèm Lỗi';
                    $statusBadge = 'mayhen_admin_badge--danger';
                } else {
                    $status = 'completed';
                    $statusLabel = 'Thành Công 100%';
                    $statusBadge = 'mayhen_admin_badge--success';
                }
            }

            $batch->progress = $progress;
            $batch->processed_jobs = $processed;
            $batch->status = $status;
            $batch->status_label = $statusLabel;
            $batch->status_badge = $statusBadge;
            $batch->created_at_human = Carbon::createFromTimestamp($batch->created_at)->format('H:i d/m/Y');
            $batch->finished_at_human = $batch->finished_at ? Carbon::createFromTimestamp($batch->finished_at)->format('H:i d/m/Y') : null;

            return $batch;
        });

        return $batches;
    }

    /**
     * Lấy danh sách các jobs đang xếp hàng chờ xử lý trong `jobs`.
     */
    public function getPendingJobs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('jobs')->orderBy('available_at');

        if (! empty($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('queue', 'like', $search)
                    ->orWhere('payload', 'like', $search);
            });
        }

        $jobs = $query->paginate($perPage, ['*'], 'pending_page')->withQueryString();

        $jobs->through(function ($job) {
            $payload = json_decode($job->payload, true) ?? [];
            $job->display_name = $payload['displayName'] ?? $payload['job'] ?? 'Unknown Job';
            $job->job_type = $this->categorizeJobType($job->display_name);
            $job->created_at_human = Carbon::createFromTimestamp($job->created_at)->format('H:i:s d/m/Y');
            $job->available_at_human = Carbon::createFromTimestamp($job->available_at)->format('H:i:s d/m/Y');
            $job->is_reserved = ! empty($job->reserved_at);

            return $job;
        });

        return $jobs;
    }

    /**
     * Lấy danh sách các failed jobs với phân loại nghiệp vụ.
     */
    public function getFailedJobs(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if (! empty($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('queue', 'like', $search)
                    ->orWhere('payload', 'like', $search)
                    ->orWhere('exception', 'like', $search);
            });
        }

        $jobs = $query->paginate($perPage, ['*'], 'failed_page')->withQueryString();

        $jobs->through(function ($job) {
            $payload = json_decode($job->payload, true) ?? [];
            $displayName = $payload['displayName'] ?? $payload['job'] ?? 'Unknown Job';
            $job->display_name = $displayName;
            $job->attempts = $payload['attempts'] ?? 0;
            $job->category = $this->categorizeJobType($displayName);
            $job->is_high_risk = $this->isHighRiskJobName($displayName);
            $job->sanitized_exception_snippet = $this->sanitizeSnippet($job->exception);
            $job->failed_at_human = Carbon::parse($job->failed_at)->format('H:i d/m/Y');

            return $job;
        });

        return $jobs;
    }

    /**
     * Lấy chi tiết một failed job kèm sanitized exception.
     */
    public function getFailedJobDetails(string $uuid): ?object
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if (! $job) {
            return null;
        }

        $payload = json_decode($job->payload, true) ?? [];
        $job->display_name = $payload['displayName'] ?? $payload['job'] ?? 'Unknown Job';
        $job->category = $this->categorizeJobType($job->display_name);
        $job->is_high_risk = $this->isHighRiskJobName($job->display_name);
        $job->sanitized_exception = $this->sanitizeException($job->exception);
        $job->exception = $job->sanitized_exception; // Ghi đè exception gốc bằng bản đã khử thông tin bí mật
        $job->raw_payload = $payload;

        return $job;
    }

    /**
     * Phân loại tác vụ dựa trên tên class hoặc queue.
     *
     * @return array{name: string, badge_class: string}
     */
    public function categorizeJobType(string $displayName): array
    {
        $lower = strtolower($displayName);

        if (str_contains($lower, 'mail') || str_contains($lower, 'email')) {
            return ['name' => 'Thư Điện Tử (Email)', 'badge_class' => 'mayhen_admin_badge--success'];
        }
        if (str_contains($lower, 'sms') || str_contains($lower, 'zalo') || str_contains($lower, 'message')) {
            return ['name' => 'Tin Nhắn (SMS/Zalo)', 'badge_class' => 'mayhen_admin_badge--primary'];
        }
        if (str_contains($lower, 'image') || str_contains($lower, 'media') || str_contains($lower, 'photo') || str_contains($lower, 'webp')) {
            return ['name' => 'Xử Lý Ảnh (Media/WebP)', 'badge_class' => 'mayhen_admin_badge--info'];
        }
        if (str_contains($lower, 'import') || str_contains($lower, 'guest')) {
            return ['name' => 'Khách Mời (Guest Import)', 'badge_class' => 'mayhen_admin_badge--warning'];
        }
        if (str_contains($lower, 'archive') || str_contains($lower, 'zip') || str_contains($lower, 'export')) {
            return ['name' => 'Đóng Gói ZIP Kỷ Niệm', 'badge_class' => 'mayhen_admin_badge--secondary'];
        }
        if (str_contains($lower, 'lifecycle') || str_contains($lower, 'expiry') || str_contains($lower, 'expire')) {
            return ['name' => 'Vòng Đời Hạn Dùng Thiệp', 'badge_class' => 'mayhen_admin_badge--danger'];
        }
        if (str_contains($lower, 'webhook') || str_contains($lower, 'payment') || str_contains($lower, 'sepay')) {
            return ['name' => 'Thanh Toán & Webhook', 'badge_class' => 'mayhen_admin_badge--warning'];
        }

        return ['name' => 'Tác Vụ Hệ Thống Khác', 'badge_class' => 'mayhen_admin_badge--secondary'];
    }

    /**
     * Kiểm tra tác vụ có rủi ro cao gây trùng lặp (Idempotency sensitive) hay không.
     */
    public function isHighRiskJobName(string $displayName): bool
    {
        $lower = strtolower($displayName);

        return str_contains($lower, 'mail')
            || str_contains($lower, 'sms')
            || str_contains($lower, 'zalo')
            || str_contains($lower, 'payment')
            || str_contains($lower, 'webhook')
            || str_contains($lower, 'dispatch');
    }

    /**
     * Khử sạch thông tin nhạy cảm (passwords, tokens, keys) khỏi exception stack trace.
     */
    public function sanitizeException(string $exception): string
    {
        $sanitized = $exception;
        foreach (self::SENSITIVE_PATTERNS as $pattern => $replacement) {
            $sanitized = preg_replace($pattern, $replacement, $sanitized);
        }

        return $sanitized;
    }

    /**
     * Tạo snippet ngắn gọn 200 ký tự đầu tiên của lỗi.
     */
    protected function sanitizeSnippet(string $exception): string
    {
        $firstLine = strtok($exception, "\n");
        $snippet = $this->sanitizeException($firstLine ?: $exception);

        return mb_strimwidth($snippet, 0, 180, '...');
    }

    /**
     * Thử lại một failed job theo UUID kèm ghi log kiểm toán.
     */
    public function retryJob(string $uuid, ?User $admin = null): bool
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if (! $job) {
            return false;
        }

        $payload = json_decode($job->payload, true) ?? [];
        $displayName = $payload['displayName'] ?? $payload['job'] ?? $uuid;

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        AuditLogger::log(
            'JOB_RETRY',
            'FailedJob',
            $uuid,
            "Thử lại tác vụ nền: {$displayName} (Queue: {$job->queue})",
            ['admin_id' => $admin?->id ?? auth()->id(), 'queue' => $job->queue]
        );

        return true;
    }

    /**
     * Thử lại toàn bộ các failed jobs kèm ghi log kiểm toán.
     */
    public function retryAllFailed(?User $admin = null, ?string $queue = null): int
    {
        $query = DB::table('failed_jobs');
        if ($queue) {
            $query->where('queue', $queue);
        }

        $count = $query->count();
        if ($count === 0) {
            return 0;
        }

        if ($queue) {
            Artisan::call('queue:retry', ['--queue' => $queue]);
        } else {
            Artisan::call('queue:retry', ['id' => ['all']]);
        }

        AuditLogger::log(
            'JOB_RETRY_ALL',
            'FailedJob',
            0,
            "Thử lại toàn bộ {$count} tác vụ nền thất bại".($queue ? " trong hàng đợi {$queue}" : ''),
            ['count' => $count, 'queue' => $queue, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $count;
    }

    /**
     * Xóa một failed job theo UUID kèm kiểm toán.
     */
    public function deleteFailedJob(string $uuid, ?User $admin = null): bool
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();
        if (! $job) {
            return false;
        }

        $payload = json_decode($job->payload, true) ?? [];
        $displayName = $payload['displayName'] ?? $payload['job'] ?? $uuid;

        Artisan::call('queue:forget', ['id' => $uuid]);

        AuditLogger::log(
            'JOB_FORGOTTEN',
            'FailedJob',
            $uuid,
            "Xóa bỏ tác vụ nền lỗi: {$displayName}",
            ['queue' => $job->queue, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Xóa sạch toàn bộ failed jobs (Purge).
     */
    public function purgeFailedJobs(?User $admin = null): int
    {
        $count = DB::table('failed_jobs')->count();
        if ($count === 0) {
            return 0;
        }

        Artisan::call('queue:flush');

        AuditLogger::log(
            'JOB_PURGE_ALL',
            'FailedJob',
            0,
            "Xóa sạch toàn bộ {$count} tác vụ nền lỗi khỏi hệ thống",
            ['purged_count' => $count, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return $count;
    }

    /**
     * Hủy một Lô công việc (Job Batch) đang hoạt động.
     */
    public function cancelBatch(string $batchId, ?User $admin = null): bool
    {
        $batch = Bus::findBatch($batchId);
        if (! $batch) {
            return false;
        }

        $batch->cancel();

        AuditLogger::log(
            'JOB_BATCH_CANCELLED',
            'JobBatch',
            $batchId,
            "Hủy bỏ lô công việc: {$batch->name} (#{$batchId})",
            ['name' => $batch->name, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Thử lại các jobs thất bại trong một batch.
     */
    public function retryBatch(string $batchId, ?User $admin = null): bool
    {
        $batchRecord = DB::table('job_batches')->where('id', $batchId)->first();
        if (! $batchRecord || empty($batchRecord->failed_job_ids)) {
            return false;
        }

        $failedIds = json_decode($batchRecord->failed_job_ids, true);
        if (empty($failedIds) || ! is_array($failedIds)) {
            return false;
        }

        Artisan::call('queue:retry-batch', ['id' => $batchId]);

        AuditLogger::log(
            'JOB_BATCH_RETRIED',
            'JobBatch',
            $batchId,
            "Thử lại các tác vụ lỗi trong lô công việc: {$batchRecord->name} (#{$batchId})",
            ['batch_id' => $batchId, 'failed_count' => count($failedIds), 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Xuất báo cáo danh sách lỗi dạng mảng dữ liệu (để xuất CSV).
     *
     * @return array<int, array<string, string>>
     */
    public function getErrorReportData(array $filters = []): array
    {
        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if (! empty($filters['queue'])) {
            $query->where('queue', $filters['queue']);
        }

        $rows = $query->limit(500)->get();
        $report = [];

        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true) ?? [];
            $displayName = $payload['displayName'] ?? $payload['job'] ?? 'Unknown Job';
            $firstLine = strtok($row->exception, "\n") ?: '';

            $report[] = [
                'UUID' => $row->uuid,
                'Queue' => $row->queue,
                'Tên Tác Vụ' => $displayName,
                'Phân Loại' => $this->categorizeJobType($displayName)['name'],
                'Thời Điểm Lỗi' => $row->failed_at,
                'Lỗi Rút Gọn' => $this->sanitizeException($firstLine),
            ];
        }

        return $report;
    }
}
