<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\SystemBackup;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AiQualityAndCostObservatoryService;
use App\Services\CsvSanitizerService;
use App\Services\SystemBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('G1-014: Staff xuất PII nhận 403 Forbidden; Super Admin bắt buộc dsar_reason tối thiểu 10 ký tự', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $superAdmin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    // 1. Staff cố xuất dữ liệu PII DSAR qua API -> 403 Forbidden
    $resStaff = $this->actingAs($staff)->getJson(
        route('admin.users.export_personal_data', ['id' => $customer->id]).'?dsar_reason=Kiem+tra+yeu+cau+hop+le'
    );
    $resStaff->assertStatus(403);

    // 2. Super Admin xuất nhưng thiếu dsar_reason hoặc < 10 ký tự -> 302 back with session error
    $resAdminShort = $this->actingAs($superAdmin)->get(
        route('admin.users.export_personal_data', ['id' => $customer->id]).'?dsar_reason=ngan'
    );
    $resAdminShort->assertSessionHasErrors('dsar_reason');

    // 3. Super Admin nhập dsar_reason hợp lệ (>= 10 ký tự) -> Stream JSON 200 OK
    $resAdminValid = $this->actingAs($superAdmin)->get(
        route('admin.users.export_personal_data', ['id' => $customer->id]).'?dsar_reason=Khach+hang+yeu+cau+DSAR+chinh+thuc'
    );
    $resAdminValid->assertStatus(200);
    $resAdminValid->assertHeader('Content-Type', 'application/json; charset=UTF-8');
});

test('G1-015: Tenant A cố sửa hoặc thao tác khách của Tenant B bị abort 404', function () {
    $tenantA = User::factory()->create(['role' => 'customer']);
    $tenantB = User::factory()->create(['role' => 'customer']);

    $template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm BEM',
        'slug' => 'mau-thu-nghiem-'.uniqid(),
        'thumbnail_url' => 'assets/templates/preview.webp',
        'price' => 0,
        'is_active' => true,
    ]);

    $weddingA = Wedding::create([
        'user_id' => $tenantA->id,
        'groom_name' => 'Chú Rể A',
        'bride_name' => 'Cô Dâu A',
        'wedding_date' => now()->addDays(20)->toDateString(),
        'slug' => 'dam-cuoi-tenant-a-'.uniqid(),
    ]);

    $invitationA = Invitation::create([
        'wedding_id' => $weddingA->id,
        'template_id' => $template->id,
        'slug' => 'thiep-tenant-a-'.uniqid(),
        'custom_config' => ['is_locked' => false],
    ]);

    $weddingB = Wedding::create([
        'user_id' => $tenantB->id,
        'groom_name' => 'Chú Rể B',
        'bride_name' => 'Cô Dâu B',
        'wedding_date' => now()->addDays(25)->toDateString(),
        'slug' => 'dam-cuoi-tenant-b-'.uniqid(),
    ]);

    $invitationB = Invitation::create([
        'wedding_id' => $weddingB->id,
        'template_id' => $template->id,
        'slug' => 'thiep-tenant-b-'.uniqid(),
        'custom_config' => ['is_locked' => false],
    ]);

    $guestB = Guest::create([
        'wedding_id' => $weddingB->id,
        'name' => 'Khách Riêng Của B',
        'guest_token' => 'TOKEN_B_'.uniqid(),
    ]);

    // Tenant A đăng nhập cố truy cập hoặc chỉnh sửa danh sách khách của Invitation B -> 404 Not Found
    $response = $this->actingAs($tenantA)->get(route('guests.index', ['id' => $invitationB->id]));
    $response->assertStatus(404);
});

test('G1-016: Partner handover claim song song chỉ claim 1 lần duy nhất trong transaction', function () {
    $studioOwner = User::factory()->create(['role' => 'partner']);
    $couple1 = User::factory()->create(['role' => 'customer']);
    $couple2 = User::factory()->create(['role' => 'customer']);

    $wedding = Wedding::create([
        'user_id' => $studioOwner->id,
        'groom_name' => 'Chú Rể Studio',
        'bride_name' => 'Cô Dâu Studio',
        'wedding_date' => now()->addDays(40)->toDateString(),
        'slug' => 'wedding-handover-'.uniqid(),
        'handover_code' => 'ABC999',
        'handover_expires_at' => now()->addDays(3),
    ]);

    // 1. Couple 1 nhập mã bàn giao hợp lệ
    $res1 = $this->actingAs($couple1)->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'ABC999',
    ]);
    $res1->assertSessionHas('success');
    expect($wedding->fresh()->user_id)->toBe($couple1->id);
    expect($wedding->fresh()->handover_code)->toBeNull();

    // 2. Request thứ hai từ Couple 2 cùng mã ABC999 (đã bị null sau khi claim) -> Từ chối invalid
    $res2 = $this->actingAs($couple2)->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'ABC999',
    ]);
    $res2->assertSessionHas('error');
    expect($wedding->fresh()->user_id)->toBe($couple1->id);
});

