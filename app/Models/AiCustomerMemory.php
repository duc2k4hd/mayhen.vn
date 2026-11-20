<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCustomerMemory extends Model
{
    protected $table = 'ai_customer_memories';

    protected $fillable = [
        'scope_key',
        'user_id',
        'session_hash',
        'preferred_styles',
        'budget_tier',
        'recent_topics',
        'last_intent',
        'interaction_count',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_styles' => 'array',
            'recent_topics' => 'array',
            'interaction_count' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
