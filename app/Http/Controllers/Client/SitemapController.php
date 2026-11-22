<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\NewsPost;
use App\Services\DocumentationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Bộ nhớ đệm Static RAM trong tiến trình worker PHP (FPM / Octane).
     */
    protected static ?string $memoizedIndexXml = null;

    protected static ?string $memoizedIndexEtag = null;

    protected static ?string $memoizedPagesXml = null;

    protected static ?string $memoizedPagesEtag = null;

    protected static ?string $memoizedBlogXml = null;

    protected static ?string $memoizedBlogEtag = null;

    protected static ?string $memoizedDocsXml = null;

    protected static ?string $memoizedDocsEtag = null;

    /**
     * Xóa sạch RAM memoization & Cache khi có cập nhật bài viết, tài liệu hoặc hệ thống.
     */
    public static function clearCache(): void
    {
        self::$memoizedIndexXml = null;
        self::$memoizedIndexEtag = null;
        self::$memoizedPagesXml = null;
        self::$memoizedPagesEtag = null;
        self::$memoizedBlogXml = null;
        self::$memoizedBlogEtag = null;
        self::$memoizedDocsXml = null;
        self::$memoizedDocsEtag = null;

        Cache::forget('mayhen_sitemap_index_xml');
        Cache::forget('mayhen_sitemap_pages_xml');
        Cache::forget('mayhen_sitemap_blog_xml');
        Cache::forget('mayhen_sitemap_docs_xml');

        // Xóa key sitemap cũ để tương thích hoàn toàn
        Cache::forget('mayhen_sitemap_xml');
    }

    /**
     * Khởi tạo XML Sitemap Index chính (/sitemap.xml) cho toàn bộ website Mây Hẹn (mayhen.vn).
     * Chứa danh sách các sitemap con:
     * - /sitemap-pages.xml : Các trang tĩnh & trang điều hướng công khai
     * - /sitemap-blog.xml  : Toàn bộ bài viết Cẩm nang cưới đã xuất bản
     * - /sitemap-docs.xml  : Toàn bộ tài liệu hướng dẫn khách hàng
     */
    public function index(Request $request): Response
    {
        // 1. Kiểm tra RAM memoization cho Sitemap Index
        if (self::$memoizedIndexXml !== null && self::$memoizedIndexEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedIndexXml, self::$memoizedIndexEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_index_xml', 86400, function () {
            $now = now()->toIso8601String();

            // Xác định thời điểm cập nhật mới nhất của blog
            $latestBlog = null;
            try {
                $latestBlog = BlogPost::published()
                    ->select(['updated_at', 'published_at'])
                    ->orderByDesc('updated_at')
                    ->first();
            } catch (\Throwable $e) {
                // Phòng vệ an toàn nếu chưa migrate
            }

            $blogLastmod = $latestBlog
                ? ($latestBlog->updated_at ?? $latestBlog->published_at ?? now())->toIso8601String()
                : $now;

            $sitemaps = [
                [
                    'loc' => route('sitemap.pages'),
                    'lastmod' => $now,
                ],
                [
                    'loc' => route('sitemap.blog'),
                    'lastmod' => $blogLastmod,
                ],
                [
                    'loc' => route('sitemap.docs'),
                    'lastmod' => $now,
                ],
            ];

            $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xmlOutput .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

            foreach ($sitemaps as $sitemap) {
                $xmlOutput .= "  <sitemap>\n";
                $xmlOutput .= '    <loc>'.htmlspecialchars($sitemap['loc'], ENT_XML1, 'UTF-8')."</loc>\n";
                $xmlOutput .= "    <lastmod>{$sitemap['lastmod']}</lastmod>\n";
                $xmlOutput .= "  </sitemap>\n";
            }

            $xmlOutput .= '</sitemapindex>';

            return $xmlOutput;
        });

        $etag = '"mayhen_sitemap_index_'.md5($xml).'"';
        self::$memoizedIndexXml = $xml;
        self::$memoizedIndexEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap các trang tĩnh & trang thông tin công khai chính (/sitemap-pages.xml).
     * TUYỆT ĐỐI KHÔNG chứa chi tiết mẫu thiệp (/mau-thiep/{slug}) vì các trang này dùng noindex, follow.
     */
    public function pages(Request $request): Response
    {
        // 1. Kiểm tra RAM memoization
        if (self::$memoizedPagesXml !== null && self::$memoizedPagesEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedPagesXml, self::$memoizedPagesEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_pages_xml', 86400, function () {
            $now = now()->toIso8601String();

            $pages = [
                ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('templates.index'), 'priority' => '0.95', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('event_invitations.index'), 'priority' => '0.9', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'tet-nguyen-dan'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'tet-trung-thu'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'giang-sinh-nam-moi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'day-thang-thoi-noi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'sinh-nhat-tre-em'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'sinh-nhat-nguoi-lon'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'tan-gia-nha-moi'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'tat-nien-khai-truong'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'tot-nghiep-thanh-tuu'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('event_invitations.category', 'ngay-le-chuc-mung'), 'priority' => '0.88', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('blog.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('news.index'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $now],
                ['loc' => route('pricing'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('services'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('docs.index'), 'priority' => '0.85', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('pricing.advisor'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('about'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('contact'), 'priority' => '0.7', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('faq'), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $now],
                ['loc' => route('terms'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
                ['loc' => route('privacy'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $now],
            ];

            try {
                $newsPosts = NewsPost::published()
                    ->select(['id', 'slug', 'updated_at', 'published_at'])
                    ->orderByDesc('published_at')
                    ->get();

                foreach ($newsPosts as $newsPost) {
                    $lastmod = ! empty($newsPost->updated_at)
                        ? $newsPost->updated_at->toIso8601String()
                        : (! empty($newsPost->published_at) ? $newsPost->published_at->toIso8601String() : $now);

                    $pages[] = [
                        'loc' => route('news.show', $newsPost->slug),
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                        'lastmod' => $lastmod,
                    ];
                }
            } catch (\Throwable $e) {
                // Phòng vệ an toàn nếu bảng chưa migrate
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

        $etag = '"mayhen_sitemap_pages_'.md5($xml).'"';
        self::$memoizedPagesXml = $xml;
        self::$memoizedPagesEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap toàn bộ bài viết Cẩm nang cưới đã xuất bản (/sitemap-blog.xml).
     */
    public function blog(Request $request): Response
    {
        // 1. Kiểm tra RAM memoization
        if (self::$memoizedBlogXml !== null && self::$memoizedBlogEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedBlogXml, self::$memoizedBlogEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_blog_xml', 86400, function () {
            $now = now()->toIso8601String();
            $pages = [];

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

        $etag = '"mayhen_sitemap_blog_'.md5($xml).'"';
        self::$memoizedBlogXml = $xml;
        self::$memoizedBlogEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * Tạo XML Sitemap riêng cho toàn bộ tài liệu hướng dẫn khách hàng (/docs).
     * Tuyệt đối không lập chỉ mục tài liệu quản trị nội bộ (Admin Docs / SOP).
     */
    public function docs(Request $request, DocumentationService $docsService): Response
    {
        // 1. Kiểm tra RAM memoization
        if (self::$memoizedDocsXml !== null && self::$memoizedDocsEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedDocsXml, self::$memoizedDocsEtag, $request);
        }

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
        self::$memoizedDocsXml = $xml;
        self::$memoizedDocsEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * Tạo response chuẩn XML với ETag, 304 Not Modified và HTTP Cache headers.
     */
    private function buildXmlResponse(string $xml, string $etag, Request $request): Response
    {
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
