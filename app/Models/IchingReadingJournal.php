<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IchingReadingJournal extends Model
{
    protected $fillable = [
        'reading_id',
        'user_id',
        'milestone_days',
        'actions_taken',
        'progress_note',
        'mindset_shift',
        'lessons_learned',
    ];

    protected function casts(): array
    {
        return [
            'milestone_days' => 'integer',
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
