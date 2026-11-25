<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumerologyFollowup extends Model
{
    protected $fillable = [
        'reading_id',
        'user_id',
        'topic_code',
        'question',
        'answer',
        'ai_powered',
        'ai_model',
    ];

    protected function casts(): array
    {
        return [
            'ai_powered' => 'boolean',
        ];
    }

    public function reading(): BelongsTo
    {
        return $this->belongsTo(NumerologyReading::class, 'reading_id');
    }
}
