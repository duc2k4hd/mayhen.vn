<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\MessagingDispatch;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\ReliableMessagingDispatchService;
use Carbon\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'email' => 'admin_msg_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'email' => 'manager_msg_test@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_msg_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_msg_test@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Celestial Gold Test',
        'slug' => 'celestial-gold-msg-test-'.Str::random(6),
        'thumbnail_url' => '/assets/templates/celestial.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Quốc Tuấn',
        'bride_name' => 'Mai Phương',
        'wedding_date' => Carbon::today()->addDays(20)->format('Y-m-d'),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tuan-phuong-'.Str::random(5),
        'is_published' => true,
    ]);
});

test('admin can access dispatches index and create page cleanly', function () {
    $indexResponse = $this->actingAs($this->superAdmin)->get(route('admin.messaging.index'));
    $indexResponse->assertStatus(200);
    $indexResponse->assertViewIs('admin.pages.messaging.index');
    $indexResponse->assertSee('Phát Tin Thiệp Mời Tin Cậy');
    $indexResponse->assertSee('Tổng Chiến Dịch');

    $createResponse = $this->actingAs($this->staff)->get(route('admin.messaging.create'));
    $createResponse->assertStatus(200);
    $createResponse->assertViewIs('admin.pages.messaging.create');
    $createResponse->assertSee('Tạo Đợt Phát Tin Thiệp Mời');
});

test('staff can create draft campaign and view details', function () {
    $response = $this->actingAs($this->staff)
        ->post(route('admin.messaging.store'), [
            'wedding_id' => $this->wedding->id,
            'campaign_name' => 'Đợt Gửi 1 — Bạn Bè Chú Rể',
            'channel' => 'zalo_zns',
            'filter_group' => 'Bạn Chú Rể',
            'message_template' => 'Kính gửi [DANH_XUNG] [TEN_KHACH], [CAP_DOI] mời bạn dự cưới tại [LINK_THIEP].',
        ]);

    $dispatch = MessagingDispatch::where('campaign_name', 'Đợt Gửi 1 — Bạn Bè Chú Rể')->first();
    expect($dispatch)->not->toBeNull();
    expect($dispatch->status)->toBe('draft');
    expect($dispatch->channel)->toBe('zalo_zns');
    expect((float) $dispatch->unit_cost_vnd)->toEqual(250.00);

    $response->assertRedirect(route('admin.messaging.show', $dispatch->id));
    $response->assertSessionHas('success');
});

test('deduplication engine skips duplicate phones and calculates accurate unit and total cost', function () {
    // Tạo 3 khách mời, trong đó khách 1 và 2 dùng chung 1 số điện thoại
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn An',
        'salutation' => 'Anh',
        'phone' => '0901234567',
        'guest_token' => 'token_an_'.Str::random(8),
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị Bình (Chung số với anh An)',
        'salutation' => 'Chị',
        'phone' => '0901234567', // Trùng SĐT
        'guest_token' => 'token_binh_'.Str::random(8),
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Hoàng Cường',
        'salutation' => 'Em',
        'phone' => '0987654321', // SĐT riêng
        'guest_token' => 'token_cuong_'.Str::random(8),
    ]);

    $dispatch = MessagingDispatch::create([
        'wedding_id' => $this->wedding->id,
        'campaign_name' => 'Test Khử Trùng',
        'channel' => 'sms_brandname',
        'status' => 'draft',
        'unit_cost_vnd' => 650.00,
        'message_template' => 'Kính gửi [DANH_XUNG] [TEN_KHACH], [CAP_DOI] mời bạn: [LINK_THIEP]',
        'created_by' => $this->staff->id,
    ]);

    $result = ReliableMessagingDispatchService::runDryRun($dispatch);

    expect($result['total'])->toBe(3);
    expect($result['valid'])->toBe(2);
    expect($result['deduplicated'])->toBe(1);
    expect($result['estimated_cost'])->toEqual(1300.00); // 2 tin hợp lệ * 650đ

    $dispatch->refresh();
    expect($dispatch->status)->toBe('dry_run_ready');
    expect($dispatch->deduplicated_count)->toBe(1);

    // Kiểm tra log phân loại đúng
    $skippedLog = $dispatch->logs()->where('status', 'skipped_duplicate')->first();
    expect($skippedLog)->not->toBeNull();
    expect($skippedLog->phone)->toBe('0901234567');
    expect($skippedLog->error_message)->toContain('bị trùng lặp');
});

test('personalization formatter properly replaces salutation, name, couple, and invitation link', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hùng',
        'salutation' => 'Bác',
        'phone' => '0912345678',
        'guest_token' => 'bac_hung_unique_token',
    ]);

    $template = "Kính gửi [DANH_XUNG] [TEN_KHACH]!\n[CAP_DOI] trân trọng kính mời vào ngày [NGAY_CUOI]. Xem thiệp tại: [LINK_THIEP]";

    $rendered = ReliableMessagingDispatchService::renderPersonalizedMessage($template, $guest, $this->wedding);

    expect($rendered)->toContain('Kính gửi Bác Bác Hùng!');
    expect($rendered)->toContain('Quốc Tuấn & Mai Phương');
    expect($rendered)->toContain('guest=bac_hung_unique_token');
});

test('staff is forbidden from dispatching real campaign while manager or super admin can approve with audit log', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Võ Thị Dung',
        'salutation' => 'Cô',
        'phone' => '0933445566',
        'guest_token' => 'dung_token_'.Str::random(6),
        'is_sent' => false,
    ]);

    $dispatch = MessagingDispatch::create([
        'wedding_id' => $this->wedding->id,
        'campaign_name' => 'Gửi Thiệp Zalo ZNS',
        'channel' => 'zalo_zns',
        'status' => 'draft',
        'unit_cost_vnd' => 250.00,
        'message_template' => 'Kính mời [DANH_XUNG] [TEN_KHACH] xem thiệp cưới: [LINK_THIEP]',
        'created_by' => $this->staff->id,
    ]);

    // 1. Nếu chưa Dry-Run mà gửi thật thì bị chặn
    $notReadyResponse = $this->actingAs($this->manager)
        ->post(route('admin.messaging.dispatch_real', $dispatch->id));
    $notReadyResponse->assertRedirect();
    $notReadyResponse->assertSessionHas('error');

    // Chạy Dry-Run
    ReliableMessagingDispatchService::runDryRun($dispatch);
    $dispatch->refresh();
    expect($dispatch->status)->toBe('dry_run_ready');

    // 2. Staff thông thường bị chặn bởi middleware admin.manager (Dual Approval)
    $staffResponse = $this->actingAs($this->staff)
        ->post(route('admin.messaging.dispatch_real', $dispatch->id));
    $staffResponse->assertRedirect();
    $staffResponse->assertSessionHas('error');

    // 3. Manager phê duyệt gửi thành công
    $managerResponse = $this->actingAs($this->manager)
        ->post(route('admin.messaging.dispatch_real', $dispatch->id));
    $managerResponse->assertRedirect();
    $managerResponse->assertSessionHas('success');

    $dispatch->refresh();
    expect($dispatch->status)->toBe('completed');
    expect($dispatch->approved_by)->toBe($this->manager->id);
    expect($dispatch->successful_count)->toBe(1);

    // Kiểm tra guest được đánh dấu đã gửi thiệp
    $guest->refresh();
    expect($guest->is_sent)->toBeTrue();

    // Kiểm tra Audit Log
    $logs = AuditLogger::getRecentLogs(10, 'messaging.campaign_dispatched');
    expect(count($logs))->toBeGreaterThan(0);
});
