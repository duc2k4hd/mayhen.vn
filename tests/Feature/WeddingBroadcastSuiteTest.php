<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingBroadcastService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Wedding Broadcast',
        'slug' => 'mau-test-wedding-broadcast-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Minh Trí',
        'bride_name' => 'Hương Giang',
        'wedding_date' => now()->addMonths(1)->toDateString(),
        'venue_name' => 'GEM Center',
        'venue_address' => '8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tri-giang-'.uniqid(),
        'title' => 'Thiệp Cưới Minh Trí & Hương Giang',
        'is_published' => true,
    ]);
});

test('cặp đôi có thể truy cập màn hình cấu hình thông báo khẩn cấp ngày cưới', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.broadcast.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Băng Tin Khẩn Cấp & Điều Phối Ngày Cưới');
    $response->assertSee('Thay đổi sảnh tiệc / Di chuyển vào trong nhà');
    $response->assertSee('Lùi giờ đón khách & khai tiệc');
});

test('người dùng không thể truy cập hoặc sửa đổi thông báo khẩn cấp của thiệp người khác', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.broadcast.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể lưu và phát sóng thông báo khẩn cấp lên thiệp cưới', function () {
    $payload = [
        'is_active' => '1',
        'template_key' => 'venue_change',
        'urgency_level' => 'critical',
        'display_type' => 'both',
        'title' => 'Khẩn Cấp: Chuyển Sang Sảnh Grand Ballroom B',
        'message' => 'Do thời tiết mưa lớn, tiệc cưới ngoài trời chuyển sang Sảnh Grand Ballroom B (Tầng 3). Kính mong quý khách lưu ý.',
        'action_text' => 'Gọi Lễ Tân',
        'action_url' => 'tel:0909123456',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('account.broadcast.update', $this->invitation->id), $payload);

    $response->assertRedirect(route('account.broadcast.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $config = WeddingBroadcastService::resolveBroadcastConfig($this->invitation);

    expect($config['is_active'])->toBeTrue();
    expect($config['urgency_level'])->toBe('critical');
    expect($config['title'])->toBe('Khẩn Cấp: Chuyển Sang Sảnh Grand Ballroom B');
    expect($config['action_text'])->toBe('Gọi Lễ Tân');
    expect($config['updated_at'])->not->toBeNull();
});

test('cặp đôi có thể tắt thông báo khẩn cấp khi sự cố đã ổn định', function () {
    WeddingBroadcastService::updateBroadcast($this->invitation, [
        'is_active' => true,
        'title' => 'Thông Báo Cần Tắt',
        'message' => 'Nội dung kiểm thử',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('account.broadcast.deactivate', $this->invitation->id));

    $response->assertRedirect(route('account.broadcast.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $config = WeddingBroadcastService::resolveBroadcastConfig($this->invitation);

    expect($config['is_active'])->toBeFalse();
});

test('component băng tin khẩn cấp render chuẩn xác sticky banner và popup modal trên thiệp', function () {
    WeddingBroadcastService::updateBroadcast($this->invitation, [
        'is_active' => true,
        'urgency_level' => 'critical',
        'display_type' => 'both',
        'title' => 'Thay Đổi Sảnh Tiệc Sát Giờ',
        'message' => 'Quý khách vui lòng lên tầng 3 sảnh Grand Ballroom',
        'action_text' => 'Xem Bản Đồ',
        'action_url' => 'https://maps.google.com',
    ]);

    $this->invitation->refresh();

    $view = $this->view('client.components.broadcast-banner', [
        'invitation' => $this->invitation,
        'isEmbed' => false,
    ]);

    $rendered = (string) $view;
    expect($rendered)->toContain('mayhen_invitation_broadcast_banner');
    expect($rendered)->toContain('mayhen_broadcast--critical');
    expect($rendered)->toContain('Thay Đổi Sảnh Tiệc Sát Giờ');
    expect($rendered)->toContain('Quý khách vui lòng lên tầng 3 sảnh Grand Ballroom');
    expect($rendered)->toContain('mayhen_invitation_broadcast_modal');
});

test('thông báo khẩn cấp không render khi đang tắt hoặc đang nhúng trong Studio editor', function () {
    WeddingBroadcastService::updateBroadcast($this->invitation, [
        'is_active' => true,
        'title' => 'Thông Báo Thử Nghiệm',
        'message' => 'Nội dung',
    ]);

    $this->invitation->refresh();

    // Trường hợp nhúng trong Studio Editor (?embed=1)
    $viewEmbed = $this->view('client.components.broadcast-banner', [
        'invitation' => $this->invitation,
        'isEmbed' => true,
    ]);
    expect((string) $viewEmbed)->toBe('');

    // Trường hợp tắt thông báo
    WeddingBroadcastService::deactivateBroadcast($this->invitation);
    $this->invitation->refresh();

    $viewInactive = $this->view('client.components.broadcast-banner', [
        'invitation' => $this->invitation,
        'isEmbed' => false,
    ]);
    expect((string) $viewInactive)->toBe('');
});

test('service phân giải cấu hình fallback an toàn khi thiệp chưa có dữ liệu thông báo', function () {
    $emptyInvitation = new Invitation;
    $config = WeddingBroadcastService::resolveBroadcastConfig($emptyInvitation);

    expect($config['is_active'])->toBeFalse();
    expect($config['urgency_level'])->toBe('warning');
    expect($config['display_type'])->toBe('both');
    expect($config['title'])->toBe('');
    expect($config['message'])->toBe('');
});
