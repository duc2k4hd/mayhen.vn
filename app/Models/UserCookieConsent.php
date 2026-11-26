<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCookieConsent extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'ip_hash',
        'user_agent',
        'policy_version',
        'consent_essential',
        'consent_analytics',
        'consent_personalization',
        'consent_marketing',
        'source_url',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'consent_essential' => 'boolean',
            'consent_analytics' => 'boolean',
            'consent_personalization' => 'boolean',
            'consent_marketing' => 'boolean',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
