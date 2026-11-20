<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AutonomousBrainWorkerService;
use Illuminate\Console\Command;

class AiBrainStatusCommand extends Command
{
    protected $signature = 'ai:brain:status';

    protected $description = 'Hiển thị trạng thái vận hành, tài nguyên, hàng đợi và độ phủ tri thức (L0-L4) của AI Brain V2';

    public function handle(AutonomousBrainWorkerService $worker): int
    {
        $status = $worker->getStatusSummary();

        $research = (array) ($status['research'] ?? []);
        $v4 = (array) ($status['v4'] ?? []);
        $claims = (array) ($v4['atomic_claims'] ?? []);
        $spaces = (array) ($v4['knowledge_spaces'] ?? []);
        $bench = is_array($v4['latest_benchmark'] ?? null) ? $v4['latest_benchmark'] : null;

        $benchLine = $bench
            ? "Run #{$bench['id']} ({$bench['benchmark_phase']}): {$bench['passed_cases']}/{$bench['total_cases']} đạt • Grounded={$bench['grounded_score']}% • Delta=".($bench['delta_vs_baseline'] !== null ? $bench['delta_vs_baseline'].'%' : 'N/A').' • Regression='.(! empty($bench['regression_detected']) ? 'YES' : 'NO')
            : 'Chưa chạy (dùng php artisan ai:brain:benchmark)';

        $this->info('=== MÂY HẸN AUTONOMOUS INTELLIGENCE ENGINE V4.0 — STATUS ===');
        $this->table(
            ['Hạng mục', 'Giá trị hiện tại'],
            [
                ['Trạng thái học nền', $status['operational']['allowed'] ? 'Sẵn sàng' : 'Đang dừng ('.$status['operational']['reason'].')'],
                ['Chế độ tài nguyên', (string) $status['mode']],
                ['PAUSE ALL AI LEARNING', $status['learning_paused'] ? 'ĐANG TẠM DỪNG ('.$status['learning_pause_reason'].')' : 'Không'],
                ['Ngân sách học nền', $status['budget']['allowed'] ? 'Còn hạn mức' : $status['budget']['reason']],
                ['External Web Research', (! empty($research['external_enabled']) ? 'BẬT' : 'TẮT').' ('.($research['country'] ?? 'VN').'/'.($research['language'] ?? 'vi').' • Tự chủ: '.($research['autonomy_level'] ?? 'supervised').')'],
                ['Mục tiêu & Curriculum', 'Mục tiêu mở: '.($research['goals_active'] ?? 0).' | Chủ đề: '.($research['topics_total'] ?? 0).' (Hoàn thành: '.($research['topics_completed'] ?? 0).')'],
                ['Nguồn Web & Bằng chứng', 'Nguồn phát hiện: '.($research['sources_discovered'] ?? 0).' | Đã trích: '.($research['sources_extracted'] ?? 0).' | Loại: '.($research['sources_rejected'] ?? 0)],
                ['Atomic Claims V4 (8 Gates)', 'Verified: '.($claims['verified'] ?? 0).' | Partial (1 nguồn): '.($claims['partially_supported'] ?? 0).' | Mâu thuẫn: '.($claims['contradicted'] ?? 0).' | Approved Ref: '.($claims['approved_reference'] ?? 0).' | Loại/Trùng: '.(($claims['rejected'] ?? 0) + ($claims['duplicate'] ?? 0))],
                ['4 Không gian Tri thức', 'Chính thức: '.($spaces['official_mayhen'] ?? 0).' | Văn hóa/Ngành: '.($spaces['industry_cultural'] ?? 0).' | Ý tưởng SP: '.($spaces['product_innovation'] ?? 0).' | Khoảng trống KH: '.($spaces['customer_intelligence'] ?? 0)],
                ['Benchmark Học tập V4', $benchLine],
                ['Worker Heartbeat', ($status['worker']['state'] ?? 'stopped').' — '.($status['worker']['last_message'] ?? '')],
                ['RAM / DB Latency', "{$status['metrics']['memory_mb']} MB / {$status['metrics']['db_latency_ms']} ms"],
                ['Backlog Queue Nghiệp vụ', (string) $status['metrics']['business_queue_backlog']],
                ['Hàng đợi AI Tasks', "Queued: {$status['tasks']['queued']} | Processing: {$status['tasks']['processing']} | Completed: {$status['tasks']['completed']} | Dead-Letter: {$status['tasks']['dead_letter']}"],
                ['Cấp độ nội bộ mở khóa', 'Level '.$status['coverage']['current_unlocked_level'].' / 4'],
                ['Checkpoints đã lưu', (string) $status['checkpoints_count']],
            ]
        );

        $levelRows = array_map(fn (array $lvl) => [
            'L'.$lvl['level'],
            $lvl['name'],
            $lvl['approved_count'],
            $lvl['pending_count'],
            $lvl['relations_count'],
            $lvl['open_gaps'],
            $lvl['unlocked'] ? 'Đã mở' : 'Đang khóa',
        ], $status['coverage']['levels']);

        $this->table(['Cấp', 'Lĩnh vực', 'Đã duyệt', 'Chờ duyệt', 'Quan hệ Graph', 'Khoảng trống', 'Trạng thái'], $levelRows);

        if (! empty($status['storage']['warnings'])) {
            foreach ($status['storage']['warnings'] as $warn) {
                $this->warn('Cảnh báo lưu trữ: '.$warn);
            }
        }

        return self::SUCCESS;
    }
}
