<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AutonomousBrainWorkerService;
use Illuminate\Console\Command;

/**
 * Artisan Autonomous Research & Learning Worker V3 — tương thích Windows, Linux, VPS & cPanel Shared Hosting Cron.
 * - Chế độ Cron hosting: php artisan ai:brain:work --once --budget-seconds=20 --max-tasks=2
 * - Chế độ Local liên tục: php artisan ai:brain:work (dừng an toàn bằng Ctrl+C, không cần PCNTL/Redis/Supervisor).
 * - Ghi Structured Telemetry thật cho từng chu kỳ (Mục XVIII), tuyệt đối không giả lập số liệu hay lộ secret/PII.
 */
class AiBrainWorkCommand extends Command
{
    protected $signature = 'ai:brain:work
        {--once : Chạy đúng 1 chu kỳ hữu hạn rồi thoát ngay (khuyên dùng cho cPanel Cron)}
        {--budget-seconds= : Giới hạn thời gian chạy mỗi chu kỳ (giây)}
        {--max-tasks= : Số tác vụ tối đa mỗi chu kỳ}
        {--sleep=5 : Số giây nghỉ khi ở chế độ liên tục và đang chờ lịch tiếp theo}
        {--max-cycles=0 : Giới hạn số chu kỳ ở chế độ liên tục (0 = vô hạn cho tới Ctrl+C)}';

    protected $description = 'Chạy Mây Hẹn Autonomous Research & Intelligence Engine V3 (local liên tục & cPanel Cron --once)';

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
            $this->printStructuredCycleLog(1, $res);

            return $res['ok'] ? self::SUCCESS : self::FAILURE;
        }

        $this->registerGracefulShutdownHandlers();
        $this->info("Khởi động Mây Hẹn Autonomous Research & Intelligence Worker V3 ({$workerId}). Nhấn Ctrl+C để dừng an toàn.");

        $cycles = 0;
        while (! $this->shouldStop) {
            $cycles++;
            $res = $worker->runSingleCycle($budgetSeconds, $maxTasks, $workerId);
            $this->printStructuredCycleLog($cycles, $res);

            if ($maxCycles > 0 && $cycles >= $maxCycles) {
                break;
            }

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            // Nghỉ từng nhịp 1 giây để phản hồi Ctrl+C tức thì và không tạo vòng lặp ngốn CPU
            $waitSec = in_array($res['status'], ['learning_paused', 'kill_switch', 'learning_disabled', 'mode_paused', 'waiting_for_budget', 'paused_by_resource_policy', 'failure_cooldown', 'idle_until_next_research'], true)
                ? max(12, $sleepSeconds)
                : ($res['tasks_processed'] > 0 ? 2 : $sleepSeconds);

            for ($s = 0; $s < $waitSec && ! $this->shouldStop; $s++) {
                sleep(1);
            }
        }

        $this->info('Mây Hẹn Autonomous Brain Worker V3 đã dừng an toàn.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $res
     */
    protected function printStructuredCycleLog(int $cycleNo, array $res): void
    {
        $ramMb = round(memory_get_usage(true) / 1048576, 1);
        $this->info(sprintf('[%s][Cycle #%d][%s]', now()->format('H:i:s'), $cycleNo, (string) ($res['status'] ?? 'unknown')));

        $telemetry = (array) ($res['telemetry'] ?? []);
        if ($telemetry !== []) {
            foreach ($telemetry as $ev) {
                if (! is_array($ev)) {
                    continue;
                }
                if (! empty($ev['domain']) || ! empty($ev['topic'])) {
                    $this->line(sprintf('  Domain: %s | Topic: %s', (string) ($ev['domain'] ?? '-'), (string) ($ev['topic'] ?? '-')));
                }
                if (isset($ev['queries'])) {
                    $this->line(sprintf(
                        '  Queries: %d | Sources discovered: %d | Eligible: %d | Duplicates: %d | Rejected: %d',
                        (int) $ev['queries'],
                        (int) ($ev['sources_discovered'] ?? 0),
                        (int) ($ev['eligible_sources'] ?? 0),
                        (int) ($ev['duplicates'] ?? 0),
                        (int) ($ev['rejected'] ?? 0)
                    ));
                }
                if (isset($ev['documents_processed'])) {
                    $this->line(sprintf(
                        '  Documents processed: %d | Claims extracted: %d | Rejected: %d | Tokens: %d',
                        (int) $ev['documents_processed'],
                        (int) ($ev['claims_extracted'] ?? 0),
                        (int) ($ev['rejected'] ?? 0),
                        (int) ($ev['tokens_used'] ?? 0)
                    ));
                }
                if (isset($ev['verified'])) {
                    $this->line(sprintf(
                        '  Claims verified: %d | Needs review: %d | Auto-published ref: %d | Rejected: %d | Tokens: %d',
                        (int) $ev['verified'],
                        (int) ($ev['needs_review'] ?? 0),
                        (int) ($ev['auto_published'] ?? 0),
                        (int) ($ev['rejected'] ?? 0),
                        (int) ($ev['tokens_used'] ?? 0)
                    ));
                }
                if (isset($ev['new_related_topics'])) {
                    $this->line(sprintf('  New related topics: %d', (int) $ev['new_related_topics']));
                }
                if (! empty($ev['next_plan'])) {
                    $this->line('  Next plan: '.$ev['next_plan']);
                }
                if (! empty($ev['next_research_at'])) {
                    $this->line('  Next scheduled research: '.$ev['next_research_at']);
                }
            }
        }

        $this->line(sprintf(
            '  Summary: %s (tasks=%d, candidates=%d, discovered=%d, ram=%.1fMB, elapsed=%dms)',
            (string) ($res['message'] ?? ''),
            (int) ($res['tasks_processed'] ?? 0),
            (int) ($res['candidates_created'] ?? 0),
            (int) ($res['tasks_discovered'] ?? 0),
            $ramMb,
            (int) ($res['elapsed_ms'] ?? 0)
        ));
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
