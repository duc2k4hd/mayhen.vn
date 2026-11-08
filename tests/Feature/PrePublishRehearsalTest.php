<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Rehearsal',
        'slug' => 'mau-rehearsal-'.uniqid(),
        'description' => 'Test Template Rehearsal Mode',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->owner->id,
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Minh Anh',
        'wedding_date' => now()->addDays(25),
        'venue_name' => 'Adora Center',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'quoc-bao-minh-anh-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'persona_messages' => [
                'enabled' => true,
                'groups' => [
                    'Bạn Bè' => [
                        'title' => 'Gửi Những Người Bạn Cực Chất',
                        'subtitle' => 'Thời Thanh Xuân & Tuổi Trẻ',
                        'content' => 'Rất mong gặp lại anh em bạn bè quẩy hết mình trong ngày vui của chúng mình!',
                    ],
                    'Khách VIP' => [
                        'title' => 'Kính Gửi Quý Khách Danh Dự',
                        'subtitle' => 'Trân Trọng Đón Tiếp',
                        'content' => 'Sự hiện diện của Quý vị là niềm vinh dự to lớn cho gia đình chúng tôi.',
                    ],
                ],
            ],
        ],
    ]);
});

test('wedding owner can enter pre-publish rehearsal mode', function () {
    $response = $this->actingAs($this->owner)->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'rehearsal' => 1,
    ]));

    $response->assertOk();
    $response->assertSee('mayhen_rehearsal_topbar');
    $response->assertSee('CHẾ ĐỘ TỔNG DUYỆT');
    $response->assertSee('Góc nhìn nhóm khách:');
    $response->assertSee('Nhóm: Bạn Bè');
    $response->assertSee('Nhóm: Khách VIP');
    $response->assertSee('Thoát Tổng Duyệt');
});

test('rehearsal mode switches persona and guest identity seamlessly', function () {
    $response = $this->actingAs($this->owner)->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'rehearsal' => 1,
        'rehearsal_group' => 'Bạn Bè',
    ]));

    $response->assertOk();
    $response->assertSee('mayhen_rehearsal_topbar');
    $response->assertSee('Gửi Những Người Bạn Cực Chất');
    $response->assertSee('Rất mong gặp lại anh em bạn bè quẩy hết mình');
    $response->assertSee('Khách Quý (Bạn Bè)');
});

test('stranger cannot access rehearsal control bar', function () {
    $response = $this->actingAs($this->otherUser)->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'rehearsal' => 1,
        'rehearsal_group' => 'Bạn Bè',
    ]));

    $response->assertOk();
    $response->assertDontSee('mayhen_rehearsal_topbar');
    $response->assertDontSee('CHẾ ĐỘ TỔNG DUYỆT');
});

test('rehearsal control bar is safely hidden inside studio embed iframe', function () {
    $response = $this->actingAs($this->owner)->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'rehearsal' => 1,
        'embed' => 1,
    ]));

    $response->assertOk();
    $response->assertDontSee('mayhen_rehearsal_topbar');
});

test('persona thay thế placeholder động {guest_name}, {salutation}, {couple_name} chính xác khi khách mở thiệp', function () {
    // Cập nhật cấu hình có placeholder
    $config = $this->invitation->custom_config;
    $config['persona_messages']['groups']['Họ Hàng'] = [
        'title' => 'Kính Gửi {salutation} {guest_name}',
        'content' => 'Gia đình chúng con và hai cháu {couple_name} kính mời {salutation} đến dự tiệc.',
    ];
    $this->invitation->update(['custom_config' => $config]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Hùng',
        'salutation' => 'Bác',
        'group_name' => 'Họ Hàng',
        'guest_token' => 'token-bac-hung-123',
    ]);

    $response = $this->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'token' => $guest->guest_token,
    ]));

    $response->assertOk();
    $response->assertSee('Kính Gửi Bác Nguyễn Văn Hùng');
    $response->assertSee('Gia đình chúng con và hai cháu Quốc Bảo &amp; Minh Anh kính mời Bác đến dự tiệc.', false);
});

test('khách không có nhóm hoặc nhóm chưa định nghĩa tự động fallback về thông điệp mặc định', function () {
    $config = $this->invitation->custom_config;
    $config['persona_messages']['groups']['default'] = [
        'title' => 'Lời Ngỏ Trân Quý Từ Hai Đứa',
        'content' => 'Cảm ơn bạn đã luôn đồng hành cùng chúng mình trong suốt chặng đường qua.',
    ];
    $this->invitation->update(['custom_config' => $config]);

    // Khách thuộc nhóm chưa được định nghĩa riêng
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Thuỳ Trang',
        'group_name' => 'Bạn Cũ Cấp 2',
        'guest_token' => 'token-trang-456',
    ]);

    $response = $this->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'token' => $guest->guest_token,
    ]));

    $response->assertOk();
    $response->assertSee('Lời Ngỏ Trân Quý Từ Hai Đứa');
    $response->assertSee('Cảm ơn bạn đã luôn đồng hành cùng chúng mình');
});

test('server không rò rỉ nội dung của các nhóm khác xuống client (zero group leakage)', function () {
    $guestFriend = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Hoàng Dũng',
        'group_name' => 'Bạn Bè',
        'guest_token' => 'token-dung-friend',
    ]);

    $response = $this->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'token' => $guestFriend->guest_token,
    ]));

    $response->assertOk();
    // Thấy nội dung nhóm Bạn Bè
    $response->assertSee('Gửi Những Người Bạn Cực Chất');

    // Tuyệt đối không chứa bất kỳ từ ngữ nào của nhóm Khách VIP
    $response->assertDontSee('Kính Gửi Quý Khách Danh Dự');
    $response->assertDontSee('Sự hiện diện của Quý vị là niềm vinh dự to lớn cho gia đình chúng tôi');
});
