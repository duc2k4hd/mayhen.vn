<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingWish extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'message',
        'reply',
        'replied_at',
        'is_approved',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'replied_at' => 'datetime',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }
}
