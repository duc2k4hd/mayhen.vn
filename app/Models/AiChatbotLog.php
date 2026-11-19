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

    public const MODE_FALLBACK_GROUNDED = 'fallback_grounded';

    public const MODE_NO_CONTEXT_FALLBACK = 'no_context_fallback';

    public const MODE_BLOCKED_SECURITY = 'blocked_security';

    protected $fillable = [
        'user_id',
        'session_hash',
        'ip_hash',
        'user_message',
        'bot_reply',
        'intent',
        'response_mode',
        'matched_knowledge_ids',
        'matched_template_slugs',
        'matched_doc_urls',
        'security_flags',
        'provider_status',
        'latency_ms',
        'feedback',
    ];

    protected function casts(): array
    {
        return [
            'matched_knowledge_ids' => 'array',
            'matched_template_slugs' => 'array',
            'matched_doc_urls' => 'array',
            'security_flags' => 'array',
            'latency_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getResponseModeLabelAttribute(): string
    {
        return match ($this->response_mode) {
            self::MODE_LLM_GROUNDED => 'AI + Dữ Liệu Xác Thực',
            self::MODE_FALLBACK_GROUNDED => 'Tri Thức Dự Án Trực Tiếp',
            self::MODE_NO_CONTEXT_FALLBACK => 'Chưa Có Dữ Liệu Khớp',
            self::MODE_BLOCKED_SECURITY => 'Chặn Bảo Mật (Zero-Trust)',
            default => $this->response_mode,
        };
    }
}
