<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\SitemapGeneratorService;
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

    protected static ?string $memoizedNewsXml = null;

    protected static ?string $memoizedNewsEtag = null;

    protected static ?string $memoizedDocsXml = null;

    protected static ?string $memoizedDocsEtag = null;

    /**
     * Xóa sạch RAM memoization & Cache, đồng thời tự động xuất lại file .xml tĩnh vào /public khi có thay đổi nội dung.
     */
    public static function clearCache(bool $regenerateStaticFiles = true): void
    {
        self::$memoizedIndexXml = null;
        self::$memoizedIndexEtag = null;
        self::$memoizedPagesXml = null;
        self::$memoizedPagesEtag = null;
        self::$memoizedBlogXml = null;
        self::$memoizedBlogEtag = null;
        self::$memoizedNewsXml = null;
        self::$memoizedNewsEtag = null;
        self::$memoizedDocsXml = null;
        self::$memoizedDocsEtag = null;

        Cache::forget('mayhen_sitemap_index_xml');
        Cache::forget('mayhen_sitemap_pages_xml');
        Cache::forget('mayhen_sitemap_blog_xml');
        Cache::forget('mayhen_sitemap_news_xml');
        Cache::forget('mayhen_sitemap_docs_xml');
        Cache::forget('mayhen_sitemap_xml');

        if ($regenerateStaticFiles && ! app()->runningUnitTests()) {
            try {
                app(SitemapGeneratorService::class)->generateAndWriteAll();
            } catch (\Throwable $e) {
                // Phòng vệ an toàn nếu đang chạy migration hoặc chưa sẵn sàng ghi file
            }
        }
    }

    /**
     * XML Sitemap Index chính (/sitemap.xml).
     */
    public function index(Request $request, SitemapGeneratorService $generator): Response
    {
        if (self::$memoizedIndexXml !== null && self::$memoizedIndexEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedIndexXml, self::$memoizedIndexEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_index_xml', 86400, fn (): string => $generator->buildIndexXml());
        $etag = '"mayhen_sitemap_index_'.md5($xml).'"';

        self::$memoizedIndexXml = $xml;
        self::$memoizedIndexEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap các trang tĩnh & 10 danh mục Thiệp Sự Kiện (/sitemap-pages.xml).
     */
    public function pages(Request $request, SitemapGeneratorService $generator): Response
    {
        if (self::$memoizedPagesXml !== null && self::$memoizedPagesEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedPagesXml, self::$memoizedPagesEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_pages_xml', 86400, fn (): string => $generator->buildPagesXml()['xml']);
        $etag = '"mayhen_sitemap_pages_'.md5($xml).'"';

        self::$memoizedPagesXml = $xml;
        self::$memoizedPagesEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap toàn bộ bài viết Cẩm nang cưới đã xuất bản (/sitemap-blog.xml).
     */
    public function blog(Request $request, SitemapGeneratorService $generator): Response
    {
        if (self::$memoizedBlogXml !== null && self::$memoizedBlogEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedBlogXml, self::$memoizedBlogEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_blog_xml', 86400, fn (): string => $generator->buildBlogXml()['xml']);
        $etag = '"mayhen_sitemap_blog_'.md5($xml).'"';

        self::$memoizedBlogXml = $xml;
        self::$memoizedBlogEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap toàn bộ Tin Tức & Ra Mắt Mẫu Thiệp đã xuất bản (/sitemap-news.xml).
     */
    public function news(Request $request, SitemapGeneratorService $generator): Response
    {
        if (self::$memoizedNewsXml !== null && self::$memoizedNewsEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedNewsXml, self::$memoizedNewsEtag, $request);
        }

        $xml = Cache::remember('mayhen_sitemap_news_xml', 86400, fn (): string => $generator->buildNewsXml()['xml']);
        $etag = '"mayhen_sitemap_news_'.md5($xml).'"';

        self::$memoizedNewsXml = $xml;
        self::$memoizedNewsEtag = $etag;

        return $this->buildXmlResponse($xml, $etag, $request);
    }

    /**
     * XML Sitemap toàn bộ tài liệu hướng dẫn khách hàng (/sitemap-docs.xml).
     */
    public function docs(Request $request, SitemapGeneratorService $generator): Response
    {
        if (self::$memoizedDocsXml !== null && self::$memoizedDocsEtag !== null) {
            return $this->buildXmlResponse(self::$memoizedDocsXml, self::$memoizedDocsEtag, $request);
        }

        $xml = app()->isLocal()
            ? $generator->buildDocsXml()['xml']
            : Cache::remember('mayhen_sitemap_docs_xml', 86400, fn (): string => $generator->buildDocsXml()['xml']);

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
