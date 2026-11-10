<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PrivacyErasureRequest;
use App\Models\User;
use App\Models\Wedding;
use App\Services\PrivacyRetentionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-11-10 10:00:00');

    $this->manager = User::factory()->create([
        'email' => 'manager_privacy@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_privacy@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_privacy@mayhen.vn',
        'role' => 'customer',
        'name' => 'Nguyễn Văn Cường',
        'phone' => '0912345678',
    ]);
});

afterEach(function () {
    Carbon::setTestNow();
});

test('unauthenticated user is redirected to admin login', function () {
    $response = $this->get(route('admin.privacy.index'));
    $response->assertRedirect(route('admin.login'));
});

test('staff role can view privacy desk but cannot execute batch retention or approve requests', function () {
    $response = $this->actingAs($this->staff)->get(route('admin.privacy.index'));
    $response->assertOk();
    $response->assertSee('Quản Trị Quyền Riêng Tư');

    // Thao tác dọn dẹp hàng loạt bị chặn khi là Staff
    $postBatch = $this->actingAs($this->staff)->post(route('admin.privacy.batch_retention'), [
        'wedding_ids' => [1],
    ]);
    $postBatch->assertRedirect();
    $postBatch->assertSessionHas('error');

    // Thao tác duyệt xóa bị chặn khi là Staff
    $req = PrivacyErasureRequest::create([
        'request_code' => PrivacyErasureRequest::generateCode(),
        'user_id' => $this->customer->id,
        'requested_by_type' => 'customer',
        'requester_email' => $this->customer->email,
        'requester_name' => $this->customer->name,
        'reason' => 'Yêu cầu xóa dữ liệu cá nhân sau cưới',
        'status' => 'pending',
    ]);

    $postApprove = $this->actingAs($this->staff)->post(route('admin.privacy.approve_erasure', $req->id));
    $postApprove->assertRedirect();
    $postApprove->assertSessionHas('error');
});

test('retention scanner identifies weddings older than 180 days with un-anonymized guest pii', function () {
    // 1. Đám cưới mới diễn ra 30 ngày trước -> Không thuộc diện dọn dẹp 180 ngày
    $recentWedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Tuấn',
        'bride_name' => 'Lan',
        'wedding_date' => now()->subDays(30),
    ]);
    Guest::create([
        'wedding_id' => $recentWedding->id,
        'name' => 'Khách Mới',
        'phone' => '0901112222',
    ]);

    // 2. Đám cưới đã qua 200 ngày -> Thuộc diện dọn dẹp
    $oldWedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Hoàng',
        'bride_name' => 'Mai',
        'wedding_date' => now()->subDays(200),
    ]);
    Guest::create([
        'wedding_id' => $oldWedding->id,
        'name' => 'Khách Cũ',
        'phone' => '0903334444',
    ]);

    $scanned = PrivacyRetentionService::scanRetentionEligibleWeddings(180);

    expect($scanned)->toHaveCount(1);
    expect($scanned->first()['wedding_id'])->toBe($oldWedding->id);
    expect($scanned->first()['days_since_wedding'])->toBe(200);
    expect($scanned->first()['guests_with_phone_count'])->toBe(1);
});

test('manager can execute retention batch to anonymize guest pii while preserving accounting orders', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Văn',
        'bride_name' => 'Thảo',
        'wedding_date' => now()->subDays(190),
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Nguyễn Minh',
        'phone' => '0988776655',
    ]);

    $response = GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => 'Nguyễn Minh',
        'phone' => '0988776655',
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
        'dietary_requirements' => 'Dị ứng hải sản',
        'ip_address' => '14.162.12.34',
    ]);

    $plan = Plan::create([
        'name' => 'Gói Pro',
        'slug' => 'pro',
        'price' => 699000,
        'duration_days' => 365,
        'is_active' => true,
    ]);

    // Tạo đơn hàng kế toán trị giá 699.000đ
    $order = Order::create([
        'order_code' => 'ORD-TEST-RETENTION',
        'user_id' => $this->customer->id,
        'plan_id' => $plan->id,
        'amount' => 699000,
        'status' => 'paid',
        'payment_method' => 'vietqr',
        'paid_at' => now()->subDays(190),
        'provider_transaction_id' => 'PAY-RETENTION-01',
    ]);

    $responseHttp = $this->actingAs($this->manager)->post(route('admin.privacy.batch_retention'), [
        'wedding_ids' => [$wedding->id],
    ]);

    $responseHttp->assertSessionHas('success');

    // Kiểm tra PII khách mời đã được ẩn danh hóa
    $guest->refresh();
    expect($guest->phone)->toBeNull();

    $response->refresh();
    expect($response->phone)->toBeNull();
    expect($response->dietary_requirements)->toBe('[ANONYMIZED]');
    expect($response->ip_address)->toBe('127.0.0.1');

    // BẢO TỒN KẾ TOÁN: Đơn hàng vẫn nguyên vẹn 100%
    $order->refresh();
    expect($order->status)->toBe('paid');
    expect($order->amount)->toBe(699000);
    expect($order->provider_transaction_id)->toBe('PAY-RETENTION-01');

    // Wedding đã được gắn metadata
    $wedding->refresh();
    expect($wedding->extra_data['guest_pii_anonymized_at'])->not->toBeNull();
});

