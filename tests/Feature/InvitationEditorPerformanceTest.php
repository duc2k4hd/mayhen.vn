<?php

namespace Tests\Feature;

use App\Http\Controllers\Client\InvitationEditorController;
use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvitationEditorPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        InvitationEditorController::clearMemoization();
    }

    /**
     * PERF-EDIT-001: Khách vãng lai truy cập /tao-thiep và /tao-thiep?template=... thành công.
     */
    public function test_editor_page_loads_successfully_with_default_and_queried_templates(): void
    {
        $freeTemplate = InvitationTemplate::create([
            'slug' => 'thiep-cuc-quang-free',
            'name' => 'Thiệp Cực Quang Miễn Phí',
            'category' => 'minimal',
            'thumbnail_url' => '/images/cuc-quang.jpg',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => true,
        ]);

        // 1. Truy cập không có query param -> nạp mẫu Free mặc định
        $respDefault = $this->get(route('invitation.create'));
        $respDefault->assertOk();
        $respDefault->assertSee('Thiệp Cực Quang Miễn Phí');

        // 2. Truy cập có query param ?template=...
        $respQueried = $this->get(route('invitation.create', ['template' => $freeTemplate->slug]));
        $respQueried->assertOk();
        $respQueried->assertSee('Thiệp Cực Quang Miễn Phí');
    }

    /**
     * PERF-EDIT-002: Đạt tuyệt đối Zero Database Query khi có cache và memoization.
     */
    public function test_editor_page_achieves_zero_database_queries_on_cached_requests(): void
    {
        $template = InvitationTemplate::create([
            'slug' => 'thiep-to-lua-free',
            'name' => 'Thiệp Tơ Lụa Hoàng Gia',
            'category' => 'luxury',
            'thumbnail_url' => '/images/to-lua.jpg',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => true,
        ]);

        // Request mồi nạp Cache & Static RAM
        $warmup = $this->get(route('invitation.create'));
        $warmup->assertOk();

        // Bật Query Log để kiểm chứng không có bất kỳ câu query nào
        DB::flushQueryLog();
        DB::enableQueryLog();

        // 1. Request thứ 2 vào /tao-thiep
        $resp1 = $this->get(route('invitation.create'));
        $resp1->assertOk();
        $this->assertEmpty(DB::getQueryLog(), 'Truy cập /tao-thiep phải đạt 0 câu truy vấn DB!');

        // 2. Request thứ 3 vào /tao-thiep?template=thiep-to-lua-free
        DB::flushQueryLog();
        $resp2 = $this->get(route('invitation.create', ['template' => $template->slug]));
        $resp2->assertOk();
        $this->assertEmpty(DB::getQueryLog(), 'Truy cập /tao-thiep?template=... phải đạt 0 câu truy vấn DB!');
    }

    /**
     * PERF-EDIT-003: Hỗ trợ ETag và phản hồi HTTP 304 Not Modified cho khách vãng lai.
     */
    public function test_editor_page_supports_etag_and_304_not_modified_for_guests(): void
    {
        $template = InvitationTemplate::create([
            'slug' => 'thiep-vintage-free',
            'name' => 'Thiệp Cưới Vintage',
            'category' => 'floral',
            'thumbnail_url' => '/images/vintage.jpg',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => true,
        ]);

        // Request 1: Nhận ETag và Cache-Control
        $resp1 = $this->get(route('invitation.create'));
        $resp1->assertOk();
        $etag = $resp1->headers->get('ETag');

        $this->assertNotNull($etag);
        $this->assertStringContainsString('public', $resp1->headers->get('Cache-Control'));

        // Request 2: Gửi kèm If-None-Match nhận 304 Not Modified tức thì
        $resp2 = $this->withHeaders(['If-None-Match' => $etag])->get(route('invitation.create'));
        $this->assertEquals(304, $resp2->getStatusCode());

        // Reset headers để request tiếp theo không mang header cũ
        $this->flushHeaders();

        // Request 3: Cho URL có query param ?template=...
        $respQuery = $this->get(route('invitation.create', ['template' => $template->slug]));
        $respQuery->assertOk();
        $queryEtag = $respQuery->headers->get('ETag');
        $this->assertNotNull($queryEtag);

        $respQuery304 = $this->withHeaders(['If-None-Match' => $queryEtag])->get(route('invitation.create', ['template' => $template->slug]));
        $this->assertEquals(304, $respQuery304->getStatusCode());
    }

    /**
     * PERF-EDIT-004: Người dùng đăng nhập không bị cache tĩnh qua CDN (bảo vệ tính riêng tư).
     */
    public function test_editor_page_serves_private_uncached_view_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        InvitationTemplate::create([
            'slug' => 'thiep-tinh-yeu-pro',
            'name' => 'Thiệp Tình Yêu Pro',
            'category' => 'editorial',
            'thumbnail_url' => '/images/pro.jpg',
            'default_config' => ['tier' => 'free'],
            'current_version' => 'v1',
            'is_active' => true,
        ]);

        $resp = $this->actingAs($user)->get(route('invitation.create'));
        $resp->assertOk();

        // Không có ETag công khai và không bật public cache trên CDN
        $this->assertNull($resp->headers->get('ETag'));
    }

    /**
     * PERF-EDIT-005: Mẫu Pro/Plus yêu cầu đăng nhập nếu người dùng chưa có tài khoản.
     */
    public function test_editor_page_redirects_guests_attempting_pro_template(): void
    {
        $proTemplate = InvitationTemplate::create([
            'slug' => 'thiep-hoang-gia-pro',
            'name' => 'Thiệp Hoàng Gia Cao Cấp Pro',
            'category' => 'luxury',
            'thumbnail_url' => '/images/pro.jpg',
            'default_config' => ['tier' => 'pro'],
            'current_version' => 'v1',
            'is_active' => true,
        ]);

        $response = $this->get(route('invitation.create', ['template' => $proTemplate->slug]));
        $response->assertRedirect(route('login', ['redirect' => url('/tao-thiep?template=thiep-hoang-gia-pro')]));
    }
}
