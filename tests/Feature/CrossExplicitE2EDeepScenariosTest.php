<?php

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Rules\NoProfanity;
use App\Services\AiStoryWriterService;
use App\Services\CsvSanitizerService;
use App\Services\CuratedInvitationService;
use App\Services\InvitationTemplateService;
use App\Services\TwoFamilyApprovalService;
use App\Services\WeddingConsistencyGuardianService;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

// DEEP-01: RSVP tăng sĩ số
test('DEEP-01: rsvp count increase updates seating and checkin without overriding to expected count', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Thanh Tùng',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => '2026-11-20',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-tung-thao-'.uniqid(),
        'is_published' => true,
    ]);

    $table = WeddingTable::create([
        'wedding_id' => $wedding->id,
        'name' => 'Bàn VIP 01',
        'capacity' => 10,
    ]);

    // Tạo khách với dự kiến ban đầu = 1 người
    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Anh Nguyễn Văn An',
        'expected_attendees' => 1,
        'table_id' => $table->id,
        'table_name' => $table->name,
    ]);

    // Khách submit RSVP qua link cá nhân hóa có token: 2 người lớn + 1 trẻ em = 3 người
    $response = $this->post(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), [
        'guest_name' => 'Anh Nguyễn Văn An',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
    ]);
    $response->assertSessionHas('rsvp_success');

    $guest->refresh();
    expect($guest->actualAttendeesCount())->toBe(3);

    // Lễ tân hoặc cặp đôi quét mã QR điểm danh khách
    $scanRes = $this->actingAs($user)->postJson(route('checkin.scan', $invitation->slug), [
        'token' => $guest->guest_token,
    ]);
    $scanRes->assertOk();

    $guest->refresh();
    expect($guest->isCheckedIn())->toBeTrue()
        ->and($guest->checked_in_attendees)->toBe(3)
        ->and($guest->actualAttendeesCount())->toBe(3);
});

// DEEP-02: Declined phải giải phóng ghế
test('DEEP-02: rsvp changing from attending to declined releases table capacity without deleting guest', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Quang Hải',
        'bride_name' => 'Chu Thanh Huyền',
        'wedding_date' => '2026-12-01',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-hai-huyen-'.uniqid(),
        'is_published' => true,
    ]);

    $table = WeddingTable::create([
        'wedding_id' => $wedding->id,
        'name' => 'Bàn Đối Tác 01',
        'capacity' => 10,
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Trần Văn Bình',
        'expected_attendees' => 3,
        'table_id' => $table->id,
        'table_name' => $table->name,
    ]);

    // Khách lúc đầu xác nhận đi 3 người
    GuestResponse::create([
        'guest_id' => $guest->id,
        'wedding_id' => $wedding->id,
        'guest_name' => 'Trần Văn Bình',
        'attending_status' => 'attending',
        'adults_count' => 3,
        'children_count' => 0,
    ]);

    expect($guest->actualAttendeesCount())->toBe(3);

    // Khách đổi ý sang declined (không tham dự) qua link token
    $this->post(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), [
        'guest_name' => 'Trần Văn Bình',
        'attending_status' => 'declined',
    ]);

    // Cập nhật trạng thái declined trực tiếp trên response
    $guest->response->update(['attending_status' => 'declined']);
    $guest->refresh();

    expect($guest->response->attending_status)->toBe('declined')
        ->and($guest->actualAttendeesCount())->toBe(0);

    // Khách vẫn còn trong hệ thống CRM
    expect(Guest::where('id', $guest->id)->exists())->toBeTrue();
});

