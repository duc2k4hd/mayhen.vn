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

    public function invitationViews(): HasMany
    {
        return $this->hasMany(InvitationView::class);
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

    public function keepsake(): HasOne
    {
        return $this->hasOne(WeddingKeepsake::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
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

    public function guestDeliveries(): HasMany
    {
        return $this->hasMany(GuestDelivery::class)->latest('id');
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(WeddingBudgetItem::class)->orderBy('display_order')->orderBy('id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(WeddingVendor::class)->orderBy('display_order')->orderBy('id');
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

    /**
     * Lấy chế độ kiểm duyệt lời chúc ('auto' hoặc 'manual').
     */
    public function getWishModerationMode(): string
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);

        return $extra['wish_moderation_mode'] ?? 'auto';
    }

    /**
     * Cập nhật chế độ kiểm duyệt lời chúc ('auto' hoặc 'manual').
     */
    public function setWishModerationMode(string $mode): void
    {
        $mode = in_array($mode, ['auto', 'manual'], true) ? $mode : 'auto';
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);
        $extra['wish_moderation_mode'] = $mode;

        $this->update([
            'extra_data' => $extra,
        ]);
    }

    /**
     * Kiểm tra trạng thái bật/tắt hiển thị mã QR và thông tin mừng cưới công khai trên thiệp.
     */
    public function isGiftQrEnabled(): bool
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);

        // Mặc định là true nếu đã có thông tin ngân hàng, hoặc theo cờ cấu hình chủ động
        return (bool) ($extra['is_gift_qr_enabled'] ?? true);
    }

    /**
     * Bật hoặc tắt hiển thị mã QR mừng cưới công khai.
     */
    public function setGiftQrEnabled(bool $enabled): void
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);
        $extra['is_gift_qr_enabled'] = $enabled;

        $this->update([
            'extra_data' => $extra,
        ]);
    }

    /**
     * Lấy danh sách đa tài khoản ngân hàng nhận mừng cưới (Nhà trai / Nhà gái / Cặp đôi).
     * Tự động fallback từ cấu hình cũ nếu chưa lưu danh sách mới.
     */
    public function getBankAccounts(): array
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);

        if (! empty($extra['bank_accounts']) && is_array($extra['bank_accounts'])) {
            return $extra['bank_accounts'];
        }

        // Tự động xây dựng danh sách fallback từ các trường cũ
        $fallback = [];

        if (! empty($extra['groom_bank_account']) || ! empty($extra['groom_bank_name'])) {
            $fallback[] = [
                'id' => 'ba_groom',
                'label' => 'Nhà Trai (Chú Rể)',
                'bank_name' => $extra['groom_bank_name'] ?? 'Vietcombank',
                'bank_account_number' => $extra['groom_bank_account'] ?? '',
                'account_holder' => $extra['groom_account_name'] ?? ($this->groom_name ?? 'Chú Rể'),
                'qr_image_url' => $extra['qr_groom_image'] ?? null,
                'is_active' => true,
            ];
        }

        if (! empty($extra['bride_bank_account']) || ! empty($extra['bride_bank_name'])) {
            $fallback[] = [
                'id' => 'ba_bride',
                'label' => 'Nhà Gái (Cô Dâu)',
                'bank_name' => $extra['bride_bank_name'] ?? 'Techcombank',
                'bank_account_number' => $extra['bride_bank_account'] ?? '',
                'account_holder' => $extra['bride_account_name'] ?? ($this->bride_name ?? 'Cô Dâu'),
                'qr_image_url' => $extra['qr_bride_image'] ?? null,
                'is_active' => true,
            ];
        }

        if (empty($fallback) && (! empty($this->bank_account_number) || ! empty($this->bank_name))) {
            $fallback[] = [
                'id' => 'ba_default',
                'label' => 'Tài Khoản Mừng Cưới',
                'bank_name' => $this->bank_name ?? 'Ngân Hàng',
                'bank_account_number' => $this->bank_account_number ?? '',
                'account_holder' => $this->bank_account_name ?? ($this->groom_name.' & '.$this->bride_name),
                'qr_image_url' => null,
                'is_active' => true,
            ];
        }

        return $fallback;
    }

    /**
     * Cập nhật danh sách tài khoản ngân hàng nhận mừng cưới.
     */
    public function setBankAccounts(array $accounts): void
    {
        $extra = is_array($this->extra_data) ? $this->extra_data : (json_decode((string) $this->extra_data, true) ?? []);
        $extra['bank_accounts'] = array_values($accounts);

        // Đồng bộ ngược lại các trường phẳng truyền thống cho tương thích ngược
        if (! empty($accounts[0])) {
            $this->bank_name = $accounts[0]['bank_name'] ?? $this->bank_name;
            $this->bank_account_number = $accounts[0]['bank_account_number'] ?? $this->bank_account_number;
            $this->bank_account_name = $accounts[0]['account_holder'] ?? $this->bank_account_name;
        }

        $this->extra_data = $extra;
        $this->save();
    }

    /**
     * Masking số tài khoản ngân hàng bảo vệ an toàn PII và nguyên tắc Least Privilege (Section 35 PRD V3).
     * Ví dụ: "0123456789" -> "012****789"
     */
    public static function maskAccountNumber(?string $accountNumber): string
    {
        if (empty($accountNumber)) {
            return '—';
        }

        $clean = preg_replace('/\s+/', '', (string) $accountNumber);
        $len = strlen($clean);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        if ($len <= 7) {
            return substr($clean, 0, 2).str_repeat('*', $len - 4).substr($clean, -2);
        }

        return substr($clean, 0, 3).'****'.substr($clean, -3);
    }
}
