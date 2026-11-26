<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolInsightSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tool_key',
        'intent_category',
        'region_or_topic',
        'user_id',
        'user_plan_tier',
        'session_hash',
        'user_inputs_json',
        'analysis_summary_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'user_inputs_json' => 'array',
            'analysis_summary_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
