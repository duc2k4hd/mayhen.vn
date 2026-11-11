<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifecycleAutomationRule extends Model
{
    use HasFactory;

    protected $table = 'lifecycle_automation_rules';

    protected $fillable = [
        'code',
        'name',
        'description',
        'trigger_event',
        'offset_days',
        'channel',
        'template_code',
        'send_time',
        'is_active',
        'execution_count',
        'last_executed_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'offset_days' => 'integer',
            'is_active' => 'boolean',
            'execution_count' => 'integer',
            'last_executed_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LifecycleAutomationLog::class, 'rule_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrigger(Builder $query, string $event): Builder
    {
        return $query->where('trigger_event', $event);
    }

    public function getOffsetLabelAttribute(): string
    {
        if ($this->offset_days === 0) {
            return 'Đúng ngày cưới (D-Day)';
        }

        if ($this->offset_days < 0) {
            $abs = abs($this->offset_days);

            return "Trước ngày cưới {$abs} ngày (D-{$abs})";
        }

        return "Sau ngày cưới {$this->offset_days} ngày (D+{$this->offset_days})";
    }

    public function getChannelLabelAttribute(): string
    {
        return match ($this->channel) {
            'email' => 'Email Tự Động',
            'zalo_zns' => 'Zalo ZNS',
            'sms_brandname' => 'SMS Brandname',
            'in_app' => 'Thông Báo In-App',
            default => strtoupper($this->channel),
        };
    }

    public function getChannelBadgeClassAttribute(): string
    {
        return match ($this->channel) {
            'email' => 'mayhen_admin_badge--primary',
            'zalo_zns' => 'mayhen_admin_badge--gold',
            'sms_brandname' => 'mayhen_admin_badge--success',
            'in_app' => 'mayhen_admin_badge--neutral',
            default => 'mayhen_admin_badge--neutral',
        };
    }
}
