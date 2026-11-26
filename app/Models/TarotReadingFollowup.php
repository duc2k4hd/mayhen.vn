<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarotReadingFollowup extends Model
{
    protected $table = 'tarot_reading_followups';

    protected $fillable = [
        'tarot_reading_id',
        'user_id',
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
        return $this->belongsTo(TarotReading::class, 'tarot_reading_id');
    }
}
