<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DocumentationService;

test('khách vãng lai truy cập trang chủ Client Docs /docs thành công', function () {
    $response = $this->get(route('docs.index'));

    $response->assertOk();
    $response->assertSee('TRUNG TÂM TRỢ GIÚP MÂY HẸN');
    $response->assertSee('Bắt đầu với Mây Hẹn');
    $response->assertSee('Studio Tạo Thiệp Cưới V2');
    $response->assertSee('TÀI LIỆU HƯỚNG DẪN');
});

test('truy cập bài viết chi tiết Client Docs /docs/{category}/{slug} thành công', function () {
    $response = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-002',
    ]));

    $response->assertOk();
    $response->assertSee('Cách thay đổi ảnh cưới và chọn tiêu điểm Smart Focal Point trong Studio');
    $response->assertSee('Smart Focal Point');
    $response->assertSee('MỤC LỤC BÀI VIẾT');
});

test('tìm kiếm Client Docs trả về kết quả JSON chính xác và không rò rỉ bài Admin', function () {
    $response = $this->getJson(route('docs.search', ['q' => 'tiêu điểm']));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['title'])->toContain('tiêu điểm');

    // Chặn rò rỉ: từ khóa nhạy cảm không được trả về bài của Admin
    $securityCheck = $this->getJson(route('docs.search', ['q' => 'audit log']));
    $securityData = $securityCheck->json('data');
    foreach ($securityData as $item) {
        expect($item['url'])->not->toContain('/admin/docs/');
    }
});

test('truy cập bài viết Client Docs không tồn tại trả về lỗi 404', function () {
    $response = $this->get('/docs/khong-ton-tai/bai-viet-ao-123');

    $response->assertNotFound();
});

test('khách chưa đăng nhập truy cập /admin/docs bị chặn và chuyển hướng về /admin/login', function () {
    $response = $this->get(route('admin.docs.index'));

    $response->assertRedirect(route('admin.login'));
});

test('quản trị viên đăng nhập truy cập /admin/docs thành công', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.docs.index'));

    $response->assertOk();
    $response->assertSee('Sổ tay Quy trình Vận hành Tiêu chuẩn (Admin SOP)');
    $response->assertSee('ADMIN KNOWLEDGE BASE');
    $response->assertSee('QUY TRÌNH VẬN HÀNH TIÊU CHUẨN (SOP)');
});

test('quản trị viên xem chi tiết quy trình SOP /admin/docs/{category}/{slug} thành công', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.docs.show', [
        'category' => 'orders-refunds',
        'slug' => 'ADM-ORDR-002',
    ]));

    $response->assertOk();
    $response->assertSee('ADM-SOP-ORDR-002: Quy trình Đối soát SePay & Kích hoạt Đơn hàng Chuyển khoản Thủ công');
    $response->assertSee('Cam kết Tuân thủ Quy trình Vận hành');
    $response->assertSee('audit_logs');
});

test('tìm kiếm Admin Docs trả về kết quả quy trình SOP nội bộ', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->getJson(route('admin.docs.search', ['q' => 'sepay']));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $data = $response->json('data');
    expect($data)->not->toBeEmpty();
    expect($data[0]['url'])->toContain('/admin/docs/');
});

test('nhân viên vai trò staff không có quyền truy cập quy trình SOP manager bị chặn 403', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
    ]);

    $response = $this->actingAs($staff)->get(route('admin.docs.show', [
        'category' => 'orders-refunds',
        'slug' => 'ADM-ORDR-003',
    ]));

    $response->assertForbidden();
});

test('nội dung markdown trong tài liệu được lọc sạch thẻ nguy hiểm chống XSS', function () {
    $service = app(DocumentationService::class);
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('renderMarkdown');
    $method->setAccessible(true);

    $toc = [];
    $rawMd = "Đây là văn bản thử nghiệm <script>alert('XSS')</script> và <img src=x onerror=alert(1)>";
    $html = $method->invokeArgs($service, [$rawMd, &$toc]);

    expect($html)->not->toContain('<script>');
    expect($html)->not->toContain('<img src=x onerror=alert(1)>');
    expect($html)->toContain('&lt;script&gt;');
});

test('thẻ SEO và OpenGraph hiện diện đầy đủ trên Client Docs và bị chặn trên Admin Docs', function () {
    $clientResp = $this->get(route('docs.index'));
    $clientResp->assertOk();
    $clientResp->assertSee('rel="canonical"', false);
    $clientResp->assertSee('property="og:title"', false);

    $admin = User::factory()->create(['role' => 'admin']);
    $adminResp = $this->actingAs($admin)->get(route('admin.docs.index'));
    $adminResp->assertOk();
    $adminResp->assertSee('<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">', false);
});

