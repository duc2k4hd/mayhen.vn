<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarFamilyEvent extends Model
{
    protected $table = 'calendar_family_events';

    protected $fillable = [
        'user_id',
        'title',
        'event_type',
        'calendar_type',
        'event_day',
        'event_month',
        'event_year',
        'is_leap_month',
        'is_recurring',
        'short_month_rule',
        'leap_month_rule',
        'notes',
    ];

    protected $casts = [
        'event_day' => 'integer',
        'event_month' => 'integer',
        'event_year' => 'integer',
        'is_leap_month' => 'boolean',
        'is_recurring' => 'boolean',
    ];
}
