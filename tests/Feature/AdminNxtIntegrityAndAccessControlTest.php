<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\OrderReconciliation;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNxtIntegrityAndAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $manager;
    protected User $staff;
    protected User $customer;
    protected Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin_nxt@mayhen.vn',
            'role' => 'admin',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Operations Manager',
            'email' => 'manager_nxt@mayhen.vn',
            'role' => 'manager',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Reception Staff',
            'email' => 'staff_nxt@mayhen.vn',
            'role' => 'staff',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer Couple',
            'email' => 'customer_nxt@mayhen.vn',
            'role' => 'customer',
            'phone' => '0912345678',
        ]);

        $this->plan = Plan::create([
            'name' => 'Gói Hạnh Phúc Plus',
            'slug' => 'goi-hanh-phuc-nxt',
            'price' => 199000,
            'duration_days' => 180,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // NXT-01: Finance Evidence Ledger & Dual Approval Tests
    // =========================================================================

    public function test_manual_approve_requires_reference_code_and_note(): void
    {
        $order = Order::create([
            'order_code' => 'MH_NXT_001',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'amount' => 199000,
            'status' => 'pending',
            'payment_method' => 'vietqr',
        ]);

        // Gửi không có reference_code và note
        $response = $this->actingAs($this->superAdmin)->post(route('admin.orders.approve', $order->id), []);
        $response->assertSessionHasErrors(['reference_code', 'reconciliation_note']);

        // Gửi note quá ngắn (< 6 ký tự)
        $responseShort = $this->actingAs($this->superAdmin)->post(route('admin.orders.approve', $order->id), [
            'reference_code' => 'FT1234',
            'reconciliation_note' => 'ngan',
        ]);
        $responseShort->assertSessionHasErrors(['reconciliation_note']);
    }

    public function test_manual_approve_prevents_duplicate_replay_reference_code(): void
    {
        // 1. Đơn cũ đã thanh toán thành công với mã GD FT999999
        Order::create([
            'order_code' => 'MH_PAID_PREVIOUS',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'amount' => 199000,
            'status' => 'paid',
            'payment_method' => 'vietqr',
            'provider_transaction_id' => 'FT999999',
        ]);

        // 2. Đơn mới cố tình dùng lại mã FT999999
        $orderNew = Order::create([
            'order_code' => 'MH_NXT_REPLAY',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'amount' => 199000,
            'status' => 'pending',
            'payment_method' => 'vietqr',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.orders.approve', $orderNew->id), [
            'reference_code' => 'FT999999',
            'reconciliation_note' => 'Thử đối soát mã cũ',
        ]);

        $response->assertSessionHas('error');
        expect($orderNew->fresh()->status)->toBe('pending');
    }

    public function test_manager_approval_creates_pending_dual_approval_ledger(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể A',
            'bride_name' => 'Cô Dâu B',
            'wedding_date' => Carbon::today()->addDays(30),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Mẫu Thử Nghiệm',
            'slug' => 'mau-thu-nghiem-nxt',
            'thumbnail_url' => '/assets/thumb.jpg',
            'is_active' => true,
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'thiep-cuoi-nxt-maker',
            'is_published' => false,
            'expires_at' => null,
        ]);

        $order = Order::create([
            'order_code' => 'MH_MAKER_CHECKER',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $invitation->id,
            'amount' => 199000,
            'status' => 'pending',
            'payment_method' => 'vietqr',
        ]);

        // Manager phê duyệt (Đóng vai trò Maker - Yêu cầu Dual Approval)
        $response = $this->actingAs($this->manager)->post(route('admin.orders.approve', $order->id), [
            'reference_code' => 'FT_MAKER_2026',
            'reconciliation_note' => 'Đã kiểm tra sao kê VietinBank khớp 199.000đ',
            'bank_name' => 'VietinBank',
            'dual_approval' => true,
        ]);

        $response->assertSessionHas('success');

        // Bất biến: Đơn hàng chưa chuyển paid, thiệp chưa được kích hoạt khi chưa có Checker duyệt!
        $order->refresh();
        expect($order->status)->toBe('pending');
        expect($order->reconciliation_status)->toBe('pending_approval');
        expect($invitation->fresh()->is_published)->toBeFalse();

        // Kiểm tra ledger được tạo với status pending
        $recon = OrderReconciliation::where('order_id', $order->id)->first();
        expect($recon)->not->toBeNull();
        expect($recon->creator_id)->toBe($this->manager->id);
        expect($recon->approver_id)->toBeNull();
        expect($recon->status)->toBe('pending');
        expect($recon->reference_code)->toBe('FT_MAKER_2026');
    }

    public function test_super_admin_can_approve_dual_approval_ledger_and_activate(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể X',
            'bride_name' => 'Cô Dâu Y',
            'wedding_date' => Carbon::today()->addDays(30),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Mẫu Thử Nghiệm 2',
            'slug' => 'mau-thu-nghiem-nxt2',
            'thumbnail_url' => '/assets/thumb2.jpg',
            'is_active' => true,
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'thiep-cuoi-nxt-checker',
            'is_published' => false,
            'expires_at' => null,
        ]);

        $order = Order::create([
            'order_code' => 'MH_CHECKER_APPROVE',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $invitation->id,
            'amount' => 199000,
            'status' => 'pending',
            'payment_method' => 'vietqr',
            'reconciliation_status' => 'pending_approval',
        ]);

        $recon = OrderReconciliation::create([
            'order_id' => $order->id,
            'creator_id' => $this->manager->id,
            'approver_id' => null,
            'action_type' => 'manual_approval',
            'status' => 'pending',
            'amount' => 199000,
            'reference_code' => 'FT_CHECKER_OK',
            'reconciliation_note' => 'Quản lý đề xuất',
        ]);

        // Super Admin duyệt (Checker)
        $response = $this->actingAs($this->superAdmin)->post(route('admin.orders.reconciliations.approve', $recon->id), [
            'review_note' => 'Đã đối chiếu sao kê tài khoản ngân hàng chính thức',
        ]);

        $response->assertSessionHas('success');

        // Bất biến: Đơn chuyển paid, thiệp được kích hoạt và gia hạn, ledger approved
        $order->refresh();
        expect($order->isPaid())->toBeTrue();
        expect($order->provider_transaction_id)->toBe('FT_CHECKER_OK');
        expect($order->reconciliation_status)->toBe('approved');

        $invitation->refresh();
        expect($invitation->is_published)->toBeTrue();
        expect($invitation->expires_at)->not->toBeNull();

        $recon->refresh();
        expect($recon->status)->toBe('approved');
        expect($recon->approver_id)->toBe($this->superAdmin->id);
    }

    public function test_request_refund_creates_ledger_and_keeps_order_safe(): void
    {
        $order = Order::create([
            'order_code' => 'MH_REFUND_REQ',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'amount' => 199000,
            'status' => 'paid',
            'paid_at' => now()->subDay(),
            'payment_method' => 'vietqr',
        ]);

        $response = $this->actingAs($this->manager)->post(route('admin.orders.request_refund', $order->id), [
            'refund_amount' => 199000,
            'customer_bank_name' => 'Vietcombank',
            'customer_bank_account' => '0071001234567',
            'customer_bank_holder' => 'NGUYEN VAN A',
            'reason' => 'Khách hàng dời lịch cưới sang năm sau',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        expect($order->isRefundPending())->toBeTrue();
        expect($order->status)->toBe('paid'); // Chưa đổi sang refunded khi tiền chưa chi trả thật!

        $recon = OrderReconciliation::where('order_id', $order->id)
            ->where('action_type', 'refund_request')
            ->first();
        expect($recon)->not->toBeNull();
        expect($recon->bank_account_number)->toBe('0071001234567');
        expect($recon->bank_name)->toBe('Vietcombank');
    }

    public function test_confirm_refund_payout_with_real_bank_reference_revokes_invitation(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'wedding_date' => Carbon::today()->addDays(10),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Mẫu Test',
            'slug' => 'mau-test-ref',
            'thumbnail_url' => '/assets/thumb_ref.jpg',
            'is_active' => true,
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'thiep-tuan-ha-ref',
            'is_published' => true,
            'expires_at' => now()->addDays(90),
        ]);

        $order = Order::create([
            'order_code' => 'MH_PAYOUT_DONE',
            'user_id' => $this->customer->id,
            'plan_id' => $this->plan->id,
            'invitation_id' => $invitation->id,
            'amount' => 199000,
            'status' => 'paid',
            'reconciliation_status' => 'refund_pending',
            'refund_amount' => 199000,
            'payment_method' => 'vietqr',
        ]);

        // Xác nhận đã chuyển khoản hoàn tiền
        $response = $this->actingAs($this->superAdmin)->post(route('admin.orders.confirm_refund', $order->id), [
            'payout_reference_code' => 'PAYOUT_BANK_REF_9999',
            'payout_note' => 'Đã chuyển khoản qua app ngân hàng thành công',
        ]);

        $response->assertSessionHas('success');

        $order->refresh();
        expect($order->status)->toBe('refunded');
        expect($order->reconciliation_status)->toBe('refund_completed');
        expect($order->refunded_at)->not->toBeNull();

        // Thiệp cưới bị hết hạn lưu trữ trực tuyến
        $invitation->refresh();
        expect($invitation->expires_at->isPast() || $invitation->expires_at->isToday())->toBeTrue();

        $payoutRecon = OrderReconciliation::where('order_id', $order->id)
            ->where('action_type', 'refund_payout')
            ->first();
        expect($payoutRecon)->not->toBeNull();
        expect($payoutRecon->reference_code)->toBe('PAYOUT_BANK_REF_9999');
        expect($payoutRecon->status)->toBe('completed');
    }

    // =========================================================================
    // NXT-02: Fine-Grained Access Policy Center & Data Protection (DSAR)
    // =========================================================================

    public function test_staff_and_manager_cannot_export_personal_data(): void
    {
        // 1. Staff bị chặn (403 khi gọi Json, redirect kèm error khi gọi thông thường)
        $responseStaffJson = $this->actingAs($this->staff)->getJson(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'Yêu cầu trích xuất hồ sơ kiểm toán hợp lệ',
        ]));
        $responseStaffJson->assertStatus(403);

        $responseStaff = $this->actingAs($this->staff)->get(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'Yêu cầu trích xuất hồ sơ kiểm toán hợp lệ',
        ]));
        $responseStaff->assertRedirect(route('admin.dashboard'));
        $responseStaff->assertSessionHas('error');

        // 2. Manager cũng bị chặn (chỉ Super Admin mới được xuất DSAR PII)
        $responseManagerJson = $this->actingAs($this->manager)->getJson(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'Yêu cầu trích xuất hồ sơ kiểm toán hợp lệ',
        ]));
        $responseManagerJson->assertStatus(403);
    }

    public function test_super_admin_export_dsar_requires_reason(): void
    {
        // Gọi không có lý do -> Bị chặn validation 302
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
        ]));
        $response->assertSessionHasErrors(['dsar_reason']);

        // Lý do dưới 10 ký tự -> Bị chặn
        $responseShort = $this->actingAs($this->superAdmin)->get(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'ngan',
        ]));
        $responseShort->assertSessionHasErrors(['dsar_reason']);
    }

    public function test_super_admin_export_dsar_masks_pii_by_default(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể DSAR',
            'bride_name' => 'Cô Dâu DSAR',
            'bank_name' => 'Techcombank',
            'bank_account_number' => '19036789123456',
            'wedding_date' => Carbon::today(),
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Mời DSAR',
            'phone' => '0988776655',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'Yêu cầu kiểm toán định kỳ theo Luật 91/2025/QH15',
        ]));

        $response->assertOk();
        $data = json_decode($response->streamedContent(), true);

        // Data Minimization Check: Phone & Bank number must be masked with ****
        expect($data['personal_profile']['phone'])->toContain('****');
        expect($data['weddings'][0]['bank_info']['bank_account_number'])->toContain('****');
        expect($data['weddings'][0]['guests'][0]['phone'])->toContain('****');
        expect($data['dsar_metadata']['is_pii_masked'])->toBeTrue();

        // Audit log check
        $logs = AuditLogger::getRecentLogs(10, 'export_personal_data_dsar');
        expect($logs)->not->toBeEmpty();
        expect($logs[0]['target_id'])->toBe((string) $this->customer->id);
    }

    // =========================================================================
    // NXT-03: Wedding-Day Consistency Transaction Engine
    // =========================================================================

    public function test_checkin_is_idempotent_with_action_param(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể C',
            'bride_name' => 'Cô Dâu D',
            'wedding_date' => Carbon::today(),
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách VIP Idempotent',
            'expected_attendees' => 2,
            'checked_in_at' => null,
        ]);

        // Lần 1: Check-in thành công
        $res1 = $this->actingAs($this->staff)->postJson(route('admin.guests.toggle_checkin', $guest->id), [
            'action' => 'check_in',
        ]);
        $res1->assertOk();
        $res1->assertJson(['success' => true, 'is_checked_in' => true, 'status_changed' => true]);
        expect($guest->fresh()->isCheckedIn())->toBeTrue();

        // Lần 2: Double-click hoặc retry cùng action 'check_in' -> Không được lật ngược trạng thái về hủy!
        $res2 = $this->actingAs($this->staff)->postJson(route('admin.guests.toggle_checkin', $guest->id), [
            'action' => 'check_in',
        ]);
        $res2->assertOk();
        $res2->assertJson(['success' => true, 'is_checked_in' => true, 'status_changed' => false]);
        expect($guest->fresh()->isCheckedIn())->toBeTrue();
    }

    public function test_checkin_undo_is_idempotent(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể E',
            'bride_name' => 'Cô Dâu F',
            'wedding_date' => Carbon::today(),
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Undo Test',
            'checked_in_at' => now(),
            'checked_in_attendees' => 1,
        ]);

        // Lần 1: Undo check-in
        $res1 = $this->actingAs($this->staff)->postJson(route('admin.guests.toggle_checkin', $guest->id), [
            'action' => 'undo_check_in',
        ]);
        $res1->assertOk();
        $res1->assertJson(['success' => true, 'is_checked_in' => false, 'status_changed' => true]);
        expect($guest->fresh()->isCheckedIn())->toBeFalse();

        // Lần 2: Retry undo -> Giữ nguyên chưa check-in
        $res2 = $this->actingAs($this->staff)->postJson(route('admin.guests.toggle_checkin', $guest->id), [
            'action' => 'undo_check_in',
        ]);
        $res2->assertOk();
        $res2->assertJson(['success' => true, 'is_checked_in' => false, 'status_changed' => false]);
        expect($guest->fresh()->isCheckedIn())->toBeFalse();
    }

    public function test_quick_assign_table_blocks_overcapacity_without_flag(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể G',
            'bride_name' => 'Cô Dâu H',
            'wedding_date' => Carbon::today(),
        ]);

        // Bàn chỉ có 2 ghế
        $table = WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bàn 01 (VIP Nhỏ)',
            'capacity' => 2,
        ]);

        // Khách đi 3 người
        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Gia Đình Khách Mời 3 Người',
            'expected_attendees' => 3,
        ]);

        // Thử xếp vào bàn không có cờ allow_overflow -> Bị chặn 422
        $response = $this->actingAs($this->staff)->postJson(route('admin.guests.assign_table', $guest->id), [
            'table_id' => $table->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error_code' => 'OVER_CAPACITY',
            'capacity' => 2,
        ]);

        expect($guest->fresh()->table_id)->toBeNull();
    }

    public function test_quick_assign_table_allows_overflow_with_flag_and_reason(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể G',
            'bride_name' => 'Cô Dâu H',
            'wedding_date' => Carbon::today(),
        ]);

        $table = WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bàn 02 (VIP)',
            'capacity' => 2,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Kê Ghế',
            'expected_attendees' => 3,
        ]);

        // Gửi kèm allow_overflow và lý do
        $response = $this->actingAs($this->staff)->postJson(route('admin.guests.assign_table', $guest->id), [
            'table_id' => $table->id,
            'allow_overflow' => true,
            'overflow_reason' => 'Đã thêm 1 ghế phụ theo yêu cầu gia đình',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        expect($guest->fresh()->table_id)->toBe($table->id);

        $logs = AuditLogger::getRecentLogs(10, 'rescue_assign_table');
        expect($logs[0]['details']['is_over_capacity'])->toBeTrue();
    }

    public function test_create_emergency_table_handles_duplicate_naming_safely(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Chú Rể I',
            'bride_name' => 'Cô Dâu K',
            'wedding_date' => Carbon::today(),
        ]);

        // Tạo bàn đầu tiên với tên 'Bàn Dự Phòng A'
        $this->actingAs($this->staff)->post(route('admin.guests.emergency_table', $wedding->id), [
            'name' => 'Bàn Dự Phòng A',
            'capacity' => 10,
        ]);

        // Thao tác đồng thời tạo tiếp bàn cùng tên 'Bàn Dự Phòng A'
        $this->actingAs($this->staff)->post(route('admin.guests.emergency_table', $wedding->id), [
            'name' => 'Bàn Dự Phòng A',
            'capacity' => 10,
        ]);

        $tables = WeddingTable::where('wedding_id', $wedding->id)->orderBy('id')->get();
        expect($tables)->toHaveCount(2);
        expect($tables[0]->name)->toBe('Bàn Dự Phòng A');
        expect($tables[1]->name)->toBe('Bàn Dự Phòng A (1)');
        expect($tables[0]->display_order)->not->toBe($tables[1]->display_order);
    }
}
