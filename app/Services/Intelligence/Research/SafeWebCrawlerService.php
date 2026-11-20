<?php

declare(strict_types=1);

namespace App\Services\Intelligence\Research;

use App\Models\AiResearchSource;
use App\Services\Intelligence\SecurityPolicyGate;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Safe Web Crawler & Content Extractor V3:
 * - Chặn tuyệt đối SSRF (localhost, private/reserved IP, link-local, internal hostnames).
 * - Tôn trọng robots.txt thật (cache 24h theo domain).
 * - Không vượt CAPTCHA, không truy cập trang yêu cầu đăng nhập / trả phí.
 * - Rate limit theo từng domain, giới hạn dung lượng response (<= 512KB), timeout ngắn (<= 8s), max 3 redirects.
 * - Dùng Native DOMDocument/DOMXPath (không cần Headless Browser), lọc sạch script/nav/ads & chống Prompt Injection nhúng trong HTML.
 */
class SafeWebCrawlerService
{
    public function __construct(
        protected SecurityPolicyGate $policyGate
    ) {}

    /**
     * Xác thực URL có phải mục tiêu public hợp lệ, an toàn SSRF hay không.
     *
     * @return array{safe: bool, reason: string, canonical_url?: string, domain?: string}
     */
    public function validateSafePublicUrl(string $rawUrl, bool $skipDnsLookup = false): array
    {
        $trimmed = trim($rawUrl);
        if ($trimmed === '' || strlen($trimmed) > 500) {
            return ['safe' => false, 'reason' => 'url_empty_or_too_long'];
        }

        $parsed = parse_url($trimmed);
        if (! is_array($parsed)) {
            return ['safe' => false, 'reason' => 'url_malformed'];
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return ['safe' => false, 'reason' => 'disallowed_scheme'];
        }

        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return ['safe' => false, 'reason' => 'url_credentials_disallowed'];
        }

        $host = strtolower(trim((string) ($parsed['host'] ?? '')));
        if ($host === '' || ! str_contains($host, '.')) {
            return ['safe' => false, 'reason' => 'invalid_or_local_host'];
        }