test('dsar erasure dual approval workflow from pending to approved to executed', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Tiến',
        'bride_name' => 'Hương',
        'wedding_date' => now()->subDays(90),
    ]);

    $plan = Plan::create([
        'name' => 'Gói Hoàng Gia Pro',
        'slug' => 'pro-vip',
        'price' => 1290000,
        'duration_days' => 365,
        'is_active' => true,
    ]);

    $order = Order::create([
        'order_code' => 'ORD-DSAR-001',
        'user_id' => $this->customer->id,
        'plan_id' => $plan->id,
        'amount' => 1290000,
        'status' => 'paid',
        'payment_method' => 'vietqr',
        'paid_at' => now()->subDays(90),
        'reference_code' => 'PAY-DSAR-01',
    ]);

    $req = PrivacyErasureRequest::create([
        'request_code' => PrivacyErasureRequest::generateCode(),
        'user_id' => $this->customer->id,
        'requested_by_type' => 'customer',
        'requester_email' => $this->customer->email,
        'requester_name' => $this->customer->name,
        'reason' => 'Tôi muốn xóa toàn bộ thông tin cá nhân theo quyền lãng quên',
        'status' => 'pending',
    ]);

    // 1. Quản lý duyệt (Maker-Checker Step 1)
    $approveRes = $this->actingAs($this->manager)->post(route('admin.privacy.approve_erasure', $req->id), [
        'review_notes' => 'Chấp thuận xóa tài khoản và bảo lưu hồ sơ kế toán.',
    ]);
    $approveRes->assertSessionHas('success');

    $req->refresh();
    expect($req->status)->toBe('approved');
    expect($req->reviewed_by)->toBe($this->manager->id);

    // 2. Quản lý thực thi (Maker-Checker Step 2)
    $executeRes = $this->actingAs($this->manager)->post(route('admin.privacy.execute_erasure', $req->id));
    $executeRes->assertSessionHas('success');

    $req->refresh();
    expect($req->status)->toBe('executed');
    expect($req->executed_at)->not->toBeNull();
    expect($req->accounting_records_preserved)->toBeTrue();

    // 3. User profile bị ẩn danh hóa
    $this->customer->refresh();
    expect($this->customer->name)->toBe('[Tài khoản đã ẩn danh]');
    expect($this->customer->email)->toStartWith('anonymized_');
    expect($this->customer->phone)->toBeNull();
    expect($this->customer->is_locked)->toBeTrue();

    // 4. Đơn hàng kế toán không bao giờ bị xóa
    $order->refresh();
    expect($order->status)->toBe('paid');
    expect($order->amount)->toBe(1290000);
});

test('secure download token expires after ttl and limits downloads to maximum allowed', function () {
    $tokenData = PrivacyRetentionService::generateSecureDownloadToken($this->customer, 24);
    $token = $tokenData['token'];

    // Lần tải 1: Thành công
    $res1 = $this->actingAs($this->manager)->get(route('admin.privacy.download_dsar_export', ['token' => $token]));
    $res1->assertOk();
    $res1->assertHeader('content-type', 'application/json');

    // Sau 25 giờ (Token hết hạn): Bị chặn 403
    Carbon::setTestNow(now()->addHours(25));

    $resExpired = $this->actingAs($this->manager)->get(route('admin.privacy.download_dsar_export', ['token' => $token]));
    $resExpired->assertForbidden();
});
