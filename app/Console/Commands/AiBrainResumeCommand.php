<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AdaptiveResourceController;
use App\Services\Intelligence\AiGovernanceService;
use Illuminate\Console\Command;

class AiBrainResumeCommand extends Command
{
    protected $signature = 'ai:brain:resume';

    protected $description = 'Gỡ trạng thái tạm dừng PAUSE ALL AI LEARNING và đặt lại bộ đếm lỗi';

    public function handle(AiGovernanceService $governance, AdaptiveResourceController $resources): int
    {
        $resources->resetFailures();
        $governance->setLearningPaused(false, null, '');

        $op = $governance->isLearningOperational(false);
        $this->info('Đã gỡ trạng thái tạm dừng AI Learning. Trạng thái hiện tại: '.$op['reason']);

        return self::SUCCESS;
    }
}
