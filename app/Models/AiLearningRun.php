<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLearningRun extends Model
{
    public const TYPE_SELF_TRAIN = 'self_train';

    public const TYPE_INSIGHT_SCAN = 'insight_scan';

    public const TYPE_REGRESSION_EVAL = 'regression_eval';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'ai_learning_runs';

    protected $fillable = [
        'run_type',
        'status',
        'triggered_by_user_id',
        'topics',
        'signals_analyzed',
        'candidates_proposed',
        'candidates_passed',
        'candidates_rejected',
        'metrics',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'signals_analyzed' => 'integer',
            'candidates_proposed' => 'integer',
            'candidates_passed' => 'integer',
            'candidates_rejected' => 'integer',
            'metrics' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function triggerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function getRunTypeLabelAttribute(): string
    {
        return match ($this->run_type) {
            self::TYPE_SELF_TRAIN => 'Multi-Agent Self-Learning',
            self::TYPE_INSIGHT_SCAN => 'Phân Tích Nhu Cầu Khách Hàng',
            self::TYPE_REGRESSION_EVAL => 'Kiểm Định Chất Lượng (Regression Eval)',
            default => $this->run_type,
        };
    }
}
