<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingExpense extends Model
{
    protected $fillable = [
        'wedding_project_id',
        'category',
        'title',
        'payer_side',
        'status',
        'budgeted_vnd',
        'estimated_vnd',
        'committed_vnd',
        'deposit_vnd',
        'paid_vnd',
        'refunded_vnd',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'budgeted_vnd' => 'integer',
            'estimated_vnd' => 'integer',
            'committed_vnd' => 'integer',
            'deposit_vnd' => 'integer',
            'paid_vnd' => 'integer',
            'refunded_vnd' => 'integer',
            'due_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class, 'wedding_project_id');
    }
}
