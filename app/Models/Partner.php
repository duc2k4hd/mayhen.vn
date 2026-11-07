<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'partner_code',
        'phone',
        'email',
        'address',
        'logo_url',
        'website',
        'tier',
        'commission_rate',
        'wallet_balance',
        'status',
        'custom_config',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'wallet_balance' => 'decimal:2',
            'custom_config' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weddings(): HasMany
    {
        return $this->hasMany(Wedding::class);
    }

    public function invitations(): HasManyThrough
    {
        return $this->hasManyThrough(Invitation::class, Wedding::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(PartnerCommission::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public static function generatePartnerCode(string $prefix = 'MH-PARTNER-'): string
    {
        do {
            $code = $prefix.strtoupper(Str::random(6));
        } while (static::where('partner_code', $code)->exists());

        return $code;
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format((float) $this->wallet_balance, 0, ',', '.').' ₫';
    }

    public function getTierLabelAttribute(): string
    {
        return match ($this->tier) {
            'platinum' => 'Bạch Kim (Platinum)',
            'gold' => 'Hoàng Kim (Gold)',
            default => 'Bạc (Silver)',
        };
    }
}
