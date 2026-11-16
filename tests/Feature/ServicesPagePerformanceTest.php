<?php

use App\Http\Controllers\Client\PageController;
use App\Models\User;
use App\Services\SeoService;
use App\Services\SiteAnnouncementService;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Reset Static RAM memoization trước mỗi test case
    PageController::clearMemoization();
    SeoService::clearMemoization();
    SiteAnnouncementService::clearMemoization();
});

test('khách vãng lai truy cập trang dịch vụ in thiệp thành công với HTTP 200 và đầy đủ headers tối ưu CDN', function () {
    $response = $this->get(route('services'));

    $response->assertOk();
    $response->assertSee('Dịch Vụ In Thiệp Cưới Cao Cấp');
    $response->assertSee('In thiệp cưới tối giản Minimalist');
    $response->assertSee('In thiệp cưới ép kim Foil Stamping');
    $response->assertSee('In thiệp cưới phong thư hoàng gia Wax Seal');

    // Kiểm tra các headers CDN & Browser caching
    expect($response->headers->has('ETag'))->toBeTrue()
        ->and($response->headers->has('Last-Modified'))->toBeTrue()
        ->and($response->headers->get('Cache-Control'))->toContain('public')
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=3600');
});

test('triệt tiêu tuyệt đối câu truy vấn database (Zero DB Bottleneck) khi phục vụ hàng triệu user vãng lai', function () {
    // Lượt 1: Khởi động ấm cache & static memoization
    $this->get(route('services'))->assertOk();

    // Lượt 2: Bật Query Log và kiểm tra các lượt truy cập tiếp theo
    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->get(route('services'));
    $response->assertOk();

    $queries = DB::getQueryLog();

    // Khẳng định tuyệt đối 0 câu truy vấn DB đè vào MySQL
    expect($queries)->toBeEmpty('Lỗi: Đã phát hiện truy vấn database chạy vào MySQL khi phục vụ trang dịch vụ!');
});

test('trình duyệt hoặc Cloudflare gửi If-None-Match hợp lệ sẽ nhận ngay phản hồi 304 Not Modified', function () {
    $firstResponse = $this->get(route('services'));
    $etag = $firstResponse->headers->get('ETag');

    expect($etag)->not->toBeEmpty();

    // Gửi lại request với If-None-Match
    $conditionalResponse = $this->withHeaders([
        'If-None-Match' => $etag,
    ])->get(route('services'));

    $conditionalResponse->assertStatus(304);
    expect($conditionalResponse->getContent())->toBeEmpty();
    expect($conditionalResponse->headers->get('ETag'))->toBe($etag);
});

test('người dùng đã đăng nhập nhận được view động không cache public để bảo toàn phiên cá nhân hóa', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('services'));

    $response->assertOk();
    $response->assertSee('Dịch Vụ In Thiệp Cưới Cao Cấp');

    // Trang của authenticated user không chứa header ETag public
    expect($response->headers->has('ETag'))->toBeFalse();
});
