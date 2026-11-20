<?php

declare(strict_types=1);

namespace App\Services\Intelligence\V4;

use App\Models\AiResearchClaim;
use App\Models\AiResearchSource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Multi-Dimensional Knowledge Quality Evaluation Framework & 8 Gates (Gate A -> Gate H) V4.0:
 * - Xóa bỏ hoàn toàn điểm đơn "Độ chính xác 97/100" thiếu giải thích do LLM tự chấm.
 * - Tính 9 chỉ số độc lập (ghi rõ heuristic_uncalibrated nếu chưa qua benchmark độc lập).
 * - Không bao giờ gắn trạng thái `verified` cho khẳng định chỉ có 1 nguồn Wikipedia hoặc 1 trang cộng đồng!
 */
class QualityEvaluationGateService
{
    public function __construct(
        protected OntologyAndClassificationService $ontology
    ) {}

    /**
     * Đánh giá toàn diện một Atomic Claim qua 8 Cổng Chất Lượng (Gate A -> Gate H).
     *
     * @param  array<string, mixed>  $criticAssessment
     * @return array{
     *     passed_mandatory_gates: bool,
     *     verification_status: string,
     *     approval_status: string,
     *     classification: array<string, mixed>,
     *     quality_metrics: array<string, mixed>,
     *     rejection_reason: ?string,
     *     contradiction_notes: ?string
     * }
     */
    public function evaluateAtomicClaim(
        AiResearchClaim $claim,
        ?AiResearchSource $source,
        array $criticAssessment = [],
        string $autonomyLevel = 'supervised'
    ): array {
        $statement = trim((string) ($claim->canonical_statement ?: $claim->claim_text));
        $snippet = trim((string) ($claim->evidence_snippet ?? ''));

        // GATE A: Source Eligibility
        $sourceValid = $source !== null
            && ! $source->is_blocked
            && ! in_array((string) $source->status, [AiResearchSource::STATUS_REJECTED, AiResearchSource::STATUS_ROBOTS_BLOCKED, AiResearchSource::STATUS_DUPLICATE], true)
            && ! empty($source->canonical_url)
            && ! empty($source->content_hash);

        $tierInfo = $source
            ? $this->ontology->classifySourceTier((string) $source->domain, (string) $source->discovery_method)
            : ['tier' => 'missing', 'label' => 'Thiếu nguồn hợp lệ', 'max_reliability' => 0];

        $gateA = $sourceValid;

        // GATE B: Evidence Grounding (chứng minh claim thực sự khớp evidence_snippet, không tự bịa)
        $evidenceOverlap = $this->computeTokenOverlapRatio($statement, $snippet);
        $independentCount = max(1, (int) $claim->independent_sources_count);
        $rawEvidenceSupport = $sourceValid
            ? min(100, (int) round(($evidenceOverlap * 70) + min(30, $independentCount * 15)))
            : 0;
        // Nếu chỉ có 1 nguồn không phải authority_official -> trần evidence_support tối đa 74 (chỉ đạt partially_supported!)
        if ($tierInfo['tier'] !== 'authority_official' && $independentCount < 2) {
            $rawEvidenceSupport = min(74, $rawEvidenceSupport);
        }
        $gateB = $gateA && mb_strlen($snippet, 'UTF-8') >= 15 && $evidenceOverlap >= 0.15;

        // GATE C: Semantic Deduplication (chống tạo nhiều claim/FAQ trùng nghĩa)
        $duplicateHit = $this->findSemanticDuplicate($claim, $statement);
        $novelty = $duplicateHit !== null ? 15 : 85;
        $gateC = $duplicateHit === null;

        // GATE D: Topic & Ontology Classification (tự phân loại đúng domain, knowledge_space & category)
        $classification = $this->ontology->classifyKnowledgeRecord(
            title: $statement,
            body: $snippet,
            sourceUrl: $source?->canonical_url,
            sourceDomain: $source?->domain,
            sourceOrigin: $tierInfo['tier'] === 'authority_official' && str_contains((string) ($source?->domain ?? ''), 'mayhen.vn')
                ? 'internal_official'
                : 'web_research'
        );
        $gateD = true;

        // GATE E: Contradiction Detection (kiểm mâu thuẫn với các claim cùng domain & cùng vùng miền)
        $criticVerdict = strtolower(trim((string) ($criticAssessment['verdict'] ?? '')));
        $contradictionNote = $criticVerdict === 'contradicted'
            ? trim((string) ($criticAssessment['notes'] ?? 'Phát hiện mâu thuẫn giữa các nguồn độc lập.'))
            : $this->detectLocalContradiction($claim, $statement, (string) $classification['ontology_domain'], (string) $classification['applicable_region']);
        $consistency = $contradictionNote !== null ? 30 : 88;
        $gateE = $contradictionNote === null;

        // GATE F: Business Relevance (loại bỏ tri thức sơ đẳng / lạc đề khỏi Mây Hẹn)
        $businessRelevance = (int) $classification['business_relevance'];
        $culturalApplicability = (int) $classification['cultural_applicability'];
        $gateF = ! $classification['is_trivial_or_off_topic'] && $businessRelevance >= 35;

        // GATE G: Actionability & Risk Classification
        $actionability = match ((string) $classification['claim_type']) {
            'process', 'ux_principle', 'policy' => 82,
            'cultural_norm', 'comparison' => 75,
            'product_idea' => 70,
            default => 62,
        };
        $riskClassification = $this->classifyRisk($statement, (string) $classification['knowledge_space']);

        // Source Reliability được giới hạn chặt theo tier (Wikipedia tối đa 65, cộng đồng 58)
        $sourceReliability = $sourceValid
            ? min($tierInfo['max_reliability'], max(35, (int) ($source->trust_score ?? 50)))
            : 0;

        // Quyết định Verification Status trung thực (KHÔNG dùng điểm tổng hợp vượt qua điều kiện bắt buộc!)
        $rejectionReason = null;
        if (! $gateA) {
            $verificationStatus = AiResearchClaim::STATUS_REJECTED;
            $approvalStatus = AiResearchClaim::STATUS_REJECTED;
            $rejectionReason = 'Gate A (Source Eligibility): Nguồn bị chặn, không hợp lệ hoặc thiếu provenance.';
        } elseif (! $gateB) {
            $verificationStatus = AiResearchClaim::STATUS_UNVERIFIED;
            $approvalStatus = AiResearchClaim::STATUS_REJECTED;
            $rejectionReason = 'Gate B (Evidence Grounding): Trích dẫn nguồn không đủ chứng minh phát biểu.';
        } elseif (! $gateF) {
            $verificationStatus = AiResearchClaim::STATUS_REJECTED;
            $approvalStatus = AiResearchClaim::STATUS_REJECTED;
            $rejectionReason = 'Gate F (Business Relevance): '.$classification['classification_note'];
        } elseif (! $gateE) {
            $verificationStatus = AiResearchClaim::STATUS_CONTRADICTED;
            $approvalStatus = AiResearchClaim::STATUS_NEEDS_REVIEW;
        } elseif (! $gateC) {
            $verificationStatus = AiResearchClaim::STATUS_DUPLICATE;
            $approvalStatus = AiResearchClaim::STATUS_DUPLICATE;
            $rejectionReason = 'Gate C (Deduplication): Trùng nghĩa với Atomic Claim #'.$duplicateHit.'.';
        } elseif ($criticVerdict === 'reject') {
            $verificationStatus = AiResearchClaim::STATUS_REJECTED;
            $approvalStatus = AiResearchClaim::STATUS_REJECTED;
            $rejectionReason = 'Critic Agent bác bỏ: '.trim((string) ($criticAssessment['notes'] ?? 'Không đạt chuẩn kiểm định'));
        } else {
            // CHỈ ĐẠT `verified` khi có >= 2 nguồn độc lập khác domain/hash HOẶC nguồn chính thức nội bộ!
            $hasIndependentCorroboration = $independentCount >= 2 || $tierInfo['tier'] === 'authority_official';
            $verificationStatus = $hasIndependentCorroboration
                ? AiResearchClaim::STATUS_VERIFIED
                : AiResearchClaim::STATUS_PARTIALLY_SUPPORTED;

            // GATE H: Publication Policy
            if ($classification['knowledge_space'] === AiResearchClaim::SPACE_OFFICIAL_MAYHEN) {
                $approvalStatus = AiResearchClaim::STATUS_NEEDS_REVIEW;
            } elseif ($classification['knowledge_space'] === AiResearchClaim::SPACE_PRODUCT_INNOVATION) {
                $approvalStatus = AiResearchClaim::STATUS_NEEDS_REVIEW;
            } elseif (
                $autonomyLevel === 'autonomous'
                && $verificationStatus === AiResearchClaim::STATUS_VERIFIED
                && $riskClassification === 'low'
                && $businessRelevance >= 65
            ) {
                $approvalStatus = AiResearchClaim::STATUS_APPROVED_REFERENCE;
            } else {
                $approvalStatus = AiResearchClaim::STATUS_NEEDS_REVIEW;
            }
        }

        $qualityMetrics = [
            'methodology' => 'heuristic_multi_gate_v4_uncalibrated',
            'evidence_support' => $rawEvidenceSupport,
            'source_reliability' => $sourceReliability,
            'source_tier' => $tierInfo['tier'],
            'source_tier_label' => $tierInfo['label'],
            'independent_sources_count' => $independentCount,
            'business_relevance' => $businessRelevance,
            'novelty' => $novelty,
            'cultural_applicability' => $culturalApplicability,
            'consistency' => $consistency,
            'actionability' => $actionability,
            'risk_classification' => $riskClassification,
            'gates' => [
                'A_source_eligibility' => $gateA,
                'B_evidence_grounding' => $gateB,
                'C_deduplication' => $gateC,
                'D_classification' => $gateD,
                'E_consistency' => $gateE,
                'F_business_relevance' => $gateF,
                'H_publication_status' => $approvalStatus,
            ],
        ];

        return [
            'passed_mandatory_gates' => $gateA && $gateB && $gateC && $gateF && $criticVerdict !== 'reject',
            'verification_status' => $verificationStatus,
            'approval_status' => $approvalStatus,
            'classification' => $classification,
            'quality_metrics' => $qualityMetrics,
            'rejection_reason' => $rejectionReason,
            'contradiction_notes' => $contradictionNote,
        ];
    }

