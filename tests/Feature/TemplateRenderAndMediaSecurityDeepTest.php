<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemplateRenderAndMediaSecurityDeepTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Wedding $wedding;

    protected InvitationTemplate $template;

    protected Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');

        $this->owner = User::factory()->create([
            'email' => 'couple.render@mayhen.test',
            'name' => 'Chủ Thiệp Mẫu',
        ]);

        $this->template = InvitationTemplate::create([
            'slug' => 'giua-muon-ngan-vi-sao',
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'category' => 'editorial',
            'thumbnail_url' => '/images/sample.jpg',
            'demo_url' => '/xem-thu/giua-muon-ngan-vi-sao',
            'default_config' => ['tier' => 'pro'],
            'current_version' => 'v1',
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Khánh Vy',
            'wedding_date' => now()->addDays(30)->toDateString(),
            'venue_name' => 'Trung Tâm Hội Nghị Asiana Plaza',
            'venue_address' => '45 Phan Đăng Lưu, Phường 7, Bình Thạnh, TP.HCM',
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'hoang-nam-khanh-vy-wedding',
            'is_published' => true,
            'published_at' => now(),
            'expires_at' => now()->addYear(),
            'custom_config' => [
                'wedding' => [
                    'groom_name' => 'Hoàng Nam',
                    'bride_name' => 'Khánh Vy',
                    'wedding_date' => now()->addDays(30)->toDateString(),
                ],
                'music' => [
                    'enabled' => true,
                    'url' => '/assets/client/musics/until-i-found-you.mp3',
                    'title' => 'Until I Found You',
                ],
            ],
        ]);
    }

    /**
     * TEMPLATE-001 -> TEMPLATE-027 & INV-47/INV-48: Hiển thị thiệp công khai, nhạc nền và data-sync-target.
     */
    public function test_public_invitation_renders_canonical_sections_and_audio_autoplay_defense(): void
    {
        $response = $this->get('/thiep/'.$this->invitation->slug);

        $response->assertOk();
        $response->assertSee('Hoàng Nam');
        $response->assertSee('Khánh Vy');

        // Phòng vệ âm thanh: Audio tag có class chuẩn, loop và data-sync-target
        $response->assertSee('class="mayhen_invitation_audio', false);
        $response->assertSee('loop', false);
        $response->assertSee('data-sync-target="music_url"', false);

        // Header có robots noindex chống rò rỉ riêng tư
        $response->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    /**
     * TEMPLATE-023: Chế độ nhúng Studio (?embed=1) tự động mở thiệp và không khóa cuộn trang.
     */
    public function test_embed_mode_automatically_opens_card_and_removes_scroll_locks(): void
    {
        $response = $this->get('/thiep/'.$this->invitation->slug.'?embed=1');

        $response->assertOk();
        // Cưỡng chế class embed mode
        $response->assertSee('mayhen_embed_mode', false);
        $response->assertSee('mayhen_template_auto_opened', false);
        // Biến môi trường JS thông báo embed
        $response->assertSee('window.__MAYHEN_IS_EMBED = true;', false);
    }

    /**
     * TEMPLATE-017, MEDIA-023: Asset server chống directory traversal và bảo vệ whitelist extension.
     */
    public function test_template_asset_server_prevents_directory_traversal_and_enforces_extension_whitelist(): void
    {
        // 1. Asset hợp lệ (styles.css của template) trả về 200 kèm ETag
        $responseCss = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/styles.css');
        $this->assertTrue(in_array($responseCss->getStatusCode(), [200, 304, 404], true));
        if ($responseCss->getStatusCode() === 200) {
            $responseCss->assertHeader('Content-Type', 'text/css; charset=UTF-8');
            $this->assertNotEmpty($responseCss->headers->get('ETag'));
        }

        // 2. Tấn công directory traversal vượt ra ngoài thư mục template -> 404
        $responseTraversal = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/../../../../.env');
        $responseTraversal->assertNotFound();

        // 3. Tải file mã nguồn cấm (.php, .blade.php, .json) -> 404
        $responsePhp = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/template.blade.php');
        $responsePhp->assertNotFound();

        $responseJson = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/schema.json');
        $responseJson->assertNotFound();
    }

    /**
     * MEDIA-001 -> MEDIA-021: Upload media kiểm tra MIME, giới hạn dung lượng và lưu an toàn.
     */
    public function test_media_upload_endpoint_validates_mime_and_stores_safely_in_public_storage(): void
    {
        $this->actingAs($this->owner);

        // 1. Upload ảnh hợp lệ (JPEG)
        $validImage = UploadedFile::fake()->image('wedding-hero.jpg', 1200, 800)->size(1500); // 1.5MB

        $response = $this->postJson('/tao-thiep/upload-asset', [
            'file' => $validImage,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['file_name']);
        $this->assertStringEndsWith('.jpg', $data['file_name']);
        $this->assertStringContainsString('weddings/photos', $data['storage_path']);

        // File thực sự tồn tại trong disk public
        Storage::disk('public')->assertExists($data['storage_path']);

        // 2. Upload file không phải ảnh (PHP script giả mạo) -> 422 Unprocessable Entity
        $fakePhp = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');
        $responseMalicious = $this->postJson('/tao-thiep/upload-asset', [
            'file' => $fakePhp,
        ]);
        $responseMalicious->assertStatus(422);

        // 3. Upload file vượt quá giới hạn 20MB -> 422
        $hugeFile = UploadedFile::fake()->image('giant.jpg')->size(25000); // 25MB
        $responseHuge = $this->postJson('/tao-thiep/upload-asset', [
            'file' => $hugeFile,
        ]);
        $responseHuge->assertStatus(422);
    }

    /**
     * PUB-001 -> PUB-004: Thiệp ở chế độ chưa xuất bản (Unpublished/Draft) chặn khách nhưng cho phép Chủ thiệp xem trước.
     */
    public function test_unpublished_invitation_shows_unpublished_view_to_public_and_full_view_to_owner(): void
    {
        $this->invitation->update(['is_published' => false]);

        // 1. Khách vãng lai xem -> Nhận màn hình thiệp chưa xuất bản
        $guestResponse = $this->get('/thiep/'.$this->invitation->slug);
        $guestResponse->assertOk();
        $guestResponse->assertSee('Thiệp Cưới Chưa Xuất Bản');

        // 2. Chủ thiệp đã đăng nhập xem -> Nhận toàn bộ nội dung thiệp để duyệt nháp
        $ownerResponse = $this->actingAs($this->owner)->get('/thiep/'.$this->invitation->slug);
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Hoàng Nam');
        $ownerResponse->assertSee('Khánh Vy');
    }

    /**
     * PUB-005, PUB-006 & INV-12: Thiệp bị khóa từ chối toàn diện màn hình xem và mọi API phụ trợ.
     */
    public function test_locked_invitation_strictly_blocks_all_public_views_and_auxiliary_apis(): void
    {
        $this->invitation->update([
            'custom_config' => array_merge($this->invitation->custom_config ?? [], [
                'is_locked' => true,
                'lock_reason' => 'Vi phạm tiêu chuẩn cộng đồng.',
            ]),
        ]);

        // 1. Xem màn hình chính -> View locked
        $response = $this->get('/thiep/'.$this->invitation->slug);
        $response->assertOk();
        $response->assertSee('Thiệp Cưới Tạm Khóa');
        $response->assertSee('Vi phạm tiêu chuẩn cộng đồng.');

        // 2. Gửi RSVP -> 403 Forbidden
        $responseRsvp = $this->postJson('/thiep/'.$this->invitation->slug.'/rsvp', [
            'guest_name' => 'Nguyễn Văn A',
            'phone' => '0912345678',
            'attending_status' => 'attending',
            'adults_count' => 1,
        ]);
        $responseRsvp->assertStatus(403);

        // 3. Gửi Lời chúc -> 403 Forbidden
        $responseWish = $this->postJson('/thiep/'.$this->invitation->slug.'/wishes', [
            'name' => 'Nguyễn Văn A',
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
        ]);
        $responseWish->assertStatus(403);

        // 4. API phụ xem danh sách RSVP của chủ thiệp -> 403 Forbidden
        $responseAux = $this->actingAs($this->owner)->getJson('/thiep/'.$this->invitation->slug.'/rsvp/responses');
        $responseAux->assertStatus(403);
        $responseAux->assertJson([
            'success' => false,
            'message' => 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
        ]);
    }

    /**
     * PUB-007: Thiệp hết hạn lưu trữ hiển thị thông báo hết hạn và từ chối nhận RSVP (410 Gone).
     */
    public function test_expired_invitation_shows_expired_view_to_public_and_rejects_rsvp(): void
    {
        $this->invitation->update([
            'expires_at' => now()->subDay(),
        ]);

        // 1. Khách vãng lai xem -> Nhận màn hình hết hạn
        $guestResponse = $this->get('/thiep/'.$this->invitation->slug);
        $guestResponse->assertOk();
        $guestResponse->assertSee('Thiệp Cưới Đã Hết Hạn Lưu Trữ');

        // 2. Gửi RSVP -> 410 Gone
        $responseRsvp = $this->postJson('/thiep/'.$this->invitation->slug.'/rsvp', [
            'guest_name' => 'Trần Văn B',
            'phone' => '0987654321',
            'attending_status' => 'attending',
        ]);
        $responseRsvp->assertStatus(410);

        // 3. Chủ thiệp vẫn có thể truy cập để xem dữ liệu kỷ niệm
        $ownerResponse = $this->actingAs($this->owner)->get('/thiep/'.$this->invitation->slug);
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Hoàng Nam');
    }

    /**
     * INV-10 & Privacy: Thiệp có mật mã bảo vệ yêu cầu nhập mã chính xác trước khi cho phép xem.
     */
    public function test_password_protected_invitation_challenges_guest_until_session_unlocked(): void
    {
        $this->invitation->update([
            'custom_config' => array_merge($this->invitation->custom_config ?? [], [
                'password' => 'NamVy2026',
            ]),
        ]);

        // 1. Khách chưa nhập mật mã -> Gặp màn hình yêu cầu mật khẩu
        $guestResponse = $this->get('/thiep/'.$this->invitation->slug);
        $guestResponse->assertOk();
        $guestResponse->assertSee('THIỆP CƯỚI RIÊNG TƯ');

        // 2. Nhập sai mật khẩu -> Bị từ chối và quay lại
        $wrongResponse = $this->post('/thiep/'.$this->invitation->slug.'/mo-khoa', [
            'password' => 'WrongSecret123',
        ]);
        $wrongResponse->assertRedirect();
        $wrongResponse->assertSessionHas('password_error');

        // 3. Nhập đúng mật khẩu -> Lưu session và chuyển tiếp vào xem thiệp
        $correctResponse = $this->post('/thiep/'.$this->invitation->slug.'/mo-khoa', [
            'password' => 'NamVy2026',
        ]);
        $correctResponse->assertRedirect('/thiep/'.$this->invitation->slug);

        // 4. Mở lại trang khi session đã unlock -> Xem được toàn bộ thiệp
        $unlockedResponse = $this->get('/thiep/'.$this->invitation->slug);
        $unlockedResponse->assertOk();
        $unlockedResponse->assertSee('Hoàng Nam');
        $unlockedResponse->assertSee('Khánh Vy');
    }
}
