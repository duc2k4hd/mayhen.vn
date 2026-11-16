<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\DocumentationService;
use Illuminate\Support\Facades\DB;

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
    $response->assertSee('Căn chỉnh tiêu điểm ảnh cưới');
    $response->assertSee('Smart Focal Point');
    $response->assertSee('MỤC LỤC BÀI VIẾT');
    $response->assertSee('mayhen_docs_detail_help_button');
    $response->assertSee('mayhen_docs_detail_font_stepper');
    $response->assertSee('mayhen_docs_detail_bookmark');
    $response->assertSee('mayhen_docs_detail_bookmarks_panel');
});

test('các bài nhập môn mới xuất hiện trên trang hướng dẫn công khai', function () {
    foreach (['CLI-START-001', 'CLI-START-003', 'CLI-START-004', 'CLI-START-005'] as $articleId) {
        $this->get(route('docs.show', ['category' => 'getting-started', 'slug' => $articleId]))
            ->assertOk()
            ->assertSee('Khi');
    }
});

test('các bài vận hành mới yêu cầu quyền quản trị phù hợp', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $staff = User::factory()->create(['role' => 'staff']);

    foreach (['ADM-SOP-001', 'ADM-SOP-002', 'ADM-SOP-003', 'ADM-SOP-004', 'ADM-SOP-005'] as $articleId) {
        $url = route('admin.docs.show', ['category' => 'sop-onboarding', 'slug' => $articleId]);
        $this->get($url)->assertRedirect(route('admin.login'));
        $this->actingAs($staff)->get($url)->assertForbidden();
        $this->actingAs($admin)->get($url)->assertOk();
        auth()->logout();
    }
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
    $response->assertSee('Đối soát đơn hàng chuyển khoản thủ công');
    $response->assertSee('Mã GD Ngân Hàng / Tham Chiếu Thật');
    $response->assertSee('chưa được kiểm thử');
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
    $startResp->assertSee('Đăng ký tài khoản và khởi tạo thiệp cưới');
    $startResp->assertSee('Tiếp Tục Chọn Mẫu Thiệp');
    $startResp->assertDontSee('Aesthetic Moodboard');

    // 2. Client RSVP
    $rsvpResp = $this->get(route('docs.show', [
        'category' => 'guest-rsvp',
        'slug' => 'CLI-GUEST-004',
    ]));
    $rsvpResp->assertOk();
    $rsvpResp->assertSee('Xác nhận tham dự trên thiệp cưới');
    $rsvpResp->assertSee('không chứng minh');
    $rsvpResp->assertDontSee('Đăng ký khẩu phần ẩm thực');

    // 3. Admin User Customer 360
    $manager = User::factory()->create(['role' => 'manager']);
    $userResp = $this->actingAs($manager)->get(route('admin.docs.show', [
        'category' => 'users-rbac',
        'slug' => 'ADM-USER-001',
    ]));
    $userResp->assertOk();
    $userResp->assertSee('Tra cứu hồ sơ khách hàng và lịch sử liên quan');
    $userResp->assertSee('mọi lượt xem hồ sơ');
    $userResp->assertDontSee('chỉ hiển thị đầy đủ khi nhân viên nhấp xem chi tiết có ghi log');

    // 4. Admin Emergency Offline Cockpit
    $cockResp = $this->actingAs($manager)->get(route('admin.docs.show', [
        'category' => 'checkin-cockpit',
        'slug' => 'ADM-COCK-002',
    ]));
    $cockResp->assertOk();
    $cockResp->assertSee('Xử lý gián đoạn mạng khi điểm danh tại sảnh tiệc');
    $cockResp->assertSee('Chưa xác minh được chức năng điểm danh ngoại tuyến');
    $cockResp->assertDontSee('offline_checkin_queue');
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
    $sopResp->assertSee('Xác định môi trường và tài khoản');
    $sopResp->assertSee('quyền cụ thể còn phụ thuộc từng chức năng');
});

test('các bài hướng dẫn nâng cao Studio V2 hiển thị đầy đủ tính năng AI, Quality Doctor và âm nhạc', function () {
    // 1. Quality Doctor (CLI-EDIT-009)
    $doctorResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-009',
    ]));
    $doctorResp->assertOk();
    $doctorResp->assertSee('Visual Quality Doctor');
    $doctorResp->assertSee('Cap Score Defense');

    // 2. AI Story Writer (CLI-EDIT-007)
    $aiResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-007',
    ]));
    $aiResp->assertOk();
    $aiResp->assertSee('AI Story Writer');
    $aiResp->assertSee('NoProfanity');

    // 3. Audio & Autoplay Policy (CLI-EDIT-006)
    $musicResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-006',
    ]));
    $musicResp->assertOk();
    $musicResp->assertSee('Autoplay Policy');
    $musicResp->assertSee('Wax Seal Handshake');

    // 4. 10 Sections Standard (CLI-EDIT-005)
    $sectionsResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-005',
    ]));
    $sectionsResp->assertOk();
    $sectionsResp->assertSee('10 Section Tiêu Chuẩn');
    $sectionsResp->assertSee('Flow Layout');

    // 5. Publish Diff & Rollback (CLI-EDIT-010)
    $publishResp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-010',
    ]));
    $publishResp->assertOk();
    $publishResp->assertSee('Publish Impact Report');
    $publishResp->assertSee('Emergency Rollback Snapshot');
});

