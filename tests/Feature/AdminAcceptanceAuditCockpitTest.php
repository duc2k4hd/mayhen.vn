<?php

use App\Models\User;
use App\Services\AuditLogger;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->staff = User::factory()->create([
        'role' => 'staff',
    ]);
});

test('guest cannot access acceptance audit cockpit', function () {
    $response = $this->get(route('admin.system.acceptance.index'));
    $response->assertRedirect(route('admin.login'));
});

test('non-super admin cannot access acceptance audit cockpit', function () {
    $response = $this->actingAs($this->staff)->get(route('admin.system.acceptance.index'));
    $response->assertRedirect(route('admin.dashboard'));
});

test('super admin can access acceptance audit cockpit and see matrix score and checks', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('admin.system.acceptance.index'));

    $response->assertOk();
    $response->assertSee('Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện');
    $response->assertSee('Điểm Tuân Thủ Toàn Diện');
    $response->assertSee('AUTH-01');
    $response->assertSee('TMP-01');
    $response->assertSee('PAY-01');
    $response->assertSee('SHARE-01');
    $response->assertSee('OPS-01');
});

test('super admin can trigger on demand audit run with audit logger entry', function () {
    $response = $this->actingAs($this->superAdmin)
        ->post(route('admin.system.acceptance.run'));

    $response->assertRedirect(route('admin.system.acceptance.index'));
    $response->assertSessionHas('success');

    $logs = AuditLogger::getRecentLogs();
    $found = false;
    foreach ($logs as $log) {
        if ($log['action'] === 'SYSTEM_ACCEPTANCE_AUDIT_RUN') {
            $found = true;
            break;
        }
    }
    expect($found)->toBeTrue();
});

test('super admin can export acceptance matrix report as utf8 bom csv', function () {
    $response = $this->actingAs($this->superAdmin)
        ->get(route('admin.system.acceptance.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $content = $response->getContent();

    expect($content)->toStartWith("\xEF\xBB\xBF")
        ->toContain('BÁO CÁO KIỂM ĐỊNH & NGHIỆM THU HỆ THỐNG MÂY HẸN')
        ->toContain('Mã Tiêu Chí,Nhóm Nghiệp Vụ')
        ->toContain('AUTH-01')
        ->toContain('TMP-01')
        ->toContain('PAY-01');
});
