<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'guest_id',
        'uploader_name',
        'photo_url',
        'file_size',
        'caption',
        'is_approved',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_approved' => 'boolean',
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

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
}
