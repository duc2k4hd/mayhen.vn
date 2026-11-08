<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingConsistencyGuardianService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'customer']);

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first() ?? InvitationTemplate::create([
        'name' => 'Giữa Muôn Ngàn Vì Sao',
        'slug' => 'giua-muon-ngan-vi-sao',
        'description' => 'Mẫu thử nghiệm',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.giua-muon-ngan-vi-sao.template',
        'category' => 'modern',
        'is_active' => true,
    ]);
});

test('bác sĩ phát hiện thiệp thiếu ngày cưới không bao giờ cho điểm vượt quá 65% (cap score defense)', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addDays(30),
        'venue_name' => 'Trung Tâm Tiệc Cưới GEM Center',
        'venue_address' => 'Số 8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1',
        'extra_data' => [
            'event_party_map_url' => 'https://maps.app.goo.gl/sampleGEM',
            'groom_bank_account' => '1234567890',
            'groom_bank_name' => 'Vietcombank',
            'groom_father' => 'ÔNG: Nguyễn Văn A',
            'bride_father' => 'ÔNG: Trần Văn B',
        ],
    ]);

    // Giả lập trạng thái thiếu ngày cưới trong quá trình biên tập
    $wedding->wedding_date = null;

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-nam-thao-wedding-test-1',
        'is_published' => false,
        'custom_config' => ['checkin_pin' => '987654'],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $inspection = $guardian->inspect($wedding, $invitation);

    // Điểm số tuyệt đối không được vượt quá 65 và không thể là 'excellent'
    expect($inspection['score'])->toBeLessThanOrEqual(65);
    expect($inspection['status'])->not->toBe('excellent');
    expect($inspection['status'])->toBe('needs_attention');

    $criticalIssues = collect($inspection['issues'])->where('severity', 'critical');
    expect($criticalIssues)->not->toBeEmpty();
    expect($criticalIssues->first()['field'])->toBe('wedding_date');
    expect($criticalIssues->first()['field_target'])->toBe('input_wedding_date');
});

test('bác sĩ phát hiện thiệp thiếu cả tên sảnh tiệc và địa chỉ không bao giờ cho điểm vượt quá 65%', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addDays(40),
        'venue_name' => null,
        'venue_address' => null,
        'extra_data' => [
            'event_party_map_url' => 'https://maps.app.goo.gl/sampleGEM',
            'groom_bank_account' => '1234567890',
            'groom_bank_name' => 'Vietcombank',
            'groom_father' => 'ÔNG: Nguyễn Văn A',
            'bride_father' => 'ÔNG: Trần Văn B',
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-nam-thao-wedding-test-2',
        'is_published' => false,
        'custom_config' => ['checkin_pin' => '987654'],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $inspection = $guardian->inspect($wedding, $invitation);

    expect($inspection['score'])->toBeLessThanOrEqual(65);
    expect($inspection['status'])->not->toBe('excellent');

    $venueIssue = collect($inspection['issues'])->firstWhere('field', 'venue_name');
    expect($venueIssue)->not->toBeNull();
    expect($venueIssue['severity'])->toBe('critical');
    expect($venueIssue['field_target'])->toBe('input_venue_name');
});

test('bác sĩ phát hiện hạn chót rsvp sau ngày cưới là lỗi nghiêm trọng (critical)', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => '2027-05-10',
        'venue_name' => 'White Palace',
        'venue_address' => '194 Hoàng Văn Thụ',
        'extra_data' => [
            'rsvp_deadline' => '2027-05-15', // Lỗi: Sau ngày cưới 5 ngày!
            'event_party_map_url' => 'https://maps.app.goo.gl/sampleWP',
            'groom_bank_account' => '1234567890',
            'groom_bank_name' => 'MB Bank',
            'groom_father' => 'Nguyễn A',
            'bride_father' => 'Trần B',
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-nam-thao-wedding-test-3',
        'is_published' => false,
        'custom_config' => ['checkin_pin' => '987654'],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $inspection = $guardian->inspect($wedding, $invitation);

    expect($inspection['score'])->toBeLessThanOrEqual(65);
    $rsvpIssue = collect($inspection['issues'])->firstWhere('field', 'rsvp_deadline');
    expect($rsvpIssue)->not->toBeNull();
    expect($rsvpIssue['severity'])->toBe('critical');
    expect($rsvpIssue['field_target'])->toBe('input_rsvp_deadline');
});

test('thiệp cưới hoàn thiện đầy đủ mọi tiêu chí đạt 100 điểm xuất sắc', function () {
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Anh',
        'bride_name' => 'Minh Hằng',
        'wedding_date' => now()->addDays(60),
        'venue_name' => 'Trung Tâm Hội Nghị Asiana Plaza',
        'venue_address' => '45 Phan Đăng Lưu, Phường 3, Bình Thạnh',
        'groom_parents' => 'ÔNG: Tuấn Dũng & BÀ: Thu Hà',
        'bride_parents' => 'ÔNG: Minh Hải & BÀ: Bích Thảo',
        'cover_photo_url' => 'https://mayhen.vn/assets/client/images/hero.webp',
        'extra_data' => [
            'event_party_map_url' => 'https://maps.app.goo.gl/asianaPlaza123',
            'groom_bank_account' => '0987654321',
            'groom_bank_name' => 'Vietcombank',
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-anh-minh-hang-perfect',
        'is_published' => true,
        'custom_config' => ['checkin_pin' => '654321'],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $inspection = $guardian->inspect($wedding, $invitation);

    expect($inspection['score'])->toBe(100);
    expect($inspection['status'])->toBe('excellent');
    expect($inspection['checks_passed'])->toBe(6);
    expect($inspection['issues'])->toBeEmpty();
});

test('các cảnh báo chất lượng không chặn việc lưu nháp hay xuất bản thiệp cưới', function () {
    // Thiệp chưa có map url (info) và tài khoản ngân hàng chưa chọn (warning)
    $wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Hương Tràm',
        'wedding_date' => now()->addDays(20),
        'venue_name' => 'Nhà Hàng Tiệc Cưới',
        'venue_address' => '123 Đường Cưới',
        'groom_parents' => 'ÔNG: A & BÀ: B',
        'bride_parents' => 'ÔNG: C & BÀ: D',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'quoc-bao-huong-tram-test',
        'is_published' => false,
    ]);

    // 1. Lưu nháp qua Studio thành công
    $responseSave = $this->actingAs($this->user)->post(route('invitation.store'), [
        'template_id' => $this->template->id,
        'invitation_id' => $invitation->id,
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Hương Tràm',
        'wedding_date' => now()->addDays(20)->format('Y-m-d'),
        'event_party_venue' => 'Nhà Hàng Tiệc Cưới',
        'event_party_address' => '123 Đường Cưới',
    ]);
    $responseSave->assertRedirect();

    // 2. Xuất bản công khai thành công mà không bị chặn bởi các khuyến nghị chất lượng
    $responsePublish = $this->actingAs($this->user)
        ->from(route('account.invitations.settings', $invitation->id))
        ->post(route('account.invitations.toggle_publish', $invitation->id));

    $responsePublish->assertRedirect();
    $invitation->refresh();
    expect($invitation->is_published)->toBeTrue();
});
