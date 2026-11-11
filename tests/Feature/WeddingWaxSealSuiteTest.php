<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingWaxSealService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Wax Seal',
        'slug' => 'mau-test-wax-seal-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Bảo Ngọc',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'kiet-ngoc-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Kiệt & Bảo Ngọc',
        'is_published' => true,
    ]);
});

test('cặp đôi có thể truy cập Studio thiết kế con dấu sáp hoàng gia', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.wax_seal.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Tem Niêm Phong Sáp Hoàng Gia');
    $response->assertSee('Đỏ Rượu Bordeaux (Burgundy)');
    $response->assertSee('Vàng Kim Hoàng Gia (Royal Gold)');
    $response->assertSee('Monogram 2 Chữ Cái Đầu (Initials)');
});

test('người dùng không thể can thiệp vào Studio con dấu sáp của thiệp người khác', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.wax_seal.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật màu sắc, họa tiết dập nổi và monogram của con dấu sáp', function () {
    $payload = [
        'color_palette' => 'royal_gold',
        'emblem' => 'monogram',
        'rim_style' => 'royal_beaded',
        'monogram_text' => 'K & N',
        'motto_text' => 'ETERNAL LOVE',
        'crack_sound' => '1',
        'shimmer_effect' => '1',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('account.wax_seal.update', $this->invitation->id), $payload);

    $response->assertRedirect(route('account.wax_seal.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $config = WeddingWaxSealService::resolveWaxSealConfig($this->invitation);

    expect($config['color_palette'])->toBe('royal_gold');
    expect($config['primary_color'])->toBe('#D4AF37');
    expect($config['emblem'])->toBe('monogram');
    expect($config['rim_style'])->toBe('royal_beaded');
    expect($config['monogram_text'])->toBe('K & N');
    expect($config['motto_text'])->toBe('ETERNAL LOVE');
    expect($config['crack_sound'])->toBeTrue();
});

test('component con dấu sáp hoàng gia render chuẩn BEM và họa tiết được chọn', function () {
    WeddingWaxSealService::updateWaxSeal($this->invitation, [
        'color_palette' => 'sage_green',
        'emblem' => 'double_happiness',
        'rim_style' => 'vintage_deckled',
        'motto_text' => 'MAYHEN ATELIER',
    ]);

    $this->invitation->refresh();

    $view = $this->view('client.components.wax-seal-badge', [
        'invitation' => $this->invitation,
        'size' => 'large',
        'interactive' => true,
    ]);

    $rendered = (string) $view;
    expect($rendered)->toContain('mayhen_wax_seal_component');
    expect($rendered)->toContain('mayhen_wax_seal--sage_green');
    expect($rendered)->toContain('mayhen_wax_seal--vintage_deckled');
    expect($rendered)->toContain('mayhen_wax_seal--large');
    expect($rendered)->toContain('囍');
    expect($rendered)->toContain('MAYHEN ATELIER');
});

test('hệ thống xác thực chặn lưu các bảng màu hoặc họa tiết không hợp lệ', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.wax_seal.update', $this->invitation->id), [
            'color_palette' => 'invalid_neon_pink',
            'emblem' => 'fake_emblem',
            'rim_style' => 'invalid_rim',
        ]);

    $response->assertSessionHasErrors(['color_palette', 'emblem', 'rim_style']);
});

test('service tự động sinh monogram chữ cái đầu chuẩn xác khi thiệp chưa có cấu hình', function () {
    $emptyInvitation = new Invitation;
    $emptyInvitation->groom_name = 'Phan Anh';
    $emptyInvitation->bride_name = 'Thùy Chi';

    $config = WeddingWaxSealService::resolveWaxSealConfig($emptyInvitation);

    expect($config['color_palette'])->toBe('burgundy');
    expect($config['emblem'])->toBe('monogram');
    expect($config['monogram_text'])->toBe('P & T');
    expect($config['crack_sound'])->toBeTrue();
});
