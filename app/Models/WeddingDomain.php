<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeddingDomain extends Model
{
    use HasFactory;

    /**
     * Danh sách các subdomain bảo lưu của hệ thống không được phép đăng ký.
     */
    public const BLACKLISTED_SUBDOMAINS = [
        'admin',
        'api',
        'app',
        'assets',
        'auth',
        'cdn',
        'cname',
        'dashboard',
        'dev',
        'mail',
        'mayhen',
        'partner',
        'staging',
        'static',
        'support',
        'test',
        'web',
        'www',
    ];

    protected $fillable = [
        'wedding_id',
        'invitation_id',
        'subdomain',
        'custom_domain',
        'cname_target',
        'status',
        'ssl_status',
        'dns_verified_at',
        'dns_records',
    ];

    protected function casts(): array
    {
        return [
            'dns_verified_at' => 'datetime',
            'dns_records' => 'array',
        ];
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Đường dẫn đầy đủ của Vanity Subdomain (ví dụ: https://tuanlan.mayhen.vn).
     */
    public function subdomainUrl(): ?string
    {
        if (! $this->subdomain) {
            return null;
        }

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'mayhen.vn';
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return "{$scheme}://{$this->subdomain}.{$baseDomain}";
    }

    /**
     * Đường dẫn đầy đủ của Custom Domain riêng (ví dụ: https://tuanlan.wedding).
     */
    public function customDomainUrl(): ?string
    {
        if (! $this->custom_domain) {
            return null;
        }

        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return "{$scheme}://{$this->custom_domain}";
    }

    /**
     * Lấy URL ưu tiên cao nhất đang hoạt động.
     */
    public function activeUrl(): ?string
    {
        if ($this->isActive() && $this->custom_domain) {
            return $this->customDomainUrl();
        }

        if ($this->subdomain) {
            return $this->subdomainUrl();
        }

        return null;
    }
}

