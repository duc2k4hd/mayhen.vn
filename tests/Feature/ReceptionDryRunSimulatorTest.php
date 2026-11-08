<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Rose Elegance',
        'slug' => 'rose-elegance-'.uniqid(),
        'blade_view' => 'client.invitations.rose.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Kim Ngân',
        'wedding_date' => now()->addDays(15),
        'venue_name' => 'White Palace Phạm Văn Đồng',
        'venue_address' => '108 Phạm Văn Đồng, TP. Thủ Đức',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'kiet-ngan-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'checkin_pin' => '888888',
        ],
    ]);

    // Tạo 1 khách thật trong database để chứng minh không bị ảnh hưởng
    $this->realGuest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Thật Nguyễn Văn Thật',
        'salutation' => 'Anh',
        'group_name' => 'Bạn Đại Học',
        'expected_attendees' => 2,
        'is_sent' => true,
        'checked_in_at' => null,
    ]);
});

it('enables dry run mode and loads sandbox fixtures without writing to database', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('checkin.dry_run.enable', $this->invitation->slug));

    $response->assertRedirect(route('checkin.portal', ['slug' => $this->invitation->slug, 'dry_run' => 1]));

    $portalResponse = $this->get(route('checkin.portal', ['slug' => $this->invitation->slug, 'dry_run' => 1]));

    $portalResponse->assertOk()
        ->assertSee('CHẾ ĐỘ TỔNG DIỄN TẬP LỄ TÂN')
        ->assertSee('Bác Trần Văn Tuấn')
        ->assertSee('Gia đình anh Lê Hoàng Nam')
        ->assertSee('Bạn Nguyễn Thu Hà');

    // Chứng minh khách mô phỏng không được ghi vào DB
    expect(Guest::where('name', 'Bác Trần Văn Tuấn')->exists())->toBeFalse();
    expect(Guest::where('name', 'Khách Thật Nguyễn Văn Thật')->first()->checked_in_at)->toBeNull();
});

it('processes scan in dry run mode in isolated session without touching database', function () {
    $this->actingAs($this->user);

    // Kích hoạt diễn tập
    $this->post(route('checkin.dry_run.enable', $this->invitation->slug));

    // Quét mã khách mô phỏng VIP
    $scanResponse = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => 'dryrun_vip01',
    ]);

    $scanResponse->assertOk()
        ->assertJson([
            'success' => true,
            'is_dry_run' => true,
            'already_checked_in' => false,
            'guest' => [
                'name' => 'Bác Trần Văn Tuấn',
                'table_name' => 'Bàn VIP 01',
                'expected_attendees' => 2,
            ],
        ]);

    // Khách thật trong DB vẫn nguyên vẹn
    expect(Guest::where('name', 'Bác Trần Văn Tuấn')->exists())->toBeFalse();
    expect($this->realGuest->fresh()->checked_in_at)->toBeNull();
});

it('detects duplicate scans in dry run mode with proper alert', function () {
    $this->actingAs($this->user);
    $this->post(route('checkin.dry_run.enable', $this->invitation->slug));

    // Quét mã 'dryrun_duplicate' đã có sẵn checked_in_at = 17:45
    $scanResponse = $this->postJson(route('checkin.scan', $this->invitation->slug), [
        'token' => 'dryrun_duplicate',
    ]);

    $scanResponse->assertOk()
        ->assertJson([
            'success' => true,
            'is_dry_run' => true,
            'already_checked_in' => true,
            'previous_time' => '17:45',
            'guest' => [
                'name' => 'Chị Phạm Mai Hương',
            ],
        ]);
});

it('supports quick search in dry run mode', function () {
    $this->actingAs($this->user);
    $this->post(route('checkin.dry_run.enable', $this->invitation->slug));

    $response = $this->getJson(route('checkin.quickSearch', [
        'slug' => $this->invitation->slug,
        'q' => 'Thu Hà',
    ]));

    $response->assertOk()
        ->assertJson([
            'is_dry_run' => true,
        ]);

    $guests = $response->json('guests');
    expect($guests)->toHaveCount(1);
    expect($guests[0]['name'])->toBe('Bạn Nguyễn Thu Hà');
    expect($guests[0]['table_name'])->toBe('Chưa xếp bàn');
});

it('resets and disables dry run mode restoring real checkin state cleanly', function () {
    $this->actingAs($this->user);
    $this->post(route('checkin.dry_run.enable', $this->invitation->slug));

    // Reset diễn tập
    $resetResponse = $this->post(route('checkin.dry_run.reset', $this->invitation->slug));
    $resetResponse->assertRedirect();

    // Tắt diễn tập
    $disableResponse = $this->post(route('checkin.dry_run.disable', $this->invitation->slug));
    $disableResponse->assertRedirect(route('checkin.portal', ['slug' => $this->invitation->slug]));

    $portalResponse = $this->get(route('checkin.portal', ['slug' => $this->invitation->slug]));
    $portalResponse->assertOk()
        ->assertDontSee('CHẾ ĐỘ TỔNG DIỄN TẬP LỄ TÂN')
        ->assertSee('Quét Mã QR Khách Mời');
});
