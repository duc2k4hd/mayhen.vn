<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'wedding_id',
        'created_by',
        'file_name',
        'file_path',
        'status',
        'total_rows',
        'imported_count',
        'updated_count',
        'skipped_count',
        'failed_count',
        'column_mapping',
        'duplicate_mode',
        'error_summary',
        'raw_preview_data',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'imported_count' => 'integer',
        'updated_count' => 'integer',
        'skipped_count' => 'integer',
        'failed_count' => 'integer',
        'column_mapping' => 'array',
        'error_summary' => 'array',
        'raw_preview_data' => 'array',
    ];

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'uploaded' => 'Đã Tải Lên (Chờ ánh xạ)',
            'mapped' => 'Đã Ánh Xạ Cột (Chờ xác nhận)',
            'processing' => 'Đang Xử Lý Nhập...',
            'completed' => 'Hoàn Thành 100%',
            'partial_failed' => 'Hoàn Thành Một Phần',
            'failed' => 'Thất Bại',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'mayhen_admin_badge--success',
            'partial_failed' => 'mayhen_admin_badge--warning',
            'processing' => 'mayhen_admin_badge--info',
            'failed' => 'mayhen_admin_badge--danger',
            default => 'mayhen_admin_badge--neutral',
        };
    }

    public function getSuccessRateAttribute(): int
    {
        if ($this->total_rows <= 0) {
            return 0;
        }

        $successful = $this->imported_count + $this->updated_count;

        return (int) round(($successful / $this->total_rows) * 100);
    }
}
