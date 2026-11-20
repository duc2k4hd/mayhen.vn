<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\DataRetentionService;
use Illuminate\Console\Command;

class AiRetentionPurgeCommand extends Command
{
    protected $signature = 'mayhen:ai-retention-purge';

    protected $description = 'Tự động dọn dẹp dữ liệu AI quá hạn Retention (memory, logs, candidate hết hạn/trùng lặp, tasks cũ)';

    public function handle(DataRetentionService $retentionService): int
    {
        $result = $retentionService->runCleanup(null);

        $this->info(sprintf(
            'Đã dọn dẹp xong: %d memory, %d chat logs, %d candidate hết hạn, %d candidate trùng lặp, %d tasks cũ.',
            $result['memories_deleted'],
            $result['logs_deleted'],
            $result['expired_candidates_deleted'],
            $result['duplicate_candidates_deleted'],
            $result['old_tasks_deleted']
        ));

        return self::SUCCESS;
    }
}
