<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiChatbotLog;
use App\Models\AiCustomerMemory;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeGap;
use App\Models\AiKnowledgeRelation;
use App\Models\AiKnowledgeVersion;
use App\Models\AiLearningCheckpoint;
use App\Models\AiLearningRun;
use App\Models\AiLearningTask;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Adaptive Resource Controller — Bảo vệ tuyệt đối Website & Shared Hosting yếu.
 * Nguyên tắc thứ tự ưu tiên: Production Traffic > Business Jobs > AI Learning Jobs.
 * Tuyệt đối KHÔNG bịa số liệu tài nguyên nếu hệ điều hành/hosting không cung cấp.
 */
class AdaptiveResourceController
{
    public const HEARTBEAT_CACHE_KEY = 'mayhen_ai_brain_worker_heartbeat_v2';

    public const CONSECUTIVE_FAILURES_CACHE_KEY = 'mayhen_ai_brain_consecutive_failures_v2';

    public const AUTO_COOLDOWN_UNTIL_CACHE_KEY = 'mayhen_ai_brain_auto_cooldown_until_v2';

    public const AUTO_COOLDOWN_SECONDS = 60;

    public function __construct(
        protected AiGovernanceService $governance
    ) {}

    /**
     * Lấy cấu hình hồ sơ tải tương ứng với chế độ đang chọn (conservative / balanced / performance / paused).
     *
     * @return array<string, mixed>
     */
    public function getActiveProfile(): array
    {
        $policy = $this->governance->getPolicy();
        $mode = $policy['learning_mode'];
        $profiles = (array) config('ai_brain.modes', []);
        $profile = (array) ($profiles[$mode] ?? $profiles['conservative'] ?? []);

        $profile['mode'] = $mode;
        $profile['max_memory_mb'] = min(
            (int) ($profile['max_memory_mb'] ?? 128),
            (int) $policy['learning_max_memory_mb']
        );

        return $profile;
    }

    /**
     * Đo trực tiếp các chỉ số hệ thống & ứng dụng thực tế hiện tại (không giả lập số liệu).
     *
     * @return array{
     *     memory_mb: float,
     *     peak_memory_mb: float,
     *     db_latency_ms: float,
     *     db_reachable: bool,
     *     business_queue_backlog: int,
     *     client_chat_active: bool,
     *     cpu_load_1m: ?float,
     *     cpu_metric_available: bool,
     *     disk_free_mb: ?float,
     *     consecutive_failures: int,
     *     learning_tokens_today: int,
     *     learning_cost_today: float
     * }
     */
    public function measureRealMetrics(): array
    {
        $memoryMb = round(memory_get_usage(true) / 1048576, 2);
        $peakMemoryMb = round(memory_get_peak_usage(true) / 1048576, 2);

        $dbLatencyMs = 0.0;
        $dbReachable = true;
        try {
            $t0 = microtime(true);
            DB::select('SELECT 1');
            $dbLatencyMs = round((microtime(true) - $t0) * 1000, 2);
        } catch (Throwable) {
            $dbReachable = false;
            $dbLatencyMs = 9999.0;
        }

        $businessBacklog = 0;
        if ($dbReachable && Schema::hasTable('jobs')) {
            try {
                $businessBacklog = (int) DB::table('jobs')
                    ->where('queue', '!=', 'ai-learning')
                    ->count();
            } catch (Throwable) {
                $businessBacklog = 0;
            }
        }

        $lastClientChat = (int) Cache::get('mayhen_client_chat_active_at', 0);
        $clientChatActive = $lastClientChat > 0 && (time() - $lastClientChat) <= 4;

        $cpuLoad = null;
        $cpuAvailable = false;
        if (function_exists('sys_getloadavg')) {
            $loads = @sys_getloadavg();
            if (is_array($loads) && isset($loads[0]) && is_numeric($loads[0])) {
                $cpuLoad = round((float) $loads[0], 2);
                $cpuAvailable = true;
            }
        }

        $diskFreeMb = null;
        if (function_exists('disk_free_space')) {
            $bytes = @disk_free_space(storage_path());
            if ($bytes !== false && is_numeric($bytes)) {
                $diskFreeMb = round(((float) $bytes) / 1048576, 1);
            }
        }

        return [
            'memory_mb' => $memoryMb,
            'peak_memory_mb' => $peakMemoryMb,
            'db_latency_ms' => $dbLatencyMs,
            'db_reachable' => $dbReachable,
            'business_queue_backlog' => $businessBacklog,
            'client_chat_active' => $clientChatActive,
            'cpu_load_1m' => $cpuLoad,
            'cpu_metric_available' => $cpuAvailable,
            'disk_free_mb' => $diskFreeMb,
            'consecutive_failures' => (int) Cache::get(self::CONSECUTIVE_FAILURES_CACHE_KEY, 0),
            'learning_tokens_today' => $this->governance->getTodayLearningTokensUsed(),
            'learning_cost_today' => $this->governance->getTodayLearningCostUsed(),
        ];
    }

