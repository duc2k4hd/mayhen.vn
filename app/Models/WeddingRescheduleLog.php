<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingRescheduleLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'invitation_id',
        'old_date',
        'new_date',
        'old_venue',
        'new_venue',
        'reason',
        'rsvp_action',
        'affected_guests_count',
        'notified_guests_count',
    ];

    protected function casts(): array
    {
        return [
            'old_date' => 'date',
            'new_date' => 'date',
            'affected_guests_count' => 'integer',
            'notified_guests_count' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function getRsvpActionLabelAttribute(): string
    {
        return match ($this->rsvp_action) {
            'request_reconfirm' => 'Yêu cầu quan khách xác nhận lại',
            default => 'Giữ nguyên phản hồi cũ',
        };
    }
}
