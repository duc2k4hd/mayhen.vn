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

        $this->info('=== MÂY HẸN AUTONOMOUS INTELLIGENCE ENGINE V2 — STATUS ===');
        $this->table(
            ['Hạng mục', 'Giá trị hiện tại'],
            [
                ['Trạng thái học nền', $status['operational']['allowed'] ? 'Sẵn sàng' : 'Đang dừng ('.$status['operational']['reason'].')'],
                ['Chế độ tài nguyên', (string) $status['mode']],
                ['PAUSE ALL AI LEARNING', $status['learning_paused'] ? 'ĐANG TẠM DỪNG ('.$status['learning_pause_reason'].')' : 'Không'],
                ['Ngân sách học nền', $status['budget']['allowed'] ? 'Còn hạn mức' : $status['budget']['reason']],
                ['Worker Heartbeat', ($status['worker']['state'] ?? 'stopped').' — '.($status['worker']['last_message'] ?? '')],
                ['RAM / DB Latency', "{$status['metrics']['memory_mb']} MB / {$status['metrics']['db_latency_ms']} ms"],
                ['Backlog Queue Nghiệp vụ', (string) $status['metrics']['business_queue_backlog']],
                ['Hàng đợi AI Tasks', "Queued: {$status['tasks']['queued']} | Processing: {$status['tasks']['processing']} | Completed: {$status['tasks']['completed']} | Dead-Letter: {$status['tasks']['dead_letter']}"],
                ['Cấp độ mở khóa', 'Level '.$status['coverage']['current_unlocked_level'].' / 4'],
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
