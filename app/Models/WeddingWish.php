<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingWish extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'guest_id',
        'name',
        'message',
        'reply',
        'replied_at',
        'is_approved',
        'is_pinned',
        'is_private',
        'signature_url',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'is_pinned' => 'boolean',
            'is_private' => 'boolean',
            'replied_at' => 'datetime',
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

    /**
     * Scope lọc lời chúc hiển thị công khai trên thiệp cưới.
     */
    public function scopeVisibleOnInvitation(Builder $query): Builder
    {
        return $query->where('is_approved', true)->where('is_private', false);
    }

    public function getSenderNameAttribute(): string
    {
        return $this->name ?? 'Quan Khách';
    }
}
