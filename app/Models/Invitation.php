<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'wedding_id',
        'template_id',
        'slug',
        'title',
        'version_type',
        'template_version',
        'schema_version',
        'revision_id',
        'custom_config',
        'draft_config',
        'last_autosaved_at',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'revision_id' => 'integer',
            'custom_config' => 'array',
            'draft_config' => 'array',
            'last_autosaved_at' => 'datetime',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvitationTemplate::class, 'template_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function domain(): HasOne
    {
        return $this->hasOne(WeddingDomain::class);
    }

    public function timeCapsules(): HasMany
    {
        return $this->hasMany(WeddingTimeCapsule::class)->orderBy('unlock_date');
    }

    /**
     * Check if the current visitor or authenticated user is the owner/creator of this invitation.
     */
    public function isOwner(?User $user = null, ?Request $request = null): bool
    {
        $request = $request ?? request();
        $user = $user ?? (auth()->check() ? auth()->user() : null);

        // 1. Nếu đã đăng nhập và là chủ sở hữu gắn với wedding
        if ($user && $this->wedding && (int) $this->wedding->user_id === (int) $user->id) {
            return true;
        }

        // 2. Nếu tạo thiệp khi chưa đăng nhập (lưu trong session)
        if ($request && $request->hasSession()) {
            $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
            if (in_array($this->slug, $ownedSlugs, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kiểm tra thiệp có đang bị quản trị viên khóa hay không.
     */
    public function isLocked(): bool
    {
        $config = is_array($this->custom_config)
            ? $this->custom_config
            : (json_decode((string) $this->custom_config, true) ?? []);

        return ! empty($config['is_locked']);
    }

    /**
     * Kiểm tra thiệp đã hết hạn lưu trữ trực tuyến hay chưa.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Kiểm tra khách vãng lai có được phép tương tác với thiệp hay không (Invariants: Published, Not Locked, Not Expired).
     */
    public function isInteractable(?User $user = null, ?Request $request = null): bool
    {
        if ($this->isOwner($user, $request)) {
            return true;
        }

        if ($this->isLocked() || ! $this->is_published || $this->isExpired()) {
            return false;
        }

        return true;
    }
}
