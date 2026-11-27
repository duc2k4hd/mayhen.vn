<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityFollowup extends Model
{
    protected $table = 'personality_followups';

    protected $fillable = [
        'session_id',
        'user_id',
        'topic_code',
        'question',
        'answer',
        'evidence_refs_json',
    ];

    protected function casts(): array
    {
        return [
            'evidence_refs_json' => 'array',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PersonalitySession::class, 'session_id');
    }
}
