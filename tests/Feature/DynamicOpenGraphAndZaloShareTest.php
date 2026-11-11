<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Thử OpenGraph Zalo',
        'slug' => 'mau-thu-og-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đặng Minh Quân',
        'bride_name' => 'Trần Hoàng Yến',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Trung Tâm Tiệc Cưới White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
        'extra_data' => [
            'event_party_lunar' => 'Ngày 18 tháng 11 năm Bính Ngọ',
            'quote' => 'Cùng nhau đi qua năm tháng bình yên.',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dam-cuoi-quan-yen-'.uniqid(),
        'status' => 'published',
        'is_published' => true,
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Hoàng Nam',
        'salutation' => 'Anh',
        'phone' => '0901234567',
        'guest_token' => 'tok_nam_'.uniqid(),
    ]);
});

test('public endpoint generates ultra hd open graph share card svg for published invitation', function () {
    $response = $this->get(route('invitation.share_card', $this->invitation->slug));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8');
    $content = $response->getContent();

    expect($content)->toContain('<svg')
        ->toContain('Đặng Minh Quân')
        ->toContain('Trần Hoàng Yến')
        ->toContain('Trung Tâm Tiệc Cưới White Palace')
        ->toContain('QUÉT ĐỂ MỞ THIỆP')
        ->toContain('MÂY HẸN ATELIER');
});

test('unpublished invitation cannot expose public share card svg', function () {
    $this->invitation->update(['is_published' => false]);

    $response = $this->get(route('invitation.share_card', $this->invitation->slug));
    $response->assertNotFound();
});

test('guest cannot access zalo share suite without login', function () {
    $response = $this->get(route('account.share.index', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('user cannot access zalo share suite of another wedding', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get(route('account.share.index', $this->invitation->id));

    $response->assertStatus(404);
});

test('owner can access zalo share suite and view templates and guest chips', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.share.index', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Bộ Công Cụ Chia Sẻ Zalo &amp; Phân Phối Thiệp Cưới', false);
    $response->assertSee('Trang Trọng &amp; Tôn Kính', false);
    $response->assertSee('Lịch Thiệp &amp; Ấm Áp', false);
    $response->assertSee('Thân Mật &amp; Trẻ Trung', false);
    $response->assertSee('Báo Hỷ &amp; Bạn Phương Xa', false);
    $response->assertSee('Nguyễn Hoàng Nam');
});

test('api compose generates customized invitation message and zalo link', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.share.compose', $this->invitation->id), [
            'salutation' => 'Anh',
            'guest_name' => 'Nguyễn Hoàng Nam',
            'template_key' => 'formal',
            'guest_id' => $this->guest->id,
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'message',
        'target_url',
        'zalo_share_url',
    ]);

    $data = $response->json();
    expect($data['message'])->toContain('Kính gửi Anh Nguyễn Hoàng Nam')
        ->toContain('Đặng Minh Quân & Trần Hoàng Yến')
        ->toContain('White Palace');
    expect($data['zalo_share_url'])->toContain('zalo.me/share');
});

test('api log records share action in audit logger', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.share.log', $this->invitation->id), [
            'channel' => 'zalo',
            'template_key' => 'formal',
            'recipient_name' => 'Nguyễn Hoàng Nam',
        ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Đã ghi nhận lượt chia sẻ thành công.',
    ]);

    $logs = AuditLogger::getRecentLogs();
    $found = false;
    foreach ($logs as $log) {
        if ($log['action'] === 'INVITATION_SHARED' && ($log['details']['channel'] ?? '') === 'zalo') {
            $found = true;
            break;
        }
    }
    expect($found)->toBeTrue();
});
