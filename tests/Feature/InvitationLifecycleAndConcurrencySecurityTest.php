<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Services\PublishDiffInspectorService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first()
        ?? InvitationTemplate::first()
        ?? InvitationTemplate::create([
            'name' => 'Mẫu Test Lifecycle',
            'slug' => 'mau-test-lifecycle-'.uniqid(),
            'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
            'is_active' => true,
        ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quốc Huy',
        'bride_name' => 'Tuyết Nhung',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Caravelle Saigon',
        'venue_address' => '19 Lam Son Square, Dist 1, HCMC',
        'extra_data' => [
            'groom_bank_account' => '1122334455',
            'groom_bank_name' => 'Vietcombank',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'huy-nhung-'.uniqid(),
        'title' => 'Thiệp Cưới Quốc Huy & Tuyết Nhung',
        'is_published' => true,
        'revision_id' => 1,
        'published_at' => now(),
        'custom_config' => [
            'allow_rsvp' => true,
        ],
    ]);
});

test('G1-005: thiệp locked (403), unpublished (404), expired (410) chặn tương tác RSVP', function () {
    // 1. Test unpublished (404)
    $this->invitation->update(['is_published' => false]);
    $responseUnpublished = $this->postJson(route('rsvp.submit', ['slug' => $this->invitation->slug]), [
        'guest_name' => 'Khách Test',
        'attending_status' => 'attending',
    ]);
    $responseUnpublished->assertStatus(404);

    // 2. Test locked (403)
    $this->invitation->update([
        'is_published' => true,
        'custom_config' => ['is_locked' => true],
    ]);
    $responseLocked = $this->postJson(route('rsvp.submit', ['slug' => $this->invitation->slug]), [
        'guest_name' => 'Khách Test',
        'attending_status' => 'attending',
    ]);
    $responseLocked->assertStatus(403);

    // 3. Test expired (410)
    $this->invitation->update([
        'is_published' => true,
        'custom_config' => ['is_locked' => false],
        'expires_at' => now()->subDay(),
    ]);
    $responseExpired = $this->postJson(route('rsvp.submit', ['slug' => $this->invitation->slug]), [
        'guest_name' => 'Khách Test',
        'attending_status' => 'attending',
    ]);
    $responseExpired->assertStatus(410);
});

test('G1-006: thay đổi nháp ngân hàng và sảnh tiệc chưa publish không rò rỉ ra trang public', function () {
    // Bản nháp cập nhật số tài khoản mới nhưng chưa ấn Xuất Bản
    $this->invitation->update([
        'draft_config' => [
            'groom_bank_account' => '9999888877',
            'venue_name' => 'Sảnh Tiệc Kim Cương Mới',
        ],
    ]);

    // Trang public vẫn giữ nguyên thông tin gốc trong DB, draft không bị rò rỉ ra public
    $response = $this->get(route('invitation.show', ['slug' => $this->invitation->slug]));
    $response->assertStatus(200);
    expect($response->viewData('wedding')->extra_data['groom_bank_account'])->toBe('1122334455');
    $response->assertDontSee('9999888877');
});

test('G1-007: rollback phiên bản cũ bảo toàn nguyên vẹn khách, RSVP, events và owner', function () {
    // 1. Tạo event, guest và RSVP
    $event = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lễ Cưới Chính Thức',
        'event_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Caravelle',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Minh',
        'guest_token' => 'TOKEN_MINH_'.uniqid(),
    ]);

    $rsvp = GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => 'Nguyễn Văn Minh',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    // 2. Xuất bản phiên bản v2 qua Diff Inspector để tạo snapshot revision 2
    $diffService = app(PublishDiffInspectorService::class);
    $diffService->commitPublish($this->invitation, 'Phiên bản ban đầu v2', $this->user);

    // 3. Sửa thông tin và xuất bản phiên bản v3 (snapshot revision 3)
    $this->wedding->update(['venue_name' => 'Khách Sạn Mới v3']);
    $diffService->commitPublish($this->invitation, 'Phiên bản cập nhật v3', $this->user);

    expect($this->invitation->fresh()->revision_id)->toBe(3);

    // 4. Khôi phục về revision 2
    $rollbackSuccess = $diffService->rollbackToRevision($this->invitation, 2, $this->user);
    expect($rollbackSuccess)->toBeTrue();

    // 5. Kiểm tra dữ liệu khách mời, RSVP, events và owner không bị suy suyển
    expect(Guest::where('wedding_id', $this->wedding->id)->count())->toBe(1);
    expect(GuestResponse::where('wedding_id', $this->wedding->id)->count())->toBe(1);
    expect(WeddingEvent::where('wedding_id', $this->wedding->id)->count())->toBe(1);
    expect($this->wedding->fresh()->user_id)->toBe($this->user->id);
});

test('G1-008: 2 tab cùng autosave sai revision_id trả về HTTP 409 Conflict', function () {
    $this->invitation->update(['revision_id' => 5]);

    // Tab cũ gửi revision_id = 4 (lỗi thời)
    $response = $this->actingAs($this->user)->postJson(
        route('invitation.autosave', ['slug' => $this->invitation->slug]),
        [
            'invitation_id' => $this->invitation->id,
            'revision_id' => 4,
            'draft_config' => [
                'groom_name' => 'Tên mới tab cũ',
            ],
        ]
    );

    $response->assertStatus(409)
        ->assertJson([
            'conflict' => true,
            'current_revision' => 5,
        ]);
});
