<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\ToolInsightSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * AI-Ready Structured Demand & Insight Collector (Mây Hẹn).
 *
 * - Lưu gọn gàng, sạch sẽ, tách riêng từng loại công cụ (`tool_key`) & nhóm ý định (`intent_category`).
 * - Chỉ giữ dữ liệu người dùng nhập có giá trị phân tích nhu cầu + kết quả phân tích cốt lõi.
 * - TUYỆT ĐỐI loại bỏ trường rác/nhạy cảm: CSRF token, password, cookie, base64, HTML dài, dữ liệu tọa độ vẽ thô.
 */
class ToolInsightCollectorService
{
    /** @var list<string> Các khóa rác hoặc nhạy cảm tuyệt đối không lưu */
    public const BLOCKED_KEYS = [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
        'captcha',
        'g-recaptcha-response',
        'signature_data',
        'floorplan',
        'image_base64',
        'file',
        'avatar',
        'csrf_token',
        'remember',
    ];

    /** @var list<string> Các khóa kết quả phân tích cốt lõi cần trích xuất từ JSON response */
    public const ANALYSIS_WHITELIST_KEYS = [
        'activity',
        'year',
        'month',
        'summary',
        'count',
        'estimated_tables',
        'reserve_tables',
        'cost_per_guest_vnd',
        'total_budget_vnd',
        'guest_count',
        'type',
        'source',
        'title',
        'tier',
        'tier_label',
        'cao_ly',
        'du_nien',
        'day_master',
        'useful_god',
        'life_path',
        'hexagram',
        'spread',
        'valid',
        'total_tables',
        'total_assigned',
    ];

