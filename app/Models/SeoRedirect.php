<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_path',
        'target_url',
        'status_code',
        'hits',
        'last_hit_at',
        'is_active',
        'note',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Chuẩn hóa đường dẫn nguồn (loại bỏ domain, dấu / thừa, query string nếu có).
     */
    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        // Bóc tách path nếu truyền cả domain URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = $parsed ?: $path;
        }

        // Bỏ query string
        $path = explode('?', $path)[0];

        // Chuẩn hóa thành dạng /path-name
        $trimmed = trim($path, '/');

        return '/'.$trimmed;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
