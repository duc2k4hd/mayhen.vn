<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiResearchClaim extends Model
{
    public const STATUS_HYPOTHESIS = 'hypothesis';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_CONTRADICTED = 'contradicted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PROMOTED = 'promoted';

    protected $table = 'ai_research_claims';

    protected $fillable = [
        'topic_id',
        'source_id',
        'claim_hash',
        'claim_text',
        'evidence_snippet',
        'knowledge_layer',
        'cultural_region',
        'verification_status',
        'independent_sources_count',
        'source_domains',
        'confidence_score',
        'critic_notes',
        'promoted_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'independent_sources_count' => 'integer',
            'source_domains' => 'array',
            'confidence_score' => 'integer',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AiResearchTopic::class, 'topic_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(AiResearchSource::class, 'source_id');
    }

    public static function hashClaim(string $text): string
    {
        $norm = trim(preg_replace('/\s+/u', ' ', mb_strtolower($text, 'UTF-8')) ?? $text);

        return hash('sha256', $norm);
    }
}
