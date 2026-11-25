<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuViSynastryReport extends Model
{
    protected $table = 'tuvi_synastry_reports';

    protected $fillable = [
        'user_id',
        'slug',
        'chart_a_id',
        'chart_b_id',
        'partner_consent_confirmed',
        'couple_answers_json',
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

    public function chartA(): BelongsTo
    {
        return $this->belongsTo(TuViChart::class, 'chart_a_id');
    }

    public function chartB(): BelongsTo
    {
        return $this->belongsTo(TuViChart::class, 'chart_b_id');
    }
}