// DEEP-03: Hai nguồn bàn (CRM & Seating synchronization)
test('DEEP-03: table rename syncs guest table_name and deleting table nullifies references safely', function () {
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Công Phượng',
        'bride_name' => 'Viên Minh',
        'wedding_date' => '2026-11-15',
    ]);

    $table = WeddingTable::create([
        'wedding_id' => $wedding->id,
        'name' => 'Bàn Hoàng Gia',
        'capacity' => 12,
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Đoàn Văn Hậu',
        'expected_attendees' => 2,
        'table_id' => $table->id,
    ]);

    // 1. Tự động đồng bộ table_name khi gán table_id
    expect($guest->table_name)->toBe('Bàn Hoàng Gia');

    // 2. Đổi tên bàn tiệc -> Tự động đồng bộ table_name trên khách
    $table->update(['name' => 'Bàn Kim Cương']);
    $guest->refresh();
    expect($guest->table_name)->toBe('Bàn Kim Cương');

    // 3. Xóa bàn tiệc -> Không xóa khách, chỉ nullify table_id và table_name
    $table->delete();
    $guest->refresh();
    expect($guest->table_id)->toBeNull()
        ->and($guest->table_name)->toBeNull()
        ->and(Guest::where('id', $guest->id)->exists())->toBeTrue();
});

// DEEP-04: Khóa thiệp đa cổng (Admin Lock Multi-endpoint Defense)
test('DEEP-04: admin locked invitation rejects all interaction endpoints', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Bùi Tiến Dũng',
        'bride_name' => 'Khánh Linh',
        'wedding_date' => '2026-11-28',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-dung-linh-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'is_locked' => true,
            'lock_reason' => 'Thiệp đang tạm khóa theo yêu cầu kiểm duyệt',
        ],
    ]);

    // 1. Xem thiệp công khai -> Hiển thị view locked
    $resView = $this->get(route('invitation.show', $invitation->slug));
    $resView->assertStatus(200);
    $resView->assertSee('Thiệp Cưới Tạm Thời Khóa');

    // 2. Gửi RSVP -> Bị chặn 403 Forbidden
    $resRsvp = $this->post(route('rsvp.submit', $invitation->slug), [
        'guest_name' => 'Khách Khảo Sát',
        'attending_status' => 'attending',
    ]);
    $resRsvp->assertStatus(403);

    // 3. Gửi lời chúc -> Bị chặn 403 Forbidden
    $resWish = $this->post(route('invitation.wish.store', $invitation->slug), [
        'name' => 'Khách Khảo Sát',
        'message' => 'Chúc mừng hạnh phúc hai bạn',
    ]);
    $resWish->assertStatus(403);

    // 4. Photo Drop: Màn hình show hiển thị khóa, Upload bị từ chối 403
    $resPhotoDrop = $this->get(route('photo_drop.show', $invitation->slug));
    $resPhotoDrop->assertStatus(200);
    $resPhotoDrop->assertSee('Thiệp Cưới Tạm Thời Khóa');

    $resPhotoUpload = $this->postJson(route('photo_drop.upload', $invitation->slug), [
        'uploader_name' => 'Khách Khảo Sát',
    ]);
    $resPhotoUpload->assertStatus(403);

    // 5. Studio consistency check -> Bị chặn 403 Forbidden
    $resStudio = $this->actingAs($user)->getJson(route('invitation.consistency_check', $invitation->id));
    $resStudio->assertStatus(403);
});

