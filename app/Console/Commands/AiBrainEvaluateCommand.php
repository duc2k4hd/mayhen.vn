<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiBenchmarkRun;
use App\Services\Intelligence\AiEvaluationService;
use App\Services\Intelligence\V4\KnowledgeBenchmarkService;
use Illuminate\Console\Command;

class AiBrainEvaluateCommand extends Command
{
    protected $signature = 'ai:brain:evaluate
        {--with-benchmark : Chạy kèm bộ Knowledge Learning Benchmark 12 tình huống V4.0}';

    protected $description = 'Chạy bộ kiểm thử hồi quy chất lượng, bảo mật & benchmark tri thức độc lập cho Mây Hẹn Intelligence Engine';

    public function handle(AiEvaluationService $evaluationService, KnowledgeBenchmarkService $benchmarkService): int
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

        $hasFailure = $eval['failed'] > 0;

        if ((bool) $this->option('with-benchmark')) {
            $bench = $benchmarkService->runBenchmark('all', AiBenchmarkRun::PHASE_AFTER_LEARNING, null);
            $this->info("Knowledge Benchmark V4: {$bench['passed_cases']}/{$bench['total_cases']} đạt (Grounded={$bench['grounded_score']}%, Hallucination={$bench['hallucination_rate']}%, Regression=".($bench['regression_detected'] ? 'YES' : 'NO').').');
            if ($bench['regression_detected']) {
                $hasFailure = true;
            }
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }
}
