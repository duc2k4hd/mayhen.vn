<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AutonomousBrainWorkerService;
use Illuminate\Console\Command;

/**
 * Artisan Background Learning Worker — tương thích Windows, Linux, VPS & cPanel Shared Hosting Cron.
 * - Chế độ Cron hosting: php artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2
 * - Chế độ Local liên tục: php artisan ai:brain:work (dừng an toàn bằng Ctrl+C, không cần PCNTL/Redis/Supervisor).
 */
class AiBrainWorkCommand extends Command
{
    protected $signature = 'ai:brain:work
        {--once : Chạy đúng 1 chu kỳ hữu hạn rồi thoát ngay (khuyên dùng cho cPanel Cron)}
        {--budget-seconds= : Giới hạn thời gian chạy mỗi chu kỳ (giây)}
        {--max-tasks= : Số tác vụ tối đa mỗi chu kỳ}
        {--sleep=5 : Số giây nghỉ khi ở chế độ liên tục và không có việc mới}
        {--max-cycles=0 : Giới hạn số chu kỳ ở chế độ liên tục (0 = vô hạn cho tới Ctrl+C)}';

    protected $description = 'Chạy Mây Hẹn Autonomous Intelligence Engine V2 (hỗ trợ cả local liên tục và cPanel Cron --once)';

    protected bool $shouldStop = false;

    public function handle(AutonomousBrainWorkerService $worker): int
    {
        $once = (bool) $this->option('once');
        $budgetOpt = $this->option('budget-seconds');
        $maxTasksOpt = $this->option('max-tasks');
        $budgetSeconds = $budgetOpt !== null && $budgetOpt !== '' ? max(5, (int) $budgetOpt) : null;
        $maxTasks = $maxTasksOpt !== null && $maxTasksOpt !== '' ? max(1, (int) $maxTasksOpt) : null;
        $sleepSeconds = max(2, min(60, (int) $this->option('sleep')));
        $maxCycles = max(0, (int) $this->option('max-cycles'));

        $workerId = 'cli-'.gethostname().'-'.getmypid();

        if ($once) {
            $res = $worker->runSingleCycle($budgetSeconds, $maxTasks, $workerId);
            $this->line("[{$res['status']}] {$res['message']} (tasks={$res['tasks_processed']}, candidates={$res['candidates_created']}, discovered={$res['tasks_discovered']}, {$res['elapsed_ms']}ms)");

            return $res['ok'] ? self::SUCCESS : self::FAILURE;
        }

        $this->registerGracefulShutdownHandlers();
        $this->info("Khởi động Mây Hẹn Autonomous Brain Worker ({$workerId}). Nhấn Ctrl+C để dừng an toàn.");

        $cycles = 0;
        while (! $this->shouldStop) {
            $cycles++;
            $res = $worker->runSingleCycle($budgetSeconds, $maxTasks, $workerId);

            $this->line(sprintf(
                '[%s][Cycle #%d][%s] %s (tasks=%d, candidates=%d, %dms)',
                now()->format('H:i:s'),
                $cycles,
                $res['status'],
                $res['message'],
                $res['tasks_processed'],
                $res['candidates_created'],
                $res['elapsed_ms']
            ));

            if ($maxCycles > 0 && $cycles >= $maxCycles) {
                break;
            }

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            // Nghỉ từng nhịp 1 giây để phản hồi Ctrl+C tức thì và không ngốn CPU 100%
            $waitSec = in_array($res['status'], ['learning_paused', 'kill_switch', 'learning_disabled', 'mode_paused', 'budget_blocked'], true)
                ? max(10, $sleepSeconds)
                : ($res['tasks_processed'] > 0 ? 2 : $sleepSeconds);

            for ($s = 0; $s < $waitSec && ! $this->shouldStop; $s++) {
                sleep(1);
            }
        }

        $this->info('Mây Hẹn Autonomous Brain Worker đã dừng an toàn.');

        return self::SUCCESS;
    }

    protected function registerGracefulShutdownHandlers(): void
    {
        if (function_exists('sapi_windows_set_ctrl_handler')) {
            @sapi_windows_set_ctrl_handler(function (int $event): void {
                $this->shouldStop = true;
            });
        }

        if (function_exists('pcntl_async_signals') && function_exists('pcntl_signal')) {
            @pcntl_async_signals(true);
            if (defined('SIGINT')) {
                @pcntl_signal(SIGINT, function (): void {
                    $this->shouldStop = true;
                });
            }
            if (defined('SIGTERM')) {
                @pcntl_signal(SIGTERM, function (): void {
                    $this->shouldStop = true;
                });
            }
        }
    }
}
