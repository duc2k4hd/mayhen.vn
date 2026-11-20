<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiLearningRun;
use App\Services\Intelligence\MultiAgentLearningService;
use Illuminate\Console\Command;

/**
 * Worker cực nhẹ dành riêng cho Shared Hosting yếu (Azdigi / CloudLinux):
 * Mỗi lần Cron chạy chỉ thực thi ĐÚNG 1 vi-bước (< 10s) rồi thoát ngay, giải phóng 100% RAM/CPU.
 * Giúp phiên huấn luyện dài hàng tiếng vẫn chạy bền bỉ ngay cả khi Admin đã đóng trình duyệt!
 */
class AiSelfTrainStepCommand extends Command
{
    protected $signature = 'mayhen:ai-self-train-step';

    protected $description = 'Thực thi đúng 1 vi-bước (micro-step) cho phiên AI Learning đang chạy nền (tối ưu Shared Hosting yếu)';

    public function handle(MultiAgentLearningService $learningService): int
    {
        $run = $learningService->getActiveChunkedRun();
        if ($run === null || $run->status !== AiLearningRun::STATUS_RUNNING) {
            return self::SUCCESS;
        }

        $result = $learningService->executeNextMicroStep($run);
        $this->line("[Run #{$run->id}] {$result['message']}");

        return self::SUCCESS;
    }
}
