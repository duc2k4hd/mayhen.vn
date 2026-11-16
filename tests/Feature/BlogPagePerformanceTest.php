<?php

use App\Http\Controllers\Client\BlogController;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogService;
use App\Services\SeoService;
use App\Services\SiteAnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    BlogController::clearMemoization();
    BlogService::clearCache();
    SeoService::clearMemoization();
    SiteAnnouncementService::clearMemoization();

    $this->category = BlogCategory::create([
        'name' => 'Kinh Nghiệm Cưới',
        'slug' => 'kinh-nghiem-cuoi',
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->post = BlogPost::create([
        'title' => 'Top 10 lưu ý khi in thiệp cưới',
        'slug' => 'top-10-luu-y-khi-in-thiep-cuoi',
        'summary' => 'Những lưu ý quan trọng để có bộ thiệp cưới hoàn hảo.',
        'content' => '<h2>1. Thời gian chuẩn bị</h2><p>Nội dung chi tiết.</p>',
        'category_id' => $this->category->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
        'is_featured' => true,
        'reading_time_minutes' => 3,
        'view_count' => 10,
    ]);
});

test('khách vãng lai truy cập trang cẩm nang cưới thành công với HTTP 200 và đầy đủ headers tối ưu CDN', function () {
    $response = $this->get(route('blog.index'));

    $response->assertOk();
    $response->assertSee('Chuyên Đề & Cảm Hứng Ngày Chung Đôi');
    $response->assertSee('Top 10 lưu ý khi in thiệp cưới');
    $response->assertSee('Kinh Nghiệm Cưới');

    expect($response->headers->has('ETag'))->toBeTrue()
        ->and($response->headers->has('Last-Modified'))->toBeTrue()
        ->and($response->headers->get('Cache-Control'))->toContain('public')
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=1800');
});

test('triệt tiêu tuyệt đối câu truy vấn database (Zero DB Bottleneck) khi phục vụ hàng triệu user vãng lai', function () {
    // Lượt 1: Khởi động ấm cache & static memoization
    $this->get(route('blog.index'))->assertOk();

    // Lượt 2: Bật Query Log và kiểm tra các lượt truy cập tiếp theo
    DB::flushQueryLog();
    DB::enableQueryLog();

    $response = $this->get(route('blog.index'));
    $response->assertOk();

    $queries = DB::getQueryLog();

    // Khẳng định tuyệt đối 0 câu truy vấn DB đè vào MySQL
    expect($queries)->toBeEmpty('Lỗi: Đã phát hiện truy vấn database chạy vào MySQL khi phục vụ trang cẩm nang cưới!');
});

test('trình duyệt hoặc Cloudflare gửi If-None-Match hợp lệ sẽ nhận ngay phản hồi 304 Not Modified', function () {
    $firstResponse = $this->get(route('blog.index'));
    $etag = $firstResponse->headers->get('ETag');

    expect($etag)->not->toBeEmpty();

    // Gửi lại request với If-None-Match
    $conditionalResponse = $this->withHeaders([
        'If-None-Match' => $etag,
    ])->get(route('blog.index'));

    $conditionalResponse->assertStatus(304);
    expect($conditionalResponse->getContent())->toBeEmpty();
    expect($conditionalResponse->headers->get('ETag'))->toBe($etag);
});

test('lọc bài viết theo chuyên mục duy trì Zero DB Bottleneck và phân biệt ETag riêng', function () {
    $baseResponse = $this->get(route('blog.index'));
    $baseEtag = $baseResponse->headers->get('ETag');

    // Lượt 1: Khởi động ấm filter cache
    $filterResponse = $this->get(route('blog.index', ['category' => 'kinh-nghiem-cuoi']));
    $filterResponse->assertOk();
    $filterResponse->assertSee('Top 10 lưu ý khi in thiệp cưới');

    $filterEtag = $filterResponse->headers->get('ETag');
    expect($filterEtag)->not->toBeEmpty();
    expect($filterEtag)->not->toBe($baseEtag);

    // Lượt 2: Bật Query Log và kiểm tra Zero DB query với filter
    DB::flushQueryLog();
    DB::enableQueryLog();

    $repeatFilter = $this->get(route('blog.index', ['category' => 'kinh-nghiem-cuoi']));
    $repeatFilter->assertOk();

    $queries = DB::getQueryLog();
    expect($queries)->toBeEmpty('Lỗi: Lọc cẩm nang theo chuyên mục không được gây truy vấn DB sau khi đã ấm cache!');
});

test('người dùng đã đăng nhập nhận được view động không cache public để bảo toàn phiên cá nhân hóa', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('blog.index'));

    $response->assertOk();
    $response->assertSee('Top 10 lưu ý khi in thiệp cưới');

    // Trang của authenticated user không chứa header ETag public
    expect($response->headers->has('ETag'))->toBeFalse();
});

test('truy cập với query param category bất kỳ không gây lỗi cache hoặc crash', function () {
    $response = $this->get('/cam-nang-cuoi?category=y-tuong-phong-cach-thiep');

    $response->assertOk();
    $response->assertSee('Chuyên Đề & Cảm Hứng Ngày Chung Đôi');
});

test('tìm kiếm với query param q bất kỳ không gây lỗi cache hoặc crash', function () {
    $response = $this->get('/cam-nang-cuoi?q=Quiet%20Luxury');

    $response->assertOk();
    $response->assertSee('Chuyên Đề & Cảm Hứng Ngày Chung Đôi');
});
