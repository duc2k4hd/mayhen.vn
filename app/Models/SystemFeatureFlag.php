<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemFeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'is_enabled',
        'rollout_percentage',
        'target_roles',
        'kill_switch_active',
        'kill_switch_reason',
        'last_toggled_by',
        'last_toggled_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'kill_switch_active' => 'boolean',
        'rollout_percentage' => 'integer',
        'target_roles' => 'array',
        'last_toggled_at' => 'datetime',
    ];

    public function lastToggledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_toggled_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'core' => 'Hệ thống cốt lõi',
            'ai' => 'Trí tuệ nhân tạo (AI)',
            'commerce' => 'Thương mại & Gói cước',
            'collaboration' => 'Cộng tác & Studio',
            'safety' => 'An toàn & Bảo vệ',
            default => $this->category,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->kill_switch_active) {
            return 'Khóa Khẩn Cấp (Kill Switch)';
        }

        if (! $this->is_enabled) {
            return 'Đã Tắt';
        }

        if ($this->rollout_percentage < 100) {
            return "Phát hành thử nghiệm ({$this->rollout_percentage}%)";
        }

        return 'Đang Hoạt Động (100%)';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->kill_switch_active) {
            return 'mayhen_admin_badge--danger';
        }

        if (! $this->is_enabled) {
            return 'mayhen_admin_badge--neutral';
        }

        if ($this->rollout_percentage < 100) {
            return 'mayhen_admin_badge--warning';
        }

        return 'mayhen_admin_badge--success';
    }
}
