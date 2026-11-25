<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NumerologyReading extends Model
{
    protected $fillable = [
        'profile_id',
        'user_id',
        'slug',
        'version_number',
        'input_hash',
        'method_id',
        'method_version',
        'kb_version',
        'viewed_year',
        'focus_topic',
        'user_context_note',
        'calculation_payload_json',
        'interpretation_json',
        'ai_powered',
        'ai_model',
        'ai_status',
        'privacy_mode',
        'share_token',
        'share_revoked_at',
        'share_hide_birth_date',
        'feedback_rating',
        'is_favorite',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'calculation_payload_json' => 'array',
            'interpretation_json' => 'array',
            'ai_powered' => 'boolean',
            'share_revoked_at' => 'datetime',
            'share_hide_birth_date' => 'boolean',
            'is_favorite' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(NumerologyProfile::class, 'profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(NumerologyFollowup::class, 'reading_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(NumerologyJournal::class, 'reading_id');
    }

    public function isSharedActive(): bool
    {
        return $this->privacy_mode === 'shared_card'
            && ! empty($this->share_token)
            && $this->share_revoked_at === null;
    }
}
