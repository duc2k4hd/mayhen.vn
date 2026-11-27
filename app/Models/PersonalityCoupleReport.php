<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityCoupleReport extends Model
{
    public const STATUS_WAITING_PARTNER = 'waiting_partner';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'personality_couple_reports';

    protected $fillable = [
        'slug',
        'invite_code',
        'user_a_id',
        'user_b_id',
        'session_a_id',
        'session_b_id',
        'partner_a_name',
        'partner_b_name',
        'type_a',
        'type_b',
        'status',
        'share_consent_a',
        'share_consent_b',
        'couple_answers_a_json',
        'couple_answers_b_json',
        'comparison_json',
        'ai_synthesis_json',
    ];

    protected function casts(): array
    {
        return [
            'share_consent_a' => 'boolean',
            'share_consent_b' => 'boolean',
            'couple_answers_a_json' => 'array',
            'couple_answers_b_json' => 'array',
            'comparison_json' => 'array',
            'ai_synthesis_json' => 'array',
        ];
    }

    public function sessionA(): BelongsTo
    {
        return $this->belongsTo(PersonalitySession::class, 'session_a_id');
    }

    public function sessionB(): BelongsTo
    {
        return $this->belongsTo(PersonalitySession::class, 'session_b_id');
    }
}
