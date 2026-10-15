<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'groom_name',
        'bride_name',
        'groom_parents',
        'bride_parents',
        'wedding_date',
        'venue_name',
        'venue_address',
        'story',
        'cover_photo_url',
        'music_url',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'date',
            'extra_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class);
    }

    public function guestResponses(): HasMany
    {
        return $this->hasMany(GuestResponse::class);
    }

    public function wishes(): HasMany
    {
        return $this->hasMany(WeddingWish::class)->latest();
    }
}
