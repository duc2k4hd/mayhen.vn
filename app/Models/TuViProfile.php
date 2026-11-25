<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TuViProfile extends Model
{
    use SoftDeletes;

    protected $table = 'tuvi_profiles';

    protected $fillable = [
        'user_id',
        'slug',
        'nickname',
        'relationship_label',
        'gender_yin_yang',
        'calendar_type',
        'birth_year',
        'birth_month',
        'birth_day',
        'is_leap_month',
        'birth_time_mode',
        'birth_hour',
        'birth_minute',
        'birth_branch_index',
        'birth_time_window',
        'birth_timezone',
        'school_code',
        'school_version',
        'leap_month_rule',
        'late_rat_rule',
        'is_primary_self',
    ];

    protected function casts(): array
    {
        return [
            'is_leap_month' => 'boolean',
            'is_primary_self' => 'boolean',
            'birth_year' => 'integer',
            'birth_month' => 'integer',
            'birth_day' => 'integer',
            'birth_hour' => 'integer',
            'birth_minute' => 'integer',
            'birth_branch_index' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function charts(): HasMany
    {
        return $this->hasMany(TuViChart::class, 'profile_id')->orderByDesc('version_number');
    }
}
