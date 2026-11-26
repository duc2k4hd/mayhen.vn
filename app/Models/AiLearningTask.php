<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLearningTask extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_DEAD_LETTER = 'dead_letter';

    public const STATUS_SKIPPED = 'skipped';

    public const TYPE_LEARN_DOMAIN_MODULE = 'learn_domain_module';

    public const TYPE_BUILD_GRAPH_RELATION = 'build_graph_relation';

    public const TYPE_ANALYZE_KNOWLEDGE_GAP = 'analyze_knowledge_gap';

    public const TYPE_EVALUATE_REGRESSION = 'evaluate_regression';

    // V3 Autonomous Web Research Task Types
    public const TYPE_RESEARCH_DISCOVER = 'research_discover';

    public const TYPE_RESEARCH_EXTRACT = 'research_extract';

    public const TYPE_RESEARCH_VERIFY = 'research_verify';

    public const TYPE_RESEARCH_PROMOTE = 'research_promote';

    public const TYPE_RESEARCH_EXPAND = 'research_expand';

    protected $table = 'ai_learning_tasks';

    protected $fillable = [
        'idempotency_key',
        'domain_level',
        'task_type',
        'topic',
        'priority',
        'status',
        'attempts',
        'max_attempts',
        'next_retry_at',
        'locked_by',
        'locked_at',
        'fencing_token',
        'payload',
        'result',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'domain_level' => 'integer',
            'priority' => 'integer',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
            'fencing_token' => 'integer',
            'next_retry_at' => 'datetime',
            'locked_at' => 'datetime',
            'payload' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
