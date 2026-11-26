<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingScript extends Model
{
    protected $fillable = [
        'wedding_project_id',
        'user_id',
        'script_type',
        'title',
        'content',
        'source',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(WeddingProject::class, 'wedding_project_id');
    }
}
