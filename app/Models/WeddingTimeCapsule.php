<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingTimeCapsule extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'invitation_id',
        'sender_type',
        'sender_name',
        'recipient_name',
        'title',
        'content',
        'milestone_years',
        'unlock_date',
        'is_sealed',
        'seal_color',
        'sender_email',
        'media_urls',
        'unlocked_at',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'unlock_date' => 'date',
            'is_sealed' => 'boolean',
            'unlocked_at' => 'datetime',
            'notified_at' => 'datetime',
            'media_urls' => 'array',
            'milestone_years' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    /**
     * Kiểm tra bức thư đã được mở niêm phong hay chưa.
     */
    public function isUnlocked(): bool
    {
        return ! $this->is_sealed && $this->unlocked_at !== null;
    }

    /**
     * Kiểm tra đã tới hoặc qua ngày mở khóa để cho phép bẻ niêm phong sáp.
     * Thống nhất múi giờ sự kiện theo giờ Việt Nam (Asia/Ho_Chi_Minh).
     */
    public function canBeUnlocked(): bool
    {
        if (! $this->unlock_date) {
            return false;
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $target = $this->unlock_date->copy()->timezone('Asia/Ho_Chi_Minh')->startOfDay();

        return $now->gte($target);
    }

    /**
     * Số ngày còn lại đến ngày mở khóa (theo múi giờ Asia/Ho_Chi_Minh).
     */
    public function daysRemaining(): int
    {
        if (! $this->unlock_date) {
            return 0;
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $target = $this->unlock_date->copy()->timezone('Asia/Ho_Chi_Minh')->startOfDay();

        return $now->gte($target) ? 0 : (int) $now->diffInDays($target);
    }

    /**
     * Tên gọi mỹ miều theo từng cột mốc năm kỷ niệm ngày cưới.
     */
    public function milestoneLabel(): string
    {
        return match ($this->milestone_years) {
            1 => 'Kỷ niệm 1 năm (Đám cưới Giấy)',
            3 => 'Kỷ niệm 3 năm (Đám cưới Da)',
            5 => 'Kỷ niệm 5 năm (Đám cưới Gỗ)',
            10 => 'Kỷ niệm 10 năm (Đám cưới Thiếc)',
            25 => 'Kỷ niệm 25 năm (Đám cưới Bạc)',
            50 => 'Kỷ niệm 50 năm (Đám cưới Vàng)',
            default => "Kỷ niệm {$this->milestone_years} năm",
        };
    }

    /**
     * Màu sắc của con dấu niêm phong sáp (Wax Seal).
     */
    public function sealColorHex(): string
    {
        return match ($this->seal_color) {
            'ruby' => '#85182A',
            'emerald' => '#2E5A44',
            'sapphire' => '#1B2A4A',
            'gold' => '#C5A880',
            default => '#85182A',
        };
    }
}