    /**
     * Đánh giá khả năng tiếp tục thực thi 1 task học nền dựa trên đo lường thực tế & ngưỡng bảo thủ.
     *
     * @param  array<string, mixed>|null  $overrideMetrics  Chỉ dùng cho unit/integration test mô phỏng tải cao
     * @return array{action: string, allowed: bool, sleep_ms: int, code: string, reason: string, metrics: array<string, mixed>}
     */
    public function evaluateStepPermission(
        int $cycleBudgetSeconds,
        float $cycleStartedAt,
        bool $requireLlmBudget = false,
        ?array $overrideMetrics = null
    ): array {
        $metrics = $overrideMetrics !== null
            ? array_merge($this->measureRealMetrics(), $overrideMetrics)
            : $this->measureRealMetrics();

        $op = $this->governance->isLearningOperational($requireLlmBudget);
        if (! $op['allowed']) {
            return [
                'action' => 'stop',
                'allowed' => false,
                'sleep_ms' => 0,
                'code' => $op['code'],
                'reason' => $op['reason'],
                'metrics' => $metrics,
            ];
        }

        if (! $metrics['db_reachable']) {
            return [
                'action' => 'stop',
                'allowed' => false,
                'sleep_ms' => 5000,
                'code' => 'db_unreachable',
                'reason' => 'Database không phản hồi — dừng học ngay để bảo toàn dữ liệu.',
                'metrics' => $metrics,
            ];
        }

        $elapsedSeconds = microtime(true) - $cycleStartedAt;
        if ($cycleBudgetSeconds > 0 && $elapsedSeconds >= $cycleBudgetSeconds) {
            return [
                'action' => 'stop_budget',
                'allowed' => false,
                'sleep_ms' => 0,
                'code' => 'cycle_budget_exhausted',
                'reason' => "Đã hết khung thời gian chu kỳ ({$cycleBudgetSeconds}s) — kết thúc an toàn.",
                'metrics' => $metrics,
            ];
        }

        $profile = $this->getActiveProfile();
        $policy = $this->governance->getPolicy();
        $maxFailures = (int) config('ai_brain.storage_quotas.max_consecutive_worker_failures', 5);

        // Nếu đang trong thời gian hạ nhiệt tự động sau chuỗi lỗi liên tiếp -> chờ hết 60s rồi tự hồi phục!
        $cooldownUntil = (int) Cache::get(self::AUTO_COOLDOWN_UNTIL_CACHE_KEY, 0);
        if ($cooldownUntil > time()) {
            $remain = $cooldownUntil - time();

            return [
                'action' => 'throttle',
                'allowed' => false,
                'sleep_ms' => min(15000, $remain * 1000),
                'code' => 'failure_cooldown',
                'reason' => "Đang hạ nhiệt Circuit Breaker sau chuỗi lỗi liên tiếp — tự động thử lại sau {$remain}s.",
                'metrics' => $metrics,
            ];
        }

        // Hết thời gian hạ nhiệt tự động -> tự reset bộ đếm lỗi để thăm dò chạy tiếp 24/7 (không đóng băng vĩnh viễn!)
        if ($cooldownUntil > 0 && $cooldownUntil <= time()) {
            Cache::forget(self::AUTO_COOLDOWN_UNTIL_CACHE_KEY);
            Cache::forget(self::CONSECUTIVE_FAILURES_CACHE_KEY);
            $metrics['consecutive_failures'] = 0;
        }

        if ((int) $metrics['consecutive_failures'] >= $maxFailures) {
            Cache::put(
                self::AUTO_COOLDOWN_UNTIL_CACHE_KEY,
                time() + self::AUTO_COOLDOWN_SECONDS,
                self::AUTO_COOLDOWN_SECONDS + 30
            );

            return [
                'action' => 'auto_pause',
                'allowed' => false,
                'sleep_ms' => 15000,
                'code' => 'failure_threshold_exceeded',
                'reason' => "Chạm ngưỡng {$maxFailures} lỗi liên tiếp — kích hoạt Circuit Breaker hạ nhiệt ".self::AUTO_COOLDOWN_SECONDS.'s rồi tự thử lại.',
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 1: Khách thật đang tương tác chatbot -> nhường 100%
        if (! empty($metrics['client_chat_active'])) {
            return [
                'action' => 'throttle',
                'allowed' => false,
                'sleep_ms' => 3500,
                'code' => 'client_traffic_priority',
                'reason' => 'Khách hàng đang tương tác thực tế — nhường toàn bộ tài nguyên cho Production Traffic.',
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 2: Hàng đợi nghiệp vụ (email, thanh toán, RSVP) đang tồn đọng -> nhường việc nghiệp vụ
        $maxBacklog = (int) ($profile['max_business_queue_backlog'] ?? 20);
        if ((int) $metrics['business_queue_backlog'] > $maxBacklog) {
            return [
                'action' => 'throttle',
                'allowed' => false,
                'sleep_ms' => 4500,
                'code' => 'business_queue_backlog',
                'reason' => "Hàng đợi nghiệp vụ có {$metrics['business_queue_backlog']} job (> {$maxBacklog}) — hoãn AI Learning.",
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 3: Giới hạn RAM tiến trình
        $maxMemoryMb = (int) ($profile['max_memory_mb'] ?? 128);
        if ((float) $metrics['memory_mb'] >= $maxMemoryMb) {
            if ($policy['learning_auto_pause'] && (float) $metrics['memory_mb'] >= ($maxMemoryMb * 1.15)) {
                $this->governance->setLearningPaused(
                    true,
                    null,
                    "Tự động tạm dừng (Auto-Pause): RAM tiến trình đạt {$metrics['memory_mb']}MB / {$maxMemoryMb}MB."
                );
            }

            return [
                'action' => 'auto_pause',
                'allowed' => false,
                'sleep_ms' => 0,
                'code' => 'memory_limit_reached',
                'reason' => "RAM tiến trình đạt {$metrics['memory_mb']}MB (giới hạn {$maxMemoryMb}MB) — dừng học bảo vệ hosting.",
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 4: Độ trễ Database
        $maxDbLatency = (float) ($profile['max_db_latency_ms'] ?? 120);
        if ((float) $metrics['db_latency_ms'] > $maxDbLatency) {
            return [
                'action' => 'throttle',
                'allowed' => false,
                'sleep_ms' => 5000,
                'code' => 'db_latency_high',
                'reason' => "Độ trễ Database cao ({$metrics['db_latency_ms']}ms > {$maxDbLatency}ms) — tạm hoãn học nền.",
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 5: CPU Load (chỉ kiểm khi hệ điều hành có cung cấp số liệu thật)
        $maxCpu = (float) ($profile['max_cpu_load'] ?? 2.5);
        if (! empty($metrics['cpu_metric_available']) && $metrics['cpu_load_1m'] !== null && (float) $metrics['cpu_load_1m'] >= $maxCpu) {
            return [
                'action' => 'throttle',
                'allowed' => false,
                'sleep_ms' => 6000,
                'code' => 'cpu_load_high',
                'reason' => "Tải CPU máy chủ cao ({$metrics['cpu_load_1m']} >= {$maxCpu}) — tự giảm tải bảo vệ hosting.",
                'metrics' => $metrics,
            ];
        }

        // Ưu tiên 6: Ổ đĩa trống quá thấp (< 150MB)
        if ($metrics['disk_free_mb'] !== null && (float) $metrics['disk_free_mb'] < 150.0) {
            if ($policy['learning_auto_pause']) {
                $this->governance->setLearningPaused(true, null, 'Tự động tạm dừng: dung lượng ổ đĩa trống dưới 150MB.');
            }

            return [
                'action' => 'auto_pause',
                'allowed' => false,
                'sleep_ms' => 0,
                'code' => 'disk_low',
                'reason' => "Dung lượng đĩa trống thấp ({$metrics['disk_free_mb']}MB) — tạm dừng học nền.",
                'metrics' => $metrics,
            ];
        }

        return [
            'action' => 'allow',
            'allowed' => true,
            'sleep_ms' => (int) ($profile['sleep_between_tasks_ms'] ?? 2500),
            'code' => 'ok',
            'reason' => 'Tài nguyên an toàn',
            'metrics' => $metrics,
        ];
    }

    public function recordFailure(): int
    {
        $count = (int) Cache::get(self::CONSECUTIVE_FAILURES_CACHE_KEY, 0) + 1;
        Cache::put(self::CONSECUTIVE_FAILURES_CACHE_KEY, $count, 1800);

        return $count;
    }

    public function resetFailures(): void
    {
        Cache::forget(self::CONSECUTIVE_FAILURES_CACHE_KEY);
        Cache::forget(self::AUTO_COOLDOWN_UNTIL_CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function recordWorkerHeartbeat(string $workerId, string $state, array $extra = []): void
    {
        Cache::put(self::HEARTBEAT_CACHE_KEY, array_merge([
            'worker_id' => $workerId,
            'state' => $state,
            'memory_mb' => round(memory_get_usage(true) / 1048576, 2),
            'updated_at' => now()->toIso8601String(),
            'timestamp' => time(),
        ], $extra), 600);
    }

    /**
     * @return array<string, mixed>
     */
    public function getWorkerHeartbeat(): array
    {
        $raw = Cache::get(self::HEARTBEAT_CACHE_KEY);
        if (! is_array($raw)) {
            return [
                'worker_id' => null,
                'state' => 'stopped',
                'is_alive' => false,
                'memory_mb' => null,
                'updated_at' => null,
                'last_message' => 'Chưa có tiến trình nền nào hoạt động gần đây.',
            ];
        }

        $age = time() - (int) ($raw['timestamp'] ?? 0);
        $raw['is_alive'] = $age <= 120 && in_array((string) ($raw['state'] ?? ''), ['running', 'idle', 'throttled'], true);
        $raw['age_seconds'] = max(0, $age);

        return $raw;
    }

    /**
     * Thống kê dung lượng / số dòng thực tế của từng bảng AI (chống phình database).
     *
     * @return array{tables: array<string, int>, total_rows: int, warnings: list<string>}
     */
    public function getStorageMetrics(): array
    {
        $counts = [
            'knowledge_approved' => Schema::hasTable('ai_knowledge_entries') ? AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_APPROVED)->count() : 0,
            'knowledge_candidates' => Schema::hasTable('ai_knowledge_entries') ? AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->count() : 0,
            'knowledge_versions' => Schema::hasTable('ai_knowledge_versions') ? AiKnowledgeVersion::query()->count() : 0,
            'knowledge_relations' => Schema::hasTable('ai_knowledge_relations') ? AiKnowledgeRelation::query()->count() : 0,
            'knowledge_gaps_open' => Schema::hasTable('ai_knowledge_gaps') ? AiKnowledgeGap::query()->where('status', AiKnowledgeGap::STATUS_OPEN)->count() : 0,
            'learning_tasks_queued' => Schema::hasTable('ai_learning_tasks') ? AiLearningTask::query()->where('status', AiLearningTask::STATUS_QUEUED)->count() : 0,
            'learning_tasks_dead_letter' => Schema::hasTable('ai_learning_tasks') ? AiLearningTask::query()->where('status', AiLearningTask::STATUS_DEAD_LETTER)->count() : 0,
            'learning_checkpoints' => Schema::hasTable('ai_learning_checkpoints') ? AiLearningCheckpoint::query()->count() : 0,
            'learning_runs' => Schema::hasTable('ai_learning_runs') ? AiLearningRun::query()->count() : 0,
            'chatbot_logs' => Schema::hasTable('ai_chatbot_logs') ? AiChatbotLog::query()->count() : 0,
            'customer_memories' => Schema::hasTable('ai_customer_memories') ? AiCustomerMemory::query()->count() : 0,
        ];

        $warnings = [];
        $quotas = (array) config('ai_brain.storage_quotas', []);
        if ($counts['knowledge_candidates'] >= (int) ($quotas['max_pending_candidates'] ?? 250)) {
            $warnings[] = 'Hàng đợi candidate chờ duyệt đã chạm ngưỡng tối đa — vui lòng duyệt hoặc dọn dẹp trước khi tạo thêm.';
        }
        if ($counts['knowledge_approved'] >= (int) ($quotas['max_knowledge_rows_warning'] ?? 5000)) {
            $warnings[] = 'Số bản ghi tri thức đã vượt ngưỡng cảnh báo — nên rà soát đánh dấu lỗi thời các mục cũ.';
        }
        if ($counts['chatbot_logs'] >= (int) ($quotas['max_log_rows_warning'] ?? 100000)) {
            $warnings[] = 'Bảng nhật ký chat đang lớn — kiểm tra lịch chạy mayhen:ai-retention-purge.';
        }
        if ($counts['learning_tasks_dead_letter'] > 10) {
            $warnings[] = "Có {$counts['learning_tasks_dead_letter']} task rơi vào dead-letter cần kiểm tra lỗi kết nối/provider.";
        }

        return [
            'tables' => $counts,
            'total_rows' => array_sum($counts),
            'warnings' => $warnings,
        ];
    }
}
