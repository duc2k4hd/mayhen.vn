<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingPageTest extends TestCase
{
    use RefreshDatabase;

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
}
