<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\User;
use App\Models\Wedding;

class AiQualityAndCostObservatoryService
{
    /**
     * Tỷ giá quy đổi ngoại tệ tham chiếu (VND/USD).
     */
    public const USD_VND_RATE = 25400.0;

    /**
     * Bảng giá Token theo triệu token (USD / 1M tokens).
     */
    public const PROVIDER_PRICING = [
        'gemini-1.5-flash' => [
            'prompt' => 0.075,      // $0.075 / 1M prompt tokens
            'completion' => 0.30,   // $0.30 / 1M completion tokens
        ],
        'gemini-1.5-pro' => [
            'prompt' => 1.25,       // $1.25 / 1M prompt tokens
            'completion' => 5.00,   // $5.00 / 1M completion tokens
        ],
        'heuristic_fallback' => [
            'prompt' => 0.0,
            'completion' => 0.0,
        ],
    ];

    /**
     * Danh sách từ khóa cấm/nhạy cảm vi phạm quy chuẩn văn hóa ngày cưới (Brand Safety Guardrails).
     */
    public const FORBIDDEN_KEYWORDS = [
        'ly hôn', 'chia tay', 'lừa đảo', 'phản bội', 'ngoại tình',
        'chửi', 'tẩy chay', 'đòi nợ', 'phá sản', 'tang tóc',
        'ma túy', 'đánh bạc', 'xúc phạm',
    ];

    /**
     * Tính toán chi phí thực tế quy đổi sang VND.
     */
    public static function calculateTokenCost(string $provider, int $promptTokens, int $completionTokens): float
    {
        $pricing = self::PROVIDER_PRICING[$provider] ?? self::PROVIDER_PRICING['gemini-1.5-flash'];

        $promptCostUsd = ($promptTokens / 1_000_000) * $pricing['prompt'];
        $completionCostUsd = ($completionTokens / 1_000_000) * $pricing['completion'];
        $totalCostUsd = $promptCostUsd + $completionCostUsd;

        return round($totalCostUsd * self::USD_VND_RATE, 4);
    }

    /**
     * Kiểm duyệt nội dung đầu vào và đầu ra chống ngôn từ độc hại (Safety Guardrails).
     *
     * @return array{is_safe: bool, rating: string, reason: ?string}
     */
    public static function checkSafetyGuardrails(string $text): array
    {
        $lowerText = mb_strtolower($text, 'UTF-8');

        foreach (self::FORBIDDEN_KEYWORDS as $word) {
            if (str_contains($lowerText, $word)) {
                return [
                    'is_safe' => false,
                    'rating' => 'flagged_inappropriate',
                    'reason' => "Nội dung chứa từ khóa nhạy cảm không phù hợp ngày cưới: \"{$word}\"",
                ];
            }
        }

        return [
            'is_safe' => true,
            'rating' => 'passed',
            'reason' => null,
        ];
    }

    /**
     * Kho văn bản dự phòng Heuristic chuẩn phong cách Quiet Luxury khi AI bị ngắt mạng.
     */
    public static function getHeuristicFallback(string $featureName, array $context = []): string
    {
        $couple = ! empty($context['couple']) ? $context['couple'] : 'Chúng tôi';

        return match ($featureName) {
            'story_generator' => "Tình yêu của {$couple} khởi nguồn từ những điều bình dị và chân thành nhất. Giữa thế gian rộng lớn, chúng tôi may mắn tìm thấy nhau, đồng điệu trong từng nhịp đập và cùng hướng về một mái ấm an yên. Từng khoảnh khắc đi qua đều là một mảnh ghép quý giá làm nên lời hẹn ước trọn đời ngày hôm nay.",
            'rsvp_wording' => 'Sự hiện diện và lời chúc phúc của Quý khách là niềm vinh hạnh to lớn và là món quà trân quý nhất đối với hai gia đình chúng tôi trong ngày trọng đại.',
            'concierge_qa' => 'Đội ngũ Atelier Mây Hẹn luôn sẵn sàng hỗ trợ chu đáo nhất để khoảnh khắc đón tiếp quan khách của bạn diễn ra hoàn hảo và trọn vẹn.',
            default => 'Trân trọng cảm ơn tình cảm và sự đồng hành quý báu của Quý khách.',
        };
    }

