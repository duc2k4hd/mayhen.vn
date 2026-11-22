<?php

use App\Http\Controllers\Client\SitemapController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\InvitationTemplate;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    SitemapController::clearCache();
});

test('sitemap.xml là Sitemap Index hợp lệ trỏ tới toàn bộ sitemap con', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $response->assertHeader('ETag');

    $xml = $response->getContent();

    expect($xml)
        ->toContain('<?xml version="1.0" encoding="UTF-8"?>')
        ->toContain('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
        ->toContain(route('sitemap.pages'))
        ->toContain(route('sitemap.blog'))
        ->toContain(route('sitemap.news'))
        ->toContain(route('sitemap.docs'))
        ->toContain('</sitemapindex>');
});

test('sitemap-pages.xml chứa toàn bộ trang chính và TUYỆT ĐỐI không chứa chi tiết mẫu thiệp /mau-thiep/{slug}', function () {
    // Tạo thử mẫu thiệp active trong CSDL
    InvitationTemplate::create([
        'slug' => 'mau-thiep-test-vip',
        'name' => 'Mẫu thiệp Test VIP',
        'category' => 'hiendai',
        'thumbnail_url' => '/assets/test.jpg',
        'demo_url' => '/test',
        'default_config' => [],
        'is_active' => true,
    ]);

    $response = $this->get('/sitemap-pages.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)
        ->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
        ->toContain(route('home'))
        ->toContain(route('templates.index'))
        ->toContain(route('blog.index'))
        ->toContain(route('pricing'))
        ->toContain(route('services'))
        ->toContain(route('docs.index'))
        ->toContain(route('pricing.advisor'))
        ->toContain(route('about'))
        ->toContain(route('contact'))
        ->toContain(route('faq'))
        ->toContain(route('terms'))
        ->toContain(route('privacy'))
        ->not->toContain('/mau-thiep/')
        ->not->toContain('mau-thiep-test-vip');
});

test('sitemap-blog.xml chỉ chứa bài viết đã xuất bản, loại trừ bài nháp', function () {
    $author = User::factory()->create();
    $category = BlogCategory::create([
        'name' => 'Kinh nghiệm cưới',
        'slug' => 'kinh-nghiem-cuoi',
        'is_active' => true,
    ]);

    $publishedPost = BlogPost::create([
        'user_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Kế hoạch cưới hoàn hảo 2026',
        'slug' => 'ke-hoach-cuoi-hoan-hao-2026',
        'summary' => 'Tóm tắt bài viết cẩm nang',
        'content' => 'Nội dung chi tiết bài viết...',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draftPost = BlogPost::create([
        'user_id' => $author->id,
        'category_id' => $category->id,
        'title' => 'Bài viết bản nháp chưa công khai',
        'slug' => 'bai-viet-ban-nhap-chua-cong-khai',
        'summary' => 'Tóm tắt bản nháp',
        'content' => 'Nội dung nháp...',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->get('/sitemap-blog.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)
        ->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
        ->toContain(route('blog.show', $publishedPost->slug))
        ->not->toContain(route('blog.show', $draftPost->slug));
});

test('sitemap-docs.xml trả về đầy đủ tài liệu hướng dẫn khách hàng', function () {
    $response = $this->get('/sitemap-docs.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)
        ->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">')
        ->toContain(route('docs.index'));
});

test('các endpoint sitemap hỗ trợ ETag và trả về HTTP 304 khi không thay đổi', function () {
    $endpoints = [
        '/sitemap.xml',
        '/sitemap-pages.xml',
        '/sitemap-blog.xml',
        '/sitemap-news.xml',
        '/sitemap-docs.xml',
    ];

    foreach ($endpoints as $uri) {
        $initial = $this->get($uri);
        $initial->assertOk();
        $etag = $initial->headers->get('ETag');

        expect($etag)->not->toBeEmpty();

        $cached = $this->withHeaders(['If-None-Match' => $etag])->get($uri);
        $cached->assertStatus(304);
        expect($cached->getContent())->toBeEmpty();
    }
});

test('sitemap-news.xml chỉ chứa bản tin đã xuất bản và loại trừ bản nháp', function () {
    $publishedNews = NewsPost::create([
        'title' => 'Ra mắt mẫu thiệp cưới mới 2026',
        'slug' => 'ra-mat-mau-thiep-cuoi-moi-2026',
        'category_label' => 'Mẫu Thiệp Mới',
        'summary' => 'Tóm tắt bản tin ra mắt',
        'content' => '<p>Chi tiết bản tin</p>',
        'status' => 'published',
        'published_at' => now()->subHour(),
    ]);

    $draftNews = NewsPost::create([
        'title' => 'Bản tin nháp nội bộ',
        'slug' => 'ban-tin-nhap-noi-bo',
        'category_label' => 'Cập Nhật Hệ Thống',
        'summary' => 'Nháp',
        'content' => '<p>Nháp</p>',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->get('/sitemap-news.xml');
    $response->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    expect($response->getContent())
        ->toContain(route('news.index'))
        ->toContain(route('news.show', $publishedNews->slug))
        ->not->toContain(route('news.show', $draftNews->slug));
});

test('truy cập sitemap khi cache đã ấm đạt 0 database query', function () {
    // Làm ấm cache cho toàn bộ endpoint
    $this->get('/sitemap.xml');
    $this->get('/sitemap-pages.xml');
    $this->get('/sitemap-blog.xml');
    $this->get('/sitemap-docs.xml');

    DB::enableQueryLog();

    $this->get('/sitemap.xml')->assertOk();
    $this->get('/sitemap-pages.xml')->assertOk();
    $this->get('/sitemap-blog.xml')->assertOk();
    $this->get('/sitemap-docs.xml')->assertOk();

    expect(DB::getQueryLog())->toBeEmpty();
    DB::disableQueryLog();
});

test('SitemapController::clearCache xóa sạch cache và static memoization', function () {
    // 1. Tạo cache
    $this->get('/sitemap.xml')->assertOk();
    $this->get('/sitemap-pages.xml')->assertOk();
    $this->get('/sitemap-blog.xml')->assertOk();
    $this->get('/sitemap-docs.xml')->assertOk();

    expect(Cache::has('mayhen_sitemap_index_xml'))->toBeTrue();
    expect(Cache::has('mayhen_sitemap_pages_xml'))->toBeTrue();
    expect(Cache::has('mayhen_sitemap_blog_xml'))->toBeTrue();
    expect(Cache::has('mayhen_sitemap_docs_xml'))->toBeTrue();

    // 2. Xóa cache
    SitemapController::clearCache();

    expect(Cache::has('mayhen_sitemap_index_xml'))->toBeFalse();
    expect(Cache::has('mayhen_sitemap_pages_xml'))->toBeFalse();
    expect(Cache::has('mayhen_sitemap_blog_xml'))->toBeFalse();
    expect(Cache::has('mayhen_sitemap_docs_xml'))->toBeFalse();
});
