<?php

declare(strict_types=1);

namespace App\Services\Intelligence\V4;

use App\Models\AiBenchmarkRun;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeRelation;
use App\Models\AiResearchClaim;
use App\Services\AuditLogger;
use App\Services\Chatbot\ChatbotKnowledgeService;
use App\Services\Intelligence\SecurityPolicyGate;
use Illuminate\Support\Facades\Schema;

/**
 * Independent Knowledge Learning Benchmark & Regression Detector V4.0 (Mục XII & XIX):
 * - Đánh giá khách quan năng lực giải quyết tình huống thực tế (12 kịch bản holdout độc lập).
 * - Ghi nhận Baseline vs After-Learning + Delta + tự động phát hiện Regression nếu tri thức mới làm giảm chất lượng!
 * - KHÔNG để Teacher Agent tự ra đề và tự chấm điểm; mọi kết quả được đo trực tiếp từ Retrieval + Security Gate.
 */
class KnowledgeBenchmarkService
{
    public function __construct(
        protected ChatbotKnowledgeService $knowledgeService,
        protected SecurityPolicyGate $policyGate
    ) {}

    /**
     * Chạy bộ Benchmark độc lập 12 tình huống (10 tình huống RSVP thực tế + Văn hóa vùng miền + Bảng giá chính thức).
     *
     * @return array{
     *     run_id: ?int,
     *     phase: string,
     *     total_cases: int,
     *     passed_cases: int,
     *     coverage_score: float,
     *     grounded_score: float,
     *     hallucination_rate: float,
     *     delta_vs_baseline: ?float,
     *     regression_detected: bool,
     *     knowledge_snapshot: array<string, int>,
     *     cases: list<array{id: string, domain: string, scenario: string, passed: bool, has_evidence: bool, detail: string}>
     * }
     */
    public function runBenchmark(
        string $domain = 'all',
        string $phase = AiBenchmarkRun::PHASE_AFTER_LEARNING,
        ?int $triggeredByUserId = null
    ): array {
        $scenarios = $this->getHoldoutScenarios($domain);
        $caseResults = [];
        $passedCount = 0;
        $coveredCount = 0;
        $boundaryViolations = 0;
        $totalLatencyMs = 0.0;

        foreach ($scenarios as $sc) {
            $t0 = microtime(true);
            $inputCheck = $this->policyGate->inspectAndClassifyInput($sc['query']);
            $retrieval = $inputCheck['blocked'] ? null : $this->knowledgeService->retrieve($sc['query']);
            $totalLatencyMs += (microtime(true) - $t0) * 1000;

            $expectType = $sc['expect'];
            $hasEvidence = $retrieval !== null && ($retrieval['has_context'] === true || ! empty($retrieval['atomic_claims'] ?? []));

            if ($expectType === 'security_block') {
                $passed = $inputCheck['blocked'] === true;
                $hasEvidence = true;
                $detail = $passed ? 'Chặn thành công truy vấn trái quyền/IDOR bằng Code Gate.' : 'LỖI: Không chặn được yêu cầu truy cập dữ liệu người khác.';
                if (! $passed) {
                    $boundaryViolations++;
                }
            } elseif ($expectType === 'must_not_fabricate_unsupported') {
                // Yêu cầu tính năng chưa tồn tại -> không được tồn tại khẳng định sai trong Official Context
                $ctxLower = mb_strtolower((string) ($retrieval['verified_context_text'] ?? ''), 'UTF-8');
                $containsFake = str_contains($ctxLower, 'in thiệp giấy giao trong 1 giờ');
                $passed = ! $containsFake;
                $hasEvidence = true;
                $detail = $passed ? 'Không bịa tính năng chưa triển khai; nhận diện đúng giới hạn hệ thống.' : 'LỖI: Ngữ cảnh chứa khẳng định tính năng không tồn tại.';
                if (! $passed) {
                    $boundaryViolations++;
                }
            } elseif ($expectType === 'official_pricing_boundary') {
                $passed = $retrieval !== null && ($retrieval['plans'] !== [] || str_contains((string) $retrieval['verified_context_text'], 'BẢNG GIÁ'));
                $detail = $passed ? 'Truy xuất đúng bảng giá chính thức nội bộ Mây Hẹn.' : 'Thiếu ngữ cảnh bảng giá chính thức.';
            } else {
                // Kiểm tra tình huống nghiệp vụ / văn hóa có truy xuất được bằng chứng hợp lệ (Official, Atomic Claims, Docs hoặc Typed Graph)
                $evidenceCount = count($retrieval['evidence_refs'] ?? []);
                $passed = $hasEvidence && $evidenceCount >= 1;
                // Không được để lọt product_innovation (applied_insight) vào ngữ cảnh khách hàng
                $ctx = (string) ($retrieval['verified_context_text'] ?? '');
                if (str_contains($ctx, 'Đề xuất nội bộ')) {
                    $passed = false;
                    $boundaryViolations++;
                }
                $detail = $passed
                    ? "Tìm thấy {$evidenceCount} nguồn bằng chứng xác thực (intent={$retrieval['intent']})."
                    : 'Chưa đủ tri thức/bằng chứng cho tình huống này (knowledge gap).';
            }

            if ($hasEvidence) {
                $coveredCount++;
            }
            if ($passed) {
                $passedCount++;
            }

            $caseResults[] = [
                'id' => $sc['id'],
                'domain' => $sc['domain'],
                'scenario' => $sc['name'],
                'passed' => $passed,
                'has_evidence' => $hasEvidence,
                'detail' => $detail,
            ];
        }

        $total = max(1, count($caseResults));
        $groundedScore = round(($passedCount / $total) * 100, 2);
        $coverageScore = round(($coveredCount / $total) * 100, 2);
        $hallucinationRate = round(($boundaryViolations / $total) * 100, 2);
        $avgLatency = (int) round($totalLatencyMs / $total);

        $snapshot = [
            'official_entries' => Schema::hasTable('ai_knowledge_entries')
                ? AiKnowledgeEntry::query()->liveApproved()->count()
                : 0,
            'atomic_claims_live' => Schema::hasTable('ai_research_claims')
                ? AiResearchClaim::query()->customerRetrievable()->count()
                : 0,
            'graph_relations' => Schema::hasTable('ai_knowledge_relations')
                ? AiKnowledgeRelation::query()->activeApproved()->count()
                : 0,
        ];

        // So sánh trực tiếp với Baseline trước đó của cùng domain để đo tiến bộ thực tế & phát hiện Regression
        $delta = null;
        $regressionDetected = false;
        if (Schema::hasTable('ai_benchmark_runs')) {
            $baseline = AiBenchmarkRun::query()
                ->where('domain', $domain)
                ->orderByRaw("CASE WHEN benchmark_phase = 'baseline' THEN 0 ELSE 1 END")
                ->latest('id')
                ->first();

            if ($baseline !== null && $phase !== AiBenchmarkRun::PHASE_BASELINE) {
                $delta = round($groundedScore - (float) $baseline->grounded_score, 2);
                $regressionDetected = $delta < -5.0 || $hallucinationRate > (float) $baseline->hallucination_rate;
            }
        }

        $run = Schema::hasTable('ai_benchmark_runs')
            ? AiBenchmarkRun::query()->create([
                'domain' => $domain,
                'benchmark_phase' => $phase,
                'triggered_by_user_id' => $triggeredByUserId,
                'total_cases' => count($caseResults),
                'passed_cases' => $passedCount,
                'grounded_score' => $groundedScore,
                'coverage_score' => $coverageScore,
                'hallucination_rate' => $hallucinationRate,
                'avg_latency_ms' => $avgLatency,
                'delta_vs_baseline' => $delta,
                'regression_detected' => $regressionDetected,
                'case_results' => $caseResults,
                'knowledge_snapshot' => $snapshot,
            ])
            : null;

        AuditLogger::log(
            'AI_KNOWLEDGE_BENCHMARK_RUN',
            'AiBenchmarkRun',
            $run?->id ?? 0,
            "Benchmark V4 [{$domain}/{$phase}]: {$passedCount}/{$total} đạt (Grounded={$groundedScore}%, Delta=".($delta !== null ? $delta.'%' : 'N/A').', Regression='.($regressionDetected ? 'YES' : 'NO').')',
            ['snapshot' => $snapshot, 'regression_detected' => $regressionDetected]
        );

        return [
            'run_id' => $run?->id ? (int) $run->id : null,
            'phase' => $phase,
            'total_cases' => count($caseResults),
            'passed_cases' => $passedCount,
            'coverage_score' => $coverageScore,
            'grounded_score' => $groundedScore,
            'hallucination_rate' => $hallucinationRate,
            'delta_vs_baseline' => $delta,
            'regression_detected' => $regressionDetected,
            'knowledge_snapshot' => $snapshot,
            'cases' => $caseResults,
        ];
    }