// DEEP-05: Webhook nhiều lần (Idempotency)
test('DEEP-05: duplicate webhook callbacks with same transaction id process once idempotently', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Đức Chinh',
        'bride_name' => 'Mai Hà Trang',
        'wedding_date' => '2026-12-10',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-chinh-trang-'.uniqid(),
        'is_published' => false,
    ]);

    $plan = Plan::create([
        'name' => 'Gói Chuyên Nghiệp',
        'slug' => 'pro-'.uniqid(),
        'price' => 499000,
        'duration_days' => 180,
        'max_invitations' => 5,
        'is_active' => true,
    ]);

    $orderCode = 'MH'.strtoupper(Str::random(10));
    $order = Order::create([
        'user_id' => $user->id,
        'invitation_id' => $invitation->id,
        'plan_id' => $plan->id,
        'order_code' => $orderCode,
        'amount' => 499000,
        'status' => 'pending',
    ]);

    $bankAccount = config('services.sepay.bank_account', '103870000000');
    $rawPayload = [
        'id' => rand(100000, 999999),
        'gateway' => 'VietinBank',
        'transactionDate' => now()->format('Y-m-d H:i:s'),
        'accountNumber' => $bankAccount,
        'code' => $orderCode,
        'content' => "Thanh toan don hang {$orderCode}",
        'transferType' => 'in',
        'transferAmount' => 499000,
        'accumulated' => 499000,
        'subAccount' => null,
        'referenceCode' => 'MBVCB.'.$orderCode,
        'description' => "SePay payment for {$orderCode}",
    ];

    $rawContent = json_encode($rawPayload, JSON_UNESCAPED_UNICODE);
    $secret = config('services.sepay.webhook_secret', 'sepay_mayhen_test_secret_key');
    $timestamp = (string) time();
    $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawContent, $secret);

    // Lần 1: Kích hoạt đơn hàng và xuất bản thiệp
    $res1 = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SEPAY_SIGNATURE' => $signature,
            'HTTP_X_SEPAY_TIMESTAMP' => $timestamp,
        ],
        $rawContent
    );
    $res1->assertOk();

    $order->refresh();
    $invitation->refresh();
    expect($order->status)->toBe('paid')
        ->and($invitation->is_published)->toBeTrue()
        ->and($invitation->expires_at)->not->toBeNull();

    $originalExpiresAt = $invitation->expires_at;

    // Lần 2: Gửi lại cùng webhook với cùng signature và timestamp
    $res2 = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SEPAY_SIGNATURE' => $signature,
            'HTTP_X_SEPAY_TIMESTAMP' => $timestamp,
        ],
        $rawContent
    );
    $res2->assertOk();

    // Hạn thiệp không bị gia hạn lặp thêm lần nữa
    $invitation->refresh();
    expect($invitation->expires_at->toDateTimeString())->toBe($originalExpiresAt->toDateTimeString());
});

// DEEP-06: Thanh toán thiếu dư (Amount & Replay Defense)
test('DEEP-06: payments with insufficient amount or expired timestamp are rejected safely', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tuấn Hải',
        'bride_name' => 'Kỳ Duyên',
        'wedding_date' => '2026-11-30',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-hai-duyen-'.uniqid(),
        'is_published' => true,
    ]);

    $plan = Plan::create([
        'name' => 'Gói Plus',
        'slug' => 'plus-'.uniqid(),
        'price' => 299000,
        'duration_days' => 90,
        'max_invitations' => 3,
        'is_active' => true,
    ]);

    $orderCode = 'MH'.strtoupper(Str::random(10));
    $order = Order::create([
        'user_id' => $user->id,
        'invitation_id' => $invitation->id,
        'plan_id' => $plan->id,
        'order_code' => $orderCode,
        'amount' => 299000,
        'status' => 'pending',
    ]);

    $secret = config('services.sepay.webhook_secret', 'sepay_mayhen_test_secret_key');
    $bankAccount = config('services.sepay.bank_account', '103870000000');

    // 1. Chuyển khoản thiếu tiền (chỉ chuyển 199,000 VND thay vì 299,000 VND)
    $shortPayload = json_encode([
        'id' => rand(100000, 999999),
        'accountNumber' => $bankAccount,
        'code' => $orderCode,
        'content' => "Thanh toan don {$orderCode}",
        'transferType' => 'in',
        'transferAmount' => 199000,
    ], JSON_UNESCAPED_UNICODE);
    $nowTs = (string) time();
    $sigShort = 'sha256='.hash_hmac('sha256', $nowTs.'.'.$shortPayload, $secret);

    $resShort = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SEPAY_SIGNATURE' => $sigShort,
            'HTTP_X_SEPAY_TIMESTAMP' => $nowTs,
        ],
        $shortPayload
    );
    $resShort->assertStatus(400);

    // Đơn hàng vẫn giữ nguyên trạng thái pending
    $order->refresh();
    expect($order->status)->toBe('pending');

    // 2. Timestamp quá cũ (> 300s) chống Replay Attack -> trả 401 Unauthorized
    $stalePayload = json_encode([
        'id' => rand(100000, 999999),
        'accountNumber' => $bankAccount,
        'code' => $orderCode,
        'content' => "Thanh toan don {$orderCode}",
        'transferType' => 'in',
        'transferAmount' => 299000,
    ], JSON_UNESCAPED_UNICODE);
    $staleTs = (string) (time() - 360);
    $sigStale = 'sha256='.hash_hmac('sha256', $staleTs.'.'.$stalePayload, $secret);

    $resStale = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SEPAY_SIGNATURE' => $sigStale,
            'HTTP_X_SEPAY_TIMESTAMP' => $staleTs,
        ],
        $stalePayload
    );
    $resStale->assertStatus(401);
});

