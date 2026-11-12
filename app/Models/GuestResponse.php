<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'guest_id',
        'event_id',
        'guest_name',
        'phone',
        'attending_status',
        'needs_reconfirmation',
        'reconfirmed_at',
        'is_late',
        'adults_count',
        'children_count',
        'shuttle_bus_needed',
        'shuttle_pickup_point',
        'shuttle_passengers',
        'menu_preference',
        'dietary_requirements',
        'wishes',
        'is_wishes_public',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'adults_count' => 'integer',
            'children_count' => 'integer',
            'shuttle_bus_needed' => 'boolean',
            'shuttle_passengers' => 'integer',
            'is_wishes_public' => 'boolean',
            'needs_reconfirmation' => 'boolean',
            'is_late' => 'boolean',
            'reconfirmed_at' => 'datetime',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(WeddingEvent::class, 'event_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GuestResponseLog::class)->latest('id');
    }
}
