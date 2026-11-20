<?php

declare(strict_types=1);

namespace App\Services\Intelligence\Research;

use App\Models\AiResearchGoal;
use App\Models\AiResearchSource;
use App\Models\AiResearchTopic;
use App\Services\Intelligence\V4\OntologyAndClassificationService;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Autonomous Web Discovery Engine V3:
 * - Tự sinh truy vấn tìm kiếm tiếng Việt theo chủ đề & khái niệm chưa rõ (unknown_concepts).
 * - Hỗ trợ đa kênh hợp lệ không giới hạn 1 danh sách tĩnh:
 *   1. Search API (Tavily / Serper / Brave nếu Admin cấu hình API key).
 *   2. DuckDuckGo HTML Search (tìm web tiếng Việt mở rộng đa nguồn).
 *   3. Wikipedia Tiếng Việt MediaWiki Search API (CC BY-SA 4.0 — chuẩn xác về văn hóa cưới hỏi VN).
 *   4. RSS / Atom feed discovery khi có nguồn feed công khai.
 * - Mọi URL phát hiện đều qua SSRF Guard, lọc domain bị cấm và khử trùng bằng url_hash.
 */
class WebSearchDiscoveryService
{
    public function __construct(
        protected SafeWebCrawlerService $crawler,
        protected OntologyAndClassificationService $ontology
    ) {}

