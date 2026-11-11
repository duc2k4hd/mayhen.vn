<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaperInvitationScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invitation_id',
        'wedding_id',
        'image_path',
        'raw_extracted_text',
        'parsed_data',
        'verified_data',
        'confidence_score',
        'status',
        'ai_model',
        'applied_at',
    ];

    protected $casts = [
        'parsed_data' => 'array',
        'verified_data' => 'array',
        'applied_at' => 'datetime',
        'confidence_score' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    /**
     * URL hiển thị ảnh thiệp giấy đã tải lên.
     */
    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    /**
     * Nhãn trạng thái hiển thị tiếng Việt.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'uploaded' => 'Đã Tải Lên',
            'parsed' => 'Đã Bóc Tách (Chờ Rà Soát)',
            'verified' => 'Đã Xác Nhận Thủ Công',
            'applied' => 'Đã Áp Dụng Vào Thiệp',
            'rejected' => 'Đã Hủy Bỏ',
            default => 'Chờ Rà Soát',
        };
    }

    /**
     * Phân loại mức độ tin cậy của AI.
     */
    public function getConfidenceLabelAttribute(): string
    {
        if ($this->confidence_score >= 85) {
            return 'Rất Cao (Đầy Đủ Thông Tin)';
        }

        if ($this->confidence_score >= 60) {
            return 'Trung Bình (Cần Rà Soát)';
        }

        return 'Thấp (Cần Bổ Sung Thủ Công)';
    }
}

