<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaziFollowup extends Model
{
    protected $fillable = [
        'bazi_chart_id',
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

    public function chart(): BelongsTo
    {
        return $this->belongsTo(BaziChart::class, 'bazi_chart_id');
    }
}
