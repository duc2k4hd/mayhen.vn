<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiKnowledgeGap extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_DISMISSED = 'dismissed';

    protected $table = 'ai_knowledge_gaps';

    protected $fillable = [
        'gap_hash',
        'topic',
        'domain_level',
        'sample_question',
        'occurrence_count',
        'status',
        'resolved_entry_id',
        'evidence_meta',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'domain_level' => 'integer',
            'occurrence_count' => 'integer',
            'evidence_meta' => 'array',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Ghi nhận khoảng trống tri thức theo vân tay nội dung (chống phình DB: 10.000 câu trùng = 1 dòng tăng counter).
     *
     * @param  array<string, mixed>  $meta
     */
    public static function recordGap(string $maskedQuestion, string $topic = 'general', int $domainLevel = 3, array $meta = []): ?self
    {
        if (! Schema::hasTable('ai_knowledge_gaps')) {
            return null;
        }

        $clean = trim(preg_replace('/\s+/u', ' ', $maskedQuestion) ?? $maskedQuestion);
        if (mb_strlen($clean, 'UTF-8') < 6) {
            return null;
        }

        $normalized = Str::ascii(mb_strtolower($clean, 'UTF-8'));
        $normalized = trim(preg_replace('/[^a-z0-9\s]/', ' ', $normalized) ?? $normalized);
        if ($normalized === '') {
            return null;
        }

        $hash = hash('sha256', $topic.'|'.$normalized);
        $existing = self::query()->where('gap_hash', $hash)->first();

        if ($existing) {
            $existing->update([
                'occurrence_count' => (int) $existing->occurrence_count + 1,
                'last_seen_at' => now(),
                'status' => $existing->status === self::STATUS_DISMISSED ? self::STATUS_DISMISSED : ($existing->status === self::STATUS_RESOLVED ? self::STATUS_RESOLVED : $existing->status),
            ]);

            return $existing;
        }

        return self::query()->create([
            'gap_hash' => $hash,
            'topic' => array_key_exists($topic, AiKnowledgeEntry::CATEGORIES) ? $topic : 'general',
            'domain_level' => max(0, min(4, $domainLevel)),
            'sample_question' => Str::limit($clean, 190, ''),
            'occurrence_count' => 1,
            'status' => self::STATUS_OPEN,
            'evidence_meta' => $meta !== [] ? $meta : null,
            'last_seen_at' => now(),
        ]);
    }
}
