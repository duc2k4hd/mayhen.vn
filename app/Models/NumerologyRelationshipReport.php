<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumerologyRelationshipReport extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'reading_a_id',
        'reading_b_id',
        'relationship_stage',
        'partner_consent_confirmed',
        'couple_answers_json',
        'promise_30_days_json',
        'couple_messages_json',
        'comparison_payload_json',
        'interpretation_json',
        'ai_powered',
        'ai_model',
        'privacy_mode',
        'share_token',
        'share_revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'partner_consent_confirmed' => 'boolean',
            'couple_answers_json' => 'array',
            'promise_30_days_json' => 'array',
            'couple_messages_json' => 'array',
            'comparison_payload_json' => 'array',
            'interpretation_json' => 'array',
            'ai_powered' => 'boolean',
            'share_revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readingA(): BelongsTo
    {
        return $this->belongsTo(NumerologyReading::class, 'reading_a_id');
    }

    public function readingB(): BelongsTo
    {
        return $this->belongsTo(NumerologyReading::class, 'reading_b_id');
    }
}
