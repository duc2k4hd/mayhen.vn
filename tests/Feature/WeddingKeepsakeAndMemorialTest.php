<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingKeepsake;
use App\Models\WeddingWish;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Keepsake',
        'slug' => 'mau-thu-nghiem-keepsake-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Mai Anh',
        'wedding_date' => now()->subDays(5)->toDateString(),
        'venue_name' => 'Gem Center Grand Ballroom',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-anh-keepsake-test-'.uniqid(),
        'status' => 'published',
        'is_published' => true,
    ]);
});

test('guest cannot access keepsake dashboard', function () {
    $response = $this->get(route('account.keepsake.index', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('user cannot access keepsake dashboard of another user', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get(route('account.keepsake.index', $this->invitation->id));

    $response->assertStatus(404);
});

test('owner can view keepsake dashboard with stats', function () {
    // Khách mời đã check in tại sảnh
    $guestCheckedIn = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Trần Văn An',
        'salutation' => 'Bác',
        'phone' => '0988111222',
        'guest_token' => 'token-an-123',
        'checked_in_at' => now()->subHours(2),
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('account.keepsake.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Không Gian Kỷ Niệm Hôn Lễ');
    $response->assertSee('Hoàng Nam');
    $response->assertSee('Mai Anh');
    $response->assertSee('Bác Trần Văn An');
    $response->assertSee('Đã Điểm Danh');
});

test('owner can update thank you message and settings', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.keepsake.update', $this->invitation->id), [
            'thank_you_title' => 'Tri Ân Quý Quan Khách',
            'thank_you_message' => 'Cảm ơn mọi người rất nhiều vì đã đến với chúng mình!',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'show_wishes' => 1,
            'show_photos' => 1,
            'show_video' => 1,
            'hide_rsvp' => 1,
            'hide_gifts' => 0,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('wedding_keepsakes', [
        'wedding_id' => $this->wedding->id,
        'thank_you_title' => 'Tri Ân Quý Quan Khách',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'hide_rsvp' => true,
    ]);
});

test('owner can toggle memorial mode via json api', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.keepsake.toggle', $this->invitation->id), [
            'is_enabled' => true,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_enabled' => true,
        ]);

    $this->assertDatabaseHas('wedding_keepsakes', [
        'wedding_id' => $this->wedding->id,
        'is_enabled' => true,
    ]);
});

test('owner can export memorial keepsake book with wishes', function () {
    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hùng',
        'message' => 'Chúc hai cháu trăm năm hạnh phúc!',
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('account.keepsake.export', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('SỔ LƯU NIỆM NGÀY CƯỚI');
    $response->assertSee('Hoàng Nam');
    $response->assertSee('Mai Anh');
    $response->assertSee('Chúc hai cháu trăm năm hạnh phúc!');
});

test('owner can retrieve personalized thank you message for guest', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Toàn',
        'salutation' => 'Anh',
        'phone' => '0912345678',
        'guest_token' => 'token-test-123',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('account.keepsake.thank_you_message', [$this->invitation->id, $guest->id]));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'guest_name' => 'Nguyễn Văn Toàn',
            'phone' => '0912345678',
        ]);

    $this->assertStringContainsString('Kính gửi Anh Nguyễn Văn Toàn', $response->json('message'));
});

test('public invitation displays memorial banner when keepsake mode is enabled', function () {
    WeddingKeepsake::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'is_enabled' => true,
        'thank_you_title' => 'Tâm Thư Sau Ngày Chung Đôi',
        'thank_you_message' => 'Khoảnh khắc tuyệt vời nhất đời chúng mình.',
        'hide_rsvp' => true,
        'show_wishes' => true,
    ]);

    $response = $this->get(route('invitation.show', $this->invitation->slug));

    $response->assertStatus(200);
    $response->assertSee('Tâm Thư Sau Ngày Chung Đôi');
    $response->assertSee('Khoảnh khắc tuyệt vời nhất đời chúng mình.');
});