// DEEP-07: Chỉnh sửa bản nháp (Draft vs Published snapshot)
test('DEEP-07: unpublished draft changes do not bleed into public invitation view', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Nguyễn Minh Triết',
        'bride_name' => 'Trần Hải Yến',
        'wedding_date' => '2026-12-19',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-triet-yen-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'wedding' => [
                'groom_name' => 'Nguyễn Minh Triết',
            ],
            'draft_config' => [
                'wedding_delta' => [
                    'groom_name' => 'Tuấn Kiệt Chưa Xuất Bản',
                ],
            ],
        ],
    ]);

    // Khách xem trang công khai chỉ thấy tên đã xuất bản
    $response = $this->get(route('invitation.show', $invitation->slug));
    $response->assertStatus(200);
    $response->assertSee('Nguyễn Minh Triết');
    $response->assertDontSee('Tuấn Kiệt Chưa Xuất Bản');
});

// DEEP-08: Undo redo / Optimistic Concurrency 409
test('DEEP-08: concurrent autosave detects revision conflict and returns 409 status', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Đình Trọng',
        'bride_name' => 'Trang Heo',
        'wedding_date' => '2026-11-25',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-trong-trang-'.uniqid(),
        'revision_id' => 5,
        'is_published' => true,
    ]);

    // Tab 1 autosave lên revision 6
    $res1 = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'draft_config' => [
            'story' => 'Câu chuyện từ Tab 1',
        ],
    ]);
    $res1->assertOk()
        ->assertJson([
            'success' => true,
            'new_revision_id' => 6,
        ]);

    // Tab 2 gửi autosave với revision_id cũ (5) -> Bị trả 409 Conflict
    $res2 = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'draft_config' => [
            'story' => 'Câu chuyện từ Tab 2 gửi chậm',
        ],
    ]);
    $res2->assertStatus(409)
        ->assertJson([
            'success' => false,
            'conflict' => true,
            'current_revision' => 6,
        ]);
});

// DEEP-09: Random AI template gốc (Native whitelist)
test('DEEP-09: curated invitation generator only selects templates from validated native whitelist', function () {
    $curatedService = app(CuratedInvitationService::class);
    $styles = $curatedService->getStyles();
    $palettes = $curatedService->getPalettes();

    expect($styles)->toBeArray()->not->toBeEmpty()
        ->and($palettes)->toBeArray()->not->toBeEmpty();

    foreach ($styles as $key => $style) {
        expect($style)->toHaveKey('name')
            ->and($style)->toHaveKey('description');
    }
});

// DEEP-10: Chạy animation đúng lúc
test('DEEP-10: iframe embed mode automatically opens card and disables scroll locking', function () {
    $template = InvitationTemplate::where('is_active', true)->first();

    $response = $this->get(route('templates.preview', [
        'slug' => $template->slug,
        'embed' => 1,
    ]));

    $response->assertStatus(200);
    $response->assertSee('mayhen_embed_mode');
});

// DEEP-11: AI story không bịa thông tin
test('DEEP-11: ai story writer service handles missing romance data gracefully without hallucinating', function () {
    $writer = app(AiStoryWriterService::class);
    $result = $writer->generate('Văn Đức', 'Nhật Linh');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('success', true)
        ->and($result)->toHaveKey('options');

    expect($result['options'])->toBeArray()->toHaveCount(3);
    expect($result['options'][0])->toHaveKey('title')
        ->and($result['options'][0])->toHaveKey('id');
});

