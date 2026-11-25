<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IchingReading extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'method',
        'topic',
        'question',
        'lines_json',
        'primary_hexagram_no',
        'mutual_hexagram_no',
        'changed_hexagram_no',
        'moving_lines_json',
        'ti_yong_analysis_json',
        'interpretation_json',
        'ai_powered',
        'ai_model',
    ];

    protected function casts(): array
    {
        return [
            'lines_json' => 'array',
            'moving_lines_json' => 'array',
            'ti_yong_analysis_json' => 'array',
            'interpretation_json' => 'array',
            'ai_powered' => 'boolean',
            'primary_hexagram_no' => 'integer',
            'mutual_hexagram_no' => 'integer',
            'changed_hexagram_no' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
