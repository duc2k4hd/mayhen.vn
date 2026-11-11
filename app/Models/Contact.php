<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'wedding_date',
        'quantity',
        'need',
        'message',
        'status',
        'assigned_to',
        'admin_notes',
        'source',
        'ip_address',
        'user_agent',
        'contacted_at',
        'converted_at',
    ];

    protected $casts = [
        'wedding_date' => 'date',
        'contacted_at' => 'datetime',
        'converted_at' => 'datetime',
    ];

    /**
     * Nhân viên tư vấn phụ trách lead.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Nhãn hiển thị trạng thái tiếng Việt.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Mới Tiếp Nhận',
            'contacted' => 'Đã Liên Hệ',
            'consulting' => 'Đang Tư Vấn',
            'quoted' => 'Đã Gửi Báo Giá',
            'converted' => 'Đã Chốt Đơn',
            'closed' => 'Đã Đóng / Không Nhu Cầu',
            default => 'Mới Tiếp Nhận',
        };
    }

    /**
     * Nhãn nhu cầu tư vấn.
     */
    public function getNeedLabelAttribute(): string
    {
        return match ($this->need) {
            'print-high-end' => 'In thiệp cưới cao cấp (Ép kim, dệt gân, tem sáp)',
            'print-minimal' => 'In thiệp phong cách Tối Giản (Minimalist)',
            'print-fast' => 'In gấp trong 3 - 5 ngày',
            'visit-studio' => 'Đặt hẹn đến Studio xem mẫu',
            'combo-online' => 'Gói combo Thiệp In + Thiệp Online',
            'partner' => 'Hợp tác Studio / Planner',
            default => $this->need,
        };
    }

    /**
     * Màu sắc huy hiệu trạng thái.
     */
    public function getStatusBadgeStyle(): array
    {
        return match ($this->status) {
            'new' => ['bg' => '#FFF3E0', 'color' => '#E65100', 'border' => '#FFE0B2'],
            'contacted' => ['bg' => '#E3F2FD', 'color' => '#1565C0', 'border' => '#BBDEFB'],
            'consulting' => ['bg' => '#F3E5F5', 'color' => '#7B1FA2', 'border' => '#E1BEE7'],
            'quoted' => ['bg' => '#E8F5E9', 'color' => '#2E7D32', 'border' => '#C8E6C9'],
            'converted' => ['bg' => '#E8F5E9', 'color' => '#1B5E20', 'border' => '#A5D6A7'],
            'closed' => ['bg' => '#ECEFF1', 'color' => '#546E7A', 'border' => '#CFD8DC'],
            default => ['bg' => '#FAF8F5', 'color' => '#7A726A', 'border' => '#E5DCCD'],
        };
    }
}
