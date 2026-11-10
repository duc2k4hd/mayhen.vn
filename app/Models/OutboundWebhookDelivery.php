<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundWebhookDelivery extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'outbound_webhook_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'duration_ms',
        'error_message',
        'is_success',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_success' => 'boolean',
            'response_status' => 'integer',
            'duration_ms' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(OutboundWebhook::class, 'outbound_webhook_id');
    }
}
