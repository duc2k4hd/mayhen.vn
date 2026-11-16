<?php

namespace Tests\Feature;

use App\Http\Controllers\Client\PageController;
use App\Models\User;
use App\Services\SeoService;
use App\Services\SiteAnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PricingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PageController::clearMemoization();
        SeoService::clearMemoization();
        SiteAnnouncementService::clearMemoization();
    }

    public function test_pricing_page_renders_successfully(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk()
            ->assertViewIs('client.pages.pricing')
            ->assertSee('Bảng Giá Thiệp Cưới Online')
            ->assertSee('Gói Trải Nghiệm')
            ->assertSee('Gói Hạnh Phúc')
            ->assertSee('Gói Trọn Vẹn');
    }

    public function test_pricing_page_contains_plan_fit_advisor_banner(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk()
            ->assertSee('Chưa biết chọn gói nào phù hợp với quy mô hôn lễ?')
            ->assertSee(route('pricing.advisor'));
    }

    public function test_pricing_page_contains_interactive_sandbox_previews(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk()
            ->assertSee('Xem Thử Các Tính Năng Cao Cấp Trong Gói Pro')
            ->assertSee(route('premium.preview', 'seating'))
            ->assertSee(route('premium.preview', 'checkin'))
            ->assertSee(route('premium.preview', 'live-wall'))
            ->assertSee(route('premium.preview', 'memory-vault'));
    }

    public function test_pricing_page_renders_seo_json_ld_schema(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('BreadcrumbList')
            ->assertSee('Product')
            ->assertSee('FAQPage');
    }

    public function test_pricing_page_zero_db_bottleneck_for_guest(): void
    {
        // Lượt 1: Khởi động ấm cache & static memoization
        $this->get(route('pricing'))->assertOk();

        // Lượt 2: Bật Query Log và kiểm tra các lượt truy cập tiếp theo của khách vãng lai
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->get(route('pricing'));
        $response->assertOk();

        $queries = DB::getQueryLog();

        // Khẳng định tuyệt đối 0 câu truy vấn DB đè vào MySQL
        $this->assertEmpty($queries, 'Lỗi: Đã phát hiện truy vấn database chạy vào MySQL khi phục vụ trang bảng giá!');
    }

    public function test_pricing_page_sets_cdn_cache_headers_and_etag(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertOk();
        $this->assertTrue($response->headers->has('ETag'));
        $this->assertTrue($response->headers->has('Last-Modified'));
        $this->assertStringContainsString('public', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=3600', (string) $response->headers->get('Cache-Control'));
    }

    public function test_pricing_page_returns_304_not_modified_when_if_none_match_matches(): void
    {
        $firstResponse = $this->get(route('pricing'));
        $etag = $firstResponse->headers->get('ETag');

        $this->assertNotEmpty($etag);

        $conditionalResponse = $this->withHeaders([
            'If-None-Match' => $etag,
        ])->get(route('pricing'));

        $conditionalResponse->assertStatus(304);
        $this->assertEmpty($conditionalResponse->getContent());
        $this->assertSame($etag, $conditionalResponse->headers->get('ETag'));
    }

    public function test_pricing_page_supports_invitation_query_param_with_distinct_etag_and_zero_db(): void
    {
        $baseResponse = $this->get(route('pricing'));
        $baseEtag = $baseResponse->headers->get('ETag');

        // Truy cập kèm query param invitation_id
        $withParamResponse = $this->get(route('pricing', ['invitation_id' => '99']));
        $withParamResponse->assertOk();
        $withParamResponse->assertSee('Nâng Cấp Gói Thiệp Cưới');

        $paramEtag = $withParamResponse->headers->get('ETag');
        $this->assertNotEmpty($paramEtag);
        $this->assertNotSame($baseEtag, $paramEtag);

        // Kiểm tra zero DB query với query param
        DB::flushQueryLog();
        DB::enableQueryLog();

        $repeatResponse = $this->get(route('pricing', ['invitation_id' => '99']));
        $repeatResponse->assertOk();

        $queries = DB::getQueryLog();
        $this->assertEmpty($queries, 'Lỗi: Truy cập bảng giá có query param không được gây truy vấn database!');
    }

    public function test_pricing_page_serves_dynamic_view_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pricing'));

        $response->assertOk();
        $response->assertSee('Bảng Giá Thiệp Cưới Online');

        // Trang của authenticated user không chứa header ETag public
        $this->assertFalse($response->headers->has('ETag'));
    }
}
