<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiResearchTopic extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DISCOVERING = 'discovering';

    public const STATUS_EXTRACTING = 'extracting';

    public const STATUS_VERIFYING = 'verifying';

    public const STATUS_EXPANDING = 'expanding';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STAGE_LABELS = [
        1 => 'L1 — Nền tảng (Foundation)',
        2 => 'L2 — Hiểu chi tiết (Detailed)',
        3 => 'L3 — Đối chiếu đa nguồn (Cross-Source)',
        4 => 'L4 — Quan hệ tri thức (Relationships)',
        5 => 'L5 — Ứng dụng Mây Hẹn (Applied)',
        6 => 'L6 — Đánh giá & Cải tiến (Evaluation)',
        7 => 'L7 — Mở rộng chuyên sâu (Expansion)',
    ];

    protected $table = 'ai_research_topics';

    protected $fillable = [
        'goal_id',
        'parent_id',
        'slug',
        'title',
        'domain',
        'category',
        'priority',
        'learning_stage',
        'learning_objectives',
        'prerequisites',
        'known_concepts',
        'unknown_concepts',
        'search_queries',
        'source_coverage',
        'evidence_count',
        'quality_score',
        'status',
        'review_state',
        'last_researched_at',
        'next_research_at',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'learning_stage' => 'integer',
            'learning_objectives' => 'array',
            'prerequisites' => 'array',
            'known_concepts' => 'array',
            'unknown_concepts' => 'array',
            'search_queries' => 'array',
            'source_coverage' => 'integer',
            'evidence_count' => 'integer',
            'quality_score' => 'integer',
            'last_researched_at' => 'datetime',
            'next_research_at' => 'datetime',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(AiResearchGoal::class, 'goal_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(AiResearchSource::class, 'topic_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(AiResearchClaim::class, 'topic_id');
    }

    public function getStageLabelAttribute(): string
    {
        return self::STAGE_LABELS[$this->learning_stage] ?? ('L'.$this->learning_stage);
    }
}
