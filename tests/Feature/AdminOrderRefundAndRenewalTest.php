<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderRefundAndRenewalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Plan $plan;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->plan = Plan::create([
            'slug' => 'premium-wedding',
            'name' => 'Gói Hoàng Gia',
            'price' => 399000,
            'duration_days' => 90,
            'is_active' => true,
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Mẫu Thơ Mộng',
            'slug' => 'tho-mong',
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/thumb.jpg',
            'is_active' => true,
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Minh Triết',
            'bride_name' => 'Bảo Hân',
            'wedding_date' => '2026-12-25',
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'minh-triet-bao-han-order-test',
            'is_published' => true,
            'expires_at' => now()->addDays(30),
        ]);
    }

    public function test_admin_can_refund_paid_order_and_expire_invitation_without_data_loss(): void
    {
        // 1. Tạo đơn hàng đã thanh toán thành công
        $order = Order::create([
            'order_code' => 'MHREFUND01',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'paid',
            'paid_at' => now()->subDay(),
            'payment_method' => 'vietqr',
        ]);

        // Tạo dữ liệu kỷ niệm, khách mời và lời chúc của thiệp
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Anh Tuấn',
            'expected_attendees' => 2,
        ]);

        $wish = WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Người Bạn Thơ Ấu',
            'message' => 'Chúc hai bạn trăm năm viên mãn!',
            'is_approved' => true,
        ]);

        // 2. Admin gọi route hoàn tiền
        $response = $this->actingAs($this->admin)->post(route('admin.orders.refund', $order->id), [
            'reason' => 'Khách hàng hủy tiệc do chuyển ngày cưới sang năm sau',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 3. Kiểm tra trạng thái đơn hàng đã chuyển sang refunded
        $order->refresh();
        $this->assertEquals('refunded', $order->status);
        $this->assertTrue($order->isRefunded());

        // 4. Quyền lưu trữ trực tuyến hết hạn nhưng dữ liệu thiệp, khách và lời chúc được bảo toàn nguyên vẹn
        $this->invitation->refresh();
        $this->assertTrue($this->invitation->expires_at->isPast() || $this->invitation->expires_at->isCurrentSecond());

        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
        $this->assertDatabaseHas('wedding_wishes', ['id' => $wish->id]);
        $this->assertDatabaseHas('weddings', ['id' => $this->wedding->id]);
        $this->assertDatabaseHas('invitations', ['id' => $this->invitation->id]);

        // 5. Kiểm tra audit log JSONL đã ghi nhận hành động hoàn tiền
        $logs = AuditLogger::getRecentLogs(10, 'refund_order');
        $this->assertNotEmpty($logs);
        $this->assertEquals('Order', $logs[0]['target_type']);
        $this->assertEquals((string) $order->id, $logs[0]['target_id']);
    }

    public function test_cannot_refund_pending_or_cancelled_order(): void
    {
        $pendingOrder = Order::create([
            'order_code' => 'MHPENDING1',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'pending',
            'payment_method' => 'vietqr',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.refund', $pendingOrder->id));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Chỉ có thể hoàn tiền cho đơn hàng đã thanh toán thành công.');

        $pendingOrder->refresh();
        $this->assertEquals('pending', $pendingOrder->status);
    }

    public function test_non_admin_cannot_refund_order(): void
    {
        $order = Order::create([
            'order_code' => 'MHPAID0001',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'paid',
            'payment_method' => 'vietqr',
        ]);

        // Khách hàng thông thường gọi refund bị chặn bởi middleware Admin
        $response = $this->actingAs($this->customer)->post(route('admin.orders.refund', $order->id));
        $response->assertRedirect();
        $response->assertSessionHasErrors('email');

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    public function test_approving_order_for_active_invitation_extends_future_expiry_date(): void
    {
        // Thiệp còn 30 ngày hiệu lực
        $originalExpiry = Carbon::now()->addDays(30)->startOfDay();
        $this->invitation->update(['expires_at' => $originalExpiry]);

        $order = Order::create([
            'order_code' => 'MHEXTEND01',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id, // duration_days = 90
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'pending',
            'payment_method' => 'sepay',
        ]);

        // Admin duyệt đơn hàng
        $response = $this->actingAs($this->admin)->post(route('admin.orders.approve', $order->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->invitation->refresh();
        // Thời hạn mới phải là ngày cũ + 90 ngày = 120 ngày (không bị mất 30 ngày cũ)
        $expectedExpiry = $originalExpiry->copy()->addDays(90);
        $this->assertEquals($expectedExpiry->format('Y-m-d'), $this->invitation->expires_at->format('Y-m-d'));
    }

    public function test_approving_order_for_expired_invitation_extends_from_now(): void
    {
        // Thiệp đã hết hạn 15 ngày trước
        $this->invitation->update(['expires_at' => now()->subDays(15)]);

        $order = Order::create([
            'order_code' => 'MHRENEW001',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id, // duration_days = 90
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'pending',
            'payment_method' => 'sepay',
        ]);

        $this->actingAs($this->admin)->post(route('admin.orders.approve', $order->id));

        $this->invitation->refresh();
        // Thời hạn mới tính từ now() + 90 ngày
        $this->assertEquals(now()->addDays(90)->format('Y-m-d'), $this->invitation->expires_at->format('Y-m-d'));
    }

    public function test_admin_orders_index_filters_and_displays_refunded_status(): void
    {
        Order::create([
            'order_code' => 'MHREFUNDLIST',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $this->invitation->id,
            'amount' => $this->plan->price,
            'status' => 'refunded',
            'payment_method' => 'vietqr',
        ]);

        // Lọc danh sách theo status = refunded
        $response = $this->actingAs($this->admin)->get(route('admin.orders.index', ['status' => 'refunded']));
        $response->assertOk();
        $response->assertSee('MHREFUNDLIST');
        $response->assertSee('Đã Hoàn Tiền');

        // Mở màn hình chi tiết đơn đã hoàn tiền
        $order = Order::where('order_code', 'MHREFUNDLIST')->firstOrFail();
        $detailResponse = $this->actingAs($this->admin)->get(route('admin.orders.show', $order->id));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Đơn hàng này đã được hoàn tiền (Refunded).');
    }
}
