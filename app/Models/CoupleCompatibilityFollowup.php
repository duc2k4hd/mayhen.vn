<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleCompatibilityFollowup extends Model
{
    protected $fillable = [
        'report_id',
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

    public function report(): BelongsTo
    {
        return $this->belongsTo(CoupleCompatibilityReport::class, 'report_id');
    }
}
