<?php

declare(strict_types=1);

namespace App\Services\Personality;

/**
 * Scoring Engine Xác Định 100% (Deterministic) cho 16 Nhóm Tính Cách & Big Five (IPIP-50).
 *
 * - Tuyệt đối KHÔNG gọi AI trong quá trình chấm điểm.
 * - Cùng bộ câu hỏi + câu trả lời + phiên bản thuật toán luôn trả về cùng 1 kết quả và 1 calculation_hash.
 * - Phân loại dựa trên float chưa làm tròn (`> 50.0` dương, `< 50.0` âm, `=== 50.0` chưa nghiêng rõ / tie).
 * - Không bao giờ tự gắn biến thể -A/-T hay Percentile giả.
 */
class PersonalityScoringEngine
{
    public function __construct(
        protected PersonalityMethodRegistry $methods,
        protected PersonalityQuestionBankService $questionBank,
        protected PersonalityTypeRegistry $typeRegistry
    ) {}

    /**
     * Chấm điểm 1 phiên làm bài (tự động chọn thuật toán 16 nhóm hoặc Big Five theo method_id).
     *
     * @param  array<string, int|string|null>  $rawResponses  Map question_id => 1..7 (hoặc 1..5) hoặc 'unsure'/null
     * @return array<string, mixed>
     */
    public function score(string $methodId, array $rawResponses, int $durationSeconds = 0): array
    {
        $method = $this->methods->get($methodId);
        if ($method['model_code'] === 'big_five') {
            return $this->scoreBigFive($method, $rawResponses, $durationSeconds);
        }

        return $this->scoreSixteenTypes($method, $rawResponses, $durationSeconds);
    }

