<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Senior',
        'slug' => 'mau-senior-'.uniqid(),
        'description' => 'Test Template Senior Mode',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Đạt',
        'bride_name' => 'Phương Linh',
        'wedding_date' => now()->addDays(45),
        'extra_data' => [
            'groom_phone' => '0988112233',
            'bride_phone' => '0977445566',
            'event_party_map_url' => 'https://maps.google.com/test',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-dat-phuong-linh-'.uniqid(),
        'is_published' => true,
    ]);
});

test('public invitation renders senior friendly accessibility widget', function () {
    $response = $this->get(route('invitation.show', $this->invitation->slug));

    $response->assertOk();
    $response->assertSee('mayhen_senior_widget');
    $response->assertSee('mayhen_senior_toggle');
    $response->assertSee('Chữ Lớn');
    $response->assertSee('Gọi Chú Rể');
    $response->assertSee('Gọi Cô Dâu');
    $response->assertSee('Chỉ Đường');
});

test('senior friendly widget is safely hidden inside studio embed iframe', function () {
    $response = $this->get(route('invitation.show', [
        'slug' => $this->invitation->slug,
        'embed' => 1,
    ]));

    $response->assertOk();
    $response->assertDontSee('mayhen_senior_widget');
});
