<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformSettingHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'group',
        'key',
        'old_value',
        'new_value',
        'diff_summary',
        'version',
        'changed_by',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function getMaskedOldValueAttribute(): ?string
    {
        if ($this->old_value === null) {
            return '(Trống)';
        }

        $setting = PlatformSetting::where('group', $this->group)->where('key', $this->key)->first();
        if ($setting?->is_secret || str_contains(strtolower($this->key), 'secret') || str_contains(strtolower($this->key), 'key') || str_contains(strtolower($this->key), 'token') || str_contains(strtolower($this->key), 'password')) {
            return PlatformSetting::MASKED_SECRET;
        }

        return $this->old_value;
    }

    public function getMaskedNewValueAttribute(): ?string
    {
        if ($this->new_value === null) {
            return '(Trống)';
        }

        $setting = PlatformSetting::where('group', $this->group)->where('key', $this->key)->first();
        if ($setting?->is_secret || str_contains(strtolower($this->key), 'secret') || str_contains(strtolower($this->key), 'key') || str_contains(strtolower($this->key), 'token') || str_contains(strtolower($this->key), 'password')) {
            return PlatformSetting::MASKED_SECRET;
        }

        return $this->new_value;
    }
}
