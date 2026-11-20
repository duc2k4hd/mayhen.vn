<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\V4\LegacyKnowledgeAuditService;
use Illuminate\Console\Command;

class AiBrainAuditLegacyCommand extends Command
{
    protected $signature = 'ai:brain:audit-legacy
        {--apply : Áp dụng chuẩn hóa thật vào database (mặc định chỉ chạy dry-run xem trước không ghi DB)}';

    protected $description = 'Kiểm toán & chuyển đổi an toàn tri thức cũ sang Ontology, 4 Không gian Tri thức & Điểm trung thực V4.0 (không xóa dữ liệu, có lưu phiên bản Rollback)';

    public function handle(LegacyKnowledgeAuditService $auditService): int
    {
        $dryRun = ! (bool) $this->option('apply');
        $result = $auditService->auditAndMigrate(dryRun: $dryRun, adminUserId: null);

        $modeLabel = $dryRun ? 'DRY-RUN (CHỈ XEM TRƯỚC, CHƯA GHI DB)' : 'APPLY (ĐÃ ÁP DỤNG + LƯU VERSION ROLLBACK)';
        $this->info("=== MÂY HẸN INTELLIGENCE ENGINE V4.0 — LEGACY KNOWLEDGE AUDIT [{$modeLabel}] ===");
        $this->line("Đã quét: {$result['scanned']} bản ghi | Cần chuẩn hóa: {$result['changes_planned']} | Đã áp dụng: {$result['changes_applied']}");

        if ($result['plan'] !== []) {
            $rows = array_map(fn (array $p) => [
                '#'.$p['id'],
                mb_strimwidth($p['title'], 0, 44, '...'),
                "{$p['old_category']} -> {$p['new_category']}",
                "{$p['old_space']} -> {$p['new_space']}",
                $p['action'],
            ], $result['plan']);

            $this->table(['ID', 'Tiêu đề', 'Danh mục', 'Không gian', 'Hành động'], $rows);
        } else {
            $this->info('Tất cả bản ghi tri thức hiện đã đạt chuẩn Ontology & 4 Không gian V4.0.');
        }

        if ($dryRun && $result['changes_planned'] > 0) {
            $this->comment('Để áp dụng thay đổi an toàn (có bản chụp Rollback), chạy: php artisan ai:brain:audit-legacy --apply');
        }

        return self::SUCCESS;
    }
}
