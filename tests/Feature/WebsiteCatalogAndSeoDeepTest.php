<?php

namespace Tests\Feature;

use App\Models\InvitationTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WebsiteCatalogAndSeoDeepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * WEB-001 -> WEB-006: Trang chủ marketing hiển thị Hero, CTA, danh sách mẫu nổi bật.
     */
    public function test_home_marketing_page_renders_featured_templates_and_core_navigation_cta(): void
    {
        InvitationTemplate::create([
            'slug' => 'thuoc-phim-tinh-yeu',
            'name' => 'Thước Phim Tình Yêu',
            'category' => 'editorial',
            'thumbnail_url' => '/images/sample.jpg',
            'demo_url' => '/xem-thu/thuoc-phim-tinh-yeu',
            'default_config' => ['tier' => 'pro', 'stats' => ['pairs_used' => 120]],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('MÂY HẸN', false);
        $response->assertSee('Thước Phim Tình Yêu');
        // CTA điều hướng
        $response->assertSee(route('templates.index'));
    }

    /**
     * CAT-001 -> CAT-004: Kho mẫu lọc theo phong cách, gói dịch vụ và loại trừ mẫu inactive.
     */
    public function test_templates_catalog_filters_by_category_tier_and_excludes_inactive(): void
    {
        // 1. Mẫu active Free Minimal
        InvitationTemplate::create([
            'slug' => 'minimal-free-sample',
            'name' => 'Mẫu Tối Giản Miễn Phí',
            'category' => 'minimal',
            'thumbnail_url' => '/images/sample-free.jpg',
            'demo_url' => '/xem-thu/minimal-free-sample',
            'default_config' => ['tier' => 'free', 'stats' => ['pairs_used' => 50]],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => false,
        ]);

        // 2. Mẫu active Pro Luxury
        InvitationTemplate::create([
            'slug' => 'luxury-pro-sample',
            'name' => 'Mẫu Hoàng Gia Pro VIP',
            'category' => 'luxury',
            'thumbnail_url' => '/images/sample-pro.jpg',
            'demo_url' => '/xem-thu/luxury-pro-sample',
            'default_config' => ['tier' => 'pro', 'stats' => ['pairs_used' => 200]],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        // 3. Mẫu inactive (không được hiển thị trên catalog)
        InvitationTemplate::create([
            'slug' => 'inactive-hidden-sample',
            'name' => 'Mẫu Ẩn Chưa Phát Hành',
            'category' => 'minimal',
            'thumbnail_url' => '/images/sample-hidden.jpg',
            'demo_url' => '/xem-thu/inactive-hidden-sample',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => false,
            'is_featured' => false,
        ]);

        // Lọc tất cả: chỉ thấy 2 mẫu active
        $responseAll = $this->get('/mau-thiep');
        $responseAll->assertOk();
        $responseAll->assertSee('Mẫu Tối Giản Miễn Phí');
        $responseAll->assertSee('Mẫu Hoàng Gia Pro VIP');
        $responseAll->assertDontSee('Mẫu Ẩn Chưa Phát Hành');

        // Lọc theo gói free
        Cache::flush();
        $responseFree = $this->get('/mau-thiep?tier=free');
        $responseFree->assertOk();
        $responseFree->assertSee('Mẫu Tối Giản Miễn Phí');
        $responseFree->assertDontSee('Mẫu Hoàng Gia Pro VIP');

        // Lọc theo phong cách luxury
        Cache::flush();
        $responseLuxury = $this->get('/mau-thiep?category=luxury');
        $responseLuxury->assertOk();
        $responseLuxury->assertSee('Mẫu Hoàng Gia Pro VIP');
        $responseLuxury->assertDontSee('Mẫu Tối Giản Miễn Phí');
    }

    /**
     * CAT-005 & CAT-006 & WEB-019: Tìm kiếm kho mẫu theo tên và tìm kiếm tiếng Việt không dấu.
     */
    public function test_templates_catalog_search_by_name_and_unaccented_vietnamese(): void
    {
        InvitationTemplate::create([
            'slug' => 'hac-bach-trao-duyen',
            'name' => 'Hạc Bạch Trao Duyên',
            'category' => 'luxury',
            'thumbnail_url' => '/images/sample-hac.jpg',
            'demo_url' => '/xem-thu/hac-bach-trao-duyen',
            'default_config' => ['tier' => 'pro'],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        InvitationTemplate::create([
            'slug' => 'nang-tho-dong-que',
            'name' => 'Nàng Thơ Đồng Quê',
            'category' => 'floral',
            'thumbnail_url' => '/images/sample-nangtho.jpg',
            'demo_url' => '/xem-thu/nang-tho-dong-que',
            'default_config' => ['tier' => 'plus'],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => false,
        ]);

        // 1. Tìm kiếm đúng chữ có dấu
        Cache::flush();
        $responseAccented = $this->get('/mau-thiep?q=Hạc+Bạch');
        $responseAccented->assertOk();
        $responseAccented->assertSee('Hạc Bạch Trao Duyên');
        $responseAccented->assertDontSee('Nàng Thơ Đồng Quê');
        $responseAccented->assertSee('Kết quả tìm kiếm cho: "<strong>Hạc Bạch</strong>"', false);

        // 2. Tìm kiếm không dấu
        Cache::flush();
        $responseUnaccented = $this->get('/mau-thiep?q=hac+bach');
        $responseUnaccented->assertOk();
        $responseUnaccented->assertSee('Hạc Bạch Trao Duyên');
        $responseUnaccented->assertDontSee('Nàng Thơ Đồng Quê');
    }

    /**
     * CAT-010 & CAT-015: Trang chi tiết mẫu thiệp và bảo vệ từ chối mẫu inactive (404).
     */
    public function test_template_detail_page_renders_details_and_aborts_404_when_inactive(): void
    {
        $tpl = InvitationTemplate::create([
            'slug' => 'chi-tiet-mau-test',
            'name' => 'Mẫu Chi Tiết Test',
            'category' => 'editorial',
            'thumbnail_url' => '/images/sample.jpg',
            'demo_url' => '/xem-thu/chi-tiet-mau-test',
            'default_config' => ['tier' => 'pro'],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $inactiveTpl = InvitationTemplate::create([
            'slug' => 'chi-tiet-inactive',
            'name' => 'Mẫu Inactive Test',
            'category' => 'editorial',
            'thumbnail_url' => '/images/sample.jpg',
            'demo_url' => '/xem-thu/chi-tiet-inactive',
            'default_config' => ['tier' => 'pro'],
            'current_version' => 'v1',
            'is_active' => false,
            'is_featured' => false,
        ]);

        // Mẫu active: 200 OK
        $response = $this->get('/mau-thiep/'.$tpl->slug);
        $response->assertOk();
        $response->assertSee('Mẫu Chi Tiết Test');

        // Mẫu inactive: 404 Not Found
        $responseInactive = $this->get('/mau-thiep/'.$inactiveTpl->slug);
        $responseInactive->assertNotFound();
    }

    /**
     * WEB-022 & SEO: Sitemap XML chuẩn định dạng sitemap.org với Content-Type application/xml.
     */
    public function test_sitemap_xml_generates_valid_schema_and_correct_routes(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $content = $response->getContent();

        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString(route('home'), $content);
        $this->assertStringContainsString(route('templates.index'), $content);
        $this->assertStringContainsString(route('pricing'), $content);
        $this->assertStringContainsString(route('services'), $content);
        $this->assertStringContainsString(route('about'), $content);
        $this->assertStringContainsString(route('contact'), $content);
    }

    /**
     * WEB-022 & WEB-023: robots.txt bảo vệ các route nhạy cảm, ngăn Google lập chỉ mục thiệp riêng tư.
     */
    public function test_robots_txt_disallows_private_and_admin_paths(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /api/', $content);
        $this->assertStringContainsString('Disallow: /tai-khoan/', $content);
        $this->assertStringContainsString('Disallow: /thiep/', $content);
        $this->assertStringContainsString('Disallow: /tao-thiep', $content);
        $this->assertStringContainsString('Disallow: /thanh-toan/', $content);
        $this->assertStringContainsString('Sitemap: https://mayhen.vn/sitemap.xml', $content);
    }

    /**
     * WEB-017 & WEB-018: Trang lỗi 404 thân thiện theo chuẩn Quiet Luxury với CTA về trang chủ.
     */
    public function test_custom_404_error_page_renders_quiet_luxury_ui_with_home_and_templates_ctas(): void
    {
        $response = $this->get('/duong-dan-hoan-toan-khong-ton-tai-tren-he-thong-mayhen');

        $response->assertNotFound();
        $response->assertSee('404');
        $response->assertSee('Lạc bước giữa những lời hẹn');
        $response->assertSee('Về Trang Chủ');
        $response->assertSee(route('home'));
        $response->assertSee(route('templates.index'));
    }
}
