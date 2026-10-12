<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'salutation',
        'group_name',
        'guest_token',
        'phone',
        'expected_attendees',
        'is_sent',
    ];

    protected function casts(): array
    {
        return [
            'expected_attendees' => 'integer',
            'is_sent' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->guest_token)) {
                $guest->guest_token = Str::random(32);
            }
        });
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function response(): HasOne
    {
        return $this->hasOne(GuestResponse::class);
    }
}
