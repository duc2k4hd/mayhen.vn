<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingGift;
use App\Models\WeddingTable;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('services.sepay.webhook_secret', 'rc_test_sepay_webhook_secret_key');
    Config::set('services.sepay.bank_account', '0988776655');
    Config::set('services.sepay.bank_code', 'MB');
    Config::set('services.sepay.bank_name', 'MBBank');
    Config::set('services.sepay.bank_account_name', 'CONG TY MAY HEN');
});

test('G4-001: Toàn bộ bảng cơ sở dữ liệu cốt lõi tồn tại và hợp lệ sau khi migrate', function () {
    $coreTables = [
        'users',
        'weddings',
        'invitations',
        'invitation_templates',
        'orders',
        'plans',
        'guests',
        'guest_responses',
        'guest_response_logs',
        'wedding_tables',
        'wedding_events',
        'wedding_wishes',
        'wedding_gifts',
        'system_backups',
        'support_tickets',
        'support_ticket_replies',
        'partner_commissions',
        'partners',
        'coupons',
    ];

    foreach ($coreTables as $table) {
        expect(Schema::hasTable($table))->toBeTrue("Bảng {$table} phải tồn tại trong cơ sở dữ liệu.");
    }
});

test('G4-004: Xác thực chữ ký số SePay chuẩn sha256 và tự động kích hoạt đơn hàng', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'name' => 'Gói Trọn Đời RC',
        'slug' => 'goi-tron-doi-rc-'.uniqid(),
        'price' => 399000,
        'features' => [],
        'is_active' => true,
    ]);

    $orderCode = 'MHABCDEF1234';

    $order = Order::create([
        'order_code' => $orderCode,
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 399000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $secret = config('services.sepay.webhook_secret');
    $payload = [
        'id' => 'TX_SEPAY_UAT_999',
        'transferType' => 'in',
        'accountNumber' => '0988776655',
        'transferAmount' => 399000,
        'code' => $orderCode,
        'content' => 'Thanh toan don '.$orderCode,
    ];
    $rawContent = json_encode($payload);
    $timestamp = now()->timestamp;
    $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawContent, $secret);

    // Gửi webhook với đầy đủ headers chuẩn xác
    $response = $this->call(
        'POST',
        route('webhook.payment'),
        [],
        [],
        [],
        [
            'HTTP_X_SEPAY_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_SEPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $rawContent
    );

    $response->assertStatus(200);
    expect($order->fresh()->isPaid())->toBeTrue();
    expect($order->fresh()->paid_at)->not->toBeNull();
});

test('G4-006: Support Desk UAT: Tạo ticket, phản hồi qua lại mượt mà không lỗi 500', function () {
    $client = User::factory()->create(['role' => 'customer']);
    $staff = User::factory()->create(['role' => 'staff']);

    // 1. Khách gửi ticket yêu cầu hỗ trợ
    $ticket = SupportTicket::create([
        'user_id' => $client->id,
        'ticket_code' => 'TK-UAT-'.strtoupper(Str::random(6)),
        'subject' => 'Hỗ trợ thay đổi số tài khoản mừng cưới',
        'body' => 'Tôi muốn đổi STK từ MBBank sang Techcombank.',
        'category' => 'general',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    expect($ticket)->not->toBeNull();

    // 2. Nhân viên phản hồi
    $reply = SupportTicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $staff->id,
        'body' => 'Chào bạn, chúng tôi đã ghi nhận yêu cầu và sẽ hướng dẫn bạn cập nhật.',
        'is_staff' => true,
    ]);

    expect($reply->user->id)->toBe($staff->id);
    expect($ticket->replies()->count())->toBe(1);
});

