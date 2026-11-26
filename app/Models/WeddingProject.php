<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeddingProject extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'groom_name',
        'bride_name',
        'groom_birth_year',
        'bride_birth_year',
        'wedding_date',
        'status',
        'region_style',
        'wedding_style',
        'budget_total_vnd',
        'guest_target',
        'invitation_id',
        'candidates_json',
        'floorplan_json',
        'ceremony_notes',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'budget_total_vnd' => 'integer',
            'guest_target' => 'integer',
            'groom_birth_year' => 'integer',
            'bride_birth_year' => 'integer',
            'candidates_json' => 'array',
            'floorplan_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WeddingTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(WeddingExpense::class)->orderBy('id');
    }

    public function timelineItems(): HasMany
    {
        return $this->hasMany(WeddingTimelineItem::class)->orderBy('start_time')->orderBy('sort_order');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(WeddingVendor::class)->orderBy('id');
    }

    public function scripts(): HasMany
    {
        return $this->hasMany(WeddingScript::class)->latest();
    }
}
