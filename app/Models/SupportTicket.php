<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'user_id',
        'assigned_to',
        'subject',
        'category',
        'priority',
        'status',
        'body',
        'first_responded_at',
        'resolved_at',
        'closed_at',
        'sla_deadline_at',
        'escalated_at',
        'escalation_level',
        'escalation_reason',
        'is_incident',
        'incident_severity',
        'incident_declared_at',
        'incident_declared_by',
        'root_cause',
        'mitigation_action',
        'postmortem_notes',
        'customer_confirmed_resolved_at',
    ];

    protected $casts = [
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'sla_deadline_at' => 'datetime',
        'escalated_at' => 'datetime',
        'is_incident' => 'boolean',
        'incident_declared_at' => 'datetime',
        'customer_confirmed_resolved_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id')->orderBy('created_at');
    }

    public function incidentDeclarer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'incident_declared_by');
    }

    public function isEscalated(): bool
    {
        return $this->escalated_at !== null;
    }

    public function isBreached(): bool
    {
        if (in_array($this->status, ['resolved', 'closed'], true)) {
            return false;
        }

        return $this->sla_deadline_at && $this->sla_deadline_at->isPast();
    }

    // -------------------------------------------------------------------------
    // Business Logic
    // -------------------------------------------------------------------------

    /**
     * Sinh mã ticket duy nhất dạng TK-XXXXXXXX.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'TK-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8));
        } while (self::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Tính giờ phản hồi đầu tiên (SLA).
     */
    public function getFirstResponseHoursAttribute(): ?float
    {
        if (! $this->first_responded_at) {
            return null;
        }

        return round($this->created_at->diffInMinutes($this->first_responded_at) / 60, 1);
    }

    /**
     * Tính giờ xử lý tổng (resolved_at - created_at).
     */
    public function getResolutionHoursAttribute(): ?float
    {
        if (! $this->resolved_at) {
            return null;
        }

        return round($this->created_at->diffInMinutes($this->resolved_at) / 60, 1);
    }

    /**
     * Nhãn trạng thái tiếng Việt.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Mới',
            'in_progress' => 'Đang xử lý',
            'resolved' => 'Đã giải quyết',
            'closed' => 'Đã đóng',
            default => $this->status,
        };
    }

    /**
     * Nhãn ưu tiên tiếng Việt.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Thấp',
            'medium' => 'Trung bình',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
            default => $this->priority,
        };
    }

    /**
     * Nhãn phân loại tiếng Việt.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'technical' => 'Kỹ thuật',
            'billing' => 'Thanh toán',
            'invitation' => 'Thiệp cưới',
            'general' => 'Chung',
            default => $this->category,
        };
    }

    /**
     * CSS class badge dựa trên trạng thái.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'mayhen_admin_badge--warning',
            'in_progress' => 'mayhen_admin_badge--info',
            'resolved' => 'mayhen_admin_badge--success',
            'closed' => 'mayhen_admin_badge--neutral',
            default => 'mayhen_admin_badge--neutral',
        };
    }

    /**
     * CSS class badge dựa trên mức ưu tiên.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'mayhen_admin_badge--neutral',
            'medium' => 'mayhen_admin_badge--info',
            'high' => 'mayhen_admin_badge--warning',
            'urgent' => 'mayhen_admin_badge--danger',
            default => 'mayhen_admin_badge--neutral',
        };
    }

    /**
     * Kiểm tra ticket có thể transition sang status mới không.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = [
            'open' => ['in_progress', 'closed'],
            'in_progress' => ['open', 'resolved', 'closed'],
            'resolved' => ['in_progress', 'closed'],
            'closed' => [],
        ];

        return in_array($newStatus, $allowed[$this->status] ?? []);
    }
}
