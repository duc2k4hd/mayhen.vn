<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuViChartJournal extends Model
{
    protected $table = 'tuvi_chart_journals';

    protected $fillable = [
        'chart_id',
        'user_id',
        'milestone_days',
        'focus_note',
        'actions_taken',
        'lessons_learned',
    ];

    protected function casts(): array
    {
        return [
            'milestone_days' => 'integer',
        ];
    }

    public function chart(): BelongsTo
    {
        return $this->belongsTo(TuViChart::class, 'chart_id');
    }
}