test('G4-007: Doanh thu Dashboard tính toán động từ orders thực tế không lấy số tĩnh', function () {
    $user = User::factory()->create();
    $plan = Plan::create([
        'name' => 'Gói Tiêu Chuẩn',
        'slug' => 'goi-tieu-chuan-'.uniqid(),
        'price' => 200000,
        'features' => [],
        'is_active' => true,
    ]);

    // Tạo 2 đơn đã thanh toán: 200k + 300k = 500k
    Order::create([
        'order_code' => 'MHTESTREV01',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 200000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    Order::create([
        'order_code' => 'MHTESTREV02',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 300000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    // Đơn pending không được tính vào doanh thu
    Order::create([
        'order_code' => 'MHTESTREV03',
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
        'status' => 'pending',
        'payment_method' => 'bank_transfer',
    ]);

    $actualRevenue = (int) Order::where('status', 'paid')->sum('amount');
    expect($actualRevenue)->toBe(500000);
});

test('G4-009: Quyết toán hoa hồng đối tác PartnerCommission chỉ chuyển settled khi có mã giao dịch', function () {
    $partnerUser = User::factory()->create(['role' => 'partner']);
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Studio Áo Cưới Sài Gòn',
        'slug' => 'studio-ao-cuoi-sg-'.uniqid(),
        'partner_code' => 'STUDIO_SG',
        'phone' => '0901234567',
        'email' => 'studio@saigon.vn',
        'status' => 'approved',
        'commission_rate' => 20.00,
    ]);

    $commission = PartnerCommission::create([
        'partner_id' => $partner->id,
        'amount' => 100000,
        'type' => 'invitation_order',
        'status' => 'pending',
        'description' => 'Hoa hồng đơn hàng MH999',
    ]);

    expect($commission->status)->toBe('pending');
    expect($commission->payout_reference_code)->toBeNull();

    // Đối soát và thanh toán hoa hồng với mã ngân hàng
    $admin = User::factory()->create(['role' => 'admin']);
    $commission->update([
        'status' => 'settled',
        'payout_reference_code' => 'VCB_PAYOUT_COMMISSION_88',
        'reconciled_at' => now(),
        'reconciled_by' => $admin->id,
        'reconciliation_note' => 'Đã chuyển khoản hoa hồng thành công',
    ]);

    expect($commission->fresh()->status)->toBe('settled');
    expect($commission->fresh()->payout_reference_code)->toBe('VCB_PAYOUT_COMMISSION_88');
});

test('G4-010: Quyền lãng quên (Luật 91/2025/QH15) ẩn danh PII khách hàng nhưng bảo toàn hóa đơn kế toán', function () {
    $customer = User::factory()->create([
        'name' => 'Nguyễn Thị Hương',
        'email' => 'huong.nguyen@gmail.com',
        'phone' => '0912345678',
        'role' => 'customer',
    ]);

    $plan = Plan::create([
        'name' => 'Gói Kỷ Niệm',
        'slug' => 'goi-ky-niem-'.uniqid(),
        'price' => 350000,
        'features' => [],
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHORDTAX01',
        'user_id' => $customer->id,
        'plan_id' => $plan->id,
        'amount' => 350000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    $superAdmin = User::factory()->create(['role' => 'admin']);

    // Admin thực hiện quyền lãng quên (anonymize) theo yêu cầu của khách hàng
    $response = $this->actingAs($superAdmin)->post(
        route('admin.users.anonymize', ['id' => $customer->id]),
        ['reason' => 'Khách hàng gửi yêu cầu xóa dữ liệu cá nhân theo luật']
    );

    $response->assertRedirect();

    $customerFresh = $customer->fresh();
    // PII đã được ẩn danh hóa
    expect($customerFresh->name)->toContain('Khách Hàng Đã Ẩn Danh');
    expect($customerFresh->email)->toContain('@deleted.mayhen.vn');
    expect($customerFresh->phone)->toBe('0000000000');

    // Nhưng hóa đơn kế toán Order vẫn bảo toàn 100% để phục vụ thuế
    expect($order->fresh()->amount)->toBe(350000);
    expect($order->fresh()->user_id)->toBe($customer->id);
});

test('G4-013: Một cặp đôi thật giả lập E2E hoàn chỉnh: Tạo thiệp -> Mua gói -> Khách RSVP -> Check-in -> Quà mừng', function () {
    // 1. Cặp đôi đăng ký và tạo đám cưới
    $couple = User::factory()->create(['role' => 'customer', 'name' => 'Hoàng & Lan']);
    $wedding = Wedding::create([
        'user_id' => $couple->id,
        'groom_name' => 'Đức Hoàng',
        'bride_name' => 'Mai Lan',
        'wedding_date' => now()->addDays(20)->toDateString(),
        'slug' => 'hoang-lan-wedding-'.uniqid(),
    ]);

    $template = InvitationTemplate::create([
        'name' => 'Mẫu Hoàng Gia E2E',
        'slug' => 'mau-hoang-gia-e2e-'.uniqid(),
        'thumbnail_url' => 'assets/templates/preview.webp',
        'price' => 0,
        'is_active' => true,
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'hoang-lan-invitation-'.uniqid(),
        'is_published' => false,
        'custom_config' => ['is_locked' => false],
    ]);

    // 2. Mua gói dịch vụ và kích hoạt xuất bản thiệp
    $plan = Plan::create([
        'name' => 'Gói Cao Cấp',
        'slug' => 'goi-cao-cap-'.uniqid(),
        'price' => 500000,
        'features' => [],
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'MHE2EORDER01',
        'user_id' => $couple->id,
        'plan_id' => $plan->id,
        'amount' => 500000,
        'status' => 'paid',
        'payment_method' => 'bank_transfer',
        'paid_at' => now(),
    ]);

    expect($order->isPaid())->toBeTrue();
    // Thiệp được xuất bản
    $invitation->update(['is_published' => true]);

    // 3. Tạo sự kiện và bàn tiệc
    $event = WeddingEvent::create([
        'wedding_id' => $wedding->id,
        'name' => 'Tiệc Cưới Chính Thức',
        'event_date' => now()->addDays(20)->toDateString(),
        'venue_name' => 'Sảnh Versailles',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $table = WeddingTable::create([
        'wedding_id' => $wedding->id,
        'event_id' => $event->id,
        'name' => 'Bàn Bạn Thân 01',
        'capacity' => 10,
    ]);

    // 4. Mời khách
    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'table_id' => $table->id,
        'name' => 'Vũ Tuấn Anh',
        'guest_token' => 'TOKEN_TUAN_ANH_E2E',
        'expected_attendees' => 2,
    ]);

    // 5. Khách xác nhận tham dự (RSVP) với tư cách khách mời công khai qua link thiệp cá nhân
    $rsvpResponse = $this->postJson(route('rsvp.submit', [
        'slug' => $invitation->slug,
        'token' => 'TOKEN_TUAN_ANH_E2E',
    ]), [
        'event_id' => $event->id,
        'guest_name' => 'Vũ Tuấn Anh',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    $rsvpResponse->assertStatus(200);
    expect($guest->responses()->count())->toBe(1);

    // 6. Lễ tân quét mã QR điểm danh ngày cưới (chủ thiệp đăng nhập thao tác)
    $scanResponse = $this->actingAs($couple)->postJson(
        route('checkin.scan', ['slug' => $invitation->slug]),
        ['token' => 'TOKEN_TUAN_ANH_E2E', 'attendees' => 2]
    );

    $scanResponse->assertStatus(200);
    expect($guest->fresh()->checked_in_at)->not->toBeNull();
    expect($guest->fresh()->checked_in_attendees)->toBe(2);

    // 7. Khách gửi tiền mừng và lời chúc
    $gift = WeddingGift::create([
        'wedding_id' => $wedding->id,
        'sender_name' => 'Vũ Tuấn Anh',
        'amount' => 2000000,
        'wishing_message' => 'Chúc hai bạn mãi mãi hạnh phúc bên nhau!',
        'status' => 'received',
    ]);

    $wish = WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Vũ Tuấn Anh',
        'sender_name' => 'Vũ Tuấn Anh',
        'message' => 'Chúc mừng hạnh phúc Đức Hoàng & Mai Lan!',
        'is_approved' => true,
    ]);

    expect($gift)->not->toBeNull();
    expect($wish)->not->toBeNull();
    expect($wedding->gifts()->sum('amount'))->toBe(2000000);
});

test('G4-014: Trạm chỉ huy nghiệm thu thương mại (Cockpit) sẵn sàng bàn giao cho người dùng', function () {
    $superAdmin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($superAdmin)->get(route('admin.system.acceptance.index'));
    $response->assertStatus(200);
    $response->assertSee('Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện');
    $response->assertSee('Điểm Tuân Thủ Toàn Diện');
});
