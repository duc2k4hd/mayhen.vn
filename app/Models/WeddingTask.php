<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingTask extends Model
{
    protected $fillable = [
        'wedding_project_id',
        'code',
        'phase',
        'title',
        'due_date',
        'priority',
        'status',
        'assignee',
        'depends_on_task_id',
        'sort_order',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'sort_order' => 'integer',
            'depends_on_task_id' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class, 'wedding_project_id');
    }
}
