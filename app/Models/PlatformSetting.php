<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformSetting extends Model
{
    use HasFactory;

    public const MASKED_SECRET = '••••••••';

    public const GROUP_BRAND_SEO = 'brand_seo';

    public const GROUP_PAYMENT = 'payment';

    public const GROUP_MAIL = 'mail';

    public const GROUP_STORAGE = 'storage';

    public const GROUP_SECURITY = 'security';

    public const GROUP_FEATURES = 'features';

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_secret',
        'label',
        'description',
        'version',
        'updated_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
            'version' => 'integer',
        ];
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PlatformSettingHistory::class, 'key', 'key')
            ->where('group', $this->group)
            ->latest('id');
    }

    public function scopeForGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }
}
