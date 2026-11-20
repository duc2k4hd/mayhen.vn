<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiResearchGoal extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    protected $table = 'ai_research_goals';

    protected $fillable = [
        'slug',
        'title',
        'domain',
        'description',
        'priority',
        'country',
        'language',
        'max_depth',
        'autonomy_level',
        'allowed_domains',
        'disallowed_domains',
        'daily_url_limit',
        'allow_auto_expand',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'max_depth' => 'integer',
            'allowed_domains' => 'array',
            'disallowed_domains' => 'array',
            'daily_url_limit' => 'integer',
            'allow_auto_expand' => 'boolean',
        ];
    }

    public function topics(): HasMany
    {
        return $this->hasMany(AiResearchTopic::class, 'goal_id');
    }
}
