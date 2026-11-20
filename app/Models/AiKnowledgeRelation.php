<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiKnowledgeRelation extends Model
{
    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'ai_knowledge_relations';

    protected $fillable = [
        'edge_hash',
        'from_type',
        'from_key',
        'relation_type',
        'to_type',
        'to_key',
        'from_label',
        'to_label',
        'description',
        'domain_level',
        'evidence_refs',
        'source_type',
        'status',
        'is_active',
        'confidence_score',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'domain_level' => 'integer',
            'evidence_refs' => 'array',
            'is_active' => 'boolean',
            'confidence_score' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $rel): void {
            if (empty($rel->edge_hash)) {
                $rel->edge_hash = self::computeEdgeHash(
                    (string) $rel->from_type,
                    (string) $rel->from_key,
                    (string) $rel->relation_type,
                    (string) $rel->to_type,
                    (string) $rel->to_key
                );
            }
        });
        static::saved(fn () => self::clearRelationCache());
        static::deleted(fn () => self::clearRelationCache());
    }

    public static function computeEdgeHash(string $fromType, string $fromKey, string $relationType, string $toType, string $toKey): string
    {
        return hash('sha256', strtolower(trim($fromType).'|'.trim($fromKey).'|'.trim($relationType).'|'.trim($toType).'|'.trim($toKey)));
    }

    public static function clearRelationCache(): void
    {
        Cache::forget('mayhen_ai_knowledge_relations_v1');
    }

    /**
     * @param  Builder<AiKnowledgeRelation>  $query
     * @return Builder<AiKnowledgeRelation>
     */
    public function scopeActiveApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)->where('is_active', true);
    }
}
