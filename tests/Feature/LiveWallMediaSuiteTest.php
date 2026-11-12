<?php

use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Live Wall',
        'slug' => 'mau-test-live-wall-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Mai Phương',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'The Reverie Saigon',
        'venue_address' => '22-36 Nguyen Hue Blvd, Dist 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'kiet-phuong-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Kiệt & Mai Phương',
        'is_published' => true,
        'custom_config' => [
            'projection_wall' => [
                'theme' => 'luxury_gold',
                'pin' => '',
            ],
        ],
    ]);
});

test('mọi người có thể mở màn hình Live Wall trình chiếu sân khấu tỉ lệ 16:9', function () {
    $response = $this->get(route('live_wall.show', ['slug' => $this->invitation->slug]));

    $response->assertStatus(200);
    $response->assertSee('Tuấn Kiệt');
    $response->assertSee('Mai Phương');
});

test('màn hình Live Wall có PIN yêu cầu xác thực PIN trước khi vào màn chính', function () {
    $this->invitation->update([
        'custom_config' => [
            'projection_wall' => [
                'pin' => '2026',
            ],
        ],
    ]);

    // Khách vãng lai truy cập thấy màn hình nhập PIN
    $response = $this->get(route('live_wall.show', ['slug' => $this->invitation->slug]));
    $response->assertStatus(200);
    $response->assertSee('Bảo Vệ Màn Chiếu Lễ Cưới');

    // Xác thực PIN sai
    $failResponse = $this->post(route('live_wall.verify_pin', ['slug' => $this->invitation->slug]), [
        'pin' => '9999',
    ]);
    $failResponse->assertSessionHasErrors('pin');

    // Xác thực PIN đúng
    $successResponse = $this->post(route('live_wall.verify_pin', ['slug' => $this->invitation->slug]), [
        'pin' => '2026',
    ]);
    $successResponse->assertRedirect(route('live_wall.show', ['slug' => $this->invitation->slug]));

    // Sau khi mở khóa thì xem được
    $unlockedResponse = $this->get(route('live_wall.show', ['slug' => $this->invitation->slug]));
    $unlockedResponse->assertStatus(200);
    $unlockedResponse->assertSee('Tuấn Kiệt');
});

test('API Live Feed cung cấp lời chúc và ảnh photo drop đã duyệt cho màn chiếu', function () {
    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hai',
        'message' => 'Chúc hai cháu đầu bạc răng long!',
        'is_approved' => true,
    ]);

    GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Thanh Tùng',
        'photo_url' => '/storage/guest_photos/tung.jpg',
        'is_approved' => true,
    ]);

    $feedResponse = $this->getJson(route('live_wall.feed', ['slug' => $this->invitation->slug]));

    $feedResponse->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'wishes',
            'photos',
            'stats',
            'server_time',
        ]);
});