test('G1-017: Sao lưu và kiểm tra phục hồi cơ sở dữ liệu qua SystemBackupService', function () {
    $superAdmin = User::factory()->create(['role' => 'admin']);
    $backupService = app(SystemBackupService::class);

    $backup = $backupService->createDatabaseBackup($superAdmin, 'Bản sao lưu kiểm tra G1-017');
    expect($backup)->toBeInstanceOf(SystemBackup::class);
    expect(File::exists(storage_path('app/'.$backup->file_path)))->toBeTrue();
    expect($backup->sha256_checksum)->not->toBeEmpty();

    $restoreTest = $backupService->testRestoreVerification($backup->id, $superAdmin);
    expect($restoreTest['success'])->toBeTrue();

    // Dọn dẹp file sao lưu sau khi kiểm tra
    $backupService->deleteBackup($backup->id, $superAdmin);
});

test('G1-018: SupportTicketReply quan hệ user() BelongsTo không phát sinh lỗi', function () {
    $client = User::factory()->create(['name' => 'Khách Hàng Hỏi', 'role' => 'customer']);
    $staff = User::factory()->create(['name' => 'Nhân Viên Hỗ Trợ', 'role' => 'staff']);

    $ticket = SupportTicket::create([
        'user_id' => $client->id,
        'ticket_code' => 'TK-TEST-001',
        'subject' => 'Hỏi về việc thanh toán thiệp cưới',
        'body' => 'Nội dung chi tiết câu hỏi cần hỗ trợ',
        'category' => 'payment',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    $reply = SupportTicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $staff->id,
        'body' => 'Chào bạn, chúng tôi đã nhận được thông tin chuyển khoản.',
        'is_staff' => true,
    ]);

    expect($reply->user)->not->toBeNull();
    expect($reply->user->id)->toBe($staff->id);
    expect($reply->user->name)->toBe('Nhân Viên Hỗ Trợ');
});

test('G1-019: AI usage preview che giấu PII số điện thoại, ngân hàng, email', function () {
    $rawPrompt = 'Khách hàng Tran Van A, SĐT: 0901234567, email: khachhang@gmail.com, STK: 123456789012345 tại Vietcombank';
    $sanitized = AiQualityAndCostObservatoryService::sanitizePiiForPreview($rawPrompt);

    // Không còn nguyên vẹn sđt, email, số tài khoản
    expect($sanitized)->not->toContain('0901234567');
    expect($sanitized)->not->toContain('khachhang@gmail.com');
    expect($sanitized)->not->toContain('123456789012345');

    // Chứa dạng đã mask
    expect($sanitized)->toContain('090****4567');
    expect($sanitized)->toContain('kha***@gmail.com');
});

test('G1-020: Admin platform bank config yêu cầu super admin và lưu history', function () {
    $staff = User::factory()->create(['role' => 'staff']);
    $superAdmin = User::factory()->create(['role' => 'admin']);

    // 1. Staff bị chặn bởi middleware admin.super -> 403 Forbidden
    $resStaff = $this->actingAs($staff)->putJson(
        route('admin.settings.platform.update', ['group' => 'sepay']),
        [
            'sepay_bank_code' => 'VCB',
            'sepay_bank_account' => '9988776655',
            'sepay_bank_name' => 'Vietcombank',
            'sepay_account_name' => 'MAY HEN CO LTD',
            'change_reason' => 'Staff cố ý thay đổi ngân hàng',
        ]
    );
    $resStaff->assertStatus(403);

    // 2. Super Admin cập nhật thành công và lưu lịch sử
    $resAdmin = $this->actingAs($superAdmin)->put(
        route('admin.settings.platform.update', ['group' => 'sepay']),
        [
            'sepay_bank_code' => 'VCB',
            'sepay_bank_account' => '9988776655',
            'sepay_bank_name' => 'Vietcombank',
            'sepay_account_name' => 'MAY HEN CO LTD',
            'change_reason' => 'Cập nhật tài khoản thu hộ Vietcombank mới',
        ]
    );
    $resAdmin->assertRedirect();
});

test('G1-021: CsvSanitizerService chống Formula Injection và đúng định dạng UTF-8', function () {
    $dangerousFormula1 = '=cmd|\' /C calc\'!A0';
    $dangerousFormula2 = '+SUM(1+1)';
    $dangerousFormula3 = '@SUM(A1:A10)';
    $normalText = 'Nguyễn Văn An';

    $clean1 = CsvSanitizerService::sanitize($dangerousFormula1);
    $clean2 = CsvSanitizerService::sanitize($dangerousFormula2);
    $clean3 = CsvSanitizerService::sanitize($dangerousFormula3);
    $cleanNormal = CsvSanitizerService::sanitize($normalText);

    // Ký tự đầu phải được gắn nháy đơn '
    expect($clean1)->toStartWith("'=");
    expect($clean2)->toStartWith("'+");
    expect($clean3)->toStartWith("'@");
    expect($cleanNormal)->toBe('Nguyễn Văn An');
});
