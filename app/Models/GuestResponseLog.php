<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestResponseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_response_id',
        'wedding_id',
        'guest_id',
        'previous_status',
        'new_status',
        'previous_adults',
        'new_adults',
        'previous_children',
        'new_children',
        'previous_dietary',
        'new_dietary',
        'previous_wishes',
        'new_wishes',
        'source',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'previous_adults' => 'integer',
            'new_adults' => 'integer',
            'previous_children' => 'integer',
            'new_children' => 'integer',
        ];
    }

    public function guestResponse(): BelongsTo
    {
        return $this->belongsTo(GuestResponse::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
