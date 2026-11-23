<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatbotLog extends Model
{
    use HasFactory;

    public const MODE_LLM_GROUNDED = 'llm_grounded';

    public const MODE_LLM_REVIEWED = 'llm_reviewed';

    public const MODE_FALLBACK_GROUNDED = 'fallback_grounded';

    public const MODE_NO_CONTEXT_FALLBACK = 'no_context_fallback';

    public const MODE_OUT_OF_SCOPE = 'out_of_scope';

    public const MODE_BLOCKED_SECURITY = 'blocked_security';

    public const RISK_SIMPLE = 'simple';

    public const RISK_COMPLEX = 'complex';

    public const RISK_HIGH = 'high_risk';

    public const RISK_BLOCKED = 'blocked';

    protected $fillable = [
        'user_id',
        'session_hash',
        'ip_hash',
        'user_message',
        'bot_reply',
        'intent',
        'risk_level',
        'response_mode',
        'review_status',
        'matched_knowledge_ids',
        'matched_template_slugs',
        'matched_doc_urls',
        'security_flags',
        'trace_metadata',
        'provider_status',
        'latency_ms',
        'feedback',
        'feedback_valid',
    ];

    protected function casts(): array
    {
        return [
            'matched_knowledge_ids' => 'array',
            'matched_template_slugs' => 'array',
            'matched_doc_urls' => 'array',
            'security_flags' => 'array',
            'trace_metadata' => 'array',
            'latency_ms' => 'integer',
            'feedback_valid' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getResponseModeLabelAttribute(): string
    {
        return match ($this->response_mode) {
            self::MODE_LLM_REVIEWED => 'AI + Reviewer Kiểm Định',
            self::MODE_LLM_GROUNDED => 'AI + Dữ Liệu Xác Thực',
            self::MODE_FALLBACK_GROUNDED => 'Tri Thức Dự Án An Toàn',
            self::MODE_NO_CONTEXT_FALLBACK => 'Thiếu Dữ Liệu Xác Thực',
            self::MODE_OUT_OF_SCOPE => 'Ngoài Phạm Vi Mây Hẹn',
            self::MODE_BLOCKED_SECURITY => 'Chặn Bảo Mật (Zero-Trust)',
            default => $this->response_mode,
        };
    }

    public function getRiskLevelLabelAttribute(): string
    {
        return match ($this->risk_level) {
            self::RISK_HIGH => 'Rủi ro cao',
            self::RISK_COMPLEX => 'Phức tạp',
            self::RISK_BLOCKED => 'Đã chặn',
            default => 'Cơ bản',
        };
    }
}
