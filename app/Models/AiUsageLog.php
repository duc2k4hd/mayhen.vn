<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    use HasFactory;

    protected $table = 'ai_usage_logs';

    protected $fillable = [
        'user_id',
        'wedding_id',
        'feature_name',
        'provider',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_vnd',
        'latency_ms',
        'status',
        'safety_rating',
        'user_action',
        'prompt_preview',
        'output_preview',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'total_tokens' => 'integer',
            'cost_vnd' => 'decimal:4',
            'latency_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function getFeatureLabelAttribute(): string
    {
        return match ($this->feature_name) {
            'story_generator' => 'Tạo Câu Chuyện Tình Yêu',
            'rsvp_wording' => 'Trợ Lý Soạn Lời Mời RSVP',
            'concierge_qa' => 'Hỏi Đáp Lễ Nghi Cưới',
            default => $this->feature_name,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'success' => 'Thành Công (AI)',
            'fallback_applied' => 'Dự Phòng An Toàn (Fallback)',
            'blocked_safety' => 'Chặn Bởi Kiểm Duyệt (Safety Blocked)',
            'failed' => 'Lỗi Provider',
            default => $this->status,
        };
    }
}
