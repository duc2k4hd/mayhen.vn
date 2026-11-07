<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Parking',
        'slug' => 'mau-parking-'.uniqid(),
        'description' => 'Test Template Parking Concierge',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);
});

test('wedding couple can configure parking guide in settings', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Minh',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => now()->addDays(20),
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-minh-thu-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Minh & Thu Thảo',
        'is_published' => true,
        'custom_config' => [],
    ]);

    $response = $this->actingAs($this->user)->post(route('account.invitations.settings.update', $invitation->id), [
        'slug' => $invitation->slug,
        'title' => 'Thiệp Cưới Tuấn Minh & Thu Thảo',
        'parking_guide' => [
            'enabled' => '1',
            'car_parking' => 'Hầm B2 Tòa nhà Diamond, vào từ cổng số 2',
            'motorbike_parking' => 'Bãi xe máy ngoài trời phía sau hội trường',
            'fee_note' => 'Miễn phí vé xe — vui lòng đóng dấu tại bàn lễ tân',
            'entrance_guide' => 'Đi thang máy trục A lên tầng 3',
            'map_tip' => 'Đường 1 chiều, vui lòng rẽ từ ngã tư CMT8',
        ],
    ]);

    $response->assertRedirect(route('account.invitations.settings', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    expect($invitation->custom_config['parking_guide']['enabled'])->toBeTrue();
    expect($invitation->custom_config['parking_guide']['car_parking'])->toBe('Hầm B2 Tòa nhà Diamond, vào từ cổng số 2');
    expect($invitation->custom_config['parking_guide']['entrance_guide'])->toBe('Đi thang máy trục A lên tầng 3');
});

test('public invitation displays parking concierge widget when enabled', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Duy Anh',
        'bride_name' => 'Phương Linh',
        'wedding_date' => now()->addDays(15),
        'venue_name' => 'Gem Center',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
        'extra_data' => [
            'event_party_map_url' => 'https://maps.google.com/?q=gem+center',
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'duy-anh-phuong-linh-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'parking_guide' => [
                'enabled' => true,
                'car_parking' => 'Hầm B3 Gem Center',
                'motorbike_parking' => 'Bãi xe ngoài sảnh chính',
                'fee_note' => 'Miễn phí cho khách dự tiệc',
                'entrance_guide' => 'Thang cuốn tầng 1 lên sảnh tiệc',
                'map_tip' => 'Đoạn đường thường đông vào 18h',
            ],
        ],
    ]);

    $response = $this->get(route('invitation.show', $invitation->slug));
    $response->assertOk();
    $response->assertSee('mayhen_parking_concierge_root');
    $response->assertSee('Bãi Đỗ Xe & Lối Vào', false);
    $response->assertSee('Hầm B3 Gem Center');
    $response->assertSee('Thang cuốn tầng 1 lên sảnh tiệc');
});

test('parking concierge widget is safely hidden inside studio iframe embed', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Long',
        'bride_name' => 'Yến Vy',
        'wedding_date' => now()->addDays(30),
        'venue_name' => 'Asiana Plaza',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hoang-long-yen-vy-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'parking_guide' => [
                'enabled' => true,
                'car_parking' => 'Hầm gửi xe',
            ],
        ],
    ]);

    $response = $this->get(route('invitation.show', [
        'slug' => $invitation->slug,
        'embed' => 1,
    ]));
    $response->assertOk();
    $response->assertDontSee('mayhen_parking_concierge_root');
});
