<?php

namespace App\Models;

use App\Http\Controllers\Client\BlogController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\PageController;
use App\Services\DocumentationService;
use App\Services\InvitationTemplateCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

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

    public const GROUP_NOTIFICATIONS = 'notifications';

    /**
     * Lưu tên ứng dụng gốc trước khi đồng bộ từ CSDL Admin để làm fallback an toàn.
     */
    protected static ?string $initialAppName = null;

    /**
     * @var array<string, mixed>
     */
    protected static array $runtimeCache = [];

    protected static function booted(): void
    {
        static::saved(function (self $setting) {
            $groupCacheKey = "platform_setting_group_{$setting->group}";
            $cacheKey = "platform_setting_{$setting->group}_{$setting->key}";
            Cache::forget($groupCacheKey);
            Cache::forget($cacheKey);
            unset(static::$runtimeCache[$groupCacheKey], static::$runtimeCache[$cacheKey]);

            if ($setting->group === static::GROUP_BRAND_SEO) {
                HomeController::clearMemoization();
            }

            if ($setting->group === static::GROUP_BRAND_SEO && $setting->key === 'brand_name') {
                static::invalidateBrandCaches();
            }

            if ($setting->group === static::GROUP_MAIL) {
                static::applyMailRuntimeConfiguration();
            }
        });

        static::deleted(function (self $setting) {
            $groupCacheKey = "platform_setting_group_{$setting->group}";
            $cacheKey = "platform_setting_{$setting->group}_{$setting->key}";
            Cache::forget($groupCacheKey);
            Cache::forget($cacheKey);
            unset(static::$runtimeCache[$groupCacheKey], static::$runtimeCache[$cacheKey]);

            if ($setting->group === static::GROUP_BRAND_SEO) {
                HomeController::clearMemoization();
            }

            if ($setting->group === static::GROUP_BRAND_SEO && $setting->key === 'brand_name') {
                static::invalidateBrandCaches();
            }

            if ($setting->group === static::GROUP_MAIL) {
                static::applyMailRuntimeConfiguration();
            }
        });
    }

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

    /**
     * Nạp trọn bộ cấu hình của một nhóm trong duy nhất 1 truy vấn (khi cache lạnh),
     * sau đó phục vụ O(1) từ RAM / Cache với 0 truy vấn DB cho cả các khóa chưa lưu.
     *
     * @return array<string, mixed>
     */
    protected static function getGroupMap(string $group): array
    {
        $groupCacheKey = "platform_setting_group_{$group}";
        $useStaticCache = ! app()->runningUnitTests();

        if ($useStaticCache && isset(static::$runtimeCache[$groupCacheKey]) && is_array(static::$runtimeCache[$groupCacheKey])) {
            return static::$runtimeCache[$groupCacheKey];
        }

        $cached = Cache::get($groupCacheKey, '__MAYHEN_CACHE_MISS__');
        if (is_array($cached)) {
            if ($useStaticCache) {
                static::$runtimeCache[$groupCacheKey] = $cached;
            }

            return $cached;
        }

        try {
            $map = static::where('group', $group)->pluck('value', 'key')->all();
            Cache::put($groupCacheKey, $map, 86400);
        } catch (\Throwable) {
            return [];
        }

        if ($useStaticCache) {
            static::$runtimeCache[$groupCacheKey] = $map;
        }

        return $map;
    }

    public static function getValue(string $group, string $key, mixed $default = null): mixed
    {
        $map = static::getGroupMap($group);
        $value = $map[$key] ?? null;

        return $value !== null ? $value : $default;
    }

    public static function clearRuntimeCache(): void
    {
        static::$runtimeCache = [];
    }

    /**
     * Ghi nhận tên ứng dụng gốc từ config trước khi đồng bộ runtime với Admin.
     */
    public static function captureInitialAppName(?string $appName = null): void
    {
        if (static::$initialAppName === null) {
            $raw = trim((string) ($appName ?? config('app.name', 'Mây Hẹn')));
            static::$initialAppName = $raw !== '' ? $raw : 'Mây Hẹn';
        }
    }

    /**
     * Lấy tên thương hiệu chính thức đã cài đặt trong Admin (Nhóm Thương Hiệu & SEO).
     * Tối ưu 0 DB query khi cache ấm nhờ Runtime RAM Memoization + Cache 24h.
     */
    public static function getBrandName(): string
    {
        static::captureInitialAppName();
        $fallback = static::$initialAppName ?: 'Mây Hẹn';

        $value = trim((string) static::getValue(static::GROUP_BRAND_SEO, 'brand_name', $fallback));

        return $value !== '' ? $value : $fallback;
    }

    /**
     * Lấy tên thương hiệu dạng chữ in hoa UTF-8 đã memoize trong RAM.
     */
    public static function getBrandNameUpper(): string
    {
        $brandName = static::getBrandName();
        $useStaticCache = ! app()->runningUnitTests();

        if (
            $useStaticCache
            && isset(static::$runtimeCache['__brand_name_upper_src'])
            && static::$runtimeCache['__brand_name_upper_src'] === $brandName
        ) {
            return (string) static::$runtimeCache['__brand_name_upper'];
        }

        $upper = mb_strtoupper($brandName, 'UTF-8');

        if ($useStaticCache) {
            static::$runtimeCache['__brand_name_upper_src'] = $brandName;
            static::$runtimeCache['__brand_name_upper'] = $upper;
        }

        return $upper;
    }

    /**
     * Chuẩn hóa văn bản (Docs Markdown, Email Template lưu sẵn...) để đồng bộ tên thương hiệu hiện tại.
     * Bảo toàn tuyệt đối URL, domain (mayhen.vn), email (@mayhen.vn) và định danh kỹ thuật không dấu.
     */
    public static function replaceBrandName(string $text): string
    {
        if ($text === '' || ! preg_match('/mây\s*hẹn/iu', $text)) {
            return $text;
        }

        $brand = static::getBrandName();
        $brandUpper = static::getBrandNameUpper();

        // Thay các cụm đầy đủ trước để tránh lặp từ "Atelier" khi brand_name đã chứa "Atelier"
        $replacements = [
            'MÂY HẸN ATELIER' => $brandUpper,
            'Mây Hẹn Atelier' => $brand,
            'MÂY HẸN' => $brandUpper,
            'Mây Hẹn' => $brand,
            'mây hẹn' => mb_strtolower($brand, 'UTF-8'),
        ];

        return strtr($text, $replacements);
    }

    /**
     * Xóa sạch cache & đồng bộ lại config('app.name') ngay khi đổi tên thương hiệu.
     */
    public static function invalidateBrandCaches(): void
    {
        Cache::forget('platform_setting_group_'.static::GROUP_BRAND_SEO);
        Cache::forget('platform_setting_brand_seo_brand_name');
        static::clearRuntimeCache();

        $brandName = static::getBrandName();
        config(['app.name' => $brandName]);

        HomeController::clearMemoization();
        PageController::clearMemoization();
        BlogController::clearMemoization();
        InvitationTemplateCatalog::invalidate();
        DocumentationService::clearStaticMemoization();
    }

    /**
     * Lấy mã Google Tag Manager Container ID (mặc định GTM-PQF4ZWBG).
     */
    public static function getGtmContainerId(): ?string
    {
        $raw = (string) static::getValue(static::GROUP_BRAND_SEO, 'gtm_container_id', 'GTM-PQF4ZWBG');
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/GTM-[A-Z0-9]+/i', $raw, $matches)) {
            return strtoupper($matches[0]);
        }

        return $raw;
    }

    /**
     * Lấy đường dẫn URL tuyệt đối của Favicon thương hiệu đã cấu hình trong Admin.
     */
    public static function getFaviconUrl(): string
    {
        $val = (string) static::getValue(static::GROUP_BRAND_SEO, 'brand_favicon', '/storage/client/images/business/mayhen-favicon.png');
        $val = trim($val);
        if ($val === '') {
            $val = '/storage/client/images/business/mayhen-favicon.png';
        }

        // Tự động chuẩn hóa nếu đường dẫn trong DB lưu /storage/images/client/... nhưng thực tế là /storage/client/images/...
        if (str_starts_with($val, '/storage/images/client/business/') && ! file_exists(public_path($val))) {
            $corrected = str_replace('/storage/images/client/business/', '/storage/client/images/business/', $val);
            if (file_exists(public_path($corrected))) {
                $val = $corrected;
            }
        }

        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }

        return url($val);
    }

    /**
     * Lấy địa chỉ email hệ thống nội bộ (admin@mayhen.vn).
     */
    public static function getAdminEmail(): string
    {
        return (string) static::getValue(static::GROUP_MAIL, 'mail_from_admin', 'admin@mayhen.vn');
    }

    /**
     * Lấy địa chỉ email hỗ trợ khách hàng (support@mayhen.vn).
     */
    public static function getSupportEmail(): string
    {
        return (string) static::getValue(static::GROUP_MAIL, 'mail_from_support', 'support@mayhen.vn');
    }

    /**
     * Lấy địa chỉ email chỉ gửi thông báo tự động (no-reply@mayhen.vn).
     */
    public static function getNoreplyEmail(): string
    {
        return (string) static::getValue(static::GROUP_MAIL, 'mail_from_noreply', 'no-reply@mayhen.vn');
    }

    /**
     * Tự động áp dụng cấu hình máy chủ thư điện tử (SMTP) từ Admin vào Laravel Mail Runtime.
     */
    public static function applyMailRuntimeConfiguration(): void
    {
        try {
            $settings = static::getGroupMap(static::GROUP_MAIL);
            if (empty($settings['mail_host'])) {
                return;
            }

            $mailer = $settings['mail_mailer'] ?? 'smtp';
            $host = $settings['mail_host'];
            $port = (int) ($settings['mail_port'] ?? 587);
            $encryption = $settings['mail_encryption'] ?? 'tls';
            if ($encryption === 'none') {
                $encryption = null;
            }

            $username = $settings['mail_username'] ?? null;
            $password = $settings['mail_password'] ?? null;
            $fromAddress = $settings['mail_from_address'] ?? ($settings['mail_from_noreply'] ?? 'no-reply@mayhen.vn');
            $fromName = $settings['mail_from_name'] ?? static::getBrandName();

            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);
        } catch (\Throwable $e) {
            // Phòng vệ an toàn khi khởi tạo ban đầu hoặc migrate
        }
    }

    public static function setValue(string $group, string $key, mixed $value, ?int $userId = null): static
    {
        $setting = static::firstOrNew(['group' => $group, 'key' => $key]);
        $setting->value = (string) $value;
        $setting->updated_by = $userId;
        $setting->save();

        return $setting;
    }
}
