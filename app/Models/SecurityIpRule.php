<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIpRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'type',
        'reason',
        'blocked_until',
        'hit_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'blocked_until' => 'datetime',
            'hit_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBlacklist(Builder $query): Builder
    {
        return $query->where('type', 'blacklist');
    }

    public function scopeWhitelist(Builder $query): Builder
    {
        return $query->where('type', 'whitelist');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('blocked_until')
                ->orWhere('blocked_until', '>', Carbon::now());
        });
    }

    public function isExpired(): bool
    {
        return $this->blocked_until !== null && $this->blocked_until->isPast();
    }

    public function isActive(): bool
    {
        return ! $this->isExpired();
    }

    public function recordHit(): void
    {
        $this->increment('hit_count');
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->type === 'whitelist') {
            return 'Tin Cậy (Whitelist)';
        }

        if ($this->isExpired()) {
            return 'Đã Hết Hạn';
        }

        return $this->blocked_until ? 'Chặn Có Thời Hạn' : 'Chặn Vĩnh Viễn';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->type === 'whitelist') {
            return 'success';
        }

        if ($this->isExpired()) {
            return 'muted';
        }

        return 'danger';
    }
}
