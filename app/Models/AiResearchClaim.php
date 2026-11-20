<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AiResearchClaim extends Model
{
    public const STATUS_DISCOVERED = 'discovered';

    public const STATUS_EXTRACTED = 'extracted';

    public const STATUS_UNVERIFIED = 'unverified';

    public const STATUS_PARTIALLY_SUPPORTED = 'partially_supported';

    public const STATUS_HYPOTHESIS = 'hypothesis';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_CONTRADICTED = 'contradicted';

    public const STATUS_DUPLICATE = 'duplicate';

    public const STATUS_NEEDS_REVIEW = 'needs_review';

    public const STATUS_APPROVED_REFERENCE = 'approved_reference';

    public const STATUS_APPROVED_OFFICIAL = 'approved_official';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_OUTDATED = 'outdated';

    public const STATUS_PROMOTED = 'promoted';

    public const SPACE_OFFICIAL_MAYHEN = 'official_mayhen';

    public const SPACE_INDUSTRY_CULTURAL = 'industry_cultural';

    public const SPACE_PRODUCT_INNOVATION = 'product_innovation';

    public const SPACE_CUSTOMER_INTELLIGENCE = 'customer_intelligence';

    public const CLAIM_TYPES = [
        'fact' => 'Sự kiện / Khái niệm',
        'process' => 'Quy trình / Trình tự',
        'cultural_norm' => 'Tập quán văn hóa vùng miền',
        'ux_principle' => 'Nguyên tắc trải nghiệm UX',
        'product_idea' => 'Ý tưởng cải tiến sản phẩm',
        'policy' => 'Chính sách / Giới hạn',
        'comparison' => 'So sánh / Đối chiếu',
    ];

    protected $table = 'ai_research_claims';

    protected $fillable = [
        'topic_id',
        'source_id',
        'claim_hash',
        'claim_text',
        'canonical_statement',
        'claim_type',
        'evidence_snippet',
        'knowledge_layer',
        'knowledge_space',
        'ontology_domain',
        'cultural_region',
        'verification_status',
        'approval_status',
        'security_classification',
        'effective_from',
        'effective_until',
        'independent_sources_count',
        'source_domains',
        'confidence_score',
        'version',
        'quality_metrics',
        'critic_notes',
        'contradiction_notes',
        'promoted_entry_id',
        'approved_by_user_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'independent_sources_count' => 'integer',
            'source_domains' => 'array',
            'confidence_score' => 'integer',
            'version' => 'integer',
            'quality_metrics' => 'array',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $claim): void {
            if (Schema::hasColumn('ai_research_claims', 'canonical_statement') && empty($claim->canonical_statement)) {
                $claim->canonical_statement = $claim->claim_text;
            }
        });
        static::saved(fn () => self::clearAtomicClaimCache());
        static::deleted(fn () => self::clearAtomicClaimCache());
    }

    public static function clearAtomicClaimCache(): void
    {
        Cache::forget('mayhen_ai_atomic_claims_live_v4');
    }

    /**
     * Chỉ truy xuất các Atomic Claims hợp lệ, được phép dùng phục vụ khách hàng (cấm product_innovation / outdated / rejected).
     *
     * @param  Builder<AiResearchClaim>  $query
     * @return Builder<AiResearchClaim>
     */
    public function scopeCustomerRetrievable(Builder $query): Builder
    {
        $now = now();

        if (Schema::hasColumn('ai_research_claims', 'approval_status')) {
            $query->whereIn('approval_status', [self::STATUS_APPROVED_OFFICIAL, self::STATUS_APPROVED_REFERENCE])
                ->where('security_classification', 'public_customer_safe')
                ->whereIn('knowledge_space', [self::SPACE_OFFICIAL_MAYHEN, self::SPACE_INDUSTRY_CULTURAL])
                ->where(function (Builder $q) use ($now) {
                    $q->whereNull('effective_from')->orWhere('effective_from', '<=', $now);
                })
                ->where(function (Builder $q) use ($now) {
                    $q->whereNull('effective_until')->orWhere('effective_until', '>=', $now);
                });
        } else {
            $query->whereIn('verification_status', [self::STATUS_VERIFIED, self::STATUS_PROMOTED]);
        }

        return $query;
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
