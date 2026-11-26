<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Admin\ToolsGovernanceService;
use Illuminate\Console\Command;

class PruneExpiredToolRecordsCommand extends Command
{
    protected $signature = 'mayhen:prune-expired-tools
                            {--months=6 : Số tháng lưu trữ tối đa cho link & dữ liệu chi tiết công cụ}
                            {--tool= : Chỉ chạy cho 1 tool_key cụ thể (tùy chọn)}
                            {--dry-run : Chạy thử kiểm đếm, không xóa dữ liệu thật}';

    protected $description = 'Lưu bản tóm tắt phân tích và dọn dẹp dữ liệu/link công cụ quá hạn 6 tháng để tối ưu lưu trữ và tuân thủ bảo mật dữ liệu';

    public function handle(ToolsGovernanceService $service): int
    {
        $months = max(1, (int) $this->option('months'));
        $dryRun = (bool) $this->option('dry-run');
        $tool = $this->option('tool') ? (string) $this->option('tool') : null;

        $result = $service->archiveAndPruneExpired($months, $dryRun, $tool);

        $this->info(($dryRun ? '[DRY-RUN] ' : '')."Chính sách lưu trữ {$result['retention_months']} tháng (mốc trước {$result['cutoff']}):");
        $this->line("- Tổng bản ghi quá hạn phát hiện: {$result['total_candidates']}");
        $this->line("- Tổng bản ghi đã lưu phân tích & dọn dẹp: {$result['total_archived_and_pruned']}");

        foreach ($result['per_tool'] as $key => $row) {
            if ($row['candidates'] > 0) {
                $this->line("  • {$row['label']} ({$key}): {$row['candidates']} quá hạn, {$row['pruned']} đã xử lý");
            }
        }

        return self::SUCCESS;
    }
}