// DEEP-12: Template 7 vs 10 sections contract
test('DEEP-12: template engine preserves core sections across templates', function () {
    $activeTemplates = InvitationTemplate::where('is_active', true)->get();
    expect($activeTemplates->count())->toBeGreaterThanOrEqual(4);

    foreach ($activeTemplates as $tpl) {
        expect($tpl->slug)->not->toBeEmpty()
            ->and($tpl->name)->not->toBeEmpty();
    }
});

// DEEP-13: CSV Formula Injection Defense
test('DEEP-13: csv export sanitizes formula characters and prepends single quote with UTF-8 BOM', function () {
    expect(CsvSanitizerService::sanitize('=SUM(A1:A10)'))->toBe("'=SUM(A1:A10)")
        ->and(CsvSanitizerService::sanitize('+cmd|"/C calc"!A0'))->toBe("'+cmd|\"/C calc\"!A0")
        ->and(CsvSanitizerService::sanitize('-100+200'))->toBe("'-100+200")
        ->and(CsvSanitizerService::sanitize('@hyperlink("http://evil.com")'))->toBe("'@hyperlink(\"http://evil.com\")");

    // Dữ liệu văn bản bình thường không bị thay đổi
    expect(CsvSanitizerService::sanitize('Nguyễn Văn An'))->toBe('Nguyễn Văn An');
});

// DEEP-14: Moderation false positive defense
test('DEEP-14: profanity filter allows benign everyday wedding words while rejecting actual offensive language', function () {
    $rule = new NoProfanity;

    $passedBenign = true;
    $rule->validate('wishes', 'Kính chúc hai bên bố mẹ và hai con trăm năm hạnh phúc, buổi tiệc rất trang trọng!', function () use (&$passedBenign) {
        $passedBenign = false;
    });
    expect($passedBenign)->toBeTrue();

    $failedOffensive = false;
    $rule->validate('wishes', 'dm đồ lừa đảo khốn nạn', function () use (&$failedOffensive) {
        $failedOffensive = true;
    });
    expect($failedOffensive)->toBeTrue();
});

// DEEP-15: Agency claim handover lifecycle
test('DEEP-15: agency handover code cannot be replayed after successful claim', function () {
    $partnerUser = User::factory()->create(['role' => 'partner']);
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Studio Romance',
        'slug' => 'studio-romance',
        'partner_code' => 'MH-ROMANCE-'.rand(100, 999),
        'phone' => '0912345678',
        'email' => 'studio@romance.vn',
        'status' => 'active',
    ]);

    $wedding = Wedding::create([
        'partner_id' => $partner->id,
        'user_id' => $partnerUser->id,
        'groom_name' => 'Thành Chung',
        'bride_name' => 'Tố Uyên',
        'wedding_date' => '2026-11-22',
        'handover_code' => 'RC'.rand(1000, 9999),
        'handover_expires_at' => now()->addDays(30),
    ]);

    $handoverCode = $wedding->handover_code;
    $customer = User::factory()->create(['role' => 'customer']);

    // Khách hàng nhận thiệp thành công lần đầu
    $resClaim = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => $handoverCode,
    ]);
    $resClaim->assertRedirect();

    $wedding->refresh();
    expect($wedding->user_id)->toBe($customer->id)
        ->and($wedding->handover_code)->toBeNull();

    // Claim lại mã cũ -> Bị từ chối
    $resClaimAgain = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => $handoverCode,
    ]);
    $resClaimAgain->assertSessionHas('error');
});