    /**
     * @param  array<string, mixed>  $method
     * @param  array<string, int|string|null>  $rawResponses
     * @return array<string, mixed>
     */
    protected function scoreSixteenTypes(array $method, array $rawResponses, int $durationSeconds): array
    {
        $questions = $this->questionBank->getQuestionsForMethod($method['method_id']);
        $dimsMeta = $this->typeRegistry->dimensions16();
        $scaleMin = (int) $method['scale_min'];
        $scaleMax = (int) $method['scale_max'];
        $reverseBase = $scaleMin + $scaleMax; // 8 cho Likert 1..7
        $scaleSpan = (float) ($scaleMax - $scaleMin); // 6.0
        $minValidRatio = (float) $method['min_valid_ratio_per_dimension'];
        [$boundaryLow, $boundaryHigh] = $method['boundary_band'];

        $buckets = ['EI' => [], 'SN' => [], 'TF' => [], 'JP' => []];
        $evidenceByDim = ['EI' => [], 'SN' => [], 'TF' => [], 'JP' => []];
        $validNumericRawValues = [];
        $unsureCount = 0;
        $skippedCount = 0;
        $invalidCount = 0;
        $normalizedResponses = [];

        foreach ($questions as $q) {
            $qid = $q['id'];
            $dim = $q['dimension'];
            $val = $rawResponses[$qid] ?? null;

            if ($val === null || $val === '') {
                $skippedCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }
            if ($val === 'unsure') {
                $unsureCount++;
                $normalizedResponses[$qid] = 'unsure';

                continue;
            }
            if (! is_numeric($val)) {
                $invalidCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }

            $intVal = (int) $val;
            if ((float) $val !== (float) $intVal || $intVal < $scaleMin || $intVal > $scaleMax) {
                $invalidCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }

            $normalizedResponses[$qid] = $intVal;
            $validNumericRawValues[] = $intVal;
            $adjusted = $q['direction'] === 1 ? $intVal : ($reverseBase - $intVal);
            $buckets[$dim][] = $adjusted;

            $distanceFromNeutral = abs($adjusted - (($scaleMin + $scaleMax) / 2.0));
            $evidenceByDim[$dim][] = [
                'question_id' => $qid,
                'text' => $q['text'],
                'context' => $q['context'],
                'direction' => $q['direction'],
                'raw_answer' => $intVal,
                'adjusted_score' => $adjusted,
                'signal_strength' => $distanceFromNeutral,
            ];
        }

        $dimensionScores = [];
        $boundaryFlags = [];
        $insufficientDimensions = [];
        $letterOptionsPerDim = [];
        $hasTie = false;

        foreach (['EI', 'SN', 'TF', 'JP'] as $dim) {
            $meta = $dimsMeta[$dim];
            $totalDimQuestions = (int) $method['questions_per_dimension'];
            $validCount = count($buckets[$dim]);
            $validRatio = $totalDimQuestions > 0 ? ($validCount / $totalDimQuestions) : 0.0;

            if ($validCount === 0 || $validRatio < $minValidRatio) {
                $insufficientDimensions[] = $dim;
                $letterOptionsPerDim[$dim] = [$meta['positive_code'], $meta['negative_code']];
                $dimensionScores[$dim] = [
                    'dimension' => $dim,
                    'name' => $meta['name'],
                    'positive_code' => $meta['positive_code'],
                    'positive_name' => $meta['positive_name'],
                    'negative_code' => $meta['negative_code'],
                    'negative_name' => $meta['negative_name'],
                    'valid_count' => $validCount,
                    'total_questions' => $totalDimQuestions,
                    'raw_sum' => 0,
                    'mean' => null,
                    'positive_index_exact' => null,
                    'positive_index' => null,
                    'opposite_index' => null,
                    'tendency' => 'insufficient_data',
                    'is_tie' => false,
                    'is_boundary' => true,
                    'tendency_label' => 'Chưa đủ câu trả lời hợp lệ để đánh giá chiều này',
                    'evidence' => [],
                ];
                $boundaryFlags[$dim] = 'insufficient_data';

                continue;
            }

            $rawSum = array_sum($buckets[$dim]);
            $mean = $rawSum / $validCount;
            $positiveExact = (($mean - $scaleMin) / $scaleSpan) * 100.0;
            $oppositeExact = 100.0 - $positiveExact;

            // So sánh bằng epsilon cực nhỏ để tránh sai số dấu phẩy động khi điểm chính xác bằng 50.0
            $isTie = abs($positiveExact - 50.0) < 1e-9;
            if ($isTie) {
                $tendency = 'undecided';
                $hasTie = true;
                $letterOptionsPerDim[$dim] = [$meta['positive_code'], $meta['negative_code']];
            } elseif ($positiveExact > 50.0) {
                $tendency = $meta['positive_code'];
                $letterOptionsPerDim[$dim] = [$meta['positive_code']];
            } else {
                $tendency = $meta['negative_code'];
                $letterOptionsPerDim[$dim] = [$meta['negative_code']];
            }

            $isBoundary = $isTie || ($positiveExact >= $boundaryLow && $positiveExact <= $boundaryHigh);
            if ($isTie) {
                $boundaryFlags[$dim] = 'exact_tie';
            } elseif ($isBoundary) {
                $boundaryFlags[$dim] = 'near_boundary';
            }

            // Sắp xếp dẫn chứng theo mức độ thể hiện rõ nhất (không bịa căn cứ)
            $evList = $evidenceByDim[$dim];
            usort($evList, fn (array $a, array $b): int => $b['signal_strength'] <=> $a['signal_strength']);

            $dimensionScores[$dim] = [
                'dimension' => $dim,
                'name' => $meta['name'],
                'positive_code' => $meta['positive_code'],
                'positive_name' => $meta['positive_name'],
                'negative_code' => $meta['negative_code'],
                'negative_name' => $meta['negative_name'],
                'valid_count' => $validCount,
                'total_questions' => $totalDimQuestions,
                'raw_sum' => $rawSum,
                'mean' => round($mean, 4),
                'positive_index_exact' => $positiveExact,
                'positive_index' => round($positiveExact, 1),
                'opposite_index' => round($oppositeExact, 1),
                'tendency' => $tendency,
                'is_tie' => $isTie,
                'is_boundary' => $isBoundary,
                'tendency_label' => $this->buildTendencyLabel($tendency, $positiveExact, $meta),
                'evidence' => array_slice($evList, 0, 5),
            ];
        }

        // Sinh danh sách các nhóm khả dĩ (khi có điểm hòa hoặc thiếu dữ liệu)
        $possibleTypes = $this->cartesianTypeCodes($letterOptionsPerDim);
        $hasInsufficient = $insufficientDimensions !== [];

        $suggestedType = (! $hasInsufficient && ! $hasTie && count($possibleTypes) === 1)
            ? $possibleTypes[0]
            : 'UNDECIDED';

        // Sinh danh sách nhóm gần kề cần đối chiếu Best-Fit dựa trên các chiều rơi vào vùng ranh giới 45–55
        $alternativeTypes = [];
        if ($suggestedType !== 'UNDECIDED') {
            foreach (['EI', 'SN', 'TF', 'JP'] as $idx => $dim) {
                if (! empty($dimensionScores[$dim]['is_boundary'])) {
                    $meta = $dimsMeta[$dim];
                    $altLetter = $suggestedType[$idx] === $meta['positive_code']
                        ? $meta['negative_code']
                        : $meta['positive_code'];
                    $altCode = substr_replace($suggestedType, $altLetter, $idx, 1);
                    if (! in_array($altCode, $alternativeTypes, true)) {
                        $alternativeTypes[] = $altCode;
                    }
                }
            }
        } else {
            $alternativeTypes = $possibleTypes;
        }

        $qualityFlags = $this->evaluateResponseQuality(
            totalQuestions: count($questions),
            validNumericValues: $validNumericRawValues,
            unsureCount: $unsureCount,
            skippedCount: $skippedCount,
            invalidCount: $invalidCount,
            durationSeconds: $durationSeconds,
            insufficientDimensions: $insufficientDimensions
        );

        $hashPayload = [
            'method_id' => $method['method_id'],
            'method_version' => $method['version'],
            'scoring_version' => $method['scoring_version'],
            'responses' => $normalizedResponses,
            'suggested_type' => $suggestedType,
            'means' => array_map(fn (array $d) => $d['mean'], $dimensionScores),
        ];

        return [
            'model_code' => 'sixteen_types',
            'method_id' => $method['method_id'],
            'method_version' => $method['version'],
            'scoring_version' => $method['scoring_version'],
            'suggested_type' => $suggestedType,
            'possible_types' => $possibleTypes,
            'alternative_types' => $alternativeTypes,
            'has_tie' => $hasTie,
            'has_insufficient_data' => $hasInsufficient,
            'dimensions' => $dimensionScores,
            'boundary_flags' => $boundaryFlags,
            'quality_flags' => $qualityFlags,
            'answered_count' => count($validNumericRawValues),
            'unsure_count' => $unsureCount,
            'skipped_count' => $skippedCount,
            'calculation_hash' => hash('sha256', (string) json_encode($hashPayload, JSON_UNESCAPED_UNICODE)),
        ];
    }