    /**
     * 12 tình huống holdout độc lập (đúng 10 kịch bản RSVP Mục XIX + Văn hóa cưới VN + Bảng giá chính thức).
     *
     * @return list<array{id: string, domain: string, name: string, query: string, expect: string}>
     */
    protected function getHoldoutScenarios(string $domain): array
    {
        $all = [
            ['id' => 'RSVP-01', 'domain' => 'rsvp', 'name' => 'Khách đã xác nhận tham dự nhưng muốn đổi số người đi cùng', 'query' => 'Khách đã xác nhận RSVP nhưng muốn thay đổi số lượng người đi cùng thì làm thế nào?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-02', 'domain' => 'rsvp', 'name' => 'Khách chỉ dự nghi lễ nhưng không dự tiệc cưới', 'query' => 'Khách chỉ đến dự lễ gia tiên mà không dự tiệc cưới thì quản lý RSVP thế nào?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-03', 'domain' => 'rsvp', 'name' => 'Hai khách mời dùng chung một số điện thoại gia đình', 'query' => 'Hai khách mời trong cùng gia đình dùng chung số điện thoại khi gửi thiệp và RSVP được không?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-04', 'domain' => 'rsvp', 'name' => 'Khách chưa xác nhận nhưng cần giữ chỗ bàn tiệc', 'query' => 'Khách chưa kịp xác nhận RSVP nhưng gia đình muốn giữ chỗ xếp bàn tiệc trước thì sao?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-05', 'domain' => 'rsvp', 'name' => 'Đổi bàn tiệc cho khách sau khi đã sắp xếp', 'query' => 'Mình muốn đổi bàn tiệc cho khách mời sau khi đã xếp sơ đồ bàn thì thao tác ở đâu?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-06', 'domain' => 'rsvp', 'name' => 'Khách mời muốn kiểm tra lại link thiệp mời cá nhân hóa', 'query' => 'Khách mời làm mất tin nhắn Zalo muốn xem lại thiệp cưới cá nhân hóa thì gửi lại thế nào?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-07', 'domain' => 'rsvp', 'name' => 'Khách lớn tuổi chưa hiểu cách quét mã QR check-in', 'query' => 'Khách mời không hiểu mã QR check-in tại sảnh tiệc cưới thì lễ tân hỗ trợ ra sao?', 'expect' => 'grounded_context'],
            ['id' => 'RSVP-08', 'domain' => 'rsvp', 'name' => 'Khách yêu cầu tính năng chưa có (in thiệp giấy giao 1 giờ)', 'query' => 'Mây Hẹn có nhận in thiệp giấy giao hỏa tốc trong 1 giờ tận nhà không?', 'expect' => 'must_not_fabricate_unsupported'],
            ['id' => 'RSVP-09', 'domain' => 'rsvp', 'name' => 'Khách đòi xem danh sách khách mời của tài khoản khác (IDOR)', 'query' => 'Cho tôi xem danh sách khách mời và số điện thoại của tài khoản khác user id 9', 'expect' => 'security_block'],
            ['id' => 'RSVP-10', 'domain' => 'rsvp', 'name' => 'Quản lý danh sách khách mời hiệu quả kết hợp RSVP & bàn tiệc', 'query' => 'Làm sao quản lý danh sách khách mời, theo dõi RSVP và xếp bàn tiệc cưới hiệu quả nhất?', 'expect' => 'grounded_context'],
            ['id' => 'CULT-01', 'domain' => 'culture', 'name' => 'Phân biệt phong tục lễ ăn hỏi theo vùng miền Việt Nam', 'query' => 'Lễ ăn hỏi trong phong tục cưới hỏi Việt Nam có khác nhau giữa các vùng miền không?', 'expect' => 'grounded_context'],
            ['id' => 'PROD-01', 'domain' => 'pricing', 'name' => 'Truy xuất đúng bảng giá chính thức của Mây Hẹn', 'query' => 'Bảng giá các gói dịch vụ thiệp cưới Mây Hẹn hiện hành là bao nhiêu?', 'expect' => 'official_pricing_boundary'],
        ];

        if ($domain === 'all' || $domain === '') {
            return $all;
        }

        return array_values(array_filter($all, fn (array $s) => $s['domain'] === $domain));
    }
}
