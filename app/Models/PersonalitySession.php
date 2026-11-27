<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalitySession extends Model
{
    public const MODEL_SIXTEEN_TYPES = 'sixteen_types';

    public const MODEL_BIG_FIVE = 'big_five';

    public const STATUS_CREATED = 'created';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_SCORED = 'scored';

    public const STATUS_REPORT_READY = 'report_ready';

    public const STATUS_ABANDONED = 'abandoned';

    public const STATUS_EXPIRED = 'expired';

    protected $table = 'personality_sessions';

    protected $fillable = [
        'user_id',
        'guest_token',
        'session_token',
        'slug',
        'share_token',
        'model_code',
        'assessment_version',
        'method_version',
        'scoring_version',
        'status',
        'display_name',
        'question_count',
        'answered_count',
        'duration_seconds',
        'question_order_json',
        'responses_json',
        'scores_json',
        'suggested_type',
        'best_fit_type',
        'possible_types_json',
        'boundary_flags_json',
        'quality_flags_json',
        'calculation_hash',
        'ai_report_json',
        'ai_status',
        'growth_plan_json',
        'user_notes',
        'is_shared',
        'started_at',
        'submitted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'question_count' => 'integer',
            'answered_count' => 'integer',
            'duration_seconds' => 'integer',
            'question_order_json' => 'array',
            'responses_json' => 'array',
            'scores_json' => 'array',
            'possible_types_json' => 'array',
            'boundary_flags_json' => 'array',
            'quality_flags_json' => 'array',
            'ai_report_json' => 'array',
            'growth_plan_json' => 'array',
            'is_shared' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(PersonalityFollowup::class, 'session_id')->latest();
    }

    public function effectiveType(): ?string
    {
        $best = trim((string) ($this->best_fit_type ?? ''));
        if ($best !== '') {
            return strtoupper($best);
        }

        $suggested = trim((string) ($this->suggested_type ?? ''));

        return $suggested !== '' ? strtoupper($suggested) : null;
    }
}