    /**
     * Khám phá các nguồn web mới hợp lệ cho một chủ đề trong Curriculum.
     *
     * @return array{
     *     queries_run: int,
     *     sources_discovered: int,
     *     eligible_sources: int,
     *     duplicates: int,
     *     rejected: int,
     *     channels_used: list<string>,
     *     created_source_ids: list<int>
     * }
     */
    public function discoverForTopic(AiResearchTopic $topic, bool $skipDnsLookup = false): array
    {
        $goal = $topic->goal;
        $queries = $this->generateQueriesForTopic($topic, $goal);
        $maxQueries = max(1, (int) config('ai_brain.research.max_queries_per_discover', 3));
        $queries = array_slice($queries, 0, $maxQueries);

        $disallowedDomains = array_values(array_unique(array_merge(
            (array) config('ai_brain.research.disallowed_domains', []),
            (array) ($goal?->disallowed_domains ?? [])
        )));
        $allowedDomains = array_values(array_filter(array_map('strtolower', (array) ($goal?->allowed_domains ?? []))));

        $discoveredTotal = 0;
        $eligibleTotal = 0;
        $duplicatesTotal = 0;
        $rejectedTotal = 0;
        $channelsUsed = [];
        $createdIds = [];

        foreach ($queries as $query) {
            $candidates = [];

            // Kênh 1: Search API cấu hình (nếu có)
            $apiProvider = strtolower(trim((string) config('ai_brain.research.search_api_provider', '')));
            $apiKey = trim((string) config('ai_brain.research.search_api_key', ''));
            if ($apiProvider !== '' && $apiKey !== '') {
                $apiHits = $this->searchViaConfiguredApi($apiProvider, $apiKey, $query);
                if ($apiHits !== []) {
                    $channelsUsed[] = 'api:'.$apiProvider;
                    $candidates = array_merge($candidates, $apiHits);
                }
            }

            // Kênh 2: Bing Web Search RSS tiếng Việt (nhẹ, không CAPTCHA, trả về đa dạng website Việt Nam thật)
            $bingHits = $this->searchViaBingRss($query);
            if ($bingHits !== []) {
                $channelsUsed[] = 'bing_web_rss';
                $candidates = array_merge($candidates, $bingHits);
            }

            // Kênh 3: DuckDuckGo HTML Search tiếng Việt (bổ sung nguồn mở đa dạng)
            $ddgHits = $this->searchViaDuckDuckGoHtml($query);
            if ($ddgHits !== []) {
                $channelsUsed[] = 'duckduckgo_html';
                $candidates = array_merge($candidates, $ddgHits);
            }

            // Kênh 4: Wikipedia Tiếng Việt API (mở CC BY-SA 4.0, có lọc sát tiêu đề/chủ đề)
            $wikiHits = $this->searchViaVietnameseWikipedia($query);
            if ($wikiHits !== []) {
                $channelsUsed[] = 'wikipedia_vi_api';
                $candidates = array_merge($candidates, $wikiHits);
            }

            $topicRelevanceTokens = $this->extractTopicRelevanceTokens($topic, $goal);

            foreach ($candidates as $hit) {
                $discoveredTotal++;
                $rawUrl = (string) ($hit['url'] ?? '');
                $hitTitle = trim((string) ($hit['title'] ?? ''));
                $hitSnippet = trim((string) ($hit['snippet'] ?? ''));
                $check = $this->crawler->validateSafePublicUrl($rawUrl, $skipDnsLookup);

                if (! $check['safe']) {
                    $rejectedTotal++;

                    continue;
                }

                $canonical = (string) $check['canonical_url'];
                $domain = (string) $check['domain'];

                if ($this->isDomainDisallowed($domain, $disallowedDomains)) {
                    $rejectedTotal++;

                    continue;
                }

                if ($allowedDomains !== [] && ! $this->isDomainInList($domain, $allowedDomains)) {
                    $rejectedTotal++;

                    continue;
                }

                // Kiểm tra domain đã bị Admin chặn khẩn cấp (is_blocked) hay chưa
                if (Schema::hasColumn('ai_research_sources', 'is_blocked')
                    && AiResearchSource::query()->where('domain', $domain)->where('is_blocked', true)->exists()) {
                    $rejectedTotal++;

                    continue;
                }

                // V4 Strict Relevance Gate: chặn Wix/Squarespace/Google Sites/Gmail/Website sơ đẳng/ngoại lai lạc đề
                $relCheck = $this->ontology->evaluateDiscoveryRelevance($hitTitle, $hitSnippet, $canonical, $domain, $topic, $goal);
                if (! $relCheck['eligible']) {
                    $rejectedTotal++;

                    continue;
                }

                $urlHash = AiResearchSource::hashUrl($canonical);
                if (AiResearchSource::query()->where('url_hash', $urlHash)->exists()) {
                    $duplicatesTotal++;

                    continue;
                }

                $tierInfo = $this->ontology->classifySourceTier($domain, (string) ($hit['method'] ?? ''));

                $attrs = [
                    'topic_id' => $topic->id,
                    'url_hash' => $urlHash,
                    'canonical_url' => $canonical,
                    'domain' => Str::limit($domain, 115, ''),
                    'title' => Str::limit($hitTitle, 250, '') ?: null,
                    'discovery_method' => (string) ($hit['method'] ?? 'web_search'),
                    'discovery_query' => Str::limit($query, 185, ''),
                    'status' => AiResearchSource::STATUS_DISCOVERED,
                    'trust_score' => $tierInfo['max_reliability'],
                    'relevance_score' => $relCheck['relevance_score'],
                ];
                if (Schema::hasColumn('ai_research_sources', 'source_tier')) {
                    $attrs['source_tier'] = $tierInfo['tier'];
                }

                $src = AiResearchSource::query()->create($attrs);

                $eligibleTotal++;
                $createdIds[] = (int) $src->id;

                if ($eligibleTotal >= 12) {
                    break 2;
                }
            }
        }

        return [
            'queries_run' => count($queries),
            'sources_discovered' => $discoveredTotal,
            'eligible_sources' => $eligibleTotal,
            'duplicates' => $duplicatesTotal,
            'rejected' => $rejectedTotal,
            'channels_used' => array_values(array_unique($channelsUsed)),
            'created_source_ids' => $createdIds,
        ];
    }

