<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\AiEvaluationService;
use Illuminate\Console\Command;

class AiBrainEvaluateCommand extends Command
{
    protected $signature = 'ai:brain:evaluate';

    protected $description = 'Chạy bộ kiểm thử hồi quy chất lượng & bảo mật độc lập cho Mây Hẹn Intelligence Engine';

    public function handle(AiEvaluationService $evaluationService): int
    {
        $eval = $evaluationService->runRegressionSuite(null);

        $rows = array_map(fn (array $c) => [
            $c['id'],
            $c['passed'] ? 'PASS' : 'FAIL',
            $c['name'],
            $c['detail'],
        ], $eval['cases']);

        $this->table(['Mã', 'Trạng thái', 'Kịch bản kiểm tra', 'Chi tiết'], $rows);
        $this->info("Tổng kết Regression Evaluation: {$eval['passed']}/{$eval['total']} đạt ({$eval['pass_rate']}%).");

        return $eval['failed'] === 0 ? self::SUCCESS : self::FAILURE;
    }
}
