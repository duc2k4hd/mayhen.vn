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
        'name' => 'Mẫu Test Companion Card',
        'slug' => 'mau-test-companion-card-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Minh Hoàng',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => now()->addMonths(3)->toDateString(),
        'venue_name' => 'InterContinental Danang',
        'venue_address' => 'Bai Bac, Son Tra Peninsula, Danang',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hoang-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Minh Hoàng & Thu Thảo',
        'is_published' => true,
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Anh Dũng',
        'guest_token' => 'TOKEN_DUNG_'.uniqid(),
    ]);
});

test('khách vãng lai chưa đăng nhập bị chuyển hướng về login khi vào studio thẻ kẹp', function () {
    $response = $this->get(route('account.companion_card.index', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('người dùng khác bị 403 khi vào thẻ kẹp của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.companion_card.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('chủ thiệp mở thành công Studio Thiết Kế Thẻ Kẹp Thiệp Giấy', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.companion_card.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Thẻ Kẹp');
    $response->assertSee('Minh Hoàng');
    $response->assertSee('Thu Thảo');
});

test('cặp đôi có thể lưu cấu hình thẻ kẹp qua AJAX', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.companion_card.update', $this->invitation->id), [
            'size' => 'namecard',
            'palette' => 'gold',
            'motif' => 'royal_crest',
            'kicker' => 'THIỆP CƯỚI TRỰC TUYẾN',
            'couple_title' => 'Minh Hoàng & Thu Thảo',
            'instructions' => 'Quét mã QR để xác nhận tham dự và xem bản đồ đường đi',
            'qr_label' => 'QUÉT MÃ ĐỂ MỞ THIỆP',
            'show_date' => true,
            'show_venue' => true,
            'include_guest_name' => false,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $config = $this->invitation->fresh()->custom_config;
    expect($config['companion_card']['size'])->toBe('namecard');
    expect($config['companion_card']['palette'])->toBe('gold');
});

test('chủ thiệp có thể render trang in khổ A4 chuẩn xưởng in', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.companion_card.print', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Minh Hoàng &amp; Thu Thảo', false);
    $response->assertSee('MÂY HẸN • MAYHEN.VN');
});

test('chủ thiệp có thể tải tệp vector SVG độc lập độ phân giải cao cho xưởng in', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.companion_card.download_svg', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'image/svg+xml; charset=utf-8');
    $response->assertSee('<svg', false);
});