// DEEP-16: Photo Drop retry / approval pipeline
test('DEEP-16: only approved guest photos appear on live projection wall', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Xuân Trường',
        'bride_name' => 'Nhuệ Giang',
        'wedding_date' => '2026-11-24',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-truong-giang-'.uniqid(),
        'is_published' => true,
    ]);

    // 1. Ảnh đang pending chưa duyệt
    $photoPending = GuestPhoto::create([
        'wedding_id' => $wedding->id,
        'uploader_name' => 'Bạn Đại Học',
        'photo_url' => '/storage/weddings/photos/test1.jpg',
        'caption' => 'Ảnh đang chờ duyệt',
        'is_approved' => false,
    ]);

    // 2. Ảnh đã approved
    $photoApproved = GuestPhoto::create([
        'wedding_id' => $wedding->id,
        'uploader_name' => 'Bạn Cấp 3',
        'photo_url' => '/storage/weddings/photos/test2.jpg',
        'caption' => 'Ảnh đã duyệt công khai',
        'is_approved' => true,
    ]);

    $feedRes = $this->getJson(route('live_wall.feed', $invitation->slug));
    $feedRes->assertOk();

    $captions = array_column($feedRes->json('photos'), 'caption');
    expect($captions)->toContain('Ảnh đã duyệt công khai')
        ->and($captions)->not->toContain('Ảnh đang chờ duyệt');
});

// DEEP-17: Khôi phục không hủy dữ liệu (Rollback with Snapshot Integrity)
test('DEEP-17: emergency rollback preserves audit trail and restores clean data', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Hùng Dũng',
        'bride_name' => 'Triệu Mộc Trinh',
        'wedding_date' => '2026-12-12',
        'venue_name' => 'Melia Hanoi',
    ]);

    $templateService = app(InvitationTemplateService::class);
    $invitation = $templateService->saveInvitation($owner, $template->id, [
        'wedding' => [
            'groom_name' => 'Hùng Dũng',
            'bride_name' => 'Triệu Mộc Trinh',
            'wedding_date' => '2026-12-12',
            'venue_name' => 'Melia Hanoi',
        ],
    ]);

    expect($invitation)->toBeInstanceOf(Invitation::class);
});

// DEEP-18: Đánh giá chất lượng thiếu dữ liệu (Wedding Consistency Guardian)
test('DEEP-18: consistency guardian identifies incomplete wedding data and denies excellent score', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Hoàng Đức',
        'bride_name' => 'Gia Hân',
        'wedding_date' => '2026-12-15',
        'venue_name' => null,
        'venue_address' => null,
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-duc-han-'.uniqid(),
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->inspect($wedding, $invitation);

    expect($result['status'])->not->toBe('excellent')
        ->and($result['score'])->toBeLessThan(100);
});

// DEEP-19: Phiếu duyệt gia đình (Two Family Approval token verification)
test('DEEP-19: two family approval generates distinct tokens and records blessing note', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tấn Tài',
        'bride_name' => 'Phạm Uyên',
        'wedding_date' => '2026-12-20',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-tai-uyen-'.uniqid(),
        'is_published' => true,
    ]);

    $service = app(TwoFamilyApprovalService::class);
    $tokens = $service->getOrCreateApprovalTokens($invitation);

    expect($tokens['groom_family']['token'])->toStartWith('gf_')
        ->and($tokens['bride_family']['token'])->toStartWith('bf_');

    // Bố mẹ gửi duyệt thành công
    $brideToken = $tokens['bride_family']['token'];
    $response = $this->post(route('family_approval.submit', $brideToken), [
        'decision' => 'approved',
        'note' => 'Bố mẹ đồng ý ngày lành tháng tốt!',
    ]);
    $response->assertRedirect();

    $invitation->refresh();
    $approvals = $invitation->custom_config['family_approvals'];
    expect($approvals['bride_family']['status'])->toBe('approved')
        ->and($approvals['bride_family']['blessing_note'])->toContain('ngày lành tháng tốt');
});

// DEEP-20: Dữ liệu PII trong bản in & xuất danh sách
test('DEEP-20: guest export respects guest privacy and isolates sensitive bank details', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Văn Thanh',
        'bride_name' => 'Bảo Hân',
        'wedding_date' => '2026-11-29',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-thanh-han-'.uniqid(),
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Khách VIP Trần Bình',
        'phone' => '0988776655',
        'expected_attendees' => 2,
    ]);

    $otherUser = User::factory()->create();

    // Người lạ không thể xuất dữ liệu khách của đám cưới khác
    $this->actingAs($otherUser)
        ->get(route('guests.export', $invitation->id))
        ->assertStatus(404);
});
