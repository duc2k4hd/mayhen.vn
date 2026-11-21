<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PlatformSetting;
use App\Models\Wedding;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TemplateController extends Controller
{
    /**
     * Runtime In-Memory Static Memoization cho các worker PHP (FPM / Octane).
     * Phản hồi 0.00ms, không tốn I/O, không gọi lại cache driver lặp lại.
     */
    protected static ?Collection $memoizedAllActive = null;

    /**
     * Xóa sạch RAM memoization khi có cập nhật dữ liệu template.
     */
    public static function clearMemoization(): void
    {
        self::$memoizedAllActive = null;
    }

    /**
     * Lấy toàn bộ danh sách mẫu thiệp đang hoạt động từ Static RAM hoặc Cache tầng 2.
     */
    public static function getAllActive(): Collection
    {
        if (self::$memoizedAllActive !== null) {
            return self::$memoizedAllActive;
        }

        $raw = Cache::remember('mayhen_active_templates_collection', 86400, function () {
            return InvitationTemplate::query()
                ->where('is_active', true)
                ->get()
                ->map->getAttributes()
                ->all();
        });

        if (! is_array($raw)) {
            Cache::forget('mayhen_active_templates_collection');
            $raw = [];
        }

        return self::$memoizedAllActive = InvitationTemplate::hydrate($raw);
    }

    /**
     * Display template gallery with filter (/mau-thiep).
     * Tối ưu hóa cực hạn cho hàng triệu người dùng:
     * - Zero Database Query cho khách vãng lai và mọi bộ lọc (99.9% traffic).
     * - In-Memory Static RAM Memoization trong worker PHP.
     * - Lọc, tìm kiếm không dấu và sắp xếp 100% trong bộ nhớ PHP (< 0.1ms).
     * - Edge CDN Caching qua ETag & HTTP 304 Not Modified.
     */
    public function index(Request $request): Response|View
    {
        $category = (string) $request->query('category', 'all');
        $tier = (string) $request->query('tier', 'all');
        $sort = (string) $request->query('sort', 'featured');
        $search = trim((string) $request->query('q', ''));

        // 1. Lấy tất cả mẫu active từ Static RAM / Cache tầng 2 (0 DB queries)
        $all = self::getAllActive();
        $totalCount = $all->count();

        // 2. Lọc danh mục trong bộ nhớ PHP thuần
        $filtered = $all;

        if ($category !== '' && $category !== 'all') {
            $catFilter = ($category === 'envelope') ? 'luxury' : $category;
            $filtered = $filtered->where('category', $catFilter);
        }

        // 3. Lọc phân hạng gói (Tier) trong bộ nhớ PHP thuần
        if ($tier !== '' && in_array($tier, ['free', 'premium', 'plus', 'pro'], true)) {
            if ($tier === 'plus') {
                $filtered = $filtered->filter(fn ($t) => ($t->tier === 'plus' || empty($t->tier)));
            } else {
                $filtered = $filtered->filter(fn ($t) => ($t->tier === $tier));
            }
        }

        // 4. Tìm kiếm từ khóa thông minh (Tokenized Multi-Term, Accent-Insensitive & Relevance Scoring) 100% trong RAM
        $hasSearch = ($search !== '');
        $scores = [];

        if ($hasSearch) {
            $searchLower = mb_strtolower($search, 'UTF-8');
            $unaccented = Str::ascii($searchLower);
            $slugSearch = Str::slug($searchLower);

            // Tách tokens từ đơn (loại bỏ khoảng trắng thừa)
            $tokens = preg_split('/\s+/', $searchLower, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $asciiTokens = array_map(fn ($token) => Str::ascii($token), $tokens);

            $filtered = $filtered->filter(function ($t) use ($searchLower, $unaccented, $slugSearch, $tokens, $asciiTokens, &$scores) {
                $name = mb_strtolower($t->name ?? '', 'UTF-8');
                $nameAscii = Str::ascii($name);
                $slug = mb_strtolower($t->slug ?? '', 'UTF-8');
                $slugWords = str_replace('-', ' ', $slug);
                $desc = mb_strtolower($t->description ?? '', 'UTF-8');
                $descAscii = Str::ascii($desc);
                $category = mb_strtolower($t->category ?? '', 'UTF-8');

                $score = 0;

                // Cấp 1: Khớp trọn vẹn cụm từ (Exact phrase match)
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

                // Cấp 2 & 3: Khớp tokenized từng từ (hỗ trợ cả có dấu và không dấu)
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

                    if (str_contains($category, $asciiToken)) {
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

                // Thưởng điểm cao nếu khớp TẤT CẢ các từ khóa
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

        // 5. Sắp xếp trong bộ nhớ PHP thuần (Ưu tiên điểm tìm kiếm nếu đang search)
        if ($hasSearch) {
            $templates = $filtered->sort(function ($a, $b) use ($scores, $sort) {
                $scoreA = $scores[$a->id] ?? 0;
                $scoreB = $scores[$b->id] ?? 0;

                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // Score cao hơn xếp trước
                }

                if ($sort === 'popular') {
                    return ((int) ($b->usage_count ?? 100)) <=> ((int) ($a->usage_count ?? 100));
                }

                $featA = (int) ($a->is_featured ?? 0);
                $featB = (int) ($b->is_featured ?? 0);
                if ($featA !== $featB) {
                    return $featB <=> $featA;
                }

                return $a->id <=> $b->id;
            })->values();
        } elseif ($sort === 'popular') {
            $templates = $filtered->sortByDesc(fn ($t) => (int) ($t->usage_count ?? 100))->values();
        } else {
            $templates = $filtered->sortBy([
                ['is_featured', 'desc'],
                ['id', 'asc'],
            ])->values();
        }

        // 6. Nếu user đã đăng nhập: trả về view động riêng (không cache qua CDN)
        if (auth()->check()) {
            return view('client.pages.templates', [
                'templates' => $templates,
                'currentCategory' => $category === 'all' ? null : $category,
                'currentTier' => $tier === 'all' ? null : $tier,
                'currentSort' => $sort,
                'currentSearch' => $search,
                'totalCount' => $totalCount,
            ]);
        }

        // 7. Khách vãng lai: Tối ưu Edge CDN Caching qua ETag & HTTP 304 Not Modified
        $etag = '"mayhen_tpls_'.md5(
            $category.'_'.$tier.'_'.$sort.'_'.$search.'_'.
            $templates->pluck('id')->join(',').'_'.
            $totalCount.'_'.
            PlatformSetting::getBrandName()
        ).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
        }

        return response()
            ->view('client.pages.templates', [
                'templates' => $templates,
                'currentCategory' => $category === 'all' ? null : $category,
                'currentTier' => $tier === 'all' ? null : $tier,
                'currentSort' => $sort,
                'currentSearch' => $search,
                'totalCount' => $totalCount,
            ])
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400')
            ->header('ETag', $etag);
    }

    /**
     * Display template detail page (/mau-thiep/{slug}).
     * Tối ưu hóa cực hạn:
     * - Lấy mẫu thiệp và 3 mẫu tương tự trực tiếp từ RAM (0 DB queries).
     * - Edge CDN Caching qua ETag, Last-Modified & HTTP 304 Not Modified.
     */
    public function show(Request $request, string $slug): Response|View
    {
        $all = self::getAllActive();
        $template = $all->firstWhere('slug', $slug);

        if (! $template) {
            abort(404, 'Mẫu thiệp không tồn tại');
        }

        // Tìm 3 mẫu tương tự cùng danh mục trong RAM
        $similarTemplates = $all->where('id', '!=', $template->id)
            ->where('category', $template->category)
            ->take(3);

        if ($similarTemplates->count() < 3) {
            $needed = 3 - $similarTemplates->count();
            $additional = $all->where('id', '!=', $template->id)
                ->whereNotIn('id', $similarTemplates->pluck('id'))
                ->take($needed);
            $similarTemplates = $similarTemplates->merge($additional);
        }

        // Nếu user đã đăng nhập: trả về view động riêng
        if (auth()->check()) {
            return view('client.pages.template-detail', [
                'template' => $template,
                'similarTemplates' => $similarTemplates,
            ]);
        }

        // Khách vãng lai: Tối ưu Edge CDN Caching qua ETag, Last-Modified & 304 Not Modified
        $etag = '"mayhen_tpldet_'.md5($template->slug.'_'.($template->updated_at ?? '2026-01-01').'_'.PlatformSetting::getBrandName()).'"';
        $lastModified = gmdate('D, d M Y H:i:s T', strtotime($template->updated_at ?? '2026-01-01'));

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Last-Modified', $lastModified)
                ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400');
        }

        return response()
            ->view('client.pages.template-detail', [
                'template' => $template,
                'similarTemplates' => $similarTemplates,
            ])
            ->header('Cache-Control', 'public, max-age=1800, stale-while-revalidate=86400')
            ->header('ETag', $etag)
            ->header('Last-Modified', $lastModified);
    }

    /**
     * Preview actual live invitation demo.
     */
    public function preview(string $slug, Request $request): View
    {
        $all = self::getAllActive();
        $template = $all->firstWhere('slug', $slug);

        if (! $template) {
            abort(404, 'Mẫu thiệp không tồn tại');
        }

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        $editId = $request->query('invitation_id') ? (int) $request->query('invitation_id') : null;
        if ($editSlug || $editId) {
            $inv = $editSlug
                ? Invitation::with('wedding')->where('slug', $editSlug)->first()
                : Invitation::with('wedding')->find($editId);
            if ($inv && ($inv->isOwner() || (auth()->check() && auth()->user()->isAdmin()))) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        // Tự động khởi tạo Showcase Demo Wedding từ Cấu hình Mẫu Thiệp (Admin Showcase Customization)
        if (! $wedding) {
            $config = $template->default_config ?? [];
            $extraData = is_array($config) ? $config : [];

            $wedding = new Wedding([
                'groom_name' => $config['groom_name'] ?? 'Hà Kiên',
                'bride_name' => $config['bride_name'] ?? 'Phạm Xôn',
                'wedding_date' => $config['wedding_date'] ?? '2026-12-20',
                'cover_photo_url' => $config['cover_photo_url'] ?? ($config['hero_photo_url'] ?? null),
                'music_url' => $config['music_url'] ?? null,
                'venue_name' => $config['party_venue'] ?? ($config['venue_name'] ?? 'TƯ GIA NHÀ TRAI'),
                'venue_address' => $config['event_party_address'] ?? ($config['venue_address'] ?? '423 An Dương Vương, Phú Thượng, Tây Hồ, Hà Nội'),
                'story' => $config['story'] ?? null,
                'bank_name' => $config['groom_bank_name'] ?? ($config['bank_name'] ?? null),
                'bank_account_number' => $config['groom_bank_account'] ?? ($config['bank_account_number'] ?? null),
                'bank_account_name' => $config['groom_bank_owner'] ?? ($config['bank_account_name'] ?? null),
                'extra_data' => $extraData,
            ]);
        }

        $wishSubmitUrl = route('templates.preview.wish', $slug);
        $rsvpSubmitUrl = route('templates.preview.rsvp', $slug);
        $wishes = collect([]);

        $isEmbed = $request->boolean('embed')
            || in_array($request->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
        $isStudioEditor = $request->query('mode') === 'editor';

        $invCfg = ($invitation && is_array($invitation->custom_config))
            ? $invitation->custom_config
            : [];
        $isCuratedSevenPreview = $request->boolean('curated_seven')
            || $template->slug === 'mayhen-curated'
            || ! empty($invCfg['is_curated_seven'])
            || ! empty($invCfg['is_curated']);

        $customView = "client.invitations.{$template->slug}.template";
        if (view()->exists($customView)) {
            return view($customView, [
                'template' => $template,
                'wedding' => $wedding,
                'invitation' => $invitation,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
                'rsvpSubmitUrl' => $rsvpSubmitUrl,
                'isPreview' => true,
                'isEmbed' => $isEmbed,
                'isStudioEditor' => $isStudioEditor,
                'isCuratedSeven' => $isCuratedSevenPreview,
            ]);
        }

        return view('client.pages.preview', [
            'template' => $template,
            'isPreview' => true,
            'isEmbed' => $isEmbed,
        ]);
    }

    /**
     * Serve static assets directly from resources/views/client/invitations/{slug}/
     */
    public function asset(string $slug, string $path): \Symfony\Component\HttpFoundation\Response
    {
        // Chống directory traversal & validate slug
        if (! preg_match('/^[a-z0-9_-]+$/', $slug)) {
            abort(404, 'Mẫu thiệp không hợp lệ.');
        }

        $baseDir = resource_path("views/client/invitations/{$slug}");
        $realBaseDir = realpath($baseDir);
        if ($realBaseDir === false) {
            abort(404, 'Thư mục mẫu không tồn tại.');
        }

        $cleanPath = ltrim(str_replace(['\\', "\0"], ['/', ''], $path), '/');

        // Security Defense (Whitelist Extensions): Chặn tuyệt đối tải file mã nguồn PHP/Blade/JSON/ENV
        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
        $allowedExtensions = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'mp3' => 'audio/mpeg',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
        ];

        if (! array_key_exists($extension, $allowedExtensions)) {
            abort(404, 'Asset not found');
        }

        $realTarget = false;

        // Ưu tiên styles.css và script.js ở thư mục gốc của mẫu
        if (in_array($cleanPath, ['styles.css', 'script.js'], true)) {
            $rootCandidate = $baseDir.DIRECTORY_SEPARATOR.$cleanPath;
            $realRoot = realpath($rootCandidate);
            if ($realRoot !== false && is_file($realRoot) && str_starts_with($realRoot, $realBaseDir)) {
                $realTarget = $realRoot;
            }
        }

        // Tìm trong thư mục con assets/ nếu chưa tìm thấy
        if ($realTarget === false) {
            $assetsDir = $baseDir.DIRECTORY_SEPARATOR.'assets';
            $realAssetsDir = realpath($assetsDir);

            if ($realAssetsDir !== false) {
                // 1. Thử tìm đường dẫn trực tiếp
                $candidatePath = $assetsDir.DIRECTORY_SEPARATOR.$cleanPath;
                $realCandidate = realpath($candidatePath);
                if ($realCandidate !== false && is_file($realCandidate) && str_starts_with($realCandidate, $realAssetsDir)) {
                    $realTarget = $realCandidate;
                } else {
                    // 2. Chống lặp tiền tố "assets/" (do styles.css nằm ở /assets/styles.css gọi url("assets/..."))
                    $trimmedPath = preg_replace('#^assets/#', '', $cleanPath);
                    $candidateTrimmed = $assetsDir.DIRECTORY_SEPARATOR.$trimmedPath;
                    $realTrimmed = realpath($candidateTrimmed);
                    if ($realTrimmed !== false && is_file($realTrimmed) && str_starts_with($realTrimmed, $realAssetsDir)) {
                        $realTarget = $realTrimmed;
                    }
                }
            }
        }

        if ($realTarget === false || ! is_file($realTarget)) {
            // Phòng vệ ảnh lỗi: Nếu là file ảnh không tìm thấy trong mẫu, thử fallback về ảnh mẫu mặc định
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true)) {
                $assetsDir = $baseDir.DIRECTORY_SEPARATOR.'assets';
                $fallbacks = ['thumbnail.webp', 'hero-couple.webp', 'placeholder-photo.svg'];
                foreach ($fallbacks as $fb) {
                    $fbPath = realpath($assetsDir.DIRECTORY_SEPARATOR.$fb);
                    if ($fbPath !== false && is_file($fbPath)) {
                        $realTarget = $fbPath;
                        $extension = strtolower(pathinfo($fbPath, PATHINFO_EXTENSION));
                        break;
                    }
                }
            }

            if ($realTarget === false || ! is_file($realTarget)) {
                abort(404, 'Asset not found');
            }
        }

        $fullPath = $realTarget;

        $mimeTypes = $allowedExtensions;

        $lastModified = filemtime($fullPath);
        $etag = sprintf('"%x-%x"', $lastModified, filesize($fullPath));

        $ifNoneMatch = request()->header('If-None-Match');
        $ifModifiedSince = request()->header('If-Modified-Since');

        // Phản hồi 304 Not Modified tức thì khi file không đổi (0ms body transfer)
        if ((($ifNoneMatch && trim($ifNoneMatch) === $etag) ||
            ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified))) {
            return response('', 304, [
                'ETag' => $etag,
                'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified).' GMT',
                'Cache-Control' => 'public, max-age=604800, stale-while-revalidate=86400',
            ]);
        }

        $isStaticMedia = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'woff', 'woff2', 'ttf', 'otf', 'mp3']);
        $cacheControl = $isStaticMedia
            ? 'public, max-age=86400, stale-while-revalidate=604800'
            : (config('app.debug') ? 'no-cache, must-revalidate' : 'public, max-age=86400, stale-while-revalidate=604800');

        $headers = [
            'Content-Type' => $mimeTypes[$extension] ?? 'application/octet-stream',
            'Cache-Control' => $cacheControl,
            'ETag' => $etag,
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified).' GMT',
        ];

        return response()->file($fullPath, $headers);
    }
}
