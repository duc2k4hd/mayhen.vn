<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumerologyJournal extends Model
{
    protected $fillable = [
        'reading_id',
        'user_id',
        'milestone_days',
        'current_goal',
        'actions_taken',
        'lessons_learned',
        'relationship_note',
    ];

    public function reading(): BelongsTo
    {
        return $this->belongsTo(NumerologyReading::class, 'reading_id');
    }
}
