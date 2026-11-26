<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaziChart extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'profile_name',
        'gender',
        'method_id',
        'engine_version',
        'rule_version',
        'calendar_version',
        'calculation_hash',
        'day_master_label',
        'four_pillars_summary',
        'input_json',
        'chart_json',
        'ai_report_json',
        'notes',
        'is_shared',
        'share_token',
    ];

    protected function casts(): array
    {
        return [
            'input_json' => 'array',
            'chart_json' => 'array',
            'ai_report_json' => 'array',
            'is_shared' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followups(): HasMany
    {
        return $this->hasMany(BaziFollowup::class)->latest();
    }
}
