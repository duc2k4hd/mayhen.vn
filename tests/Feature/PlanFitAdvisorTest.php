<?php

use App\Models\Plan;
use App\Services\PlanFitAdvisorService;

beforeEach(function () {
    Plan::updateOrCreate(['slug' => 'premium'], [
        'name' => 'Hạnh Phúc (Premium)',
        'price' => 149000,
        'duration_days' => 180,
        'max_invitations' => 1,
        'features' => ['Xuất bản link riêng', 'RSVP gia đình'],
        'is_active' => true,
    ]);

    Plan::updateOrCreate(['slug' => 'pro'], [
        'name' => 'Trọn Vẹn (Wedding Pro)',
        'price' => 299000,
        'duration_days' => 730,
        'max_invitations' => 3,
        'features' => ['Nhân bản 3 thiệp', 'Sơ đồ xếp bàn', 'QR check-in'],
        'is_active' => true,
    ]);
});

it('renders plan advisor questionnaire page with initial analysis', function () {
    $response = $this->get(route('pricing.advisor'));

    $response->assertOk()
        ->assertSee('Cố Vấn Đề Xuất Gói Cưới Phù Hợp')
        ->assertSee('ƯỚC TÍNH TIẾT KIỆM SO VỚI THIỆP GIẤY')
        ->assertSee('PLAN FIT ADVISOR');
});

it('recommends wedding pro plan when couple has multiple events or advanced operations', function () {
    $service = app(PlanFitAdvisorService::class);

    $analysis = $service->analyzeNeeds([
        'guest_scale' => 'large',
        'events_count' => 'dual',
        'operations_need' => 'advanced',
        'memory_retention' => 'long',
    ]);

    expect($analysis['recommended_plan']['slug'])->toBe('pro')
        ->and($analysis['fit_score'])->toBeGreaterThanOrEqual(90)
        ->and($analysis['fit_reasons'])->toContain('Hai bạn tổ chức tiệc riêng biệt cho Nhà Trai và Nhà Gái, cần nhân bản thiệp để phân tách sảnh tiệc và gia tiên.')
        ->and($analysis['traditional_cost_comparison']['savings_amount'])->toBeGreaterThan(1000000);
});

it('recommends premium plan when couple only needs intimate single wedding and basic operations', function () {
    $service = app(PlanFitAdvisorService::class);

    $analysis = $service->analyzeNeeds([
        'guest_scale' => 'small',
        'events_count' => 'single',
        'operations_need' => 'basic',
        'memory_retention' => 'short',
    ]);

    expect($analysis['recommended_plan']['slug'])->toBe('premium')
        ->and($analysis['traditional_cost_comparison']['savings_percent'])->toBeGreaterThan(50);
});

it('returns interactive analysis via api endpoint with validation', function () {
    $response = $this->postJson(route('pricing.advisor.analyze'), [
        'guest_scale' => 'medium',
        'events_count' => 'dual',
        'operations_need' => 'advanced',
        'memory_retention' => 'long',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'analysis' => [
                'recommended_plan' => [
                    'slug' => 'pro',
                ],
            ],
        ]);

    // Validation lỗi nếu thiếu tham số
    $invalid = $this->postJson(route('pricing.advisor.analyze'), [
        'guest_scale' => 'unknown',
    ]);

    $invalid->assertUnprocessable();
});
