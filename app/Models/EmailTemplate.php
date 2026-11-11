<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_ZALO = 'zalo';

    public const CODE_ORDER_CONFIRMED = 'order_confirmed';

    public const CODE_INVITATION_EXPIRING_SOON = 'invitation_expiring_soon';

    public const CODE_INVITATION_EXPIRED_GRACE = 'invitation_expired_grace';

    public const CODE_TICKET_REPLIED = 'ticket_replied';

    public const CODE_WELCOME_COUPLE = 'welcome_couple';

    public const CODE_GUEST_INVITATION_EMAIL = 'guest_invitation_email';

    protected $fillable = [
        'code',
        'name',
        'channel',
        'subject',
        'body_html',
        'body_text',
        'variables',
        'is_active',
        'last_tested_at',
        'last_tested_email',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope only active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by delivery channel.
     */
    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope email templates.
     */
    public function scopeEmail(Builder $query): Builder
    {
        return $query->where('channel', self::CHANNEL_EMAIL);
    }

    /**
     * Scope SMS templates.
     */
    public function scopeSms(Builder $query): Builder
    {
        return $query->where('channel', self::CHANNEL_SMS);
    }

    /**
     * Scope Zalo templates.
     */
    public function scopeZalo(Builder $query): Builder
    {
        return $query->where('channel', self::CHANNEL_ZALO);
    }

    /**
     * Returns channel label for UI badges.
     */
    public function getChannelBadgeAttribute(): array
    {
        return match ($this->channel) {
            self::CHANNEL_SMS => ['label' => 'Tin Nhắn SMS', 'class' => 'mayhen_admin_badge--info'],
            self::CHANNEL_ZALO => ['label' => 'Zalo ZNS', 'class' => 'mayhen_admin_badge--primary'],
            default => ['label' => 'Thư Điện Tử Email', 'class' => 'mayhen_admin_badge--success'],
        };
    }
}
