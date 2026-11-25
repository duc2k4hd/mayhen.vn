<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuViChart extends Model
{
    protected $table = 'tuvi_charts';

    protected $fillable = [
        'profile_id',
        'user_id',
        'slug',
        'version_number',
        'input_hash',
        'school_code',
        'rule_version',
        'kb_version',
        'viewed_year',
        'focus_topic',
        'chart_payload_json',
        'patterns_json',
        'interpretation_json',
        'ai_powered',
        'ai_model',
        'ai_status',
        'privacy_mode',
        'share_token',
        'share_revoked_at',
        'share_hide_birth_info',
        'idempotency_key',
        'is_favorite',
        'feedback_rating',
    ];

    protected function casts(): array
    {
        return [
            'chart_payload_json' => 'array',
            'patterns_json' => 'array',
            'interpretation_json' => 'array',
            'ai_powered' => 'boolean',
            'share_hide_birth_info' => 'boolean',
            'is_favorite' => 'boolean',
            'share_revoked_at' => 'datetime',
            'version_number' => 'integer',
            'viewed_year' => 'integer',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(TuViProfile::class, 'profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(TuViChartFollowup::class, 'chart_id')->orderBy('created_at');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(TuViChartJournal::class, 'chart_id')->orderByDesc('created_at');
    }

    public function isSharedActive(): bool
    {
        return $this->privacy_mode === 'shared_card'
            && ! empty($this->share_token)
            && $this->share_revoked_at === null;
    }
}
