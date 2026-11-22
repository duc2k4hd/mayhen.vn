<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventInvitationResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_invitation_id',
        'guest_name',
        'guest_phone',
        'attendance_status',
        'guests_count',
        'children_count',
        'wish_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'guests_count' => 'integer',
            'children_count' => 'integer',
        ];
    }

    public function eventInvitation(): BelongsTo
    {
        return $this->belongsTo(EventInvitation::class, 'event_invitation_id');
    }
}
