<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarotReading extends Model
{
    protected $table = 'tarot_readings';

    protected $fillable = [
        'user_id',
        'guest_session_id',
        'session_token',
        'slug',
        'status',
        'deck_id',
        'deck_version',
        'spread_id',
        'spread_version',
        'randomization_version',
        'orientation_mode',
        'selection_mode',
        'category',
        'relationship_stage',
        'question',
        'is_question_redacted',
        'context_note',
        'commitment_hash',
        'commitment_nonce',
        'shuffled_deck_json',
        'selected_cards_json',
        'interpretation_json',
        'ai_powered',
        'ai_model',
        'ai_status',
        'privacy_mode',
        'share_token',
        'share_hide_question',
        'feedback_rating',
        'is_favorite',
        'daily_key',
        'idempotency_key',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'shuffled_deck_json' => 'array',
            'selected_cards_json' => 'array',
            'interpretation_json' => 'array',
            'is_question_redacted' => 'boolean',
            'ai_powered' => 'boolean',
            'share_hide_question' => 'boolean',
            'is_favorite' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(TarotReadingFollowup::class, 'tarot_reading_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(TarotReadingJournal::class, 'tarot_reading_id');
    }

    public function isSharedActive(): bool
    {
        return $this->privacy_mode === 'shared_card' && ! empty($this->share_token);
    }
}