test('trang chi tiết tài liệu không chứa thẻ h1 thừa trong thân bài viết và chỉ có 1 thẻ h1 duy nhất cho tiêu đề', function () {
    $resp = $this->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-001',
    ]));

    $resp->assertOk();
    $content = $resp->getContent();

    // Đảm bảo chỉ có đúng 1 thẻ <h1> trên toàn trang (tiêu đề bài viết)
    expect(substr_count($content, '<h1'))->toBe(1);

    // Kiểm tra cấu trúc mới: có blockquote, hr, callout, figure, img, strong highlight
    expect($content)->toContain('mayhen_docs_blockquote');
    expect($content)->toContain('mayhen_docs_hr');
    expect($content)->toContain('mayhen_docs_callout');
    expect($content)->toContain('mayhen_docs_figure');

    // Kiểm tra tương tự trên Admin Docs SOP
    $admin = User::factory()->create(['role' => 'admin']);
    $adminResp = $this->actingAs($admin)->get(route('admin.docs.show', [
        'category' => 'orders-refunds',
        'slug' => 'ADM-ORDR-003',
    ]));
    $adminResp->assertOk();
    expect(substr_count($adminResp->getContent(), '<h1'))->toBe(1);
});

test('sitemap-docs.xml hợp lệ chứa đầy đủ /docs và toàn bộ docs khách hàng, không index tài liệu admin', function () {
    $resp = $this->get('/sitemap-docs.xml');

    $resp->assertOk();
    $resp->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $content = $resp->getContent();

    // 1. Chứa URL gốc /docs
    expect($content)->toContain(route('docs.index'));

    // 2. Chứa các bài viết client mẫu
    expect($content)->toContain(route('docs.show', ['category' => 'getting-started', 'slug' => 'CLI-START-001']));
    expect($content)->toContain(route('docs.show', ['category' => 'studio-editor', 'slug' => 'CLI-EDIT-005']));
    expect($content)->toContain(route('docs.show', ['category' => 'time-capsule', 'slug' => 'CLI-TIME-004']));

    // 3. Tuyệt đối KHÔNG chứa bất kỳ tài liệu admin SOP nào
    expect($content)->not->toContain('/admin/docs');
    expect($content)->not->toContain('ADM-ORDR-003');
    expect($content)->not->toContain('ADM-USER-001');

    // 4. Đếm số lượng thẻ <loc>: đúng 59 URL (1 trang index /docs + 58 bài client)
    $docsCount = substr_count($content, '<loc>');
    expect($docsCount)->toBe(59);

    // 5. Cấu trúc XML hợp lệ
    expect($content)->toContain('<?xml version="1.0" encoding="UTF-8"?>');
    expect($content)->toContain('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">');
    expect($content)->toContain('</urlset>');
});

test('đảm bảo zero database queries khi truy cập /docs và /docs/{category}/{slug}', function () {
    DB::flushQueryLog();
    DB::enableQueryLog();

    // 1. Truy cập trang chủ /docs
    $indexResp = $this->get(route('docs.index'));
    $indexResp->assertOk();
    expect(DB::getQueryLog())->toBeEmpty();

    // 2. Truy cập chi tiết bài viết
    $detailResp = $this->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-001',
    ]));
    $detailResp->assertOk();
    expect(DB::getQueryLog())->toBeEmpty();

    // 3. Tìm kiếm docs
    $searchResp = $this->getJson(route('docs.search', ['q' => 'tiêu điểm']));
    $searchResp->assertOk();
    expect(DB::getQueryLog())->toBeEmpty();

    // 4. Sitemap docs
    $sitemapResp = $this->get('/sitemap-docs.xml');
    $sitemapResp->assertOk();
    expect(DB::getQueryLog())->toBeEmpty();
});

test('kiểm tra ETag và phản hồi 304 Not Modified tối ưu CDN cho hàng triệu user', function () {
    // 1. Request đầu tiên nhận ETag
    $resp1 = $this->get(route('docs.index'));
    $resp1->assertOk();
    $etag = $resp1->headers->get('ETag');
    expect($etag)->not->toBeNull();
    expect($resp1->headers->get('Cache-Control'))->toContain('public');

    // 2. Request thứ 2 gửi If-None-Match nhận 304 Not Modified
    $resp2 = $this->withHeaders(['If-None-Match' => $etag])->get(route('docs.index'));
    expect($resp2->getStatusCode())->toBe(304);

    // 3. Tương tự cho trang chi tiết bài viết
    $artResp = $this->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-001',
    ]));
    $artResp->assertOk();
    $artEtag = $artResp->headers->get('ETag');
    expect($artEtag)->not->toBeNull();

    $artResp304 = $this->withHeaders(['If-None-Match' => $artEtag])->get(route('docs.show', [
        'category' => 'getting-started',
        'slug' => 'CLI-START-001',
    ]));
    expect($artResp304->getStatusCode())->toBe(304);
});

test('DocumentationService memoization và tốc độ thực thi siêu tốc', function () {
    $service = app(DocumentationService::class);

    // Đo thời gian lấy categories
    $start = microtime(true);
    $categories = $service->getCategories('client');
    $time1 = microtime(true) - $start;

    // Lần 2 lấy từ static memoization
    $start2 = microtime(true);
    $categories2 = $service->getCategories('client');
    $time2 = microtime(true) - $start2;

    expect($categories)->toBe($categories2);
    // Lần 2 gần như 0s (dưới 5ms)
    expect($time2)->toBeLessThan(0.005);
});
