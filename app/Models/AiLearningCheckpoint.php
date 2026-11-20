<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLearningCheckpoint extends Model
{
    protected $table = 'ai_learning_checkpoints';

    protected $fillable = [
        'source_key',
        'domain_level',
        'last_cursor',
        'last_source_updated_at',
        'content_hash',
        'records_scanned',
        'last_run_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'domain_level' => 'integer',
            'last_cursor' => 'integer',
            'last_source_updated_at' => 'datetime',
            'records_scanned' => 'integer',
            'last_run_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
