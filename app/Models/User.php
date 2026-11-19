<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'phone',
        'avatar',
        'email_verified_at',
        'password',
        'role',
        'is_locked',
        'lock_reason',
        'locked_at',
        'last_password_reset_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'last_password_reset_at' => 'datetime',
        ];
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    public function weddings(): HasMany
    {
        return $this->hasMany(Wedding::class);
    }

    public function invitations(): HasManyThrough
    {
        return $this->hasManyThrough(Invitation::class, Wedding::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function partner(): HasOne
    {
        return $this->hasOne(Partner::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isPartner(): bool
    {
        return $this->role === 'partner' || ($this->relationLoaded('partner') ? (bool) $this->partner : $this->partner()->exists());
    }

    public function isCustomer(): bool
    {
        return in_array($this->role, ['customer', 'user', null], true);
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'staff', 'designer', 'finance', 'support'], true);
    }

    /**
     * Xác định tài khoản quản trị/nhân viên được miễn trừ các hạn chế quên mật khẩu (cooldown 7 ngày & link in-flight).
     */
    public function isExemptFromPasswordResetRestrictions(): bool
    {
        return $this->isAdmin() || $this->isStaff() || $this->canAccessAdminPanel();
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Quản Trị Viên',
            'manager' => 'Quản Lý Vận Hành',
            'designer' => 'Chuyên Viên Mẫu Thiệp',
            'finance' => 'Kế Toán & Tài Chính',
            'support' => 'Chăm Sóc Khách Hàng',
            'staff' => 'Nhân Viên Sự Kiện',
            'partner' => 'Cộng Tác Viên',
            default => 'Người Dùng',
        };
    }
}
