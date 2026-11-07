<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'phone',
        'password',
        'role',
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
        ];
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
        return $this->role === 'partner';
    }

    public function isCustomer(): bool
    {
        return in_array($this->role, ['customer', 'user', null], true);
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'staff'], true);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Quản Trị Viên',
            'manager' => 'Quản Lý',
            'staff' => 'Nhân Viên',
            'partner' => 'Cộng Tác Viên',
            default => 'Người Dùng',
        };
    }
}
