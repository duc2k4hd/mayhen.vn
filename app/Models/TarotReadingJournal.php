<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarotReadingJournal extends Model
{
    protected $table = 'tarot_reading_journals';

    protected $fillable = [
        'tarot_reading_id',
        'user_id',
        'feeling_note',
        'realization_note',
        'action_note',
        'outcome_note',
    ];

    public function reading(): BelongsTo
    {
        return $this->belongsTo(TarotReading::class, 'tarot_reading_id');
    }
}
