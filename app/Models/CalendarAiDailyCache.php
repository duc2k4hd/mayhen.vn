<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarAiDailyCache extends Model
{
    protected $table = 'calendar_ai_daily_caches';

    protected $fillable = [
        'solar_date',
        'rule_version',
        'payload_json',
        'ai_model',
    ];

    protected $casts = [
        'payload_json' => 'array',
    ];
}