        // Chặn tuyệt đối tên miền nội bộ / metadata cloud
        $blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1', '169.254.169.254', 'metadata.google.internal'];
        if (in_array($host, $blockedHosts, true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.internal')
            || str_ends_with($host, '.localhost')) {
            return ['safe' => false, 'reason' => 'ssrf_internal_host_blocked'];
        }

        // Nếu host là IP trực tiếp -> bắt buộc phải là Public IP
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return ['safe' => false, 'reason' => 'ssrf_private_ip_blocked'];
            }
        } elseif (! $skipDnsLookup) {
            $ips = @gethostbynamel($host);
            if (is_array($ips) && $ips !== []) {
                foreach ($ips as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                        return ['safe' => false, 'reason' => 'ssrf_resolved_private_ip_blocked'];
                    }
                }
            }
        }

        // Chặn file tải xuống nhị phân & calendar/pagination traps
        $path = (string) ($parsed['path'] ?? '/');
        if (preg_match('/\.(pdf|zip|rar|exe|dmg|jpg|jpeg|png|gif|webp|svg|mp4|mp3|avi|docx?|xlsx?|pptx?)$/i', $path) === 1) {
            return ['safe' => false, 'reason' => 'binary_file_skipped'];
        }

        $query = (string) ($parsed['query'] ?? '');
        if (preg_match('/(replytocom=|ical=|calendar|action=edit|Special:)/i', $path.'?'.$query) === 1) {
            return ['safe' => false, 'reason' => 'crawl_trap_skipped'];
        }

        // Chuẩn hóa bỏ fragment & UTM tracking
        $cleanPath = $path === '' ? '/' : $path;
        $canonical = $scheme.'://'.$host.(isset($parsed['port']) && ! in_array((int) $parsed['port'], [80, 443], true) ? ':'.$parsed['port'] : '').$cleanPath;
        if ($query !== '') {
            parse_str($query, $qParams);
            foreach (array_keys($qParams) as $k) {
                if (str_starts_with(strtolower((string) $k), 'utm_') || in_array(strtolower((string) $k), ['fbclid', 'gclid', 'ref'], true)) {
                    unset($qParams[$k]);
                }
            }
            if ($qParams !== []) {
                $canonical .= '?'.http_build_query($qParams);
            }
        }

        return [
            'safe' => true,
            'reason' => 'ok',
            'canonical_url' => $canonical,
            'domain' => preg_replace('/^www\./', '', $host) ?? $host,
        ];
    }

    /**
     * Kiểm tra robots.txt thật của domain (cache 24h để không gửi thừa request).
     */
    public function isAllowedByRobotsTxt(string $url): bool
    {
        $parsed = parse_url($url);
        if (! is_array($parsed) || empty($parsed['host'])) {
            return false;
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? 'https'));
        $host = strtolower((string) $parsed['host']);
        $path = (string) ($parsed['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }

        $cacheKey = 'mayhen_robots_rules_'.md5($scheme.'://'.$host);
        $disallowPrefixes = Cache::remember($cacheKey, 86400, function () use ($scheme, $host): array {
            try {
                $res = Http::withHeaders([
                    'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
                ])
                    ->timeout(5)
                    ->withoutRedirecting()
                    ->get("{$scheme}://{$host}/robots.txt");

                if (! $res->successful()) {
                    return [];
                }

                return $this->parseRobotsDisallowRules((string) $res->body());
            } catch (Throwable) {
                return [];
            }
        });

        foreach ((array) $disallowPrefixes as $rule) {
            if ($rule === '/') {
                return false;
            }
            if ($rule !== '' && str_starts_with($path, $rule)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tải và trích xuất nội dung sạch từ một AiResearchSource đã phát hiện.
     *
     * @return array{
     *     ok: bool,
     *     status: string,
     *     title: string,
     *     text: string,
     *     content_hash: string,
     *     published_at: ?string,
     *     security_flags: list<string>,
     *     reason: string
     * }
     */
    public function fetchAndExtract(AiResearchSource $source, bool $skipDnsLookup = false): array
    {
        $url = (string) $source->canonical_url;
        $validation = $this->validateSafePublicUrl($url, $skipDnsLookup);
        if (! $validation['safe']) {
            $source->update([
                'status' => AiResearchSource::STATUS_REJECTED,
                'rejection_reason' => $validation['reason'],
            ]);

            return $this->failExtract('rejected', $validation['reason']);
        }

        $domain = (string) $validation['domain'];

        // 1. Tôn trọng robots.txt
        if (! $this->isAllowedByRobotsTxt($url)) {
            $source->update([
                'status' => AiResearchSource::STATUS_ROBOTS_BLOCKED,
                'rejection_reason' => 'disallowed_by_robots_txt',
            ]);

            return $this->failExtract('robots_blocked', 'Bị chặn bởi robots.txt của website nguồn.');
        }

        // 2. Per-domain rate limit bảo vệ server nguồn & hosting
        $rateKey = 'mayhen_crawl_domain_ts_'.md5($domain);
        $minGap = (int) config('ai_brain.research.domain_rate_limit_seconds', 3);
        $lastTs = (int) Cache::get($rateKey, 0);
        if ($lastTs > 0 && (time() - $lastTs) < $minGap) {
            return $this->failExtract('rate_limited', "Chờ giới hạn tốc độ truy cập domain {$domain}.");
        }
        Cache::put($rateKey, time(), 120);

        $timeout = (int) config('ai_brain.research.fetch_timeout_seconds', 8);
        $maxBytes = (int) config('ai_brain.research.max_response_bytes', 524288);

        try {
            // Đặc biệt tối ưu cho Wikipedia API (nhẹ, cấu trúc sạch, CC BY-SA 4.0)
            if (str_ends_with($domain, 'wikipedia.org') && str_contains($url, '/wiki/')) {
                $wikiExtract = $this->fetchViaWikipediaApi($url, $timeout);
                if ($wikiExtract !== null) {
                    return $this->finalizeExtractedContent($source, $wikiExtract['title'], $wikiExtract['text'], $wikiExtract['published_at'], 200, 'application/json');
                }
            }

            $response = Http::withHeaders([
                'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
                'Accept' => 'text/html,application/xhtml+xml,text/plain;q=0.9',
                'Accept-Language' => 'vi-VN,vi;q=0.9,en;q=0.5',
            ])
                ->timeout($timeout)
                ->withOptions([
                    'allow_redirects' => ['max' => 3, 'strict' => true, 'protocols' => ['http', 'https']],
                ])
                ->get($url);

            $httpStatus = $response->status();
            $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

            if (in_array($httpStatus, [401, 403, 429, 451], true)) {
                $source->update([
                    'status' => AiResearchSource::STATUS_REJECTED,
                    'http_status' => $httpStatus,
                    'rejection_reason' => 'access_gated_http_'.$httpStatus,
                    'fetched_at' => now(),
                ]);

                return $this->failExtract('rejected', "Trang nguồn giới hạn truy cập (HTTP {$httpStatus}) — bỏ qua.");
            }

            if (! $response->successful()) {
                $source->update([
                    'status' => AiResearchSource::STATUS_ERROR,
                    'http_status' => $httpStatus,
                    'rejection_reason' => 'http_error_'.$httpStatus,
                    'fetched_at' => now(),
                ]);

                return $this->failExtract('error', "HTTP lỗi {$httpStatus}");
            }

            if ($contentType !== '' && ! in_array($contentType, ['text/html', 'application/xhtml+xml', 'text/plain'], true)) {
                $source->update([
                    'status' => AiResearchSource::STATUS_REJECTED,
                    'http_status' => $httpStatus,
                    'content_type' => Str::limit($contentType, 75, ''),
                    'rejection_reason' => 'unsupported_content_type',
                    'fetched_at' => now(),
                ]);

                return $this->failExtract('rejected', "Định dạng không hỗ trợ ({$contentType}).");
            }

            $rawBody = substr((string) $response->body(), 0, $maxBytes);

            // Chặn tuyệt đối CAPTCHA / Cloudflare Turnstile / trang yêu cầu đăng nhập (KHÔNG bao giờ bypass)
            if ($this->detectAccessGateOrCaptcha($rawBody)) {
                $source->update([
                    'status' => AiResearchSource::STATUS_REJECTED,
                    'http_status' => $httpStatus,
                    'rejection_reason' => 'captcha_or_login_wall_detected',
                    'fetched_at' => now(),
                ]);

                return $this->failExtract('rejected', 'Phát hiện CAPTCHA hoặc tường đăng nhập — bỏ qua đúng chính sách.');
            }

            $parsed = $this->extractReadableFromHtml($rawBody);

            return $this->finalizeExtractedContent($source, $parsed['title'], $parsed['text'], $parsed['published_at'], $httpStatus, $contentType ?: 'text/html');
        } catch (Throwable $e) {
            $source->update([
                'status' => AiResearchSource::STATUS_ERROR,
                'rejection_reason' => 'fetch_exception: '.Str::limit($e->getMessage(), 140, ''),
                'fetched_at' => now(),
            ]);

            return $this->failExtract('error', 'Không tải được nguồn: '.Str::limit($e->getMessage(), 120, ''));
        }
    }

    /**
     * Phân tích nội dung HTML bằng Native DOMDocument, bỏ sạch điều hướng/script/ads.
     *
     * @return array{title: string, text: string, published_at: ?string}
     */
    public function extractReadableFromHtml(string $html): array
    {
        if (trim($html) === '') {
            return ['title' => '', 'text' => '', 'published_at' => null];
        }

        $prev = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xpath = new DOMXPath($dom);

        // Trích tiêu đề
        $title = '';
        $titleNodes = $xpath->query('//title');
        if ($titleNodes && $titleNodes->length > 0) {
            $title = trim((string) $titleNodes->item(0)?->textContent);
        }

        // Trích ngày đăng nếu có
        $publishedAt = null;
        $metaDateNodes = $xpath->query('//meta[@property="article:published_time" or @name="pubdate" or @name="date" or @itemprop="datePublished"]/@content');
        if ($metaDateNodes && $metaDateNodes->length > 0) {
            $rawDate = trim((string) $metaDateNodes->item(0)?->textContent);
            if ($rawDate !== '') {
                $publishedAt = Str::limit($rawDate, 35, '');
            }
        }

        // Xóa bỏ toàn bộ khối nhiễu không phải nội dung chính
        foreach (['script', 'style', 'noscript', 'nav', 'footer', 'header', 'aside', 'form', 'iframe', 'svg', 'button'] as $tag) {
            $nodes = $xpath->query('//'.$tag);
            if ($nodes) {
                for ($i = $nodes->length - 1; $i >= 0; $i--) {
                    $node = $nodes->item($i);
                    if ($node && $node->parentNode) {
                        $node->parentNode->removeChild($node);
                    }
                }
            }
        }

        // Ưu tiên khối <article> hoặc <main>, fallback toàn bộ <body>
        $rootQuery = '//article//*[self::h1 or self::h2 or self::h3 or self::p or self::li]';
        $contentNodes = $xpath->query($rootQuery);
        if (! $contentNodes || $contentNodes->length < 3) {
            $contentNodes = $xpath->query('//main//*[self::h1 or self::h2 or self::h3 or self::p or self::li]');
        }
        if (! $contentNodes || $contentNodes->length < 3) {
            $contentNodes = $xpath->query('//body//*[self::h1 or self::h2 or self::h3 or self::p or self::li]');
        }

        $chunks = [];
        $seenLines = [];
        $totalChars = 0;
        if ($contentNodes) {
            foreach ($contentNodes as $node) {
                $line = trim(preg_replace('/\s+/u', ' ', (string) $node->textContent) ?? '');
                if (mb_strlen($line, 'UTF-8') < 20) {
                    continue;
                }
                $key = md5(mb_strtolower($line, 'UTF-8'));
                if (isset($seenLines[$key])) {
                    continue;
                }
                $seenLines[$key] = true;
                $chunks[] = $line;
                $totalChars += mb_strlen($line, 'UTF-8');
                if ($totalChars >= 6500) {
                    break;
                }
            }
        }

        return [
            'title' => Str::limit(trim(preg_replace('/\s+/u', ' ', $title) ?? $title), 240, ''),
            'text' => implode("\n", $chunks),
            'published_at' => $publishedAt,
        ];
    }

    /**
     * Xử lý văn bản web không đáng tin: quét & loại bỏ mọi chuỗi chỉ thị Prompt Injection nhúng trong trang.
     *
     * @return array{clean_text: string, security_flags: list<string>}
     */
    public function sanitizeUntrustedWebText(string $text): array
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $safeLines = [];
        $flags = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            $check = $this->policyGate->inspectAndClassifyInput($trimmed);
            if ($check['blocked']) {
                $flags = array_merge($flags, $check['flags']);

                continue; // Loại sạch dòng chứa chỉ thị tấn công, không đưa vào model!
            }
            $safeLines[] = $trimmed;
        }

        return [
            'clean_text' => implode("\n", $safeLines),
            'security_flags' => array_values(array_unique($flags)),
        ];
    }

    /**
     * @return array{ok: bool, status: string, title: string, text: string, content_hash: string, published_at: ?string, security_flags: list<string>, reason: string}
     */
    protected function finalizeExtractedContent(
        AiResearchSource $source,
        string $title,
        string $rawText,
        ?string $publishedAt,
        int $httpStatus,
        string $contentType
    ): array {
        $sanitized = $this->sanitizeUntrustedWebText($rawText);
        $cleanText = $sanitized['clean_text'];

        if (mb_strlen($cleanText, 'UTF-8') < 120) {
            $source->update([
                'status' => AiResearchSource::STATUS_REJECTED,
                'http_status' => $httpStatus,
                'content_type' => Str::limit($contentType, 75, ''),
                'title' => $title !== '' ? $title : $source->title,
                'rejection_reason' => 'content_too_short_or_empty',
                'security_flags' => $sanitized['security_flags'] !== [] ? $sanitized['security_flags'] : null,
                'fetched_at' => now(),
            ]);

            return $this->failExtract('rejected', 'Nội dung chính quá ngắn hoặc không đủ thông tin hữu ích.');
        }

        $contentHash = hash('sha256', preg_replace('/\s+/u', ' ', mb_strtolower($cleanText, 'UTF-8')) ?? $cleanText);

        // Kiểm tra sao chép nội dung giữa các website khác nhau (anti-copycat inflation)
        $isDuplicate = AiResearchSource::query()
            ->where('content_hash', $contentHash)
            ->where('id', '!=', $source->id)
            ->exists();

        $recheckDays = (int) config('ai_brain.research.recheck_days', 14);
        $excerpt = Str::limit($cleanText, 1800, '...');

        if ($isDuplicate) {
            $source->update([
                'status' => AiResearchSource::STATUS_DUPLICATE,
                'http_status' => $httpStatus,
                'content_type' => Str::limit($contentType, 75, ''),
                'title' => $title !== '' ? $title : $source->title,
                'content_hash' => $contentHash,
                'content_length' => mb_strlen($cleanText, 'UTF-8'),
                'rejection_reason' => 'duplicate_content_hash_across_sources',
                'fetched_at' => now(),
            ]);

            return $this->failExtract('duplicate', 'Nội dung trùng lặp (bài sao chép từ nguồn đã học) — loại bỏ để tránh tăng ảo độ tin cậy.');
        }

        $source->update([
            'status' => AiResearchSource::STATUS_FETCHED,
            'http_status' => $httpStatus,
            'content_type' => Str::limit($contentType, 75, ''),
            'title' => $title !== '' ? $title : $source->title,
            'content_hash' => $contentHash,
            'content_length' => mb_strlen($cleanText, 'UTF-8'),
            'excerpt' => $excerpt,
            'published_at_detected' => $publishedAt,
            'security_flags' => $sanitized['security_flags'] !== [] ? $sanitized['security_flags'] : null,
            'fetched_at' => now(),
            'recheck_after_at' => now()->addDays($recheckDays),
        ]);

        return [
            'ok' => true,
            'status' => 'fetched',
            'title' => $title !== '' ? $title : (string) ($source->title ?? ''),
            'text' => Str::limit($cleanText, 5500, ''),
            'content_hash' => $contentHash,
            'published_at' => $publishedAt,
            'security_flags' => $sanitized['security_flags'],
            'reason' => 'ok',
        ];
    }

    /**
     * Tải sạch tóm tắt & nội dung Wikipedia tiếng Việt qua MediaWiki API chính thức (CC BY-SA 4.0).
     *
     * @return array{title: string, text: string, published_at: ?string}|null
     */
    protected function fetchViaWikipediaApi(string $url, int $timeout): ?array
    {
        $parsed = parse_url($url);
        $path = (string) ($parsed['path'] ?? '');
        if (! str_starts_with($path, '/wiki/')) {
            return null;
        }
        $wikiTitle = urldecode(substr($path, 6));
        if ($wikiTitle === '') {
            return null;
        }

        try {
            $res = Http::withHeaders([
                'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
            ])
                ->timeout($timeout)
                ->get('https://vi.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'prop' => 'extracts|revisions',
                    'explaintext' => 1,
                    'exchars' => 5200,
                    'rvprop' => 'timestamp',
                    'titles' => $wikiTitle,
                    'format' => 'json',
                    'redirects' => 1,
                ]);

            if (! $res->successful()) {
                return null;
            }

            $pages = $res->json('query.pages');
            if (! is_array($pages)) {
                return null;
            }
            $first = reset($pages);
            if (! is_array($first) || empty($first['extract'])) {
                return null;
            }

            return [
                'title' => (string) ($first['title'] ?? $wikiTitle),
                'text' => (string) $first['extract'],
                'published_at' => isset($first['revisions'][0]['timestamp']) ? (string) $first['revisions'][0]['timestamp'] : null,
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return list<string>
     */
    public function parseRobotsDisallowRules(string $robotsTxt): array
    {
        $lines = preg_split('/\R/', $robotsTxt) ?: [];
        $activeGroup = false;
        $disallows = [];

        foreach ($lines as $rawLine) {
            $line = trim(explode('#', $rawLine, 2)[0]);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^User-agent\s*:\s*(.+)$/i', $line, $m) === 1) {
                $ua = strtolower(trim($m[1]));
                $activeGroup = $ua === '*' || str_contains($ua, 'mayhenresearchbot');

                continue;
            }

            if ($activeGroup && preg_match('/^Disallow\s*:\s*(.*)$/i', $line, $m) === 1) {
                $path = trim($m[1]);
                if ($path !== '') {
                    $disallows[] = $path;
                }
            }
        }

        return array_values(array_unique($disallows));
    }

    protected function detectAccessGateOrCaptcha(string $html): bool
    {
        $lower = strtolower(substr($html, 0, 12000));
        $patterns = [
            'cf-turnstile',
            'g-recaptcha',
            'h-captcha',
            'verify you are human',
            'checking your browser before accessing',
            'attention required! | cloudflare',
            'please enable javascript and cookies to continue',
        ];

        foreach ($patterns as $needle) {
            if (str_contains($lower, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{ok: bool, status: string, title: string, text: string, content_hash: string, published_at: ?string, security_flags: list<string>, reason: string}
     */
    protected function failExtract(string $status, string $reason): array
    {
        return [
            'ok' => false,
            'status' => $status,
            'title' => '',
            'text' => '',
            'content_hash' => '',
            'published_at' => null,
            'security_flags' => [],
            'reason' => $reason,
        ];
    }
}
