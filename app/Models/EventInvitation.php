<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_slug',
        'category_slug',
        'slug',
        'title',
        'event_badge',
        'host_name',
        'sub_host_name',
        'invitation_message',
        'event_date',
        'welcome_time',
        'party_time',
        'lunar_note',
        'venue_name',
        'venue_address',
        'map_url',
        'dresscode_note',
        'color_palette',
        'enable_rsvp',
        'enable_gift_qr',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'gift_note',
        'custom_data',
        'status',
        'published_at',
        'view_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'enable_rsvp' => 'boolean',
            'enable_gift_qr' => 'boolean',
            'custom_data' => 'array',
            'published_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(EventInvitationResponse::class, 'event_invitation_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    public function getFormattedEventDateAttribute(): string
    {
        if (! $this->event_date) {
            return 'Chưa chọn ngày';
        }

        return Carbon::parse($this->event_date)->format('d . m . Y');
    }
}