    /**
     * @param  array<string, mixed>  $method
     * @param  array<string, int|string|null>  $rawResponses
     * @return array<string, mixed>
     */
    protected function scoreBigFive(array $method, array $rawResponses, int $durationSeconds): array
    {
        $questions = $this->questionBank->getQuestionsForMethod($method['method_id']);
        $dimsMeta = $this->typeRegistry->dimensionsBigFive();
        $scaleMin = (int) $method['scale_min'];
        $scaleMax = (int) $method['scale_max'];
        $reverseBase = $scaleMin + $scaleMax; // 6 cho Likert 1..5
        $scaleSpan = (float) ($scaleMax - $scaleMin); // 4.0
        $minValidRatio = (float) $method['min_valid_ratio_per_dimension'];

        $buckets = ['O' => [], 'C' => [], 'E' => [], 'A' => [], 'N' => []];
        $evidenceByDim = ['O' => [], 'C' => [], 'E' => [], 'A' => [], 'N' => []];
        $validNumericRawValues = [];
        $skippedCount = 0;
        $invalidCount = 0;
        $normalizedResponses = [];

        foreach ($questions as $q) {
            $qid = $q['id'];
            $dim = $q['dimension'];
            $val = $rawResponses[$qid] ?? null;

            if ($val === null || $val === '') {
                $skippedCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }
            if (! is_numeric($val)) {
                $invalidCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }
            $intVal = (int) $val;
            if ((float) $val !== (float) $intVal || $intVal < $scaleMin || $intVal > $scaleMax) {
                $invalidCount++;
                $normalizedResponses[$qid] = null;

                continue;
            }

            $normalizedResponses[$qid] = $intVal;
            $validNumericRawValues[] = $intVal;
            $adjusted = $q['direction'] === 1 ? $intVal : ($reverseBase - $intVal);
            $buckets[$dim][] = $adjusted;
            $evidenceByDim[$dim][] = [
                'question_id' => $qid,
                'text' => $q['text'],
                'direction' => $q['direction'],
                'raw_answer' => $intVal,
                'adjusted_score' => $adjusted,
                'signal_strength' => abs($adjusted - 3.0),
            ];
        }

        $dimensionScores = [];
        $insufficientDimensions = [];

        foreach (['O', 'C', 'E', 'A', 'N'] as $dim) {
            $meta = $dimsMeta[$dim];
            $totalDimQuestions = (int) $method['questions_per_dimension'];
            $validCount = count($buckets[$dim]);
            $validRatio = $totalDimQuestions > 0 ? ($validCount / $totalDimQuestions) : 0.0;

            if ($validCount === 0 || $validRatio < $minValidRatio) {
                $insufficientDimensions[] = $dim;
                $dimensionScores[$dim] = [
                    'dimension' => $dim,
                    'name' => $meta['name'],
                    'low_label' => $meta['low_label'],
                    'high_label' => $meta['high_label'],
                    'summary' => $meta['summary'],
                    'valid_count' => $validCount,
                    'total_questions' => $totalDimQuestions,
                    'mean' => null,
                    'index' => null,
                    'band' => 'insufficient_data',
                    'band_label' => 'Chưa đủ dữ liệu hợp lệ',
                    'evidence' => [],
                ];

                continue;
            }

            $mean = array_sum($buckets[$dim]) / $validCount;
            $indexExact = (($mean - $scaleMin) / $scaleSpan) * 100.0;
            $indexRounded = round($indexExact, 1);

            $band = match (true) {
                $indexExact >= 66.7 => 'high',
                $indexExact <= 33.3 => 'low',
                default => 'moderate',
            };
            $bandLabel = match ($band) {
                'high' => 'Cao trên thang đo nội bộ',
                'low' => 'Thấp trên thang đo nội bộ',
                default => 'Trung bình / Cân bằng',
            };

            $evList = $evidenceByDim[$dim];
            usort($evList, fn (array $a, array $b): int => $b['signal_strength'] <=> $a['signal_strength']);

            $entry = [
                'dimension' => $dim,
                'name' => $meta['name'],
                'low_label' => $meta['low_label'],
                'high_label' => $meta['high_label'],
                'summary' => $meta['summary'],
                'valid_count' => $validCount,
                'total_questions' => $totalDimQuestions,
                'mean' => round($mean, 4),
                'index_exact' => $indexExact,
                'index' => $indexRounded,
                'band' => $band,
                'band_label' => $bandLabel,
                'evidence' => array_slice($evList, 0, 4),
            ];
            if ($dim === 'N') {
                $entry['emotional_stability_index'] = round(100.0 - $indexExact, 1);
            }
            $dimensionScores[$dim] = $entry;
        }

        $qualityFlags = $this->evaluateResponseQuality(
            totalQuestions: count($questions),
            validNumericValues: $validNumericRawValues,
            unsureCount: 0,
            skippedCount: $skippedCount,
            invalidCount: $invalidCount,
            durationSeconds: $durationSeconds,
            insufficientDimensions: $insufficientDimensions
        );

        $hashPayload = [
            'method_id' => $method['method_id'],
            'method_version' => $method['version'],
            'scoring_version' => $method['scoring_version'],
            'responses' => $normalizedResponses,
            'means' => array_map(fn (array $d) => $d['mean'], $dimensionScores),
        ];

        return [
            'model_code' => 'big_five',
            'method_id' => $method['method_id'],
            'method_version' => $method['version'],
            'scoring_version' => $method['scoring_version'],
            'suggested_type' => $insufficientDimensions === [] ? 'OCEAN' : 'INCOMPLETE',
            'possible_types' => [],
            'alternative_types' => [],
            'has_tie' => false,
            'has_insufficient_data' => $insufficientDimensions !== [],
            'dimensions' => $dimensionScores,
            'boundary_flags' => [],
            'quality_flags' => $qualityFlags,
            'answered_count' => count($validNumericRawValues),
            'unsure_count' => 0,
            'skipped_count' => $skippedCount,
            'calculation_hash' => hash('sha256', (string) json_encode($hashPayload, JSON_UNESCAPED_UNICODE)),
        ];
    }

