<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class NumerologyProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'display_name',
        'full_birth_name',
        'everyday_name',
        'name_groups_json',
        'y_mode',
        'y_overrides_json',
        'birth_date',
        'calendar_input_type',
        'relationship_status',
        'method_id',
        'method_version',
        'is_primary_self',
    ];

    protected function casts(): array
    {
        return [
            'name_groups_json' => 'array',
            'y_overrides_json' => 'array',
            'birth_date' => 'date:Y-m-d',
            'is_primary_self' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(NumerologyReading::class, 'profile_id');
    }
}
