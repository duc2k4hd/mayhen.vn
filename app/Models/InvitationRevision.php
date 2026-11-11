<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationRevision extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'version_number',
        'revision_type',
        'title',
        'config_data',
        'diff_summary',
        'checksum',
        'created_by',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'config_data' => 'array',
        'diff_summary' => 'array',
        'created_at' => 'datetime',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRevisionTypeLabelAttribute(): string
    {
        return match ($this->revision_type) {
            'published' => 'Xuất Bản Chính Thức',
            'manual_save' => 'Lưu Thủ Công',
            'autosave' => 'Tự Động Lưu (Autosave)',
            'restored' => 'Phục Hồi Dữ Liệu',
            'template_switched' => 'Chuyển Đổi Mẫu Thiệp',
            default => $this->revision_type,
        };
    }

    public function getRevisionTypeBadgeClassAttribute(): string
    {
        return match ($this->revision_type) {
            'published' => 'mayhen_admin_badge--success',
            'manual_save' => 'mayhen_admin_badge--primary',
            'autosave' => 'mayhen_admin_badge--neutral',
            'restored' => 'mayhen_admin_badge--warning',
            'template_switched' => 'mayhen_admin_badge--info',
            default => 'mayhen_admin_badge--neutral',
        };
    }

    public function getChangedFieldsCountAttribute(): int
    {
        return is_array($this->diff_summary) ? count($this->diff_summary) : 0;
    }
}
