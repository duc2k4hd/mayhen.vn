<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyTree extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'family_surname',
        'ancestral_hall',
        'template_style',
        'layout_mode',
        'intro_chronicle',
        'clan_rules',
        'members_json',
        'visibility',
        'access_password',
        'plan_slug',
        'expires_at',
        'view_count',
        'is_published',
    ];

    protected $hidden = [
        'access_password',
    ];

    protected function casts(): array
    {
        return [
            'members_json' => 'array',
            'expires_at' => 'datetime',
            'view_count' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