    /**
     * Ghi vết nhật ký sử dụng AI (AI Usage Logging).
     */
    public static function recordUsage(array $params): AiUsageLog
    {
        $promptTokens = (int) ($params['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($params['completion_tokens'] ?? 0);
        $provider = $params['provider'] ?? 'gemini-1.5-flash';

        $costVnd = isset($params['cost_vnd'])
            ? (float) $params['cost_vnd']
            : self::calculateTokenCost($provider, $promptTokens, $completionTokens);

        return AiUsageLog::create([
            'user_id' => $params['user_id'] ?? null,
            'wedding_id' => $params['wedding_id'] ?? null,
            'feature_name' => $params['feature_name'] ?? 'story_generator',
            'provider' => $provider,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
            'latency_ms' => (int) ($params['latency_ms'] ?? 0),
            'status' => $params['status'] ?? 'success',
            'safety_rating' => $params['safety_rating'] ?? 'passed',
            'user_action' => $params['user_action'] ?? 'pending',
            'prompt_preview' => mb_substr((string) ($params['prompt_preview'] ?? ''), 0, 500),
            'output_preview' => mb_substr((string) ($params['output_preview'] ?? ''), 0, 1000),
            'error_message' => $params['error_message'] ?? null,
        ]);
    }

    /**
     * Giả lập thực thi AI và đo lường an toàn trong Sandbox Admin.
     *
     * @return array{success: bool, output: string, cost_vnd: float, latency_ms: int, fallback_applied: bool, safety: array}
     */
    public static function simulateAiGeneration(
        string $featureName,
        string $prompt,
        ?User $user = null,
        ?Wedding $wedding = null,
        bool $forceFallback = false,
        string $provider = 'gemini-1.5-flash'
    ): array {
        $startTime = microtime(true);

        // 1. Kiểm duyệt Brand Safety đầu vào
        $safety = self::checkSafetyGuardrails($prompt);
        if (! $safety['is_safe']) {
            self::recordUsage([
                'user_id' => $user?->id,
                'wedding_id' => $wedding?->id,
                'feature_name' => $featureName,
                'provider' => $provider,
                'prompt_tokens' => (int) ceil(mb_strlen($prompt) / 4),
                'completion_tokens' => 0,
                'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                'status' => 'blocked_safety',
                'safety_rating' => $safety['rating'],
                'prompt_preview' => $prompt,
                'error_message' => $safety['reason'],
            ]);

            return [
                'success' => false,
                'output' => 'Yêu cầu bị từ chối do vi phạm quy chuẩn an toàn văn hóa ngày cưới: '.$safety['reason'],
                'cost_vnd' => 0.0,
                'latency_ms' => (int) round((microtime(true) - $startTime) * 1000),
                'fallback_applied' => false,
                'safety' => $safety,
            ];
        }

        // 2. Kích hoạt Fallback Heuristic nếu được yêu cầu hoặc khi AI provider gặp sự cố
        if ($forceFallback) {
            $context = [];
            if ($wedding) {
                $context['couple'] = "{$wedding->groom_name} & {$wedding->bride_name}";
            }
            $fallbackText = self::getHeuristicFallback($featureName, $context);
            $latencyMs = (int) round((microtime(true) - $startTime) * 1000) + 15;

            self::recordUsage([
                'user_id' => $user?->id,
                'wedding_id' => $wedding?->id,
                'feature_name' => $featureName,
                'provider' => 'heuristic_fallback',
                'prompt_tokens' => (int) ceil(mb_strlen($prompt) / 4),
                'completion_tokens' => (int) ceil(mb_strlen($fallbackText) / 4),
                'cost_vnd' => 0.0,
                'latency_ms' => $latencyMs,
                'status' => 'fallback_applied',
                'safety_rating' => 'passed',
                'prompt_preview' => $prompt,
                'output_preview' => $fallbackText,
            ]);

            return [
                'success' => true,
                'output' => $fallbackText,
                'cost_vnd' => 0.0,
                'latency_ms' => $latencyMs,
                'fallback_applied' => true,
                'safety' => $safety,
            ];
        }

        // 3. Xử lý qua Provider AI chuẩn mực
        $promptTokens = (int) ceil(mb_strlen($prompt) / 4) + 120; // Token hệ thống + context
        $couple = $wedding ? "{$wedding->groom_name} & {$wedding->bride_name}" : 'Chúng tôi';
        $generatedText = "Lời ngỏ từ trái tim: Giữa dòng đời tấp nập, {$couple} đã tìm thấy bình yên bên nhau. Cảm ơn vì đã cùng chúng tôi viết nên những trang ký ức thanh xuân đẹp nhất trong ngày lễ thành hôn này.";
        $completionTokens = (int) ceil(mb_strlen($generatedText) / 4);

        $latencyMs = (int) round((microtime(true) - $startTime) * 1000) + 240; // Giả lập latency mạng thực tế
        $costVnd = self::calculateTokenCost($provider, $promptTokens, $completionTokens);

        self::recordUsage([
            'user_id' => $user?->id,
            'wedding_id' => $wedding?->id,
            'feature_name' => $featureName,
            'provider' => $provider,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'cost_vnd' => $costVnd,
            'latency_ms' => $latencyMs,
            'status' => 'success',
            'safety_rating' => 'passed',
            'prompt_preview' => $prompt,
            'output_preview' => $generatedText,
        ]);

        return [
            'success' => true,
            'output' => $generatedText,
            'cost_vnd' => $costVnd,
            'latency_ms' => $latencyMs,
            'fallback_applied' => false,
            'safety' => $safety,
        ];
    }

    /**
     * Thống kê toàn cảnh Đài quan sát AI (Observatory Metrics).
     *
     * @return array{
     *     total_requests: int,
     *     total_cost_vnd: float,
     *     total_tokens: int,
     *     avg_latency_ms: float,
     *     success_rate: float,
     *     fallback_rate: float,
     *     acceptance_rate: float,
     *     safety_incidents: int,
     *     provider_breakdown: array<string, int>
     * }
     */
    public static function getObservatoryMetrics(int $days = 30): array
    {
        $query = AiUsageLog::where('created_at', '>=', now()->subDays($days));

        $totalRequests = (clone $query)->count();
        $totalCost = (float) ((clone $query)->sum('cost_vnd') ?? 0.0);
        $totalTokens = (int) ((clone $query)->sum('total_tokens') ?? 0);
        $avgLatency = (float) round((clone $query)->avg('latency_ms') ?? 0.0, 1);

        $successCount = (clone $query)->where('status', 'success')->count();
        $fallbackCount = (clone $query)->where('status', 'fallback_applied')->count();
        $safetyCount = (clone $query)->where('status', 'blocked_safety')->count();

        $successRate = $totalRequests > 0 ? round(($successCount / $totalRequests) * 100, 1) : 100.0;
        $fallbackRate = $totalRequests > 0 ? round(($fallbackCount / $totalRequests) * 100, 1) : 0.0;

        // Tỷ lệ chấp nhận nội dung của người dùng (Acceptance Rate)
        $actionedCount = (clone $query)->whereIn('user_action', ['accepted', 'edited', 'rejected'])->count();
        $acceptedCount = (clone $query)->where('user_action', 'accepted')->count();
        $acceptanceRate = $actionedCount > 0 ? round(($acceptedCount / $actionedCount) * 100, 1) : 85.0;

        // Phân bổ theo nhà cung cấp
        $providerBreakdown = (clone $query)
            ->selectRaw('provider, count(*) as count')
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        return [
            'total_requests' => $totalRequests,
            'total_cost_vnd' => $totalCost,
            'total_tokens' => $totalTokens,
            'avg_latency_ms' => $avgLatency,
            'success_rate' => $successRate,
            'fallback_rate' => $fallbackRate,
            'acceptance_rate' => $acceptanceRate,
            'safety_incidents' => $safetyCount,
            'provider_breakdown' => $providerBreakdown,
        ];
    }
}
