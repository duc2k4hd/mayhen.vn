<?php

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\IncidentResponseAndEscalationService;
use Carbon\Carbon;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'email' => 'admin_incident_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'email' => 'manager_incident_test@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_incident_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_incident_test@mayhen.vn',
        'role' => 'customer',
    ]);
});

test('sla deadline calculation prioritizes vip concierge 48h and urgent tickets', function () {
    // 1. Khách hàng thông thường, priority = medium -> SLA 12h
    $normalTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $this->customer->id,
        'subject' => 'Hỏi về cách đổi font chữ',
        'body' => 'Font chữ có đổi được sang serif không?',
        'category' => 'invitation',
        'priority' => 'medium',
        'status' => 'open',
    ]);

    $normalDeadline = IncidentResponseAndEscalationService::calculateSlaDeadline($normalTicket);
    expect($normalTicket->created_at->diffInHours($normalDeadline))->toEqual(12);

    // 2. Khách hàng có đám cưới trong vòng 24h tới -> VIP Concierge 48h -> SLA 60 phút
    Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Nam',
        'bride_name' => 'Chi',
        'wedding_date' => Carbon::tomorrow()->format('Y-m-d'),
    ]);

    $vipTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $this->customer->id,
        'subject' => 'Cần hỗ trợ gấp ngày mai cưới',
        'body' => 'Quên mã PIN điểm danh sảnh tiệc',
        'category' => 'technical',
        'priority' => 'low', // Dù gắn low nhưng cưới trong 48h vẫn ưu tiên khẩn
        'status' => 'open',
    ]);

    $vipDeadline = IncidentResponseAndEscalationService::calculateSlaDeadline($vipTicket);
    expect($vipTicket->created_at->diffInMinutes($vipDeadline))->toEqual(60);
});

test('auto escalation engine escalates breached tickets and logs audit trail', function () {
    $breachedTicket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $this->customer->id,
        'subject' => 'Lỗi không tải được ảnh cưới',
        'body' => 'Tải ảnh lên bị báo lỗi mạng',
        'category' => 'technical',
        'priority' => 'high',
        'status' => 'open',
        'sla_deadline_at' => now()->subMinutes(30), // Đã trễ hạn 30 phút
    ]);

    expect($breachedTicket->isEscalated())->toBeFalse();

    $escalated = IncidentResponseAndEscalationService::checkAndEscalateTickets();

    expect($escalated->pluck('id'))->toContain($breachedTicket->id);

    $breachedTicket->refresh();
    expect($breachedTicket->isEscalated())->toBeTrue();
    expect($breachedTicket->escalation_level)->toBe('manager');
    expect($breachedTicket->escalation_reason)->toContain('Tự động leo thang: Quá hạn cam kết phản hồi SLA');

    // Kiểm tra Audit Log
    $recentLogs = AuditLogger::getRecentLogs(20, 'ticket_auto_escalated');
    $log = collect($recentLogs)->firstWhere('target_id', (string) $breachedTicket->id);
    expect($log)->not->toBeNull();
});

test('manager can declare operational incident while staff is forbidden', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $this->customer->id,
        'subject' => 'Thiệp báo lỗi trắng màn hình tại tiệc cưới',
        'body' => 'Khách đến sảnh tiệc quét QR thiệp bị trắng xóa',
        'category' => 'technical',
        'priority' => 'high',
        'status' => 'open',
    ]);

    // 1. Staff bị chặn khi cố khai báo sự cố (phải có quyền Manager trở lên)
    $staffResponse = $this->actingAs($this->staff)
        ->post(route('admin.support.declare_incident', $ticket), [
            'severity' => 'sev1',
            'reason' => 'Sự cố trắng màn hình diện rộng tại tiệc cưới',
        ]);
    $staffResponse->assertRedirect();
    $staffResponse->assertSessionHas('error');

    $staffJsonResponse = $this->actingAs($this->staff)
        ->postJson(route('admin.support.declare_incident', $ticket), [
            'severity' => 'sev1',
            'reason' => 'Sự cố trắng màn hình diện rộng tại tiệc cưới',
        ]);
    $staffJsonResponse->assertStatus(403);

    // 2. Manager khai báo sự cố SEV-1 hợp lệ
    $managerResponse = $this->actingAs($this->manager)
        ->post(route('admin.support.declare_incident', $ticket), [
            'severity' => 'sev1',
            'reason' => 'Sự cố trắng màn hình diện rộng tại tiệc cưới',
        ]);

    $managerResponse->assertRedirect();
    $managerResponse->assertSessionHas('success');

    $ticket->refresh();
    expect($ticket->is_incident)->toBeTrue();
    expect($ticket->incident_severity)->toBe('sev1');
    expect($ticket->priority)->toBe('urgent');
    expect($ticket->escalation_level)->toBe('super_admin');
});

test('incident cannot be resolved without mandatory postmortem investigation', function () {
    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $this->customer->id,
        'subject' => 'Lỗi cổng thanh toán VietQR không ghi nhận đơn',
        'body' => 'Khách đã quét QR chuyển tiền nhưng thiệp không kích hoạt',
        'category' => 'billing',
        'priority' => 'urgent',
        'status' => 'in_progress',
        'is_incident' => true,
        'incident_severity' => 'sev1',
        'incident_declared_at' => now(),
    ]);

    // 1. Cố đóng sự cố trực tiếp qua updateStatus mà chưa có Postmortem -> BỊ CHẶN LỖI
    $directResolveResponse = $this->actingAs($this->manager)
        ->post(route('admin.support.update_status', $ticket), [
            'status' => 'resolved',
        ]);

    $directResolveResponse->assertRedirect();
    $directResolveResponse->assertSessionHas('error');

    $ticket->refresh();
    expect($ticket->status)->toBe('in_progress'); // Vẫn không được đóng

    // 2. Ký duyệt đóng sự cố với đầy đủ 3 mục Postmortem (Root Cause, Mitigation, Prevention)
    $resolveResponse = $this->actingAs($this->manager)
        ->post(route('admin.support.resolve_incident', $ticket), [
            'root_cause' => 'Webhook đối tác SePay bị nghẽn mạng cục bộ trong 15 phút',
            'mitigation_action' => 'Đã kích hoạt cơ chế đối soát sao kê ngân hàng bù đắp tự động và kích hoạt thiệp ngay',
            'postmortem_notes' => 'Bổ sung Worker dự phòng độc lập và tự động thử lại khi Webhook mất kết nối',
        ]);

    $resolveResponse->assertRedirect();
    $resolveResponse->assertSessionHas('success');

    $ticket->refresh();
    expect($ticket->status)->toBe('resolved');
    expect($ticket->root_cause)->toContain('Webhook đối tác SePay');
    expect($ticket->mitigation_action)->toContain('cơ chế đối soát');
    expect($ticket->postmortem_notes)->toContain('Worker dự phòng');
});

test('manager can run sla breach scanner route', function () {
    $response = $this->actingAs($this->manager)
        ->post(route('admin.support.scan_sla'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