    /**
     * Ghi nhận 1 bản ghi dữ liệu nhập & phân tích sạch vào kho `tool_insight_snapshots`.
     *
     * @param  array<string, mixed>  $rawInputs
     * @param  array<string, mixed>  $rawAnalysis
     */
    public function record(
        string $toolKey,
        string $intentCategory,
        array $rawInputs,
        array $rawAnalysis = [],
        ?string $regionOrTopic = null,
        ?Request $request = null
    ): ?ToolInsightSnapshot {
        if (! Schema::hasTable('tool_insight_snapshots')) {
            return null;
        }

        $cleanInputs = $this->sanitizeInputs($rawInputs);
        if ($cleanInputs === []) {
            return null;
        }

        $cleanAnalysis = $this->sanitizeAnalysis($rawAnalysis);
        $user = $request?->user();
        $sessionId = ($request && $request->hasSession()) ? (string) $request->session()->getId() : '';

        return ToolInsightSnapshot::create([
            'tool_key' => substr($toolKey, 0, 40),
            'intent_category' => substr($intentCategory, 0, 60),
            'region_or_topic' => $regionOrTopic ? substr($regionOrTopic, 0, 80) : $this->inferTopic($cleanInputs),
            'user_id' => $user?->id,
            'user_plan_tier' => $user ? (string) ($user->current_plan_slug ?? 'premium') : 'free',
            'session_hash' => $sessionId !== '' ? substr(hash('sha256', $sessionId.'|'.config('app.key')), 0, 16) : null,
            'user_inputs_json' => $cleanInputs,
            'analysis_summary_json' => $cleanAnalysis !== [] ? $cleanAnalysis : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Làm sạch dữ liệu người dùng nhập: loại bỏ khóa rác/nhạy cảm, giá trị rỗng, base64, HTML và cắt chuỗi > 500 ký tự.
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function sanitizeInputs(array $raw, int $depth = 0): array
    {
        if ($depth > 2) {
            return [];
        }

        $clean = [];
        foreach ($raw as $key => $val) {
            $k = (string) $key;
            if (in_array(strtolower($k), self::BLOCKED_KEYS, true)) {
                continue;
            }
            if ($val === null || $val === '' || $val === []) {
                continue;
            }

            if (is_bool($val) || is_int($val) || is_float($val)) {
                $clean[$k] = $val;
            } elseif (is_string($val)) {
                $trimmed = trim(strip_tags($val));
                if ($trimmed === '' || str_starts_with($trimmed, 'data:image/')) {
                    continue;
                }
                $clean[$k] = mb_substr($trimmed, 0, 500, 'UTF-8');
            } elseif (is_array($val)) {
                $sub = $this->sanitizeInputs(array_slice($val, 0, 12, true), $depth + 1);
                if ($sub !== []) {
                    $clean[$k] = $sub;
                }
            }

            if (count($clean) >= 24) {
                break;
            }
        }

        return $clean;
    }

    /**
     * Trích xuất gọn gàng kết quả phân tích cốt lõi từ response JSON (bỏ qua HTML dài / mảng lịch 30 ngày thừa).
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function sanitizeAnalysis(array $raw): array
    {
        $source = isset($raw['data']) && is_array($raw['data']) ? $raw['data'] : $raw;
        $clean = [];

        foreach (self::ANALYSIS_WHITELIST_KEYS as $key) {
            if (! array_key_exists($key, $source)) {
                continue;
            }
            $val = $source[$key];
            if (is_scalar($val)) {
                $clean[$key] = is_string($val) ? mb_substr(trim(strip_tags($val)), 0, 240, 'UTF-8') : $val;
            } elseif (is_array($val)) {
                // Giữ gọn các trường scalar bên trong 1 cấp (ví dụ summary: {eligible_count, favorable_count})
                $flat = [];
                foreach (array_slice($val, 0, 8, true) as $sk => $sv) {
                    if (is_scalar($sv)) {
                        $flat[(string) $sk] = is_string($sv) ? mb_substr(trim(strip_tags($sv)), 0, 140, 'UTF-8') : $sv;
                    }
                }
                if ($flat !== []) {
                    $clean[$key] = $flat;
                }
            }
        }

        return $clean;
    }

    /**
     * Thống kê tổng hợp nhu cầu người dùng tách riêng theo từng công cụ để AI và đội sản phẩm phân tích.
     *
     * @return array<string, mixed>
     */
    public function getDemandSummary(?string $toolKey = null): array
    {
        if (! Schema::hasTable('tool_insight_snapshots')) {
            return ['total' => 0, 'by_tool' => [], 'top_intents' => [], 'top_topics' => []];
        }

        $base = ToolInsightSnapshot::query()->when($toolKey, fn ($q) => $q->where('tool_key', $toolKey));

        $byTool = ToolInsightSnapshot::query()
            ->select('tool_key', DB::raw('COUNT(*) as total'))
            ->groupBy('tool_key')
            ->orderByDesc('total')
            ->pluck('total', 'tool_key')
            ->toArray();

        $topIntents = (clone $base)
            ->select('tool_key', 'intent_category', DB::raw('COUNT(*) as total'))
            ->groupBy('tool_key', 'intent_category')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->toArray();

        $topTopics = (clone $base)
            ->whereNotNull('region_or_topic')
            ->select('region_or_topic', DB::raw('COUNT(*) as total'))
            ->groupBy('region_or_topic')
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->toArray();

        return [
            'total' => (clone $base)->count(),
            'by_tool' => $byTool,
            'top_intents' => $topIntents,
            'top_topics' => $topTopics,
        ];
    }

    /**
     * Xuất tập dữ liệu JSONL (.jsonl) sạch tách riêng từng loại công cụ để đưa thẳng vào AI phân tích nhu cầu.
     */
    public function exportJsonl(?string $toolKey = null, int $limit = 5000): string
    {
        $rows = ToolInsightSnapshot::query()
            ->when($toolKey, fn ($q) => $q->where('tool_key', $toolKey))
            ->latest('id')
            ->limit($limit)
            ->get();

        $lines = [];
        foreach ($rows as $row) {
            $lines[] = json_encode([
                'tool_key' => $row->tool_key,
                'intent_category' => $row->intent_category,
                'region_or_topic' => $row->region_or_topic,
                'user_plan_tier' => $row->user_plan_tier,
                'created_at' => $row->created_at?->toIso8601String(),
                'user_inputs' => $row->user_inputs_json,
                'analysis_summary' => $row->analysis_summary_json,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return implode("\n", $lines).($lines !== [] ? "\n" : '');
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    protected function inferTopic(array $inputs): ?string
    {
        foreach (['activity', 'region_style', 'region', 'script_type', 'spread', 'topic', 'category', 'method_profile'] as $k) {
            if (! empty($inputs[$k]) && is_string($inputs[$k])) {
                return substr($inputs[$k], 0, 80);
            }
        }

        return null;
    }
}
