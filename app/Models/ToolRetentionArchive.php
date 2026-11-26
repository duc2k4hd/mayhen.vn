<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolRetentionArchive extends Model
{
    protected $fillable = [
        'tool_key',
        'source_table',
        'source_record_id',
        'user_id',
        'user_email',
        'user_plan_tier',
        'resource_slug',
        'title',
        'created_originally_at',
        'expired_at',
        'archived_at',
        'analytics_summary_json',
    ];

    protected function casts(): array
    {
        return [
            'created_originally_at' => 'datetime',
            'expired_at' => 'datetime',
            'archived_at' => 'datetime',
            'analytics_summary_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
