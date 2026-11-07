<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Models\WeddingTimeCapsule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingTimeCapsuleTest extends TestCase
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
            'wedding_date' => now()->format('Y-m-d'),
            'venue_name' => 'Gem Center',
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

    public function test_authenticated_owner_can_access_time_capsule_management(): void
    {
        $response = $this->actingAs($this->user)->get(route('account.time_capsule.index', $this->invitation->id));

        $response->assertStatus(200);
        $response->assertSee('Hộp Thời Gian Kỷ Niệm');
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Ngọc Lan');
    }

    public function test_unauthenticated_user_cannot_access_management(): void
    {
        $response = $this->get(route('account.time_capsule.index', $this->invitation->id));

        $response->assertRedirect(route('login'));
    }

    public function test_other_user_cannot_access_management(): void
    {
        $otherUser = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($otherUser)->get(route('account.time_capsule.index', $this->invitation->id));

        $response->assertStatus(404);
    }

    public function test_owner_can_create_sealed_time_capsule(): void
    {
        $unlockDate = Carbon::now()->addYear()->format('Y-m-d');

        $payload = [
            'sender_type' => 'groom',
            'sender_name' => 'Hoàng Tuấn',
            'recipient_name' => 'Ngọc Lan (Vợ Yêu)',
            'milestone_years' => 1,
            'unlock_date' => $unlockDate,
            'seal_color' => 'ruby',
            'title' => 'Gửi em ngày kỷ niệm 1 năm',
            'content' => 'Một năm trôi qua thật nhanh, cảm ơn em vì luôn bên anh mỗi ngày.',
        ];

        $response = $this->actingAs($this->user)->post(
            route('account.time_capsule.store', $this->invitation->id),
            $payload
        );

        $response->assertRedirect(route('account.time_capsule.index', $this->invitation->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wedding_time_capsules', [
            'wedding_id' => $this->wedding->id,
            'sender_type' => 'groom',
            'title' => 'Gửi em ngày kỷ niệm 1 năm',
            'is_sealed' => true,
            'seal_color' => 'ruby',
        ]);
    }

    public function test_create_capsule_validation_requires_mandatory_fields(): void
    {
        $response = $this->actingAs($this->user)->post(
            route('account.time_capsule.store', $this->invitation->id),
            []
        );

        $response->assertSessionHasErrors(['sender_type', 'sender_name', 'recipient_name', 'milestone_years', 'unlock_date', 'title', 'content']);
    }

    public function test_cannot_unseal_before_unlock_date(): void
    {
        $futureCapsule = WeddingTimeCapsule::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'sender_type' => 'groom',
            'sender_name' => 'Hoàng Tuấn',
            'recipient_name' => 'Ngọc Lan',
            'milestone_years' => 5,
            'unlock_date' => Carbon::now()->addYears(5),
            'seal_color' => 'gold',
            'title' => 'Tâm thư 5 năm',
            'content' => 'Nội dung bí mật tương lai',
            'is_sealed' => true,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('account.time_capsule.unseal', [$this->invitation->id, $futureCapsule->id])
        );

        $response->assertSessionHas('error');
        $futureCapsule->refresh();
        $this->assertTrue($futureCapsule->is_sealed);
        $this->assertNull($futureCapsule->unlocked_at);
    }

    public function test_can_unseal_when_unlock_date_reached(): void
    {
        $readyCapsule = WeddingTimeCapsule::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'sender_type' => 'couple',
            'sender_name' => 'Chúng ta',
            'recipient_name' => 'Cả hai',
            'milestone_years' => 1,
            'unlock_date' => Carbon::now()->subDay(), // Đã đến ngày
            'seal_color' => 'emerald',
            'title' => 'Đã tới lúc mở',
            'content' => 'Chúc mừng kỷ niệm 1 năm đám cưới giấy!',
            'is_sealed' => true,
        ]);

        $response = $this->actingAs($this->user)->post(
            route('account.time_capsule.unseal', [$this->invitation->id, $readyCapsule->id])
        );

        $response->assertRedirect(route('account.time_capsule.index', $this->invitation->id));
        $response->assertSessionHas('success');

        $readyCapsule->refresh();
        $this->assertFalse($readyCapsule->is_sealed);
        $this->assertNotNull($readyCapsule->unlocked_at);
    }

    public function test_owner_can_delete_capsule(): void
    {
        $capsule = WeddingTimeCapsule::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'sender_type' => 'guest',
            'sender_name' => 'Khách mời',
            'recipient_name' => 'Cặp đôi',
            'milestone_years' => 1,
            'unlock_date' => Carbon::now()->addYear(),
            'title' => 'Thư xóa thử',
            'content' => 'Nội dung xóa',
            'is_sealed' => true,
        ]);

        $response = $this->actingAs($this->user)->delete(
            route('account.time_capsule.destroy', [$this->invitation->id, $capsule->id])
        );

        $response->assertRedirect(route('account.time_capsule.index', $this->invitation->id));
        $this->assertDatabaseMissing('wedding_time_capsules', ['id' => $capsule->id]);
    }

    public function test_public_user_can_view_capsules_and_guest_form(): void
    {
        $response = $this->get(route('time_capsule.public', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertSee('WEDDING TIME CAPSULE');
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Ngọc Lan');
        $response->assertSee('Đóng Dấu Sáp & Gửi Vào Hộp Thời Gian', false);
    }

    public function test_public_cannot_see_sealed_content_in_html(): void
    {
        $secretContent = 'DayLaNoiDungBiMatCuaTuongLaiKhongDuocPhepXuatHienTrenHTML12345';

        WeddingTimeCapsule::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'sender_type' => 'groom',
            'sender_name' => 'Hoàng Tuấn',
            'recipient_name' => 'Ngọc Lan',
            'milestone_years' => 3,
            'unlock_date' => Carbon::now()->addYears(3),
            'seal_color' => 'ruby',
            'title' => 'Bức thư bảo mật cao',
            'content' => $secretContent,
            'is_sealed' => true,
        ]);

        $response = $this->get(route('time_capsule.public', $this->invitation->slug));

        $response->assertStatus(200);
        $response->assertSee('Bức thư bảo mật cao');
        // Nội dung tuyệt đối không được xuất hiện trong HTML
        $response->assertDontSee($secretContent);
    }

    public function test_guest_can_submit_sealed_letter(): void
    {
        $payload = [
            'sender_name' => 'Minh Đức (Bạn cấp 3)',
            'recipient_name' => 'Đôi Uyên Ương Tuấn & Lan',
            'milestone_years' => 3,
            'seal_color' => 'gold',
            'title' => 'Lời chúc sau 3 năm chung nhà',
            'content' => 'Mong 3 năm nữa hai bạn đã đón thiên thần nhỏ và mãi hạnh phúc!',
        ];

        $response = $this->post(
            route('time_capsule.guest.store', $this->invitation->slug),
            $payload
        );

        $response->assertRedirect(route('time_capsule.public', $this->invitation->slug));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wedding_time_capsules', [
            'wedding_id' => $this->wedding->id,
            'sender_type' => 'guest',
            'sender_name' => 'Minh Đức (Bạn cấp 3)',
            'title' => 'Lời chúc sau 3 năm chung nhà',
            'milestone_years' => 3,
            'seal_color' => 'gold',
            'is_sealed' => true,
        ]);
    }

    public function test_custom_domain_serves_time_capsule_on_hop_thoi_gian_path(): void
    {
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'custom_domain' => 'tuanlan-future.wedding',
            'status' => 'active',
        ]);

        $response = $this->get('http://tuanlan-future.wedding/hop-thoi-gian');

        $response->assertStatus(200);
        $response->assertSee('WEDDING TIME CAPSULE');
        $response->assertSee('Hoàng Tuấn');
        $response->assertSee('Ngọc Lan');
    }
}
