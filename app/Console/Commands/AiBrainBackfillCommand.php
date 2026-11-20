<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\IncrementalDiscoveryService;
use Illuminate\Console\Command;

class AiBrainBackfillCommand extends Command
{
    protected $signature = 'ai:brain:backfill
        {--dry-run : Chỉ xem trước kế hoạch thu thập nội bộ, TUYỆT ĐỐI KHÔNG ghi thay đổi vào DB}
        {--level= : Chỉ quét một cấp độ domain cụ thể (0-4)}';

    protected $description = 'Quét incremental nguồn nội bộ hợp lệ (hỗ trợ --dry-run xem trước không ghi DB)';

    public function handle(IncrementalDiscoveryService $discovery): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $levelOpt = $this->option('level');
        $onlyLevel = $levelOpt !== null && $levelOpt !== '' ? max(0, min(4, (int) $levelOpt)) : null;

        $plan = $discovery->discover(dryRun: $dryRun, onlyLevel: $onlyLevel);

        $this->info($dryRun
            ? '=== BACKFILL PLAN (DRY-RUN — KHÔNG GHI DỮ LIỆU) ==='
            : '=== BACKFILL INCREMENTAL DISCOVERY COMPLETED ===');

        $rows = array_map(fn (array $s) => [
            'L'.$s['level'],
            $s['source_key'],
            $s['records'],
            $s['changed'] ? 'Có thay đổi' : 'Không đổi',
            $s['action'],
        ], $plan['sources_checked']);

        $this->table(['Cấp', 'Nguồn nội bộ', 'Số bản ghi quét', 'Thay đổi', 'Hành động dự kiến'], $rows);
        $this->line("Cấp độ mở khóa hiện tại: Level {$plan['unlocked_level']} | Tác vụ dự kiến: {$plan['tasks_planned']} | Đã đưa vào hàng đợi: {$plan['tasks_enqueued']}");

        return self::SUCCESS;
    }
}
