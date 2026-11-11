<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAnnouncement extends Model
{
    use HasFactory;

    public const TYPE_INFO = 'info';

    public const TYPE_WARNING = 'warning';

    public const TYPE_DANGER = 'danger';

    public const TYPE_PROMOTION = 'promotion';

    public const PLACEMENT_TOP_BAR = 'top_bar';

    public const PLACEMENT_CLIENT_DASHBOARD = 'client_dashboard';

    public const PLACEMENT_MODAL_POPUP = 'modal_popup';

    protected $fillable = [
        'title',
        'content',
        'type',
        'placement',
        'cta_text',
        'cta_url',
        'is_active',
        'is_dismissible',
        'starts_at',
        'ends_at',
        'views_count',
        'clicks_count',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_dismissible' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'views_count' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope lấy thông báo đang trong khung giờ hiển thị.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope lọc theo vị trí hiển thị.
     */
    public function scopeForPlacement(Builder $query, string $placement): Builder
    {
        return $query->where('placement', $placement);
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function recordImpression(): void
    {
        $this->increment('views_count');
    }

    public function recordClick(): void
    {
        $this->increment('clicks_count');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_PROMOTION => 'Ưu đãi & Khuyến mãi',
            self::TYPE_WARNING => 'Cảnh báo & Bảo trì',
            self::TYPE_DANGER => 'Khẩn cấp',
            default => 'Thông tin chung',
        };
    }

    public function getPlacementLabelAttribute(): string
    {
        return match ($this->placement) {
            self::PLACEMENT_TOP_BAR => 'Thanh thông báo đầu trang (Top Bar)',
            self::PLACEMENT_CLIENT_DASHBOARD => 'Bảng điều khiển cặp đôi (Dashboard)',
            self::PLACEMENT_MODAL_POPUP => 'Hộp thoại Popup chào mừng',
            default => $this->placement,
        };
    }

    /**
     * Thuộc tính style màu sắc Quiet Luxury.
     *
     * @return array{bg: string, border: string, text: string, badge_bg: string, badge_text: string}
     */
    public function getTypeStyles(): array
    {
        return match ($this->type) {
            self::TYPE_PROMOTION => [
                'bg' => 'linear-gradient(135deg, #FFFDF9 0%, #FAF6F0 100%)',
                'border' => '#C5A880',
                'text' => '#4A3D2C',
                'badge_bg' => '#C5A880',
                'badge_text' => '#FFFFFF',
            ],
            self::TYPE_WARNING => [
                'bg' => '#FFFBEB',
                'border' => '#F59E0B',
                'text' => '#78350F',
                'badge_bg' => '#F59E0B',
                'badge_text' => '#FFFFFF',
            ],
            self::TYPE_DANGER => [
                'bg' => '#FEF2F2',
                'border' => '#EF4444',
                'text' => '#7F1D1D',
                'badge_bg' => '#EF4444',
                'badge_text' => '#FFFFFF',
            ],
            default => [
                'bg' => '#EFF6FF',
                'border' => '#3B82F6',
                'text' => '#1E3A8A',
                'badge_bg' => '#3B82F6',
                'badge_text' => '#FFFFFF',
            ],
        };
    }
}
