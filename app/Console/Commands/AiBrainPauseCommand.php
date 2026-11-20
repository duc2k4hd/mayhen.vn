<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AiGovernanceService;
use Illuminate\Console\Command;

class AiBrainPauseCommand extends Command
{
    protected $signature = 'ai:brain:pause {--reason=Tạm dừng thủ công qua CLI : Lý do tạm dừng}';

    protected $description = 'Kích hoạt PAUSE ALL AI LEARNING tức thì ở backend (không làm gián đoạn chatbot khách hàng)';

    public function handle(AiGovernanceService $governance): int
    {
        $reason = (string) $this->option('reason');
        $governance->setLearningPaused(true, null, $reason);

        $this->warn('Đã kích hoạt PAUSE ALL AI LEARNING. Toàn bộ worker và cron học nền sẽ ngừng nhận việc mới ngay lập tức.');

        return self::SUCCESS;
    }
}
