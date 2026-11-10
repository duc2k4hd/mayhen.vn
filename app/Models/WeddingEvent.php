<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'name',
        'event_type',
        'event_date',
        'event_time',
        'lunar_date',
        'venue_name',
        'venue_address',
        'map_url',
        'dress_code',
        'description',
        'display_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * Nhãn loại sự kiện thân thiện với người dùng Việt Nam.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            'ceremony_bride' => 'Lễ Vu Quy (Nhà Gái)',
            'ceremony_groom' => 'Lễ Thành Hôn (Nhà Trai)',
            'engagement' => 'Lễ Đính Hôn / Ăn Hỏi',
            'banquet' => 'Tiệc Cưới & Mừng Hạnh Phúc',
            'after_party' => 'Tiệc Thân Mật / After Party',
            default => 'Sự Kiện Khác',
        };
    }

    /**
     * Định dạng chuỗi ngày giờ đầy đủ.
     */
    public function getFormattedDateTimeAttribute(): string
    {
        $dateStr = $this->event_date ? $this->event_date->format('d/m/Y') : '';
        $timeStr = $this->event_time ? $this->event_time : '';

        if ($timeStr && $dateStr) {
            return "{$timeStr} — Ngày {$dateStr}";
        }

        return $dateStr ?: 'Chưa định thời gian';
    }

    /**
     * Kiểm tra sự kiện đã qua hay chưa.
     */
    public function isPast(): bool
    {
        if (! $this->event_date) {
            return false;
        }

        $eventDateTime = Carbon::parse($this->event_date->format('Y-m-d').' '.($this->event_time ?: '23:59:59'), 'Asia/Ho_Chi_Minh');

        return $eventDateTime->isPast();
    }
}
