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
        'reminded_at',
        'reminder_count',
        'max_attendees',
        'event_ids',
        'revoked_tokens',
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
            'reminded_at' => 'datetime',
            'reminder_count' => 'integer',
            'open_count' => 'integer',
            'max_attendees' => 'integer',
            'event_ids' => 'array',
            'revoked_tokens' => 'array',
        ];
    }

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function isOpened(): bool
    {
        return $this->open_count > 0 || $this->opened_at !== null;
    }

    /**
     * Danh sách cụm động từ lời mời (không ghép lặp lên tên người nhận).
     *
     * @var list<string>
     */
    private const INVITATION_PHRASES = [
        'kính mời',
        'thân mời',
        'thương mời',
        'trân trọng kính mời',
        'trân trọng thân mời',
    ];

    /**
     * Trả về lời mời hiển thị phía trên tên khách (ví dụ: "Kính mời", "Thân mời", "Thương mời").
     */
    public function displayInvitePhrase(string $default = 'Kính mời'): string
    {
        $salutation = trim((string) ($this->salutation ?? ''));
        if ($salutation === '') {
            return $default;
        }

        if (in_array(mb_strtolower($salutation, 'UTF-8'), self::INVITATION_PHRASES, true)) {
            return $salutation;
        }

        return $default;
    }

    /**
     * Trả về tên người nhận sạch sẽ trên thiệp (tự động chống lặp "Kính mời Kính mời...").
     */
    public function displayRecipientName(): string
    {
        $name = trim((string) ($this->name ?? ''));
        $salutation = trim((string) ($this->salutation ?? ''));

        if ($name === '') {
            return 'Quý khách';
        }

        if ($salutation === '') {
            return $name;
        }

        $lowerSalutation = mb_strtolower($salutation, 'UTF-8');
        if (in_array($lowerSalutation, self::INVITATION_PHRASES, true)) {
            return $name;
        }

        $lowerName = mb_strtolower($name, 'UTF-8');
        if (str_starts_with($lowerName, $lowerSalutation.' ') || $lowerName === $lowerSalutation) {
            return $name;
        }

        return "{$salutation} {$name}";
    }

    /**
     * Trả về câu chào mời đầy đủ dùng cho Open Graph (Zalo/Facebook) & tin nhắn mẫu.
     */
    public function formattedFullGreeting(): string
    {
        return trim($this->displayInvitePhrase().' '.$this->displayRecipientName());
    }

    /**
     * Số lượng người tối đa khách được phép xác nhận tham dự.
     */
    public function getAllowedMaxAttendees(): int
    {
        return (int) ($this->max_attendees ?: ($this->expected_attendees ?: 1));
    }

    /**
     * Kiểm tra khách có được phân quyền tham dự sự kiện cụ thể hay không.
     */
    public function isEventAllowed(?int $eventId): bool
    {
        if ($eventId === null || empty($this->event_ids)) {
            return true;
        }

        return in_array((int) $eventId, array_map('intval', (array) $this->event_ids), true);
    }

    /**
     * Thu hồi token hiện tại và tái tạo token mới cho khách mời.
     */
    public function revokeAndRegenerateToken(): string
    {
        $oldToken = $this->guest_token;
        $revoked = (array) ($this->revoked_tokens ?? []);
        if (! empty($oldToken) && ! in_array($oldToken, $revoked, true)) {
            $revoked[] = $oldToken;
        }

        $newToken = Str::random(32);
        $this->update([
            'guest_token' => $newToken,
            'revoked_tokens' => $revoked,
        ]);

        return $newToken;
    }

    /**
     * Kiểm tra token có nằm trong danh sách đã bị thu hồi hay không.
     */
    public function isTokenRevoked(string $token): bool
    {
        $revoked = (array) ($this->revoked_tokens ?? []);

        return in_array($token, $revoked, true);
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
     * Sĩ số tham dự thực tế dựa trên xác nhận RSVP (hỗ trợ đa sự kiện) hoặc dự kiến ban đầu.
     */
    public function actualAttendeesCount(?int $eventId = null): int
    {
        if ($eventId !== null) {
            $eventResponse = $this->relationLoaded('responses')
                ? $this->responses->firstWhere('event_id', $eventId)
                : $this->responses()->where('event_id', $eventId)->first();

            if ($eventResponse) {
                if ($eventResponse->attending_status === 'attending') {
                    return max(1, (int) ($eventResponse->adults_count + $eventResponse->children_count));
                }
                if ($eventResponse->attending_status === 'declined') {
                    return 0;
                }
            }

            return max(1, (int) ($this->expected_attendees ?: 1));
        }

        $responses = $this->relationLoaded('responses')
            ? $this->responses
            : $this->responses()->get();

        if ($responses->isNotEmpty()) {
            $attendingRows = $responses->where('attending_status', 'attending');
            if ($attendingRows->isNotEmpty()) {
                $maxTotal = $attendingRows->max(fn (GuestResponse $r) => (int) $r->adults_count + (int) $r->children_count);

                return max(1, (int) $maxTotal);
            }

            if ($responses->every(fn (GuestResponse $r) => $r->attending_status === 'declined')) {
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
                    $table = WeddingTable::where('wedding_id', $guest->wedding_id)->find($guest->table_id);
                    if ($table) {
                        $guest->table_name = $table->name;
                    } else {
                        $guest->table_id = null;
                        $guest->table_name = null;
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
                $table = WeddingTable::where('wedding_id', $guest->wedding_id)->find($guest->table_id);
                $guest->table_name = $table?->name;
                if (! $table) {
                    $guest->table_id = null;
                }
            } elseif (! empty($guest->table_name) && $guest->table_id === null && $guest->wedding_id) {
                $matchedId = WeddingTable::where('wedding_id', $guest->wedding_id)
                    ->where('name', trim((string) $guest->table_name))
                    ->value('id');
                $guest->table_id = $matchedId;
            }
        });

        static::deleting(function (Guest $guest) {
            GuestPhoto::where('guest_id', $guest->id)->update(['guest_id' => null]);
            WeddingWish::where('guest_id', $guest->id)->update(['guest_id' => null]);
            $guest->responses()->delete();
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
        return $this->hasOne(GuestResponse::class)
            ->orderByRaw("CASE WHEN attending_status = 'attending' THEN 0 WHEN attending_status = 'uncertain' THEN 1 ELSE 2 END")
            ->latest('id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(GuestResponse::class);
    }

    public function responseForEvent(?int $eventId = null): ?GuestResponse
    {
        if ($eventId === null) {
            return $this->responses()->whereNull('event_id')->first() ?? $this->responses()->first();
        }

        return $this->responses()->where('event_id', $eventId)->first();
    }

    public function responseLogs(): HasMany
    {
        return $this->hasMany(GuestResponseLog::class)->latest();
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(WeddingGift::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(GuestDelivery::class);
    }
}
