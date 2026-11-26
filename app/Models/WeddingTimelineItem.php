<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingTimelineItem extends Model
{
    protected $fillable = [
        'wedding_project_id',
        'event_group',
        'title',
        'start_time',
        'end_time',
        'location',
        'assignee',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class, 'wedding_project_id');
    }
}
