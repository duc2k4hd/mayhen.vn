<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuViChartFollowup extends Model
{
    protected $table = 'tuvi_chart_followups';

    protected $fillable = [
        'chart_id',
        'user_id',
        'palace_code',
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

    public function chart(): BelongsTo
    {
        return $this->belongsTo(TuViChart::class, 'chart_id');
    }
}