    protected function computeTokenOverlapRatio(string $statement, string $snippet): float
    {
        $a = $this->meaningfulTokens($statement);
        $b = $this->meaningfulTokens($snippet);
        if ($a === [] || $b === []) {
            return 0.0;
        }
        $intersect = array_intersect($a, $b);

        return round(count($intersect) / max(1, min(count($a), 12)), 3);
    }

    /**
     * @return list<string>
     */
    protected function meaningfulTokens(string $text): array
    {
        $ascii = Str::ascii(mb_strtolower($text, 'UTF-8'));
        $ascii = preg_replace('/[^a-z0-9\s]/', ' ', $ascii) ?? $ascii;
        $stop = ['la', 'va', 'cua', 'cho', 'voi', 'trong', 'cac', 'nhung', 'mot', 'co', 'duoc', 'the', 'khi', 'tai', 'tu', 'den'];

        return array_values(array_unique(array_filter(
            explode(' ', $ascii),
            fn (string $t) => strlen($t) >= 2 && ! in_array($t, $stop, true)
        )));
    }

    protected function findSemanticDuplicate(AiResearchClaim $current, string $statement): ?int
    {
        $normCurrent = Str::ascii(mb_strtolower($statement, 'UTF-8'));
        $peers = AiResearchClaim::query()
            ->where('id', '!=', $current->id)
            ->whereNotIn('verification_status', [AiResearchClaim::STATUS_REJECTED, AiResearchClaim::STATUS_DUPLICATE])
            ->latest('id')
            ->limit(80)
            ->get(['id', 'claim_text', 'canonical_statement']);

        $currentNeg = $this->hasNegationPolarity($normCurrent);

        foreach ($peers as $peer) {
            $peerNorm = Str::ascii(mb_strtolower((string) ($peer->canonical_statement ?: $peer->claim_text), 'UTF-8'));
            if ($peerNorm === $normCurrent) {
                return (int) $peer->id;
            }
            // Hai câu ngược cực khẳng định/phủ định không phải trùng lặp — để Gate E bắt mâu thuẫn!
            if ($currentNeg !== $this->hasNegationPolarity($peerNorm)) {
                continue;
            }
            similar_text($peerNorm, $normCurrent, $pct);
            if ($pct >= 78.0) {
                return (int) $peer->id;
            }
        }

        return null;
    }

