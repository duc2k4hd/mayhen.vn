<?php

use App\Models\InvitationTemplate;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_test@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Ngày Tình Yêu Đơm Hoa',
        'slug' => 'ngay-tinh-yeu-dom-hoa',
        'category' => 'modern',
        'thumbnail_url' => '/mau-thiep/ngay-tinh-yeu-dom-hoa/assets/images/hero-couple.webp',
        'is_active' => true,
        'is_featured' => false,
        'default_config' => [
            'description' => 'Mẫu thiệp cưới thanh lịch và hiện đại',
            'tier' => 'plus',
            'complexity' => 'standard',
        ],
    ]);
});

test('guest cannot access admin templates list or edit', function () {
    $this->get(route('admin.templates.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.templates.edit', $this->template->id))->assertRedirect(route('admin.login'));
});

test('regular customer cannot access admin templates', function () {
    $this->actingAs($this->customer)->get(route('admin.templates.index'))->assertRedirect(route('admin.login'));
    $this->actingAs($this->customer)->get(route('admin.templates.edit', $this->template->id))->assertRedirect(route('admin.login'));
});

test('admin can view templates list with stats and filter', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.templates.index'));

    $response->assertStatus(200);
    $response->assertSee('Ngày Tình Yêu Đơm Hoa');
    $response->assertSee('ngay-tinh-yeu-dom-hoa');
    $response->assertSee('Tổng Mẫu Thiệp');

    // Filter test
    $filterResponse = $this->actingAs($this->admin)->get(route('admin.templates.index', [
        'q' => 'Ngày Tình Yêu',
        'category' => 'modern',
        'status' => 'active',
    ]));

    $filterResponse->assertStatus(200);
    $filterResponse->assertSee('Ngày Tình Yêu Đơm Hoa');
});

test('admin can toggle active and featured status via form or ajax', function () {
    expect($this->template->is_active)->toBeTrue();

    // Toggle active standard
    $response = $this->actingAs($this->admin)->post(route('admin.templates.toggle_active', $this->template->id));
    $response->assertSessionHas('success');
    expect($this->template->fresh()->is_active)->toBeFalse();

    // Toggle active AJAX
    $ajaxResponse = $this->actingAs($this->admin)->postJson(route('admin.templates.toggle_active', $this->template->id));
    $ajaxResponse->assertStatus(200)->assertJson(['success' => true, 'is_active' => true]);
    expect($this->template->fresh()->is_active)->toBeTrue();

    // Toggle featured standard
    expect($this->template->is_featured)->toBeFalse();
    $this->actingAs($this->admin)->post(route('admin.templates.toggle_featured', $this->template->id));
    expect($this->template->fresh()->is_featured)->toBeTrue();

    // Toggle featured AJAX
    $ajaxFeatured = $this->actingAs($this->admin)->postJson(route('admin.templates.toggle_featured', $this->template->id));
    $ajaxFeatured->assertStatus(200)->assertJson(['success' => true, 'is_featured' => false]);
    expect($this->template->fresh()->is_featured)->toBeFalse();
});

test('admin can view template edit page with schema inspector', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.templates.edit', $this->template->id));

    $response->assertStatus(200);
    $response->assertSee('Cấu Hình Thông Tin Mẫu Thiệp');
    $response->assertSee('Trang Bìa Chính');
    $response->assertSee('Hạng Gói Bản Quyền');
});

test('admin can update template metadata and tier', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.templates.update', $this->template->id), [
        'name' => 'Ngày Tình Yêu Đơm Hoa Mới',
        'description' => 'Mô tả đã được cập nhật bởi Admin',
        'category' => 'luxury',
        'tier' => 'pro',
        'is_active' => '1',
        'is_featured' => '1',
    ]);

    $response->assertRedirect(route('admin.templates.index'));
    $response->assertSessionHas('success');

    $fresh = $this->template->fresh();
    expect($fresh->name)->toBe('Ngày Tình Yêu Đơm Hoa Mới');
    expect($fresh->category)->toBe('luxury');
    expect($fresh->tier)->toBe('pro');
    expect($fresh->is_featured)->toBeTrue();
});

test('admin can scan and sync templates discovery', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.templates.scan_discovery'));

    $response->assertRedirect(route('admin.templates.index'));
    $response->assertSessionHas('success');
    expect(InvitationTemplate::count())->toBeGreaterThan(0);
});

test('admin can export templates list as utf-8 bom csv', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.templates.export', [
        'q' => 'Ngày Tình Yêu',
        'status' => 'active',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('content-disposition'))->toContain('kho-mau-thiep-mayhen-');

    $content = $response->streamedContent();
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();
    expect($content)->toContain('Ngày Tình Yêu Đơm Hoa');
    expect($content)->toContain('ngay-tinh-yeu-dom-hoa');
    expect($content)->toContain('Đang hoạt động');
});

test('customer cannot export admin templates csv', function () {
    $this->actingAs($this->customer)
        ->get(route('admin.templates.export'))
        ->assertRedirect(route('admin.login'));
});
