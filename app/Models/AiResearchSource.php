<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiResearchSource extends Model
{
    public const STATUS_DISCOVERED = 'discovered';

    public const STATUS_FETCHED = 'fetched';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_DUPLICATE = 'duplicate';

    public const STATUS_ROBOTS_BLOCKED = 'robots_blocked';

    public const STATUS_ERROR = 'error';

    protected $table = 'ai_research_sources';

    protected $fillable = [
        'topic_id',
        'url_hash',
        'canonical_url',
        'domain',
        'title',
        'discovery_method',
        'discovery_query',
        'status',
        'rejection_reason',
        'http_status',
        'content_type',
        'content_hash',
        'content_length',
        'excerpt',
        'published_at_detected',
        'trust_score',
        'relevance_score',
        'claims_count',
        'security_flags',
        'fetched_at',
        'recheck_after_at',
    ];

    protected function casts(): array
    {
        return [
            'http_status' => 'integer',
            'content_length' => 'integer',
            'trust_score' => 'integer',
            'relevance_score' => 'integer',
            'claims_count' => 'integer',
            'security_flags' => 'array',
            'fetched_at' => 'datetime',
            'recheck_after_at' => 'datetime',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(AiResearchTopic::class, 'topic_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(AiResearchClaim::class, 'source_id');
    }

    public static function hashUrl(string $canonicalUrl): string
    {
        return hash('sha256', strtolower(trim($canonicalUrl)));
    }
}
