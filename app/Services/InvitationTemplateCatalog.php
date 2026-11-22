<?php

namespace App\Services;

use App\Models\InvitationTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Deep module owning active public template lookup, filtering, search scoring,
 * home showcase slices, and unified RAM + Application cache invalidation.
 */
class InvitationTemplateCatalog
{
    public const EXCLUDED_SLUG = 'mayhen-curated';

    private const CACHE_KEY_ALL = 'mayhen_active_templates_collection';

    private const CACHE_KEY_FEATURED = 'mayhen_home_featured_templates';

    private const CACHE_KEY_GALLERY = 'mayhen_home_gallery_templates';

    private const CACHE_TTL_SECONDS = 86400;

    protected static ?Collection $memoizedAllActive = null;

    protected static ?InvitationTemplate $memoizedDefaultFree = null;

    /** @var array<string, InvitationTemplate> */
    protected static array $memoizedBySlug = [];

    protected static ?Collection $memoizedFeatured = null;

    protected static ?Collection $memoizedGallery = null;

    /**
     * Rank template value tier from highest (Pro = 4) down to lowest (Free = 1).
     */
    public static function tierRank(InvitationTemplate $template): int
    {
        return match ($template->tier) {
            'pro' => 4,
            'plus' => 3,
            'premium' => 2,
            'free' => 1,
            default => 3,
        };
    }

    /**
     * Compare two templates by value tier descending (Pro -> Plus -> Premium -> Free),
     * then featured status descending, then ID ascending.
     */
    public static function compareByTierValue(InvitationTemplate $a, InvitationTemplate $b): int
    {
        $tierDiff = self::tierRank($b) <=> self::tierRank($a);
        if ($tierDiff !== 0) {
            return $tierDiff;
        }

        $featDiff = ((int) ($b->is_featured ?? 0)) <=> ((int) ($a->is_featured ?? 0));
        if ($featDiff !== 0) {
            return $featDiff;
        }

        return ((int) $a->id) <=> ((int) $b->id);
    }

    /**
     * Return all active public templates (excluding internal AI curated slug),
     * ordered by tier value descending (Pro -> Plus -> Premium -> Free).
     */
    public static function allActive(): Collection
    {
        if (self::$memoizedAllActive !== null) {
            return self::$memoizedAllActive;
        }

        $raw = Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL_SECONDS, function (): array {
            return InvitationTemplate::query()
                ->where('is_active', true)
                ->where('slug', '!=', self::EXCLUDED_SLUG)
                ->get()
                ->map->getAttributes()
                ->all();
        });

        if (! is_array($raw)) {
            Cache::forget(self::CACHE_KEY_ALL);
            $raw = [];
        }

