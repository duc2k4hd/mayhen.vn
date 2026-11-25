<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IchingReading extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'method',
        'rule_version',
        'kb_version',
        'topic',
        'sub_topic',
        'question',
        'context_note',
        'is_question_redacted',
        'is_favorite',
        'privacy_mode',
        'share_token',
        'share_revoked_at',
        'idempotency_key',
        'lines_json',
        'cast_inputs_json',
        'primary_hexagram_no',
        'mutual_hexagram_no',
        'changed_hexagram_no',
        'moving_lines_json',
        'ti_yong_analysis_json',
        'selected_texts_json',
        'guidance_state',
        'interpretation_json',
        'ai_powered',
        'ai_model',
        'ai_status',
        'feedback_rating',
        'action_checklist_json',
    ];

    protected function casts(): array
    {
        return [
            'lines_json' => 'array',
            'cast_inputs_json' => 'array',
            'moving_lines_json' => 'array',
            'ti_yong_analysis_json' => 'array',
            'selected_texts_json' => 'array',
            'interpretation_json' => 'array',
            'action_checklist_json' => 'array',
            'ai_powered' => 'boolean',
            'is_question_redacted' => 'boolean',
            'is_favorite' => 'boolean',
            'share_revoked_at' => 'datetime',
            'primary_hexagram_no' => 'integer',
            'mutual_hexagram_no' => 'integer',
            'changed_hexagram_no' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(IchingReadingFollowup::class, 'reading_id')->orderBy('created_at');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(IchingReadingJournal::class, 'reading_id')->orderByDesc('created_at');
    }

    public function isSharedActive(): bool
    {
        return $this->privacy_mode === 'shared_card'
            && ! empty($this->share_token)
            && $this->share_revoked_at === null;
    }
}
