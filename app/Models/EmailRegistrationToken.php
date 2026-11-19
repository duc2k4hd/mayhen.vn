<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailRegistrationToken extends Model
{
    public const TTL_HOURS = 24;

    public const RESEND_COOLDOWN_SECONDS = 60;

    protected $fillable = [
        'email',
        'token_hash',
        'redirect_to',
        'ip_address',
        'user_agent',
        'expires_at',
        'used_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Băm token thô bằng SHA-256 (tuyệt đối không lưu token gốc trong CSDL).
     */
    public static function hashToken(string $rawToken): string
    {
        return hash('sha256', trim($rawToken));
    }

    /**
     * Sinh cặp [rawToken, tokenHash] chuẩn mật mã 256-bit.
     *
     * @return array{0: string, 1: string}
     */
    public static function generateTokenPair(): array
    {
        $raw = bin2hex(random_bytes(32));

        return [$raw, self::hashToken($raw)];
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at !== null && $this->expires_at->isFuture();
    }
}
