<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiKnowledgeEntry;
use App\Models\AiLearningTask;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data Retention & Anti-Bloat Engine:
 * - Dọn dẹp candidate quá hạn, trùng lặp theo content_hash, task hoàn tất cũ và memory/log quá hạn.
 * - TUYỆT ĐỐI KHÔNG xóa tri thức đã duyệt, lịch sử audit hoặc dữ liệu nghiệp vụ production.
 */
class DataRetentionService
{
    public function __construct(
        protected CustomerMemoryService $memoryService
    ) {}

    /**
     * @return array{
     *     memories_deleted: int,
     *     logs_deleted: int,
     *     expired_candidates_deleted: int,
     *     duplicate_candidates_deleted: int,
     *     old_tasks_deleted: int
     * }
     */
    public function runCleanup(?int $triggeredByUserId = null): array
    {
        $base = $this->memoryService->purgeExpired();
        $candidateExpiryDays = max(7, (int) config('ai_brain.storage_quotas.candidate_expiry_days', 30));
        $taskRetentionDays = max(3, (int) config('ai_brain.storage_quotas.completed_task_retention_days', 14));

        $expiredCandidatesDeleted = 0;
        $duplicateCandidatesDeleted = 0;
        $oldTasksDeleted = 0;

        if (Schema::hasTable('ai_knowledge_entries')) {
            $hasExpiresAt = Schema::hasColumn('ai_knowledge_entries', 'expires_at');
            $candidateCutoff = now()->subDays($candidateExpiryDays);

            $expiredCandidatesDeleted = AiKnowledgeEntry::query()
                ->whereIn('status', [AiKnowledgeEntry::STATUS_PENDING, AiKnowledgeEntry::STATUS_REJECTED])
                ->where(function ($q) use ($hasExpiresAt, $candidateCutoff) {
                    $q->where('created_at', '<=', $candidateCutoff);
                    if ($hasExpiresAt) {
                        $q->orWhere(function ($q2) {
                            $q2->whereNotNull('expires_at')->where('expires_at', '<=', now());
                        });
                    }
                })
                ->delete();

            // Dọn các candidate pending trùng hoàn toàn content_hash (giữ bản mới nhất)
            if (Schema::hasColumn('ai_knowledge_entries', 'content_hash')) {
                $dupHashes = AiKnowledgeEntry::query()
                    ->where('status', AiKnowledgeEntry::STATUS_PENDING)
                    ->whereNotNull('content_hash')
                    ->select('content_hash', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
                    ->groupBy('content_hash')
                    ->having('cnt', '>', 1)
                    ->get();

                foreach ($dupHashes as $dup) {
                    $duplicateCandidatesDeleted += AiKnowledgeEntry::query()
                        ->where('status', AiKnowledgeEntry::STATUS_PENDING)
                        ->where('content_hash', $dup->content_hash)
                        ->where('id', '!=', (int) $dup->keep_id)
                        ->delete();
                }
            }
        }

        if (Schema::hasTable('ai_learning_tasks')) {
            $oldTasksDeleted = AiLearningTask::query()
                ->whereIn('status', [AiLearningTask::STATUS_COMPLETED, AiLearningTask::STATUS_SKIPPED])
                ->where('updated_at', '<=', now()->subDays($taskRetentionDays))
                ->delete();
        }

        $result = [
            'memories_deleted' => (int) $base['memories_deleted'],
            'logs_deleted' => (int) $base['logs_deleted'],
            'expired_candidates_deleted' => (int) $expiredCandidatesDeleted,
            'duplicate_candidates_deleted' => (int) $duplicateCandidatesDeleted,
            'old_tasks_deleted' => (int) $oldTasksDeleted,
        ];

        AuditLogger::log(
            'AI_DATA_RETENTION_CLEANUP',
            'AiGovernance',
            $triggeredByUserId ?? 0,
            'Dọn dẹp định kỳ dữ liệu AI chống phình database',
            $result
        );

        return $result;
    }
}
