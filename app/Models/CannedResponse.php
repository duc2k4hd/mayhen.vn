<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends Model
{
    use HasFactory;

    public const CATEGORY_TEMPLATE = 'template';

    public const CATEGORY_BILLING = 'billing';

    public const CATEGORY_TECHNICAL = 'technical';

    public const CATEGORY_INVITATION = 'invitation';

    public const CATEGORY_GENERAL = 'general';

    public const CATEGORIES = [
        self::CATEGORY_TEMPLATE => [
            'label' => 'Mẫu Thiệp & Âm Nhạc',
            'badge' => 'mayhen_admin_badge--gold',
        ],
        self::CATEGORY_BILLING => [
            'label' => 'Thanh Toán & Gói Cước',
            'badge' => 'mayhen_admin_badge--success',
        ],
        self::CATEGORY_TECHNICAL => [
            'label' => 'Kỹ Thuật & Mã QR',
            'badge' => 'mayhen_admin_badge--primary',
        ],
        self::CATEGORY_INVITATION => [
            'label' => 'Thông Tin & Sảnh Tiệc',
            'badge' => 'mayhen_admin_badge--warning',
        ],
        self::CATEGORY_GENERAL => [
            'label' => 'Hỗ Trợ Chung',
            'badge' => 'mayhen_admin_badge--neutral',
        ],
    ];

    protected $table = 'canned_responses';

    protected $fillable = [
        'title',
        'shortcut',
        'category',
        'body',
        'is_active',
        'usage_count',
        'last_used_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'usage_count' => 'integer',
            'last_used_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('usage_count');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category]['label'] ?? 'Khác';
    }

    public function getCategoryBadgeClassAttribute(): string
    {
        return self::CATEGORIES[$this->category]['badge'] ?? 'mayhen_admin_badge--neutral';
    }

    /**
     * Thay thế các biến placeholder với dữ liệu của ticket cụ thể.
     */
    public function renderMergedBody(?SupportTicket $ticket = null): string
    {
        $customerName = 'bạn';
        $ticketCode = 'TK-MÂYHẸN';
        $weddingTitle = 'ngày trọng đại';
        $hotline = config('app.hotline', '0988.776.xxx');
        $supportUrl = url('/support');

        if ($ticket) {
            $customerName = $ticket->user?->name ?: 'Quý khách';
            $ticketCode = $ticket->ticket_code;

            // Tìm đám cưới của user nếu có
            if ($ticket->user) {
                $wedding = Wedding::where('user_id', $ticket->user->id)->latest('id')->first();
                if ($wedding) {
                    $weddingTitle = "{$wedding->groom_name} & {$wedding->bride_name}";
                }
            }
        }

        $replacements = [
            '{customer_name}' => $customerName,
            '{ticket_code}' => $ticketCode,
            '{wedding_title}' => $weddingTitle,
            '{hotline}' => $hotline,
            '{support_url}' => $supportUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->body);
    }
}

