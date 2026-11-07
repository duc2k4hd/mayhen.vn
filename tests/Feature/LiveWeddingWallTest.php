<?php

namespace Tests\Feature;

use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LiveWeddingWallTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'client',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Hoàng Tuấn',
            'bride_name' => 'Ngọc Lan',
            'wedding_date' => now()->addDays(5)->format('Y-m-d'),
            'venue_name' => 'Gem Center',
            'cover_photo_url' => '/storage/cover.jpg',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'slug' => 'giua-muon-ngan-vi-sao',
            'thumbnail_url' => '/assets/thumb.jpg',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'dam-cuoi-hoang-tuan-ngoc-lan',
            'is_published' => true,
        ]);
    }

    public function test_public_user_can_access_live_projection_wall(): void
    {
        $response = $this->get(route('live_wall.show', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Ngọc Lan');
        $response->assertSee('LỄ THÀNH HÔN');
        $response->assertSee('Lời Chúc Trực Tiếp');
    }

    public function test_owner_can_access_projection_wall_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('live_wall.settings', $this->invitation->id));

        $response->assertStatus(200);
        $response->assertSee('Màn Chiếu LED Sân Khấu');
        $response->assertSee('Tùy Chỉnh Giao Diện');
    }

    public function test_unauthenticated_user_cannot_access_settings(): void
    {
        $response = $this->get(route('live_wall.settings', $this->invitation->id));

        $response->assertRedirect(route('login'));
    }

    public function test_stranger_cannot_access_other_user_wall_settings(): void
    {
        $stranger = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($stranger)
            ->get(route('live_wall.settings', $this->invitation->id));

        $response->assertStatus(404);
    }

    public function test_owner_can_update_projection_wall_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('live_wall.settings.update', $this->invitation->id), [
                'theme' => 'light',
                'display_mode' => 'photos_only',
                'speed' => 8,
                'show_qr' => 1,
                'pin' => '8888',
            ]);

        $response->assertRedirect(route('live_wall.settings', $this->invitation->id));
        $response->assertSessionHas('success');

        $this->invitation->refresh();
        $config = $this->invitation->custom_config['projection_wall'];

        $this->assertEquals('light', $config['theme']);
        $this->assertEquals('photos_only', $config['display_mode']);
        $this->assertEquals(8, $config['speed']);
        $this->assertTrue($config['show_qr']);
        $this->assertEquals('8888', $config['pin']);
    }

    public function test_pin_protected_wall_shows_pin_screen_for_visitors(): void
    {
        $this->invitation->custom_config = [
            'projection_wall' => [
                'pin' => '9999',
            ],
        ];
        $this->invitation->save();

        $response = $this->get(route('live_wall.show', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertSee('Bảo Vệ Màn Chiếu Lễ Cưới');
        $response->assertSee('Mở Màn Trình Chiếu');
    }

    public function test_visitor_with_correct_pin_can_unlock_and_view_wall(): void
    {
        $this->invitation->custom_config = [
            'projection_wall' => [
                'pin' => '9999',
            ],
        ];
        $this->invitation->save();

        $response = $this->post(route('live_wall.verify_pin', $this->invitation->slug), [
            'pin' => '9999',
        ]);

        $response->assertRedirect(route('live_wall.show', $this->invitation->slug));

        // Sau khi unlock, truy cập lại thấy màn chiếu
        $wallResponse = $this->get(route('live_wall.show', $this->invitation->slug));
        $wallResponse->assertStatus(200);
        $wallResponse->assertSee('Hoàng Tuấn');
        $wallResponse->assertSee('Ngọc Lan');
    }

    public function test_visitor_with_incorrect_pin_fails_to_unlock(): void
    {
        $this->invitation->custom_config = [
            'projection_wall' => [
                'pin' => '9999',
            ],
        ];
        $this->invitation->save();

        $response = $this->post(route('live_wall.verify_pin', $this->invitation->slug), [
            'pin' => '0000',
        ]);

        $response->assertRedirect(route('live_wall.show', $this->invitation->slug));
        $response->assertSessionHasErrors('pin');
    }

    public function test_owner_can_bypass_pin_protection(): void
    {
        $this->invitation->custom_config = [
            'projection_wall' => [
                'pin' => '9999',
            ],
        ];
        $this->invitation->save();

        $response = $this->actingAs($this->user)
            ->get(route('live_wall.show', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Lời Chúc Trực Tiếp');
    }

    public function test_live_feed_api_returns_approved_photos_and_wishes(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Minh Trí',
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
            'is_approved' => true,
        ]);

        GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Thanh Thảo',
            'photo_url' => '/storage/guest1.jpg',
            'caption' => 'Bàn tiệc số 8 cụng ly',
            'is_approved' => true,
        ]);

        $response = $this->getJson(route('live_wall.feed', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stats' => [
                'photos_count' => 1,
                'wishes_count' => 1,
            ],
        ]);
        $response->assertJsonFragment([
            'name' => 'Minh Trí',
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
        ]);
        $response->assertJsonFragment([
            'url' => '/storage/guest1.jpg',
            'author' => 'Thanh Thảo',
        ]);
    }

    public function test_live_feed_api_filters_out_unapproved_photos_and_wishes(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Spammer',
            'message' => 'Nội dung chưa duyệt',
            'is_approved' => false,
        ]);

        GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Spammer Photo',
            'photo_url' => '/storage/bad.jpg',
            'is_approved' => false,
        ]);

        $response = $this->getJson(route('live_wall.feed', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stats' => [
                'photos_count' => 0,
                'wishes_count' => 0,
            ],
        ]);
        $response->assertJsonMissing([
            'name' => 'Spammer',
        ]);
        $response->assertJsonMissing([
            'url' => '/storage/bad.jpg',
        ]);
    }

    public function test_custom_domain_serves_live_projection_wall_on_trinh_chieu_path(): void
    {
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'custom_domain' => 'damcuoituanlan.wedding',
            'status' => 'active',
        ]);

        $response = $this->get('http://damcuoituanlan.wedding/trinh-chieu');

        $response->assertStatus(200);
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Ngọc Lan');
        $response->assertSee('LỄ THÀNH HÔN');
    }
}
