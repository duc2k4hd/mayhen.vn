<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IchingReadingFollowup extends Model
{
    protected $fillable = [
        'reading_id',
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
        return $this->belongsTo(IchingReading::class, 'reading_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
