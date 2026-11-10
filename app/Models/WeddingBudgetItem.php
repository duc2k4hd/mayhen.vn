<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingBudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'category',
        'item_name',
        'estimated_cost',
        'actual_cost',
        'paid_amount',
        'vendor_name',
        'vendor_contact',
        'notes',
        'is_paid_in_full',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'integer',
            'actual_cost' => 'integer',
            'paid_amount' => 'integer',
            'is_paid_in_full' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function getPendingAmountAttribute(): int
    {
        $cost = $this->actual_cost > 0 ? $this->actual_cost : $this->estimated_cost;

        return max(0, $cost - $this->paid_amount);
    }

    public function getVarianceAttribute(): int
    {
        if ($this->actual_cost === 0) {
            return 0;
        }

        return $this->actual_cost - $this->estimated_cost;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'venue_catering' => 'Sảnh Tiệc & Ẩm Thực',
            'attire_jewelry' => 'Trang Phục, Nhẫn & Trang Sức',
            'photography_video' => 'Chụp Ảnh & Phóng Sự Cưới',
            'invitations_favors' => 'Thiệp Cưới & Quà Đáp Lễ',
            'decor_flowers' => 'Trang Trí Hoa Tươi & Gia Tiên',
            'ceremony_offerings' => 'Mâm Quả, Tráp Cưới & Xe Hoa',
            'entertainment_music' => 'Âm Nhạc, MC & Ban Nhạc',
            'contingency' => 'Chi Phí Dự Phòng',
            default => 'Hạng mục khác',
        };
    }
}
