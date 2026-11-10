<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PrivacyErasureRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_code',
        'user_id',
        'wedding_id',
        'requested_by_type',
        'requester_email',
        'requester_name',
        'reason',
        'status',
        'retention_scope',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'executed_at',
        'affected_records_count',
        'accounting_records_preserved',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'executed_at' => 'datetime',
        'affected_records_count' => 'array',
        'accounting_records_preserved' => 'boolean',
    ];

    /**
     * Sinh mã định danh DSAR Erasure duy nhất dạng DSAR-DEL-XXXXXXXX.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'DSAR-DEL-'.strtoupper(Str::random(8));
        } while (static::where('request_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt (Chờ thực thi)',
            'rejected' => 'Từ chối',
            'executed' => 'Đã ẩn danh hóa thành công',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'mayhen_admin_badge--warning',
            'approved' => 'mayhen_admin_badge--info',
            'rejected' => 'mayhen_admin_badge--danger',
            'executed' => 'mayhen_admin_badge--success',
            default => 'mayhen_admin_badge--neutral',
        };
    }
}