    protected function hasNegationPolarity(string $asciiText): bool
    {
        return str_contains($asciiText, 'khong duoc')
            || str_contains($asciiText, 'cam ')
            || str_contains($asciiText, 'khong co');
    }

    protected function detectLocalContradiction(AiResearchClaim $current, string $statement, string $domain, string $region): ?string
    {
        $norm = Str::ascii(mb_strtolower($statement, 'UTF-8'));
        $isNegated = str_contains($norm, 'khong duoc') || str_contains($norm, 'cam ') || str_contains($norm, 'khong co');
        $isPositive = str_contains($norm, 'bat buoc') || str_contains($norm, 'luon co');
        if (! $isNegated && ! $isPositive) {
            return null;
        }

        $peerQuery = AiResearchClaim::query()
            ->where('id', '!=', $current->id)
            ->where('cultural_region', $region)
            ->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PARTIALLY_SUPPORTED])
            ->limit(30);

        if (Schema::hasColumn('ai_research_claims', 'ontology_domain')) {
            $peerQuery->where('ontology_domain', $domain);
        }

        $peers = $peerQuery->get(['id', 'claim_text', 'canonical_statement']);

        foreach ($peers as $peer) {
            $peerNorm = Str::ascii(mb_strtolower((string) ($peer->canonical_statement ?: $peer->claim_text), 'UTF-8'));
            $overlap = $this->computeTokenOverlapRatio($norm, $peerNorm);
            if ($overlap < 0.45) {
                continue;
            }
            $peerNeg = str_contains($peerNorm, 'khong duoc') || str_contains($peerNorm, 'cam ') || str_contains($peerNorm, 'khong co');
            if ($isNegated !== $peerNeg) {
                return "Nghi ngờ mâu thuẫn với Claim #{$peer->id} trong cùng vùng [{$region}].";
            }
        }

        return null;
    }

    protected function classifyRisk(string $statement, string $knowledgeSpace): string
    {
        $ascii = Str::ascii(mb_strtolower($statement, 'UTF-8'));
        if ($knowledgeSpace === AiResearchClaim::SPACE_OFFICIAL_MAYHEN) {
            return 'high';
        }
        $highMarkers = ['bang gia', 'gia tien', 'gia goi', 'chi phi', 'hoan tien', 'phap ly', 'luat hon nhan', 'cam ket', 'thanh toan', 'hop dong', 'dong gioi', 'dang ky ket hon'];
        foreach ($highMarkers as $m) {
            if (str_contains($ascii, $m)) {
                return 'high';
            }
        }
        if ($knowledgeSpace === AiResearchClaim::SPACE_PRODUCT_INNOVATION) {
            return 'medium';
        }

        return 'low';
    }
}
