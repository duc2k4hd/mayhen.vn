<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AutonomousBrainWorkerService;
use Illuminate\Console\Command;

class AiBrainDoctorCommand extends Command
{
    protected $signature = 'ai:brain:doctor';

    protected $description = 'Kiểm tra sức khỏe cấu hình, CSDL, Cache Lock, Queue, Provider và bảo mật của AI Brain V2';

    public function handle(AutonomousBrainWorkerService $worker): int
    {
        $report = $worker->runDoctor();

        $rows = array_map(fn (array $c) => [
            strtoupper($c['status']),
            $c['name'],
            $c['detail'],
        ], $report['checks']);

        $this->table(['Kết quả', 'Hạng mục kiểm tra', 'Chi tiết thực tế'], $rows);

        if ($report['ok']) {
            $this->info('Doctor PASS: Hệ thống đủ điều kiện vận hành an toàn.');

            return self::SUCCESS;
        }

        $this->error('Doctor phát hiện lỗi nghiêm trọng cần khắc phục trước khi chạy học nền.');

        return self::FAILURE;
    }
}
