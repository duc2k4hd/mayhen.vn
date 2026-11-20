<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AiKnowledgeEntry extends Model
{
    use HasFactory;

    public const SOURCE_ADMIN = 'admin';

    public const SOURCE_AI_SELF_TRAIN = 'ai_self_train';

    public const SOURCE_CUSTOMER = 'customer';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REJECTED = 'rejected';

    public const CATEGORIES = [
        'templates' => 'Mẫu Thiệp Cưới',
        'docs' => 'Hướng Dẫn Sử Dụng (Docs)',
        'pricing' => 'Bảng Giá & Gói Dịch Vụ',
        'rsvp' => 'Khách Mời, RSVP & Điểm Danh QR',
        'general' => 'Thông Tin Chung & Hỗ Trợ',
    ];

    protected $fillable = [
        'category',
        'title',
        'keywords',
        'answer',
        'template_slug',
        'doc_url',
        'source',
        'status',
        'is_active',
        'is_outdated',
        'priority',
        'version',
        'trust_score',
        'evidence_refs',
        'review_summary',
        'security_flags',
        'submitted_by_user_id',
        'submitted_session_hash',
        'approved_by_user_id',
        'approved_at',
        'effective_from',
        'effective_until',
        'rejection_reason',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_outdated' => 'boolean',
            'priority' => 'integer',
            'version' => 'integer',
            'trust_score' => 'integer',
            'evidence_refs' => 'array',
            'review_summary' => 'array',
            'security_flags' => 'array',
            'approved_at' => 'datetime',
            'effective_from' => 'datetime',
            'effective_until' => 'datetime',
            'usage_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::clearKnowledgeCache());
        static::deleted(fn () => self::clearKnowledgeCache());
    }

    public static function clearKnowledgeCache(): void
    {
        Cache::forget('mayhen_ai_approved_knowledge_entries');
        Cache::forget('mayhen_ai_approved_knowledge_entries_v2');
        Cache::forget('mayhen_ai_approved_knowledge_entries_v3');
    }

    /**
     * Chỉ truy xuất các mục tri thức đã được Admin phê duyệt, đang bật, chưa lỗi thời và còn trong khung hiệu lực.
     *
     * @param  Builder<AiKnowledgeEntry>  $query
     * @return Builder<AiKnowledgeEntry>
     */
    public function scopeLiveApproved(Builder $query): Builder
    {
        $now = now();

        $query->where('status', self::STATUS_APPROVED)
            ->where('is_active', true);

        if (Schema::hasColumn('ai_knowledge_entries', 'is_outdated')) {
            $query->where('is_outdated', false);
        }

        if (Schema::hasColumn('ai_knowledge_entries', 'effective_from')) {
            $query->where(function (Builder $q) use ($now) {
                $q->whereNull('effective_from')->orWhere('effective_from', '<=', $now);
            });
        }

        if (Schema::hasColumn('ai_knowledge_entries', 'effective_until')) {
            $query->where(function (Builder $q) use ($now) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>=', $now);
            });
        }

        return $query;
    }

    /**
     * Ghi nhận một bản chụp phiên bản để hỗ trợ truy vết và Rollback an toàn.
     */
    public function recordVersion(?int $changedByUserId = null, ?string $reason = null): ?AiKnowledgeVersion
    {
        if (! Schema::hasTable('ai_knowledge_versions')) {
            return null;
        }

        return $this->versions()->create([
            'version' => (int) ($this->version ?: 1),
            'category' => (string) $this->category,
            'title' => (string) $this->title,
            'keywords' => $this->keywords,
            'answer' => (string) $this->answer,
            'template_slug' => $this->template_slug,
            'doc_url' => $this->doc_url,
            'trust_score' => (int) ($this->trust_score ?? 100),
            'evidence_refs' => $this->evidence_refs,
            'change_reason' => $reason,
            'changed_by_user_id' => $changedByUserId,
        ]);
    }

    /**
     * Khôi phục tri thức về một phiên bản đã lưu trước đó (tạo số version kế tiếp để không mất lịch sử).
     */
    public function rollbackToVersion(AiKnowledgeVersion $snapshot, ?int $adminUserId = null): self
    {
        $nextVersion = max((int) ($this->version ?: 1), (int) $this->versions()->max('version')) + 1;

        $this->update([
            'category' => $snapshot->category,
            'title' => $snapshot->title,
            'keywords' => $snapshot->keywords,
            'answer' => $snapshot->answer,
            'template_slug' => $snapshot->template_slug,
            'doc_url' => $snapshot->doc_url,
            'trust_score' => $snapshot->trust_score,
            'evidence_refs' => $snapshot->evidence_refs,
            'is_outdated' => false,
            'version' => $nextVersion,
        ]);

        $this->recordVersion($adminUserId, "Rollback về phiên bản v{$snapshot->version}");

        return $this;
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AiKnowledgeVersion::class, 'knowledge_entry_id')->orderByDesc('version');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            self::SOURCE_ADMIN => 'Admin huấn luyện',
            self::SOURCE_AI_SELF_TRAIN => 'AI tự đề xuất (Đã kiểm định chéo)',
            self::SOURCE_CUSTOMER => 'Dữ liệu cũ (Khách đề xuất)',
            default => (string) $this->source,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_outdated) {
            return 'Lỗi thời (Đã tắt)';
        }

        return match ($this->status) {
            self::STATUS_APPROVED => 'Đã duyệt (Đang chạy)',
            self::STATUS_PENDING => 'Chờ Admin duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            default => $this->status,
        };
    }
}
