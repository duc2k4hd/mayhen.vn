<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeCapsule;
use App\Models\WeddingWish;
use App\Services\PortableWeddingArchiveService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Gate3KeepsakeAndPostWeddingInvariantsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    private Invitation $invitation;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'name' => 'Celestial Romance',
            'slug' => 'celestial-romance',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'category' => 'luxury',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Gia Huy',
            'bride_name' => 'Phương Anh',
            'wedding_date' => Carbon::now()->addMonths(2),
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'huy-anh-wedding',
            'is_published' => true,
            'expires_at' => Carbon::now()->addYear(),
            'custom_config' => [],
        ]);
    }

    public function test_inv41_memory_vault_redirects_public_before_wedding_date_when_disabled(): void
    {
        // 1. Trước ngày cưới và chưa bật vault: Khách vãng lai bị chuyển hướng về thiệp công khai
        $response = $this->get("/ky-niem/{$this->invitation->slug}");
        $response->assertRedirect(route('invitation.show', $this->invitation->slug));

        // 2. Khi chủ tiệc chủ động bật hoặc đám cưới đã diễn ra: Cho phép xem
        $this->wedding->update(['wedding_date' => Carbon::now()->subDay()]);
        $response2 = $this->get("/ky-niem/{$this->invitation->slug}");
        $response2->assertStatus(200);
        $response2->assertViewIs('client.pages.memory-vault');
    }

    public function test_inv42_and_inv43_unapproved_photos_and_wishes_are_never_exposed(): void
    {
        // Lời chúc đã duyệt và chưa duyệt
        $approvedWish = WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Hùng',
            'message' => 'Chúc hai cháu trăm năm hạnh phúc!',
            'is_approved' => true,
        ]);

        $pendingWish = WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Người Lạ',
            'message' => 'Lời chúc chưa được duyệt',
            'is_approved' => false,
        ]);

        // Ảnh khách chụp đã duyệt và chưa duyệt
        $approvedPhoto = GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Bạn Thân',
            'photo_url' => '/uploads/approved.jpg',
            'is_approved' => true,
        ]);

        $pendingPhoto = GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Khách Vãng Lai',
            'photo_url' => '/uploads/pending.jpg',
            'is_approved' => false,
        ]);

        // Kiểm tra Live Wall feed
        $feedRes = $this->getJson("/api/trinh-chieu/{$this->invitation->slug}/feed");
        $feedRes->assertStatus(200);

        $feedPhotos = collect($feedRes->json('photos'))->pluck('url')->all();
        $this->assertContains('/uploads/approved.jpg', $feedPhotos);
        $this->assertNotContains('/uploads/pending.jpg', $feedPhotos);

        $feedWishes = collect($feedRes->json('wishes'))->pluck('message')->all();
        $this->assertContains('Chúc hai cháu trăm năm hạnh phúc!', $feedWishes);
        $this->assertNotContains('Lời chúc chưa được duyệt', $feedWishes);
    }

    public function test_inv44_time_capsule_cannot_be_unsealed_before_unlock_date(): void
    {
        $capsule = WeddingTimeCapsule::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'sender_type' => 'couple',
            'sender_name' => 'Gia Huy & Phương Anh',
            'recipient_name' => 'Hai đứa mình',
            'title' => 'Thư gửi 5 năm sau',
            'content' => 'Lời nhắn bí mật ngày cưới...',
            'unlock_date' => Carbon::now()->addYears(5),
            'is_sealed' => true,
        ]);

        $response = $this->actingAs($this->owner)
            ->postJson(route('account.time_capsule.unseal', [
                'id' => $this->invitation->id,
                'capsuleId' => $capsule->id,
            ]));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        $capsule->refresh();
        $this->assertTrue($capsule->is_sealed);
        $this->assertNull($capsule->unlocked_at);
    }

    public function test_inv49_and_inv50_csv_export_has_utf8_bom_and_sanitizes_formula_injection(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => '=1+1',
            'salutation' => '@SUM(A1:A10)',
            'group_name' => '-cmd| /C calc',
            'phone' => '+84987654321',
            'expected_attendees' => 2,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('guests.export', $this->invitation->id));

        $response->assertStatus(200);

        $content = $response->streamedContent();

        // 1. INV-49: Có mã UTF-8 BOM ở đầu file
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // 2. INV-50: Các ký tự formula injection nguy hiểm phải được thêm dấu nháy đơn '
        $this->assertStringContainsString('\'=1+1', $content);
        $this->assertStringContainsString('\'@SUM(A1:A10)', $content);
        $this->assertStringContainsString('\'-cmd| /C calc', $content);
    }

    public function test_portable_archive_service_generates_valid_offline_zip(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Văn Nam',
            'expected_attendees' => 2,
        ]);

        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Cô Mai',
            'message' => 'Trăm năm hòa hợp!',
            'is_approved' => true,
        ]);

        $service = app(PortableWeddingArchiveService::class);
        $archive = $service->generateArchive($this->invitation);

        $this->assertArrayHasKey('zip_path', $archive);
        $this->assertArrayHasKey('zip_filename', $archive);
        $this->assertFileExists($archive['zip_path']);

        // Dọn dẹp file test sau khi xác minh
        if (file_exists($archive['zip_path'])) {
            unlink($archive['zip_path']);
        }
    }
}