test('truy cập các bài viết thí điểm hoàn chỉnh (CLI-START-002, CLI-GUEST-004, ADM-USER-001, ADM-COCK-002) thành công', function () {
    // 1. Client Onboarding
    $startResp = $this->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-002',
    ]));
    $startResp->assertOk();
    $startResp->assertSee('Onboarding 4 bước');
    $startResp->assertSee('Quiet Luxury');

    // 2. Client RSVP
    $rsvpResp = $this->get(route('docs.show', [
        'category' => 'guest-rsvp',
        'slug' => 'CLI-GUEST-004',
    ]));
    $rsvpResp->assertOk();
    $rsvpResp->assertSee('RSVP Đa Sự Kiện');
    $rsvpResp->assertSee('Đăng ký khẩu phần ẩm thực');

    // 3. Admin User Customer 360
    $manager = User::factory()->create(['role' => 'manager']);
    $userResp = $this->actingAs($manager)->get(route('admin.docs.show', [
        'category' => 'users-rbac',
        'slug' => 'ADM-USER-001',
    ]));
    $userResp->assertOk();
    $userResp->assertSee('Customer 360 View');
    $userResp->assertSee('PII Protection');

    // 4. Admin Emergency Offline Cockpit
    $cockResp = $this->actingAs($manager)->get(route('admin.docs.show', [
        'category' => 'checkin-cockpit',
        'slug' => 'ADM-COCK-002',
    ]));
    $cockResp->assertOk();
    $cockResp->assertSee('Emergency Offline Pack');
    $cockResp->assertSee('offline_checkin_queue');
});

test('liên kết trợ giúp ngữ cảnh Wave 04 trên trang Admin Master Topbar trỏ đúng endpoint hợp lệ', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $response = $this->actingAs($admin)->get(route('admin.dashboard'));
    $response->assertOk();
    $response->assertSee(route('admin.docs.index'));
    $response->assertSee('Sổ Tay SOP');
});

test('tệp Javascript docs-main.js được nạp đầy đủ và chứa dữ liệu tìm kiếm hợp lệ trên cả Index và Detail', function () {
    // 1. Kiểm tra trên Index
    $indexResp = $this->get(route('docs.index'));
    $indexResp->assertOk();
    $indexResp->assertSee('assets/client/docs/js/docs-main.js');
    $indexResp->assertSee('id="mayhen_docs_search_data"', false);

    // 2. Kiểm tra trên Detail
    $detailResp = $this->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-001',
    ]));
    $detailResp->assertOk();
    $detailResp->assertSee('assets/client/docs/js/docs-main.js');
    $detailResp->assertSee('id="mayhen_docs_detail_search_data"', false);
    $detailResp->assertSee('id="mayhen_docs_detail_reading_progress"', false);
    $detailResp->assertSee('id="mayhen_docs_detail_search_dialog"', false);

    // 3. Kiểm tra tính toàn vẹn của tệp JS
    $jsPath = public_path('assets/client/docs/js/docs-main.js');
    expect(file_exists($jsPath))->toBeTrue();
    $jsContent = file_get_contents($jsPath);
    expect($jsContent)->toContain('initDocsIndex');
    expect($jsContent)->toContain('initDocsDetail');
    expect($jsContent)->not->toContain('TypeError');
});

test('các bài hướng dẫn ngữ cảnh trọng yếu hiển thị đầy đủ nội dung chuyên sâu và không vỡ liên kết', function () {
    // 1. Studio Editor V2 (CLI-EDIT-001)
    $editResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-001',
    ]));
    $editResp->assertOk();
    $editResp->assertSee('Studio Topbar');
    $editResp->assertSee('Live Sync');

    // 2. CSV Import (CLI-GUEST-002)
    $csvResp = $this->get(route('docs.show', [
        'category' => 'guest-rsvp',
        'slug' => 'CLI-GUEST-002',
    ]));
    $csvResp->assertOk();
    $csvResp->assertSee('UTF-8 with BOM');
    $csvResp->assertSee('CSV Formula Injection Defense');

    // 3. Seating Planner Studio (CLI-SEAT-001)
    $seatResp = $this->get(route('docs.show', [
        'category' => 'seating-planner',
        'slug' => 'CLI-SEAT-001',
    ]));
    $seatResp->assertOk();
    $seatResp->assertSee('Seating Capacity Guardian');
    $seatResp->assertSee('Dự báo bàn chính thức');

    // 4. Reception Checkin Portal (CLI-CKIN-001)
    $ckinResp = $this->get(route('docs.show', [
        'category' => 'checkin-livewall',
        'slug' => 'CLI-CKIN-001',
    ]));
    $ckinResp->assertOk();
    $ckinResp->assertSee('Reception Dry Run');
    $ckinResp->assertSee('RSVP Exception Desk');

    // 5. Admin Onboarding SOP (ADM-SOP-001)
    $manager = User::factory()->create(['role' => 'manager']);
    $sopResp = $this->actingAs($manager)->get(route('admin.docs.show', [
        'category' => 'sop-onboarding',
        'slug' => 'ADM-SOP-001',
    ]));
    $sopResp->assertOk();
    $sopResp->assertSee('Ma trận phân quyền quản trị 3 cấp');
    $sopResp->assertSee('PHP 8.5.10 / Laravel 13.35.0');
});