    /**
     * Sinh danh sách câu truy vấn tiếng Việt linh hoạt từ mục tiêu chủ đề và khái niệm chưa rõ.
     *
     * @return list<string>
     */
    public function generateQueriesForTopic(AiResearchTopic $topic, ?AiResearchGoal $goal = null): array
    {
        $queries = [];

        foreach ((array) ($topic->search_queries ?? []) as $q) {
            $clean = trim((string) $q);
            if ($clean !== '') {
                $queries[] = $clean;
            }
        }

        $countrySuffix = ($goal && strtoupper((string) $goal->country) === 'VN') ? ' Việt Nam' : '';
        $title = trim((string) $topic->title);
        if ($title !== '') {
            $queries[] = str_contains(mb_strtolower($title, 'UTF-8'), 'việt nam') ? $title : $title.$countrySuffix;
        }

        foreach (array_slice((array) ($topic->unknown_concepts ?? []), 0, 2) as $concept) {
            $c = trim((string) $concept);
            if ($c !== '') {
                $queries[] = $c.$countrySuffix;
            }
        }

        return array_values(array_unique($queries));
    }

    /**
     * Kênh tìm kiếm web mở tiếng Việt qua Bing Web RSS (nhẹ ~20KB, đa dạng domain Việt Nam, 0 CAPTCHA).
     *
     * @return list<array{url: string, title: string, snippet: string, method: string}>
     */
    protected function searchViaBingRss(string $query): array
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
                'Accept' => 'application/rss+xml, application/xml, text/xml',
            ])
                ->timeout(7)
                ->get('https://www.bing.com/search', [
                    'q' => $query,
                    'format' => 'rss',
                    'cc' => 'vn',
                    'setlang' => 'vi',
                ]);

            if (! $res->successful()) {
                return [];
            }

            $xmlBody = (string) $res->body();
            if ($xmlBody === '' || ! str_contains($xmlBody, '<item>')) {
                return [];
            }

            $prev = libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($xmlBody);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            if (! $xml || ! isset($xml->channel->item)) {
                return [];
            }

            $hits = [];
            foreach ($xml->channel->item as $item) {
                $link = trim((string) ($item->link ?? ''));
                $title = trim((string) ($item->title ?? ''));
                $desc = trim(strip_tags((string) ($item->description ?? '')));
                if ($link !== '' && (str_starts_with($link, 'http://') || str_starts_with($link, 'https://'))) {
                    $hits[] = [
                        'url' => $link,
                        'title' => $title,
                        'snippet' => $desc,
                        'method' => 'bing_web_rss',
                    ];
                }
                if (count($hits) >= 7) {
                    break;
                }
            }

            return $hits;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Kênh tìm kiếm Wikipedia Tiếng Việt (MediaWiki Open API).
     *
     * @return list<array{url: string, title: string, snippet: string, method: string}>
     */
    protected function searchViaVietnameseWikipedia(string $query): array
    {
        try {
            // Bỏ các từ nghi vấn chung để Wikipedia trả kết quả trúng đích hơn
            $cleanQuery = trim(preg_replace('/\b(là\s+gì|như\s+thế\s+nào|chi\s+tiết|hiện\s+đại)\b/iu', ' ', $query) ?? $query);
            $res = Http::withHeaders([
                'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
            ])
                ->timeout(6)
                ->get('https://vi.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $cleanQuery !== '' ? $cleanQuery : $query,
                    'srlimit' => 5,
                    'format' => 'json',
                ]);

            if (! $res->successful()) {
                return [];
            }

            $hits = [];
            foreach ((array) $res->json('query.search', []) as $item) {
                $title = trim((string) ($item['title'] ?? ''));
                $snippet = trim(strip_tags((string) ($item['snippet'] ?? '')));
                if ($title === '') {
                    continue;
                }
                $hits[] = [
                    'url' => 'https://vi.wikipedia.org/wiki/'.rawurlencode(str_replace(' ', '_', $title)),
                    'title' => $title,
                    'snippet' => $snippet,
                    'method' => 'wikipedia_api',
                ];
            }

            return $hits;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Trích xuất các từ khóa cốt lõi của chủ đề & mục tiêu để lọc bỏ nguồn lạc đề trước khi crawl.
     *
     * @return list<string>
     */
    protected function extractTopicRelevanceTokens(AiResearchTopic $topic, ?AiResearchGoal $goal = null): array
    {
        $stopWords = [
            'la', 'gi', 'va', 'cua', 'cho', 'tai', 'trong', 've', 'cac', 'nhung', 'voi', 'khi',
            'cach', 'nhu', 'the', 'nao', 'viet', 'nam', 'chuan', 'chi', 'tiet', 'hien', 'dai', 'moi', 'nhat',
        ];
        $blob = Str::ascii(mb_strtolower(
            $topic->title.' '.implode(' ', (array) ($topic->search_queries ?? [])).' '.($goal?->title ?? ''),
            'UTF-8'
        ));
        $blob = preg_replace('/[^a-z0-9\s]/', ' ', $blob) ?? $blob;
        $tokens = array_filter(
            explode(' ', $blob),
            fn (string $t) => strlen($t) >= 3 && ! in_array($t, $stopWords, true)
        );

        return array_values(array_unique($tokens));
    }

    /**
     * Chấm điểm độ liên quan (0-100) của kết quả tìm kiếm so với chủ đề nghiên cứu.
     *
     * @param  list<string>  $topicTokens
     */
    protected function computeHitRelevanceScore(string $title, string $snippet, string $url, array $topicTokens): int
    {
        if ($topicTokens === []) {
            return 60;
        }

        $titleNorm = Str::ascii(mb_strtolower($title, 'UTF-8'));
        $snippetNorm = Str::ascii(mb_strtolower($snippet, 'UTF-8'));
        $urlNorm = Str::ascii(mb_strtolower(urldecode($url), 'UTF-8'));

        $score = 0;
        foreach ($topicTokens as $tk) {
            if (str_contains($titleNorm, $tk)) {
                $score += 28;
            } elseif (str_contains($urlNorm, $tk)) {
                $score += 18;
            } elseif ($snippetNorm !== '' && str_contains($snippetNorm, $tk)) {
                $score += 10;
            }
        }

        return min(100, $score);
    }

    /**
     * Kênh tìm kiếm web mở tiếng Việt qua DuckDuckGo HTML endpoint (không bypass captcha nếu bị giới hạn).
     *
     * @return list<array{url: string, title: string, method: string}>
     */
    protected function searchViaDuckDuckGoHtml(string $query): array
    {
        try {
            $res = Http::withHeaders([
                'User-Agent' => (string) config('ai_brain.research.user_agent', 'MayHenResearchBot/3.0'),
                'Accept-Language' => 'vi-VN,vi;q=0.9',
            ])
                ->timeout(7)
                ->get('https://html.duckduckgo.com/html/', [
                    'q' => $query,
                    'kl' => 'vn-vi',
                ]);

            if (! $res->successful()) {
                return [];
            }

            $html = (string) $res->body();
            if ($html === '' || str_contains(strtolower($html), 'captcha')) {
                return [];
            }

            $prev = libxml_use_internal_errors(true);
            $dom = new DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
            libxml_clear_errors();
            libxml_use_internal_errors($prev);

            $xpath = new DOMXPath($dom);
            $links = $xpath->query('//a[contains(@class,"result__a")]');
            $hits = [];

            if ($links) {
                foreach ($links as $a) {
                    $href = trim((string) $a->attributes?->getNamedItem('href')?->textContent);
                    $title = trim((string) $a->textContent);
                    $resolved = $this->unwrapDuckDuckGoRedirectUrl($href);
                    if ($resolved !== '') {
                        $hits[] = [
                            'url' => $resolved,
                            'title' => $title,
                            'method' => 'web_search',
                        ];
                    }
                    if (count($hits) >= 6) {
                        break;
                    }
                }
            }

            return $hits;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Kênh Search API tùy chọn (Tavily / Serper / Brave) khi Admin cấu hình khóa hợp lệ.
     *
     * @return list<array{url: string, title: string, method: string}>
     */
    protected function searchViaConfiguredApi(string $provider, string $apiKey, string $query): array
    {
        try {
            if ($provider === 'tavily') {
                $res = Http::timeout(8)->post('https://api.tavily.com/search', [
                    'api_key' => $apiKey,
                    'query' => $query,
                    'max_results' => 5,
                    'search_depth' => 'basic',
                ]);
                if (! $res->successful()) {
                    return [];
                }

                return array_values(array_map(fn (array $r) => [
                    'url' => (string) ($r['url'] ?? ''),
                    'title' => (string) ($r['title'] ?? ''),
                    'method' => 'search_api_tavily',
                ], (array) $res->json('results', [])));
            }

            if ($provider === 'serper') {
                $res = Http::withHeaders(['X-API-KEY' => $apiKey])->timeout(8)->post('https://google.serper.dev/search', [
                    'q' => $query,
                    'gl' => 'vn',
                    'hl' => 'vi',
                    'num' => 6,
                ]);
                if (! $res->successful()) {
                    return [];
                }

                return array_values(array_map(fn (array $r) => [
                    'url' => (string) ($r['link'] ?? ''),
                    'title' => (string) ($r['title'] ?? ''),
                    'method' => 'search_api_serper',
                ], (array) $res->json('organic', [])));
            }

            if ($provider === 'brave') {
                $res = Http::withHeaders(['X-Subscription-Token' => $apiKey])->timeout(8)->get('https://api.search.brave.com/res/v1/web/search', [
                    'q' => $query,
                    'country' => 'VN',
                    'search_lang' => 'vi',
                    'count' => 6,
                ]);
                if (! $res->successful()) {
                    return [];
                }

                return array_values(array_map(fn (array $r) => [
                    'url' => (string) ($r['url'] ?? ''),
                    'title' => (string) ($r['title'] ?? ''),
                    'method' => 'search_api_brave',
                ], (array) $res->json('web.results', [])));
            }
        } catch (Throwable) {
            return [];
        }

        return [];
    }

    protected function unwrapDuckDuckGoRedirectUrl(string $href): string
    {
        if ($href === '') {
            return '';
        }
        if (str_starts_with($href, '//')) {
            $href = 'https:'.$href;
        }
        $parsed = parse_url($href);
        if (is_array($parsed) && ! empty($parsed['query'])) {
            parse_str($parsed['query'], $q);
            if (! empty($q['uddg']) && is_string($q['uddg'])) {
                return urldecode($q['uddg']);
            }
        }

        return str_starts_with($href, 'http://') || str_starts_with($href, 'https://') ? $href : '';
    }

    /**
     * @param  list<string>  $disallowed
     */
    protected function isDomainDisallowed(string $domain, array $disallowed): bool
    {
        return $this->isDomainInList($domain, $disallowed);
    }

    /**
     * @param  list<string>  $list
     */
    protected function isDomainInList(string $domain, array $list): bool
    {
        $d = strtolower(trim($domain));
        foreach ($list as $item) {
            $clean = strtolower(trim($item));
            if ($clean !== '' && ($d === $clean || str_ends_with($d, '.'.$clean))) {
                return true;
            }
        }

        return false;
    }

    protected function estimateInitialTrust(string $domain, string $method): int
    {
        if ($method === 'wikipedia_api' || str_ends_with($domain, 'wikipedia.org')) {
            return 82;
        }
        if (str_ends_with($domain, '.gov.vn') || str_ends_with($domain, '.edu.vn')) {
            return 88;
        }
        if (str_ends_with($domain, '.vn')) {
            return 68;
        }

        return 60;
    }
}
