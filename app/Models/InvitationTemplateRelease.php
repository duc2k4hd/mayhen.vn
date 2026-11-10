<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationTemplateRelease extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invitation_template_releases';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'version',
        'release_status',
        'rollout_percentage',
        'release_notes',
        'preflight_snapshot',
        'creator_id',
        'publisher_id',
        'published_at',
        'rolled_back_at',
        'rollback_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rollout_percentage' => 'integer',
        'preflight_snapshot' => 'array',
        'published_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    /**
     * Mẫu thiệp cưới tương ứng.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(InvitationTemplate::class, 'template_id');
    }

    /**
     * Người chuẩn bị bản phát hành (Maker/Creator).
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Người phê duyệt/công bố phát hành (Checker/Publisher).
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    /**
     * Kiểm tra trạng thái đã phát hành chính thức.
     */
    public function isPublished(): bool
    {
        return $this->release_status === 'published';
    }

    /**
     * Kiểm tra trạng thái thử nghiệm Staged.
     */
    public function isStaged(): bool
    {
        return $this->release_status === 'staged';
    }

    /**
     * Kiểm tra trạng thái đã hoàn nguyên Rollback.
     */
    public function isRolledBack(): bool
    {
        return $this->release_status === 'rolled_back';
    }

    /**
     * Nhãn trạng thái tiếng Việt thân thiện.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->release_status) {
            'published' => 'Đã Phát Hành (Published)',
            'staged' => 'Đang Thử Nghiệm (Staged)',
            'rolled_back' => 'Đã Hoàn Nguyên (Rolled Back)',
            'deprecated' => 'Ngừng Hỗ Trợ (Deprecated)',
            default => 'Bản Nháp (Draft)',
        };
    }
}

