<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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
        'partner_id',
        'handover_code',
        'client_notes',
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

    public function invitation(): HasOne
    {
        return $this->hasOne(Invitation::class)->latestOfMany();
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

    public function gifts(): HasMany
    {
        return $this->hasMany(WeddingGift::class)->latest();
    }

    public function guestPhotos(): HasMany
    {
        return $this->hasMany(GuestPhoto::class)->latest();
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(WeddingTable::class)->orderBy('display_order')->orderBy('id');
    }

    public function domain(): HasOne
    {
        return $this->hasOne(WeddingDomain::class);
    }

    public function timeCapsules(): HasMany
    {
        return $this->hasMany(WeddingTimeCapsule::class)->orderBy('unlock_date');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WeddingEvent::class)->orderBy('display_order')->orderBy('event_date')->orderBy('event_time');
    }

    public function outboundWebhooks(): HasMany
    {
        return $this->hasMany(OutboundWebhook::class)->latest('id');
    }

    public function checklistTasks(): HasMany
    {
        return $this->hasMany(WeddingChecklistTask::class)->orderBy('display_order')->orderBy('due_date');
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(WeddingBudgetItem::class)->orderBy('display_order')->orderBy('id');
    }

    public function rescheduleLogs(): HasMany
    {
        return $this->hasMany(WeddingRescheduleLog::class)->latest('id');
    }

    public function getTargetBudgetAttribute(): int
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);

        return (int) ($extra['target_budget'] ?? 0);
    }

    /**
     * Lấy danh sách các sự kiện đang hoạt động (active), tự động fallback nếu chưa có.
     */
    public function getActiveEvents()
    {
        $events = $this->events()->where('is_active', true)->get();

        if ($events->isEmpty() && $this->wedding_date) {
            $this->syncEventsFromPrimaryDetails();

            return $this->events()->where('is_active', true)->get();
        }

        return $events;
    }

    /**
     * Đồng bộ khởi tạo sự kiện mặc định từ thông tin chính của đám cưới (bảo toàn 100% tương thích ngược).
     */
    public function syncEventsFromPrimaryDetails(): WeddingEvent
    {
        return $this->events()->firstOrCreate(
            ['wedding_id' => $this->id, 'event_type' => 'banquet'],
            [
                'name' => 'Tiệc Cưới & Mừng Hạnh Phúc',
                'event_date' => $this->wedding_date ?: now()->addDays(30),
                'event_time' => '11:00',
                'lunar_date' => $this->extra_data['event_party_lunar'] ?? null,
                'venue_name' => $this->venue_name ?: 'Trung Tâm Tiệc Cưới',
                'venue_address' => $this->venue_address ?: ($this->extra_data['event_party_address'] ?? null),
                'display_order' => 1,
                'is_active' => true,
            ]
        );
    }

    /**
     * Sinh mã bàn giao mới có thời hạn hiệu lực (mặc định 30 ngày) cho Studio/Agency.
     */
    public function generateHandoverCode(int $daysValid = 30): string
    {
        $code = strtoupper(Str::random(6));
        $extra = $this->extra_data ?? [];
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $extra['handover_generated_at'] = $now->toIso8601String();
        $extra['handover_expires_at'] = $now->copy()->addDays($daysValid)->toIso8601String();
        unset($extra['handover_revoked_at']);

        $this->update([
            'handover_code' => $code,
            'extra_data' => $extra,
        ]);

        return $code;
    }

    /**
     * Kiểm tra mã bàn giao đã hết hạn hiệu lực hay chưa.
     */
    public function isHandoverCodeExpired(): bool
    {
        if (! $this->handover_code) {
            return false;
        }

        $expiresAt = $this->extra_data['handover_expires_at'] ?? null;
        if (! $expiresAt) {
            return false;
        }

        return Carbon::parse($expiresAt)->isPast();
    }

    /**
     * Lấy mốc thời gian hết hạn của mã bàn giao (nếu có).
     */
    public function getHandoverExpiresAt(): ?Carbon
    {
        $expiresAt = $this->extra_data['handover_expires_at'] ?? null;

        return $expiresAt ? Carbon::parse($expiresAt) : null;
    }

    /**
     * Thu hồi mã bàn giao, ngăn chặn việc claim ngoài ý muốn.
     */
    public function revokeHandoverCode(): void
    {
        $extra = $this->extra_data ?? [];
        $extra['handover_revoked_at'] = Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String();

        $this->update([
            'handover_code' => null,
            'extra_data' => $extra,
        ]);
    }
}
