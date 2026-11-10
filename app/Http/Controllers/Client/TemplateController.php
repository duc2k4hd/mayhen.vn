<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TemplateController extends Controller
{
    /**
     * Display template gallery with filter.
     */
    public function index(Request $request): View
    {
        $category = (string) $request->query('category', 'all');
        $tier = (string) $request->query('tier', 'all');
        $sort = (string) $request->query('sort', 'featured');
        $search = trim((string) $request->query('q', ''));

        $searchKey = $search !== '' ? '_'.md5($search) : '';
        $cacheKey = "mayhen_templates_list_{$category}_{$tier}_{$sort}{$searchKey}";

        $rawTemplates = Cache::remember($cacheKey, 1800, function () use ($category, $tier, $sort, $search) {
            $query = InvitationTemplate::query()->where('is_active', true);

            if ($category && $category !== 'all') {
                $catFilter = ($category === 'envelope') ? 'luxury' : $category;
                $query->where('category', $catFilter);
            }

            if ($tier && in_array($tier, ['free', 'plus', 'pro'])) {
                if ($tier === 'plus') {
                    $query->where(function ($q) {
                        $q->where('default_config->tier', 'plus')
                            ->orWhereNull('default_config->tier');
                    });
                } else {
                    $query->where('default_config->tier', $tier);
                }
            }

            if ($search !== '') {
                $unaccented = Str::ascii($search);
                $slugSearch = Str::slug($search);
                $query->where(function ($q) use ($search, $unaccented, $slugSearch) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$unaccented}%")
                        ->orWhere('description', 'like', "%{$search}%");

                    if ($slugSearch !== '') {
                        $q->orWhere('slug', 'like', "%{$slugSearch}%");
                    }
                });
            }

            if ($sort === 'popular') {
                $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(default_config, '$.stats.pairs_used')) AS UNSIGNED) DESC");
            } else {
                $query->orderBy('is_featured', 'desc')->orderBy('id', 'asc');
            }

            return $query->get()->map->getAttributes()->all();
        });

        $templates = InvitationTemplate::hydrate($rawTemplates);

        $totalCount = Cache::remember('mayhen_templates_total_count', 3600, function () {
            return InvitationTemplate::where('is_active', true)->count();
        });

        return view('client.pages.templates', [
            'templates' => $templates,
            'currentCategory' => $category === 'all' ? null : $category,
            'currentTier' => $tier === 'all' ? null : $tier,
            'currentSort' => $sort,
            'currentSearch' => $search,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Display template detail page.
     */
    public function show(string $slug): View
    {
        $data = Cache::remember("mayhen_template_detail_{$slug}", 3600, function () use ($slug) {
            $template = InvitationTemplate::where('slug', $slug)
                ->where('is_active', true)
                ->first();

            if (! $template) {
                return null;
            }

            $similarTemplates = InvitationTemplate::where('id', '!=', $template->id)
                ->where('is_active', true)
                ->where('category', $template->category)
                ->take(3)
                ->get();

            if ($similarTemplates->count() < 3) {
                $needed = 3 - $similarTemplates->count();
                $additional = InvitationTemplate::where('id', '!=', $template->id)
                    ->where('is_active', true)
                    ->whereNotIn('id', $similarTemplates->pluck('id'))
                    ->take($needed)
                    ->get();
                $similarTemplates = $similarTemplates->merge($additional);
            }

            return [
                'template_raw' => $template->getAttributes(),
                'similar_raw' => $similarTemplates->map->getAttributes()->all(),
            ];
        });

        if (! $data) {
            abort(404, 'Mẫu thiệp không tồn tại');
        }

        $template = new InvitationTemplate;
        $template->setRawAttributes($data['template_raw'], true);
        $similarTemplates = InvitationTemplate::hydrate($data['similar_raw']);

        return view('client.pages.template-detail', [
            'template' => $template,
            'similarTemplates' => $similarTemplates,
        ]);
    }

    /**
     * Preview actual live invitation demo.
     */
    public function preview(string $slug, Request $request): View
    {
        $template = InvitationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        if ($editSlug) {
            $inv = Invitation::with('wedding')->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        $wishSubmitUrl = route('templates.preview.wish', $slug);
        $rsvpSubmitUrl = route('templates.preview.rsvp', $slug);
        $wishes = collect([]);

        $isEmbed = $request->boolean('embed')
            || in_array($request->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);

        $customView = "client.invitations.{$template->slug}.template";
        if (view()->exists($customView)) {
            return view($customView, [
                'template' => $template,
                'wedding' => $wedding,
                'invitation' => $invitation,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
                'rsvpSubmitUrl' => $rsvpSubmitUrl,
                'noNavBottom' => true,
                'isEmbed' => $isEmbed,
            ]);
        }

        return view('client.pages.preview', [
            'template' => $template,
            'isEmbed' => $isEmbed,
        ]);
    }

    /**
     * Serve static assets directly from resources/views/client/invitations/{slug}/
     */
    public function asset(string $slug, string $path): Response
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
                $candidatePath = $assetsDir.DIRECTORY_SEPARATOR.$cleanPath;
                $realCandidate = realpath($candidatePath);
                if ($realCandidate !== false && is_file($realCandidate) && str_starts_with($realCandidate, $realAssetsDir)) {
                    $realTarget = $realCandidate;
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
