<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiBenchmarkRun extends Model
{
    public const PHASE_BASELINE = 'baseline';

    public const PHASE_AFTER_LEARNING = 'after_learning';

    public const PHASE_REGRESSION_CHECK = 'regression_check';

    protected $table = 'ai_benchmark_runs';

    protected $fillable = [
        'domain',
        'benchmark_phase',
        'triggered_by_user_id',
        'total_cases',
        'passed_cases',
        'grounded_score',
        'coverage_score',
        'hallucination_rate',
        'avg_latency_ms',
        'delta_vs_baseline',
        'regression_detected',
        'case_results',
        'knowledge_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'total_cases' => 'integer',
            'passed_cases' => 'integer',
            'grounded_score' => 'float',
            'coverage_score' => 'float',
            'hallucination_rate' => 'float',
            'avg_latency_ms' => 'integer',
            'delta_vs_baseline' => 'float',
            'regression_detected' => 'boolean',
            'case_results' => 'array',
            'knowledge_snapshot' => 'array',
        ];
    }
}
