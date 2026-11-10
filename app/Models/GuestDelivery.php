<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'guest_id',
        'channel',
        'status',
        'recipient',
        'message_body',
        'sent_at',
        'error_message',
        'batch_id',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
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

    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
