<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Persona',
        'slug' => 'mau-test-persona-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đức Tuấn',
        'bride_name' => 'Bảo Trâm',
        'wedding_date' => now()->addMonths(5)->toDateString(),
        'venue_name' => 'Gem Center',
        'venue_address' => '8 Nguyen Binh Khiem St, District 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-tram-'.uniqid(),
        'title' => 'Thiệp Cưới Đức Tuấn & Bảo Trâm',
        'is_published' => true,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Hoàng Long',
        'group_name' => 'Bạn Đại Học',
        'guest_token' => 'TOKEN_LONG_'.uniqid(),
    ]);
});

test('chủ thiệp có thể truy cập Studio Cá Nhân Hóa Lời Ngỏ Theo Nhóm Khách', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.persona.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Lời Ngỏ');
    $response->assertSee('Đức Tuấn');
    $response->assertSee('Bảo Trâm');
});

test('người dùng khác bị 403 khi truy cập Persona Studio của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.persona.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật cấu hình lời ngỏ cá nhân hóa theo từng nhóm khách', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.persona.update', $this->invitation->id), [
            'enabled' => true,
            'groups' => [
                'Ban Dai Hoc' => [
                    'title' => 'Gửi Những Người Bạn Đại Học',
                    'subtitle' => 'Kỷ niệm thời thanh xuân',
                    'content' => 'Rất mong gặp lại các bạn trong ngày trọng đại của chúng mình!',
                ],
            ],
        ]);

    $response->assertRedirect(route('account.persona.index', $this->invitation->id));
    $response->assertSessionHas('status');

    $config = $this->invitation->fresh()->custom_config;
    expect($config['persona_messages']['enabled'])->toBeTrue();
    expect($config['persona_messages']['groups']['Ban Dai Hoc']['title'])->toBe('Gửi Những Người Bạn Đại Học');
});

test('cặp đôi có thể đặt lại lời ngỏ một nhóm về mặc định', function () {
    $this->actingAs($this->user)
        ->post(route('account.persona.update', $this->invitation->id), [
            'enabled' => true,
            'groups' => [
                'Ban Than' => [
                    'title' => 'Gửi Bạn Thân',
                    'content' => 'Nội dung riêng',
                ],
            ],
        ]);

    $response = $this->actingAs($this->user)
        ->post(route('account.persona.reset_group', $this->invitation->id), [
            'group_name' => 'Ban Than',
        ]);

    $response->assertRedirect(route('account.persona.index', $this->invitation->id));
    $response->assertSessionHas('status');

    $config = $this->invitation->fresh()->custom_config;
    expect($config['persona_messages']['groups'])->not->toHaveKey('Ban Than');
});
