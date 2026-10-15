<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'custom_config',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_config' => 'array',
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
}
