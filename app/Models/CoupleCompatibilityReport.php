<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoupleCompatibilityReport extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'husband_can_chi',
        'wife_can_chi',
        'cao_ly_id',
        'du_nien_code',
        'engine_version',
        'calculation_hash',
        'report_json',
        'ai_report_json',
        'is_shared',
        'share_token',
    ];

    protected function casts(): array
    {
        return [
            'report_json' => 'array',
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
        return $this->hasMany(CoupleCompatibilityFollowup::class, 'report_id')->latest();
    }
}
