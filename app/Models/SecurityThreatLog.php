<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityThreatLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'ip_address',
        'user_agent',
        'endpoint',
        'payload_summary',
        'action_taken',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', Carbon::now()->subHours($hours));
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event_type) {
            'rate_limit_exceeded' => 'Vượt Ngưỡng Tần Suất (Rate Limit)',
            'failed_login_burst' => 'Dò Mật Khẩu (Brute Force / Stuffing)',
            'bot_spam' => 'Hành Vi Spam Bot Tự Động',
            'sql_injection_attempt' => 'Nghi Vấn Chèn Mã SQL Injection',
            'path_traversal_attempt' => 'Nghi Vấn Đọc File Path Traversal',
            default => $this->event_type,
        };
    }

    public function getEventBadgeColorAttribute(): string
    {
        return match ($this->event_type) {
            'failed_login_burst', 'sql_injection_attempt' => 'danger',
            'rate_limit_exceeded' => 'warning',
            default => 'info',
        };
    }
}

