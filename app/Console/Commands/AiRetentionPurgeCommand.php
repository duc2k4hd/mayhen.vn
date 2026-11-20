<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\Intelligence\CustomerMemoryService;
use Illuminate\Console\Command;

class AiRetentionPurgeCommand extends Command
{
    protected $signature = 'mayhen:ai-retention-purge';

    protected $description = 'Tự động dọn dẹp dữ liệu AI Customer Memory và nhật ký hội thoại quá thời hạn Retention theo chính sách bảo mật';

    public function handle(CustomerMemoryService $memoryService): int
    {
        $result = $memoryService->purgeExpired();

        AuditLogger::log(
            'AI_RETENTION_PURGE',
            'AiGovernance',
            0,
            "Dọn dẹp tự động dữ liệu AI quá hạn retention: {$result['memories_deleted']} memory, {$result['logs_deleted']} logs",
            $result
        );

        $this->info("Đã dọn dẹp xong: {$result['memories_deleted']} bộ nhớ khách hàng hết hạn, {$result['logs_deleted']} nhật ký hội thoại quá hạn.");

        return self::SUCCESS;
    }
}
