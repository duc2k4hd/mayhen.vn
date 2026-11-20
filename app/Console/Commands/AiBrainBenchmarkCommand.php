<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiBenchmarkRun;
use App\Services\Intelligence\V4\KnowledgeBenchmarkService;
use Illuminate\Console\Command;

class AiBrainBenchmarkCommand extends Command
{
    protected $signature = 'ai:brain:benchmark
        {--domain=all : Phạm vi đo kiểm (all | rsvp | culture | pricing)}
        {--phase=after_learning : Pha đánh giá (baseline | after_learning | regression_check)}';

    protected $description = 'Chạy bộ Benchmark độc lập 12 tình huống thực tế (10 RSVP + Văn hóa VN + Bảng giá) để đo tiến bộ Trước/Sau học & phát hiện Regression';

    public function handle(KnowledgeBenchmarkService $benchmarkService): int
    {
        $domain = (string) $this->option('domain') ?: 'all';
        $phase = (string) $this->option('phase') ?: AiBenchmarkRun::PHASE_AFTER_LEARNING;

        if (! in_array($phase, [AiBenchmarkRun::PHASE_BASELINE, AiBenchmarkRun::PHASE_AFTER_LEARNING, AiBenchmarkRun::PHASE_REGRESSION_CHECK], true)) {
            $phase = AiBenchmarkRun::PHASE_AFTER_LEARNING;
        }

        $res = $benchmarkService->runBenchmark(domain: $domain, phase: $phase, triggeredByUserId: null);

        $this->info("=== MÂY HẸN INTELLIGENCE ENGINE V4.0 — KNOWLEDGE BENCHMARK [{$domain} / {$phase}] ===");

        $rows = array_map(fn (array $c) => [
            $c['id'],
            $c['domain'],
            $c['passed'] ? 'PASS' : 'GAP/FAIL',
            mb_strimwidth($c['scenario'], 0, 52, '...'),
            mb_strimwidth($c['detail'], 0, 62, '...'),
        ], $res['cases']);

        $this->table(['Mã', 'Lĩnh vực', 'Kết quả', 'Tình huống holdout', 'Chi tiết'], $rows);

        $deltaText = $res['delta_vs_baseline'] !== null
            ? (($res['delta_vs_baseline'] >= 0 ? '+' : '').$res['delta_vs_baseline'].'%')
            : 'N/A (Baseline đầu tiên)';

        $this->table(
            ['Chỉ số Benchmark', 'Giá trị thực tế'],
            [
                ['Run ID', $res['run_id'] ? '#'.$res['run_id'] : 'N/A'],
                ['Tình huống đạt chuẩn', "{$res['passed_cases']} / {$res['total_cases']}"],
                ['Grounded Score', $res['grounded_score'].'%'],
                ['Coverage Score', $res['coverage_score'].'%'],
                ['Hallucination / Boundary Violations', $res['hallucination_rate'].'%'],
                ['Delta so với Baseline', $deltaText],
                ['Regression Detected', $res['regression_detected'] ? 'CẢNH BÁO: CÓ SUY GIẢM CHẤT LƯỢNG' : 'Không (An toàn)'],
                ['Snapshot Kho Tri Thức', "Official: {$res['knowledge_snapshot']['official_entries']} | Atomic Claims Live: {$res['knowledge_snapshot']['atomic_claims_live']} | Typed Graph: {$res['knowledge_snapshot']['graph_relations']}"],
            ]
        );

        return $res['regression_detected'] ? self::FAILURE : self::SUCCESS;
    }
}