    /**
     * @param  array<string, list<string>>  $letterOptions
     * @return list<string>
     */
    protected function cartesianTypeCodes(array $letterOptions): array
    {
        $results = [''];
        foreach (['EI', 'SN', 'TF', 'JP'] as $dim) {
            $next = [];
            foreach ($results as $prefix) {
                foreach ($letterOptions[$dim] ?? [] as $letter) {
                    $next[] = $prefix.$letter;
                }
            }
            $results = $next;
        }

        return $results;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function buildTendencyLabel(string $tendency, float $positiveExact, array $meta): string
    {
        if ($tendency === 'undecided') {
            return 'Cân bằng đúng điểm giữa (50 / 50) — chưa nghiêng rõ về '.$meta['positive_code'].' hay '.$meta['negative_code'];
        }

        $strength = abs($positiveExact - 50.0);
        $targetName = $tendency === $meta['positive_code'] ? $meta['positive_name'] : $meta['negative_name'];

        return match (true) {
            $strength <= 5.0 => "Nghiêng nhẹ về {$tendency} — {$targetName} (vùng ranh giới, nên đối chiếu thêm)",
            $strength <= 15.0 => "Nghiêng vừa về {$tendency} — {$targetName}",
            default => "Nghiêng rõ về {$tendency} — {$targetName}",
        };
    }

    /**
     * Kiểm tra chất lượng phản hồi minh bạch (CHỈ gắn cờ thông tin, KHÔNG tự động sửa điểm).
     *
     * @param  list<int>  $validNumericValues
     * @param  list<string>  $insufficientDimensions
     * @return array<string, mixed>
     */
    protected function evaluateResponseQuality(
        int $totalQuestions,
        array $validNumericValues,
        int $unsureCount,
        int $skippedCount,
        int $invalidCount,
        int $durationSeconds,
        array $insufficientDimensions
    ): array {
        $flags = [];
        $notes = [];
        $validCount = count($validNumericValues);

        if ($insufficientDimensions !== []) {
            $flags[] = 'insufficient_data';
            $notes[] = 'Một hoặc nhiều chiều chưa đủ 50% câu trả lời hợp lệ ('.implode(', ', $insufficientDimensions).') — chưa đủ cơ sở tạo kết luận đầy đủ.';
        }

        if ($totalQuestions > 0 && ($unsureCount / $totalQuestions) >= 0.25) {
            $flags[] = 'high_unsure_ratio';
            $notes[] = "Bạn chọn 'Không chắc' ở {$unsureCount}/{$totalQuestions} câu — các chỉ số có thể ở gần điểm giữa hơn bình thường.";
        }

        if ($validCount >= 8) {
            $uniqueValues = array_unique($validNumericValues);
            if (count($uniqueValues) === 1) {
                $flags[] = 'straight_lining';
                $notes[] = 'Tất cả câu trả lời đều cùng một mức chọn. Nếu đây là phản hồi thật của bạn, bạn vẫn có thể tự đối chiếu thêm ở mục Best-Fit.';
            }
        }

        if ($durationSeconds > 0 && $totalQuestions > 0 && ($durationSeconds / $totalQuestions) < 1.2) {
            $flags[] = 'very_fast_completion';
            $notes[] = 'Thời gian hoàn thành khá nhanh (dưới 1.2 giây/câu). Hệ thống vẫn giữ nguyên điểm gốc và gợi ý bạn rà lại các chiều gần ranh giới nếu muốn.';
        }

        return [
            'status' => $insufficientDimensions !== [] ? 'insufficient' : ($flags === [] ? 'normal' : 'advisory'),
            'flags' => $flags,
            'notes' => $notes,
            'valid_count' => $validCount,
            'unsure_count' => $unsureCount,
            'skipped_count' => $skippedCount,
            'invalid_count' => $invalidCount,
            'duration_seconds' => $durationSeconds,
        ];
    }
}
