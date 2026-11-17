<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\DocumentationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Bộ nhớ đệm Static RAM trong tiến trình worker PHP (FPM / Octane).
     */
    protected static ?string $memoizedSitemapXml = null;

    protected static ?string $memoizedSitemapEtag = null;

    /**
     * Xóa sạch RAM memoization khi có cập nhật bài viết hoặc mẫu thiệp.
     */
    public static function clearCache(): void
    {
        self::$memoizedSitemapXml = null;
        self::$memoizedSitemapEtag = null;
        Cache::forget('mayhen_sitemap_xml');
        Cache::forget('mayhen_sitemap_docs_xml');
    }

    /**
     * Khởi tạo XML sitemap chính cho toàn bộ website Mây Hẹn (mayhen.vn).
     * Bao gồm: Trang chủ, Kho mẫu thiệp, Toàn bộ chi tiết mẫu thiệp active,
     * Cẩm nang cưới, Toàn bộ bài viết blog đã xuất bản, Dịch vụ, Bảng giá, Docs...
     */
    public function index(Request $request): Response
    {
        // 1. Kiểm tra RAM memoization
        if (self::$memoizedSitemapXml !== null && self::$memoizedSitemapEtag !== null) {
            $etag = self::$memoizedSitemapEtag;
            if ($request->header('If-None-Match') === $etag) {
                return response('', 304, [
                    'ETag' => $etag,
                    'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
                ]);
            }

            return response(self::$memoizedSitemapXml, 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
            ]);
        }

        $xml = Cache::remember('mayhen_sitemap_xml', 86400, function () {
            $now = now()->toIso8601String();

            // 1. Các trang tĩnh & trang điều hướng quan trọng nhất
            $pages = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('templates.index'), 'priority' => '0.95', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('pricing'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('services'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('docs.index'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('how-it-works'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('pricing.advisor'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('contact'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('faq'), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ];

            // 2. Toàn bộ mẫu thiệp cưới đang hoạt động (Templates)
            try {
                $templates = TemplateController::getAllActive();
                foreach ($templates as $tpl) {
                    $lastmod = ! empty($tpl->updated_at)
                        ? $tpl->updated_at->toIso8601String()
                        : $now;

                    $pages[] = [
                        'loc' => route('templates.show', $tpl->slug),
                        'priority' => '0.85',
                        'changefreq' => 'weekly',
                        'lastmod' => $lastmod,
                    ];
                }
            } catch (\Throwable $e) {
                // Phòng vệ an toàn nếu chưa migrate
            }

            // 3. Toàn bộ bài viết Cẩm nang cưới đã xuất bản (Blog Posts)
            try {
                $posts = BlogPost::published()
                    ->select(['id', 'slug', 'updated_at', 'published_at'])
                    ->orderByDesc('published_at')
                    ->get();

                foreach ($posts as $post) {
                    $lastmod = ! empty($post->updated_at)
                        ? $post->updated_at->toIso8601String()
                        : (! empty($post->published_at) ? $post->published_at->toIso8601String() : $now);

                    $pages[] = [
                        'loc' => route('blog.show', $post->slug),
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                        'lastmod' => $lastmod,
                    ];
                }
            } catch (\Throwable $e) {
                // Phòng vệ an toàn nếu chưa migrate
            }

            $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xmlOutput .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($pages as $page) {
                $xmlOutput .= "  <url>\n";
                $xmlOutput .= '    <loc>'.htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
                $xmlOutput .= "    <lastmod>{$page['lastmod']}</lastmod>\n";
                $xmlOutput .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
                $xmlOutput .= "    <priority>{$page['priority']}</priority>\n";
                $xmlOutput .= "  </url>\n";
            }

            $xmlOutput .= '</urlset>';

            return $xmlOutput;
        });

        $etag = '"mayhen_sitemap_'.md5($xml).'"';
        self::$memoizedSitemapXml = $xml;
        self::$memoizedSitemapEtag = $etag;

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
            ]);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
        ]);
    }

    /**
     * Tạo XML Sitemap riêng cho toàn bộ tài liệu hướng dẫn khách hàng (/docs).
     * Tuyệt đối không lập chỉ mục tài liệu quản trị nội bộ (Admin Docs / SOP).
     */
    public function docs(Request $request, DocumentationService $docsService): Response
    {
        $cacheKey = 'mayhen_sitemap_docs_xml';

        $generateXml = function () use ($docsService) {
            $now = now()->toIso8601String();
            $categories = $docsService->getCategories('client');

            $pages = [
                [
                    'loc' => route('docs.index'),
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                    'lastmod' => $now,
                ],
            ];

            foreach ($categories as $cat) {
                foreach ($cat['articles'] as $art) {
                    $lastmod = ! empty($art['updated_at'])
                        ? date('c', strtotime($art['updated_at']))
                        : $now;

                    $pages[] = [
                        'loc' => $art['url'],
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                        'lastmod' => $lastmod,
                    ];
                }
            }

            $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xmlOutput .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($pages as $page) {
                $xmlOutput .= "  <url>\n";
                $xmlOutput .= '    <loc>'.htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
                $xmlOutput .= "    <lastmod>{$page['lastmod']}</lastmod>\n";
                $xmlOutput .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
                $xmlOutput .= "    <priority>{$page['priority']}</priority>\n";
                $xmlOutput .= "  </url>\n";
            }

            $xmlOutput .= '</urlset>';

            return $xmlOutput;
        };

        $xml = app()->isLocal()
            ? $generateXml()
            : Cache::remember($cacheKey, 86400, $generateXml);

        $etag = '"mayhen_docs_sitemap_'.md5($xml).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304, [
                'ETag' => $etag,
                'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
            ]);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'ETag' => $etag,
            'Cache-Control' => 'public, max-age=86400, stale-while-revalidate=43200',
        ]);
    }
}
