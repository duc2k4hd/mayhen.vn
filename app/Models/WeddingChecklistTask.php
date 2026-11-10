<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingChecklistTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'category',
        'phase',
        'title',
        'description',
        'assignee_name',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'display_order' => 'integer',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        if ($this->isCompleted() || ! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    public function getPhaseLabelAttribute(): string
    {
        return match ($this->phase) {
            '12_months' => 'Trước 9 - 12 tháng',
            '6_months' => 'Trước 4 - 6 tháng',
            '3_months' => 'Trước 2 - 3 tháng',
            '1_month' => 'Trước 1 tháng',
            '1_week' => 'Tuần lễ cưới (7 ngày trước)',
            'wedding_day' => 'Ngày diễn ra hôn lễ',
            'post_wedding' => 'Hậu sự kiện & Cảm ơn',
            default => 'Việc cần làm khác',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'Khẩn cấp',
            'high' => 'Ưu tiên cao',
            'medium' => 'Bình thường',
            'low' => 'Thấp',
            default => 'Bình thường',
        };
    }
}
