<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGift;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRelationsAndBatchModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Minh Trí',
            'bride_name' => 'Thảo My',
            'wedding_date' => '2026-12-25',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold',
            'slug' => 'celestial-gold',
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/client/images/templates/celestial-gold.jpg',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'minh-tri-thao-my-batch',
            'is_published' => true,
        ]);
    }

    public function test_guest_has_many_gifts_relationship(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Lê Hoàng Nam',
            'expected_attendees' => 2,
        ]);

        $gift1 = WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'sender_name' => 'Lê Hoàng Nam',
            'amount' => 1000000,
            'payment_method' => 'bank_transfer',
        ]);

        $gift2 = WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'sender_name' => 'Lê Hoàng Nam',
            'amount' => 500000,
            'payment_method' => 'cash',
        ]);

        $this->assertCount(2, $guest->gifts);
        $this->assertTrue($guest->gifts->contains($gift1));
        $this->assertTrue($guest->gifts->contains($gift2));
    }

    public function test_owner_can_approve_all_pending_guest_photos(): void
    {
        GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Bạn A',
            'photo_url' => 'https://mayhen.vn/storage/guest_photos/1.jpg',
            'is_approved' => false,
        ]);

        GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Bạn B',
            'photo_url' => 'https://mayhen.vn/storage/guest_photos/2.jpg',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('photo_drop.approveAll', $this->invitation->id));

        $response->assertRedirect();
        $this->assertEquals(2, $this->wedding->guestPhotos()->approved()->count());
        $this->assertEquals(0, $this->wedding->guestPhotos()->pending()->count());
    }

    public function test_other_user_cannot_approve_all_guest_photos(): void
    {
        GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Bạn C',
            'photo_url' => 'https://mayhen.vn/storage/guest_photos/3.jpg',
            'is_approved' => false,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->post(route('photo_drop.approveAll', $this->invitation->id));

        $response->assertNotFound();
        $this->assertEquals(1, $this->wedding->guestPhotos()->pending()->count());
    }

    public function test_owner_can_approve_all_hidden_wedding_wishes(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Người Chúc 1',
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
            'is_approved' => false,
        ]);

        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Người Chúc 2',
            'message' => 'Hôn lễ thật ấm cúng và ngọt ngào!',
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('account.wishes.approve_all', $this->invitation->id));

        $response->assertRedirect();
        $this->assertEquals(2, $this->wedding->wishes()->where('is_approved', true)->count());
        $this->assertEquals(0, $this->wedding->wishes()->where('is_approved', false)->count());
    }

    public function test_other_user_cannot_approve_all_wedding_wishes(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Người Chúc 3',
            'message' => 'Mãi mãi bên nhau nhé!',
            'is_approved' => false,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->post(route('account.wishes.approve_all', $this->invitation->id));

        $response->assertForbidden();
        $this->assertEquals(1, $this->wedding->wishes()->where('is_approved', false)->count());
    }

    public function test_owner_can_view_wishes_print_page_and_other_user_is_forbidden(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Năm',
            'message' => 'Chúc hai cháu đầu bạc răng long!',
            'is_approved' => true,
        ]);

        // 1. Chủ sở hữu truy cập trang in sổ lưu bút
        $response = $this->actingAs($this->user)
            ->get(route('account.wishes.print', $this->invitation->id));

        $response->assertOk()
            ->assertViewIs('client.pages.account.wishes-print')
            ->assertSee('Sổ Lưu Bút Kỷ Niệm')
            ->assertSee('Bác Năm')
            ->assertSee('Chúc hai cháu đầu bạc răng long!');

        // 2. Người dùng khác cố tình truy cập -> Bị chặn 403
        $otherUser = User::factory()->create();
        $otherResponse = $this->actingAs($otherUser)
            ->get(route('account.wishes.print', $this->invitation->id));

        $otherResponse->assertForbidden();
    }
}
