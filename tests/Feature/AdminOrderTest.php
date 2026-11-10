<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->customer = User::factory()->create([
        'name' => 'Nguyễn Thị Hoa',
        'email' => 'hoa@example.com',
        'phone' => '0901234567',
        'role' => 'customer',
    ]);

    $this->plan = Plan::create([
        'slug' => 'plus',
        'name' => 'Gói Hạnh Phúc (Plus)',
        'price' => 149000,
        'duration_days' => 365,
        'max_invitations' => 1,
        'features' => ['Tải ảnh không giới hạn', 'Nhạc nền tự chọn', 'Bản đồ chỉ đường'],
        'is_active' => true,
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Ngày Tình Yêu Đơm Hoa',
        'slug' => 'ngay-tinh-yeu-dom-hoa',
        'category' => 'modern',
        'thumbnail_url' => '/test-thumb.webp',
        'is_active' => true,
        'is_featured' => false,
        'default_config' => [
            'tier' => 'plus',
            'complexity' => 'standard',
        ],
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Văn Nam',
        'bride_name' => 'Thị Hoa',
        'wedding_date' => now()->addMonth(),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-hoa-wedding-2026',
        'title' => 'Thiệp Cưới Nam & Hoa',
        'is_published' => false,
        'expires_at' => null,
    ]);

    $this->order = Order::create([
        'order_code' => 'MHTEST12345',
        'user_id' => $this->customer->id,
        'plan_id' => $this->plan->id,
        'invitation_id' => $this->invitation->id,
        'amount' => 149000,
        'status' => 'pending',
        'payment_method' => 'vietqr',
    ]);
});

test('guest cannot access admin orders list or show', function () {
    $this->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.orders.show', $this->order->id))->assertRedirect(route('admin.login'));
});

test('regular customer cannot access admin orders', function () {
    $this->actingAs($this->customer)->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
    $this->actingAs($this->customer)->get(route('admin.orders.show', $this->order->id))->assertRedirect(route('admin.login'));
});

test('admin can view orders list with data and kpi summary', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

    $response->assertStatus(200);
    $response->assertSee('MHTEST12345');
    $response->assertSee('Nguyễn Thị Hoa');
    $response->assertSee('Gói Hạnh Phúc (Plus)');
    $response->assertSee('149.000đ');
    $response->assertSee('Doanh Thu Đã Thu');
});

test('admin can filter orders by search text, status and plan', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.orders.index', [
        'q' => 'MHTEST12345',
        'status' => 'pending',
        'plan_id' => $this->plan->id,
        'payment_method' => 'vietqr',
    ]));

    $response->assertStatus(200);
    $response->assertSee('MHTEST12345');

    // Searching for nonexistent code
    $emptyResponse = $this->actingAs($this->admin)->get(route('admin.orders.index', [
        'q' => 'MHNONEXISTENT999',
    ]));

    $emptyResponse->assertStatus(200);
    $emptyResponse->assertSee('Không tìm thấy đơn hàng nào');
});

test('admin can view order details with customer and plan features', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $this->order->id));

    $response->assertStatus(200);
    $response->assertSee('MHTEST12345');
    $response->assertSee('Nguyễn Thị Hoa');
    $response->assertSee('Tải ảnh không giới hạn');
    $response->assertSee('Duyệt Thanh Toán Thủ Công');
});

test('admin can manually approve pending order and activate invitation', function () {
    expect($this->order->isPaid())->toBeFalse();
    expect($this->invitation->is_published)->toBeFalse();
    expect($this->invitation->expires_at)->toBeNull();

    $response = $this->actingAs($this->admin)->post(route('admin.orders.approve', $this->order->id), [
        'reference_code' => 'FT26091823901',
        'reconciliation_note' => 'Đã xác nhận tiền về tài khoản ngân hàng khớp 100%',
    ]);

    $response->assertSessionHas('success');

    $freshOrder = $this->order->fresh();
    expect($freshOrder->isPaid())->toBeTrue();
    expect($freshOrder->paid_at)->not->toBeNull();
    expect($freshOrder->provider_transaction_id)->toBe('FT26091823901');

    $freshInvitation = $this->invitation->fresh();
    expect($freshInvitation->is_published)->toBeTrue();
    expect($freshInvitation->expires_at)->not->toBeNull();
    expect($freshInvitation->expires_at->isFuture())->toBeTrue();

    // Verify reconciliation ledger record
    $recon = OrderReconciliation::where('order_id', $this->order->id)->first();
    expect($recon)->not->toBeNull();
    expect($recon->reference_code)->toBe('FT26091823901');
    expect($recon->status)->toBe('approved');
});

test('admin can cancel a pending order', function () {
    expect($this->order->status)->toBe('pending');

    $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $this->order->id));

    $response->assertSessionHas('success');
    expect($this->order->fresh()->status)->toBe('cancelled');
});

test('admin cannot cancel an already paid order', function () {
    $this->order->update(['status' => 'paid', 'paid_at' => now()]);

    $response = $this->actingAs($this->admin)->post(route('admin.orders.cancel', $this->order->id));

    $response->assertSessionHas('error');
    expect($this->order->fresh()->status)->toBe('paid');
});

test('admin can view webhook audit logs with events', function () {
    $logPath = storage_path('logs/payment_webhooks.jsonl');
    $sampleLog = json_encode([
        'timestamp' => now()->toIso8601String(),
        'ip' => '127.0.0.1',
        'status' => 'success',
        'message' => 'Xác nhận thanh toán và kích hoạt gói cho đơn #MHTEST12345 thành công',
        'order_code' => 'MHTEST12345',
        'amount' => 149000,
        'content' => 'MHTEST12345 CHUYEN KHOAN GÓI HẠNH PHÚC',
        'gateway' => 'SePay',
    ], JSON_UNESCAPED_UNICODE);

    File::append($logPath, $sampleLog."\n");

    $response = $this->actingAs($this->admin)->get(route('admin.orders.webhooks'));

    $response->assertStatus(200);
    $response->assertSee('MHTEST12345');
    $response->assertSee('Thành công');
    $response->assertSee('149.000');
});

test('admin can export orders as utf-8 bom csv file with filters', function () {
    $this->order->update([
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.orders.export', ['status' => 'paid']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('bao-cao-don-hang-mayhen-');

    $content = $response->streamedContent();
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();
    expect($content)->toContain($this->order->order_code);
    expect($content)->toContain('Nguyễn Thị Hoa');
    expect($content)->toContain('149.000');
    expect($content)->toContain('Đã thanh toán');
});

test('customer cannot export admin orders csv', function () {
    $response = $this->actingAs($this->customer)->get(route('admin.orders.export'));
    $response->assertRedirect(route('admin.login'));
});
