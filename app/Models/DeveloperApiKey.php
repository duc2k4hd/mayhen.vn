<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'environment',
        'key_prefix',
        'key_hash',
        'scopes',
        'rate_limit_per_minute',
        'status',
        'last_used_at',
        'last_used_ip',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'rate_limit_per_minute' => 'integer',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            });
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('status', 'revoked');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('environment', 'live');
    }

    public function scopeTest(Builder $query): Builder
    {
        return $query->where('environment', 'test');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes ?? [];

        return in_array($scope, $scopes, true) || in_array('*', $scopes, true);
    }

    public function revoke(): bool
    {
        return $this->update(['status' => 'revoked']);
    }

    public function recordUsage(?string $ip = null): void
    {
        $this->update([
            'last_used_at' => Carbon::now(),
            'last_used_ip' => $ip,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'revoked') {
            return 'Đã thu hồi';
        }

        if ($this->isExpired()) {
            return 'Đã hết hạn';
        }

        return 'Đang hoạt động';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->status === 'revoked') {
            return 'danger'; // Rose
        }

        if ($this->isExpired()) {
            return 'warning'; // Amber
        }

        return 'success'; // Emerald
    }

    public function getMaskedKeyAttribute(): string
    {
        return $this->key_prefix.'••••••••••••••••••••••••••••••••';
    }
}

