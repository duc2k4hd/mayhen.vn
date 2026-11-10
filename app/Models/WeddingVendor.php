<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingVendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'category',
        'contact_person',
        'phone',
        'email',
        'address',
        'contract_status',
        'total_amount',
        'deposit_paid',
        'payment_due_date',
        'service_timeline',
        'notes',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'deposit_paid' => 'integer',
            'payment_due_date' => 'date',
            'display_order' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function getBalanceDueAttribute(): int
    {
        return max(0, $this->total_amount - $this->deposit_paid);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'venue' => 'Sảnh Tiệc & Trung Tâm Tiệc Cưới',
            'photo_video' => 'Chụp Ảnh & Quay Phim',
            'bridal' => 'Váy Cưới & Trang Phục',
            'makeup' => 'Trang Điểm & Làm Tóc',
            'decor' => 'Trang Trí Gia Tiên & Rạp Cưới',
            'trap_an_hoi' => 'Tráp Ăn Hỏi & Lễ Vật',
            'mc_music' => 'MC & Ban Nhạc / Âm Thanh',
            'car_transport' => 'Xe Hoa & Vận Chuyển',
            'invitation_print' => 'In Ấn Thiệp Truyền Thống',
            default => 'Hạng Mục Khác',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->contract_status) {
            'contacting' => 'Đang Liên Hệ / Khảo Giá',
            'contract_signed' => 'Đã Ký Hợp Đồng',
            'in_progress' => 'Đang Thực Hiện',
            'completed' => 'Đã Hoàn Tất',
            'cancelled' => 'Đã Hủy',
            default => 'Chưa Xác Định',
        };
    }
}
