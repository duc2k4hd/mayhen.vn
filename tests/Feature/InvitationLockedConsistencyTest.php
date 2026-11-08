<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvitationLockedConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::factory()->create();

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Quốc Huy',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => '2026-11-20',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold',
            'slug' => 'celestial-gold',
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/client/images/templates/celestial-gold.jpg',
            'is_active' => true,
        ]);

        // Tạo thiệp đang bị Quản trị viên khóa
        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'quoc-huy-thanh-thao-locked',
            'is_published' => true,
            'custom_config' => [
                'is_locked' => true,
                'lock_reason' => 'Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.',
                'checkin_pin' => '123456',
            ],
        ]);
    }

    public function test_locked_invitation_blocks_memory_vault_public_page(): void
    {
        $response = $this->get(route('memory.vault', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');
    }

    public function test_locked_invitation_blocks_live_projection_wall_and_feed(): void
    {
        // 1. Màn hình HTML
        $response = $this->get(route('live_wall.show', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Live Feed
        $apiResponse = $this->getJson(route('live_wall.feed', $this->invitation->slug));

        $apiResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_photo_drop_portal_and_upload(): void
    {
        // 1. Màn hình HTML Photo Drop
        $response = $this->get(route('photo_drop.show', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Upload ảnh
        $file = UploadedFile::fake()->image('test_photo.jpg');

        $uploadResponse = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
            'uploader_name' => 'Bạn Cùng Bàn',
            'photos' => [$file],
        ]);

        $uploadResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }

    public function test_locked_invitation_blocks_checkin_portal_and_scan(): void
    {
        // 1. Màn hình HTML Cổng Điểm Danh
        $response = $this->get(route('checkin.portal', $this->invitation->slug));

        $response->assertOk()
            ->assertViewIs('client.pages.invitation-locked')
            ->assertSee('Thiệp cưới tạm thời bị khóa do có báo cáo vi phạm nội dung.');

        // 2. API Quét QR Điểm Danh
        $scanResponse = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => 'sample_token_123',
        ]);

        $scanResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ]);
    }
}
