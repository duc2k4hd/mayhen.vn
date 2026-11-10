<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'salutation',
        'group_name',
        'table_name',
        'table_id',
        'guest_token',
        'phone',
        'expected_attendees',
        'checked_in_at',
        'checked_in_attendees',
        'check_in_note',
        'is_sent',
        'opened_at',
        'last_opened_at',
        'open_count',
        'last_device',
        'sent_at',
    ];

    protected $attributes = [
        'is_sent' => false,
        'open_count' => 0,
    ];

    protected function casts(): array
    {
        return [
            'expected_attendees' => 'integer',
            'checked_in_attendees' => 'integer',
            'checked_in_at' => 'datetime',
            'is_sent' => 'boolean',
            'opened_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'sent_at' => 'datetime',
            'open_count' => 'integer',
        ];
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function isOpened(): bool
    {
        return $this->open_count > 0 || $this->opened_at !== null;
    }

    /**
     * Ghi nhận lượt mở thiệp của khách mời.
     */
    public function recordOpen(?string $userAgent = null): void
    {
        $device = $this->detectDevice($userAgent);

        $now = now();
        $this->update([
            'opened_at' => $this->opened_at ?: $now,
            'last_opened_at' => $now,
            'open_count' => $this->open_count + 1,
            'last_device' => $device,
        ]);
    }

    /**
     * Nhận diện thiết bị truy cập từ User-Agent.
     */
    public function detectDevice(?string $userAgent): string
    {
        if (empty($userAgent)) {
            return 'Không xác định';
        }

        $ua = strtolower($userAgent);

        if (str_contains($ua, 'iphone')) {
            return 'iPhone (iOS)';
        }
        if (str_contains($ua, 'ipad')) {
            return 'iPad (iPadOS)';
        }
        if (str_contains($ua, 'android')) {
            return 'Android Mobile';
        }
        if (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os x')) {
            return 'Mac Desktop';
        }
        if (str_contains($ua, 'windows')) {
            return 'Windows PC';
        }

        return 'Trình duyệt khác';
    }

    /**
     * Sĩ số tham dự thực tế dựa trên xác nhận RSVP hoặc dự kiến ban đầu.
     */
    public function actualAttendeesCount(): int
    {
        $response = $this->response;

        if ($response) {
            if ($response->attending_status === 'attending') {
                $total = (int) ($response->adults_count + $response->children_count);

                return max(1, $total);
            }

            if ($response->attending_status === 'declined') {
                return 0;
            }
        }

        return max(1, (int) ($this->expected_attendees ?: 1));
    }

    protected static function booted(): void
    {
        static::creating(function (Guest $guest) {
            if (empty($guest->guest_token)) {
                $guest->guest_token = Str::random(32);
            }
        });

        static::saving(function (Guest $guest) {
            if ($guest->isDirty('table_id') && ! $guest->isDirty('table_name')) {
                if ($guest->table_id === null) {
                    $guest->table_name = null;
                } else {
                    $table = WeddingTable::find($guest->table_id);
                    if ($table) {
                        $guest->table_name = $table->name;
                    }
                }
            } elseif ($guest->isDirty('table_name') && ! $guest->isDirty('table_id')) {
                if (empty($guest->table_name)) {
                    $guest->table_name = null;
                    $guest->table_id = null;
                } elseif ($guest->wedding_id) {
                    $matchedId = WeddingTable::where('wedding_id', $guest->wedding_id)
                        ->where('name', trim((string) $guest->table_name))
                        ->value('id');
                    $guest->table_id = $matchedId;
                }
            } elseif (empty($guest->table_name) && $guest->table_id !== null) {
                $table = WeddingTable::find($guest->table_id);
                $guest->table_name = $table?->name;
            } elseif (! empty($guest->table_name) && $guest->table_id === null && $guest->wedding_id) {
                $matchedId = WeddingTable::where('wedding_id', $guest->wedding_id)
                    ->where('name', trim((string) $guest->table_name))
                    ->value('id');
                $guest->table_id = $matchedId;
            }
        });

        static::deleting(function (Guest $guest) {
            $guest->response()->delete();
        });
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(WeddingTable::class, 'table_id');
    }

    public function response(): HasOne
    {
        return $this->hasOne(GuestResponse::class);
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(WeddingGift::class);
    }
}