        return self::$memoizedAllActive = InvitationTemplate::hydrate($raw)
            ->sort([self::class, 'compareByTierValue'])
            ->values();
    }

    /**
     * Find an active public template by slug without querying the database.
     */
    public static function bySlug(string $slug): ?InvitationTemplate
    {
        if (isset(self::$memoizedBySlug[$slug])) {
            return self::$memoizedBySlug[$slug];
        }

        $found = self::allActive()->firstWhere('slug', $slug);

        if ($found instanceof InvitationTemplate) {
            self::$memoizedBySlug[$slug] = $found;

            return $found;
        }

        return null;
    }

    /**
     * Return the default template (preferring a free-tier template).
     */
    public static function defaultFree(): ?InvitationTemplate
    {
        if (self::$memoizedDefaultFree !== null) {
            return self::$memoizedDefaultFree;
        }

        $all = self::allActive();
        $default = $all->first(fn (InvitationTemplate $t): bool => $t->tier === 'free') ?? $all->first();

        return self::$memoizedDefaultFree = $default instanceof InvitationTemplate ? $default : null;
    }

    /**
     * Filter, accent-insensitive score-search, and sort active templates in memory.
     *
     * @return array{templates: Collection, totalCount: int}
     */
    public static function search(
        string $category = 'all',
        string $tier = 'all',
        string $sort = 'featured',
        string $query = ''
    ): array {
        $all = self::allActive();
        $totalCount = $all->count();
        $filtered = $all;

        if ($category !== '' && $category !== 'all') {
            $catFilter = ($category === 'envelope') ? 'luxury' : $category;
            $filtered = $filtered->where('category', $catFilter);
        }

        if ($tier !== '' && in_array($tier, ['free', 'premium', 'plus', 'pro'], true)) {
            if ($tier === 'plus') {
                $filtered = $filtered->filter(fn (InvitationTemplate $t): bool => $t->tier === 'plus' || empty($t->tier));
            } else {
                $filtered = $filtered->filter(fn (InvitationTemplate $t): bool => $t->tier === $tier);
            }
        }

        $search = trim($query);
        $hasSearch = ($search !== '');
        $scores = [];

        if ($hasSearch) {
            $searchLower = mb_strtolower($search, 'UTF-8');
            $unaccented = Str::ascii($searchLower);
            $slugSearch = Str::slug($searchLower);
            $tokens = preg_split('/\s+/', $searchLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $asciiTokens = array_map(fn (string $token): string => Str::ascii($token), $tokens);

            $filtered = $filtered->filter(function (InvitationTemplate $t) use ($searchLower, $unaccented, $slugSearch, $tokens, $asciiTokens, &$scores): bool {
                $name = mb_strtolower($t->name ?? '', 'UTF-8');
                $nameAscii = Str::ascii($name);
                $slug = mb_strtolower($t->slug ?? '', 'UTF-8');
                $slugWords = str_replace('-', ' ', $slug);
                $desc = mb_strtolower($t->description ?? '', 'UTF-8');
                $descAscii = Str::ascii($desc);
                $cat = mb_strtolower($t->category ?? '', 'UTF-8');

                $score = 0;

                if (str_contains($name, $searchLower)) {
                    $score += 120;
                } elseif (str_contains($nameAscii, $unaccented)) {
                    $score += 100;
                }

                if (str_contains($slug, $slugSearch) || str_contains($slugWords, $unaccented)) {
                    $score += 80;
                }

                if (str_contains($desc, $searchLower) || str_contains($descAscii, $unaccented)) {
                    $score += 40;
                }

                $matchedTokensCount = 0;
                foreach ($tokens as $idx => $token) {
                    $asciiToken = $asciiTokens[$idx] ?? Str::ascii($token);
                    $tokenMatched = false;

                    if (str_contains($name, $token) || str_contains($nameAscii, $asciiToken)) {
                        $score += 30;
                        $tokenMatched = true;
                    }

                    if (str_contains($slugWords, $asciiToken) || str_contains($slug, $asciiToken)) {
                        $score += 20;
                        $tokenMatched = true;
                    }

                    if (str_contains($cat, $asciiToken)) {
                        $score += 15;
                        $tokenMatched = true;
                    }

                    if (str_contains($desc, $token) || str_contains($descAscii, $asciiToken)) {
                        $score += 10;
                        $tokenMatched = true;
                    }

                    if ($tokenMatched) {
                        $matchedTokensCount++;
                    }
                }

                if (count($tokens) > 1 && $matchedTokensCount === count($tokens)) {
                    $score += 70;
                }

                if ($score > 0) {
                    $scores[$t->id] = $score;

                    return true;
                }

                return false;
            });
        }

        if ($hasSearch) {
            $templates = $filtered->sort(function (InvitationTemplate $a, InvitationTemplate $b) use ($scores, $sort): int {
                $scoreA = $scores[$a->id] ?? 0;
                $scoreB = $scores[$b->id] ?? 0;

                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }

                if ($sort === 'popular') {
                    $popDiff = ((int) ($b->usage_count ?? 100)) <=> ((int) ($a->usage_count ?? 100));
                    if ($popDiff !== 0) {
                        return $popDiff;
                    }
                }

                return self::compareByTierValue($a, $b);
            })->values();
        } elseif ($sort === 'popular') {
            $templates = $filtered->sort(function (InvitationTemplate $a, InvitationTemplate $b): int {
                $tierDiff = self::tierRank($b) <=> self::tierRank($a);
                if ($tierDiff !== 0) {
                    return $tierDiff;
                }

                $popDiff = ((int) ($b->usage_count ?? 100)) <=> ((int) ($a->usage_count ?? 100));
                if ($popDiff !== 0) {
                    return $popDiff;
                }

                return self::compareByTierValue($a, $b);
            })->values();
        } else {
            $templates = $filtered->sort([self::class, 'compareByTierValue'])->values();
        }

        return [
            'templates' => $templates,
            'totalCount' => $totalCount,
        ];
    }

    /**
     * Find similar templates in the same category, backfilling from remaining active templates.
     */
    public static function similar(InvitationTemplate $template, int $limit = 3): Collection
    {
        $all = self::allActive();

        $similar = $all->where('id', '!=', $template->id)
            ->where('category', $template->category)
            ->take($limit);

        if ($similar->count() < $limit) {
            $needed = $limit - $similar->count();
            $additional = $all->where('id', '!=', $template->id)
                ->whereNotIn('id', $similar->pluck('id'))
                ->take($needed);
            $similar = $similar->merge($additional);
        }

        return $similar;
    }

    /**
     * Featured templates for the homepage showcase, ordered by value tier descending (Pro -> Plus -> Premium -> Free).
     */
    public static function featured(int $limit = 8): Collection
    {
        if (self::$memoizedFeatured !== null) {
            return self::$memoizedFeatured->take($limit);
        }

        self::$memoizedFeatured = self::allActive()->values();

        return self::$memoizedFeatured->take($limit);
    }

    /**
     * Gallery templates with thumbnails for the homepage showcase, ordered by value tier descending (Pro -> Plus -> Premium -> Free).
     */
    public static function gallery(int $limit = 50): Collection
    {
        if (self::$memoizedGallery !== null) {
            return self::$memoizedGallery->take($limit);
        }

        self::$memoizedGallery = self::allActive()
            ->filter(fn (InvitationTemplate $t): bool => ! empty($t->thumbnail_url))
            ->values();

        return self::$memoizedGallery->take($limit);
    }

    /**
     * Single invalidation seam for both in-process RAM memoization and application cache.
     */
    public static function invalidate(?string $slug = null): void
    {
        self::$memoizedAllActive = null;
        self::$memoizedDefaultFree = null;
        self::$memoizedBySlug = [];
        self::$memoizedFeatured = null;
        self::$memoizedGallery = null;

        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_FEATURED);
        Cache::forget(self::CACHE_KEY_GALLERY);
        Cache::forget('mayhen_templates_total_count');
        Cache::forget('mayhen_default_free_template');

        if ($slug !== null && $slug !== '') {
            Cache::forget("mayhen_template_detail_{$slug}");
        }
    }
}
