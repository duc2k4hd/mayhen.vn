<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use Carbon\Carbon;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('partner registration creates partner profile and provides dashboard access', function () {
    $resRegister = $this->post(route('partner.register.store'), [
        'business_name' => 'Miracle Wedding Studio',
        'phone' => '0988776655',
        'email' => 'contact@miraclestudio.vn',
        'address' => '123 Lê Lợi, Quận 1, TP.HCM',
        'website' => 'https://miraclestudio.vn',
        'partner_type' => 'wedding_studio',
    ]);

    $resRegister->assertRedirect();

    $partner = Partner::where('email', 'contact@miraclestudio.vn')->first();
    expect($partner)->not->toBeNull()
        ->and($partner->business_name)->toBe('Miracle Wedding Studio')
        ->and($partner->tier)->toBe('silver')
        ->and($partner->status)->toBe('active');

    $partnerUser = $partner->user;
    expect($partnerUser->role)->toBe('partner');

    // Truy cập dashboard đối tác thành công
    $resDashboard = $this->actingAs($partnerUser)->get(route('partner.dashboard'));
    $resDashboard->assertStatus(200);
});

test('studio handover code lifecycle generates, regenerates, and revokes securely', function () {
    $partnerUser = User::factory()->create(['role' => 'partner']);
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Lumiere Atelier',
        'slug' => 'lumiere-atelier',
        'partner_code' => 'MH-LUMIERE-'.rand(100, 999),
        'phone' => '0909000111',
        'email' => 'info@lumiere.vn',
        'status' => 'active',
    ]);

    $template = InvitationTemplate::where('is_active', true)->first();

    // 1. Studio tạo đám cưới cho khách
    $resCreate = $this->actingAs($partnerUser)->post(route('partner.weddings.store'), [
        'template_id' => $template->id,
        'groom_name' => 'Thế Vinh',
        'bride_name' => 'Thảo Linh',
        'wedding_date' => '2026-12-15',
        'venue_name' => 'Park Hyatt Saigon',
        'client_notes' => 'Khách hàng gói Full Service',
    ]);
    $resCreate->assertRedirect();

    $wedding = Wedding::where('partner_id', $partner->id)->latest()->first();
    expect($wedding)->not->toBeNull()
        ->and($wedding->handover_code)->toHaveLength(6)
        ->and($wedding->isHandoverCodeExpired())->toBeFalse();

    $originalCode = $wedding->handover_code;

    // 2. Studio tái tạo mã bàn giao mới (Regenerate)
    $resRegen = $this->actingAs($partnerUser)->post(route('partner.weddings.regenerate', $wedding->id));
    $resRegen->assertSessionHas('success');

    $wedding->refresh();
    expect($wedding->handover_code)->not->toBe($originalCode)
        ->and($wedding->handover_code)->toHaveLength(6);

    // 3. Studio thu hồi mã bàn giao (Revoke)
    $resRevoke = $this->actingAs($partnerUser)->post(route('partner.weddings.revoke', $wedding->id));
    $resRevoke->assertSessionHas('success');

    $wedding->refresh();
    expect($wedding->handover_code)->toBeNull();
});

test('studio multi tenant isolation prevents other studios from accessing or altering weddings', function () {
    $partnerUserA = User::factory()->create(['role' => 'partner']);
    $partnerA = Partner::create([
        'user_id' => $partnerUserA->id,
        'business_name' => 'Studio A',
        'slug' => 'studio-a',
        'partner_code' => 'MH-STU-A',
        'phone' => '0901111111',
        'email' => 'a@studio.vn',
        'status' => 'active',
    ]);

    $partnerUserB = User::factory()->create(['role' => 'partner']);
    $partnerB = Partner::create([
        'user_id' => $partnerUserB->id,
        'business_name' => 'Studio B',
        'slug' => 'studio-b',
        'partner_code' => 'MH-STU-B',
        'phone' => '0902222222',
        'email' => 'b@studio.vn',
        'status' => 'active',
    ]);

    $weddingA = Wedding::create([
        'user_id' => $partnerUserA->id,
        'partner_id' => $partnerA->id,
        'groom_name' => 'Đức Minh',
        'bride_name' => 'Thanh Hà',
        'wedding_date' => '2026-12-20',
    ]);

    // Studio B cố tình truy cập màn hình bàn giao của Studio A -> 404
    $resAccess = $this->actingAs($partnerUserB)->get(route('partner.weddings.handover', $weddingA->id));
    $resAccess->assertStatus(404);

    // Studio B cố tình đổi mã bàn giao của Studio A -> 404
    $resRegen = $this->actingAs($partnerUserB)->post(route('partner.weddings.regenerate', $weddingA->id));
    $resRegen->assertStatus(404);
});

test('customer handover claim transfers ownership, records history, and invalidates code', function () {
    $partnerUser = User::factory()->create(['role' => 'partner']);
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Atelier Rose',
        'slug' => 'atelier-rose',
        'partner_code' => 'MH-ROSE-01',
        'phone' => '0903333333',
        'email' => 'info@atelierrose.vn',
        'status' => 'active',
    ]);

    $customer = User::factory()->create(['role' => 'customer', 'name' => 'Khách Hàng Cặp Đôi']);
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $partnerUser->id,
        'partner_id' => $partner->id,
        'groom_name' => 'Quốc Huy',
        'bride_name' => 'Mỹ Uyên',
        'wedding_date' => '2026-11-20',
    ]);
    $code = $wedding->generateHandoverCode(30);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $partnerUser->id,
        'slug' => 'thiep-quoc-huy-my-uyen-'.uniqid(),
    ]);

    // 1. Nhập mã sai -> Báo lỗi
    $resWrongCode = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'WRONG9',
    ]);
    $resWrongCode->assertSessionHas('error');

    // 2. Nhập mã hết hạn -> Báo lỗi quá thời hạn 30 ngày
    $wedding->update([
        'extra_data' => array_merge($wedding->extra_data ?? [], [
            'handover_expires_at' => Carbon::now()->subDay()->toIso8601String(),
        ]),
    ]);
    $resExpired = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => $code,
    ]);
    $resExpired->assertSessionHas('error');

    // 3. Gia hạn mã hợp lệ và claim thành công
    $wedding->update([
        'extra_data' => array_merge($wedding->extra_data ?? [], [
            'handover_expires_at' => Carbon::now()->addDays(20)->toIso8601String(),
        ]),
    ]);

    $resClaimSuccess = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => $code,
    ]);
    $resClaimSuccess->assertSessionHas('success');

    $wedding->refresh();
    expect($wedding->user_id)->toBe($customer->id)
        ->and($wedding->handover_code)->toBeNull()
        ->and($wedding->extra_data['handover_claimed_at'])->not->toBeNull()
        ->and($wedding->extra_data['handover_history'])->not->toBeEmpty();

    // 4. Nhập lại mã cũ vừa dùng -> Báo lỗi (không cho claim lần 2)
    $resClaimAgain = $this->actingAs($customer)->post(route('account.claim_partner_invitation'), [
        'handover_code' => $code,
    ]);
    $resClaimAgain->assertSessionHas('error');
});

test('vanity subdomain and custom domain validation protects reserved keywords and formats', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Minh Triết',
        'bride_name' => 'Hải Yến',
        'wedding_date' => '2026-12-19',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-minh-triet-hai-yen-'.uniqid(),
    ]);

    // 1. Thử đăng ký subdomain nằm trong danh sách cấm / bảo lưu (admin, api, pay...) -> 422
    $resBlacklist = $this->actingAs($user)->post(route('account.invitations.domain.subdomain', $invitation->id), [
        'subdomain' => 'admin',
    ]);
    $resBlacklist->assertSessionHasErrors(['subdomain']);

    // 2. Đăng ký subdomain hợp lệ -> Thành công
    $validSubdomain = 'triet-yen-wedding-'.rand(100, 999);
    $resValidSubdomain = $this->actingAs($user)->post(route('account.invitations.domain.subdomain', $invitation->id), [
        'subdomain' => $validSubdomain,
    ]);
    $resValidSubdomain->assertSessionHas('status');

    $domain = WeddingDomain::where('invitation_id', $invitation->id)->first();
    expect($domain)->not->toBeNull()
        ->and($domain->subdomain)->toBe($validSubdomain)
        ->and($domain->status)->toBe('active');

    // 3. Thử đăng ký custom domain có đuôi mayhen.vn (bị cấm tại mục custom domain) -> 422
    $resSystemDomain = $this->actingAs($user)->post(route('account.invitations.domain.custom', $invitation->id), [
        'custom_domain' => 'damcuoi.mayhen.vn',
    ]);
    $resSystemDomain->assertSessionHasErrors(['custom_domain']);

    // 4. Đăng ký custom domain riêng độc lập (ví dụ: trietyen.wedding) -> Thành công
    $resCustomValid = $this->actingAs($user)->post(route('account.invitations.domain.custom', $invitation->id), [
        'custom_domain' => 'trietyen.wedding',
    ]);
    $resCustomValid->assertSessionHas('status');

    $domain->refresh();
    expect($domain->custom_domain)->toBe('trietyen.wedding')
        ->and($domain->status)->toBe('pending');
});

test('live projection wall supports pin verification and handles locked status gracefully', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Văn Hậu',
        'bride_name' => 'Hải My',
        'wedding_date' => '2026-11-26',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-van-hau-hai-my-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'projection_wall' => [
                'pin' => '8888',
            ],
        ],
    ]);

    // 1. Khách xem trang trình chiếu khi có mã PIN -> hiển thị form nhập PIN
    $resPinForm = $this->get(route('live_wall.show', $invitation->slug));
    $resPinForm->assertStatus(200);

    // 2. Nhập sai PIN -> Báo lỗi
    $resWrongPin = $this->post(route('live_wall.verify_pin', $invitation->slug), [
        'pin' => '1234',
    ]);
    $resWrongPin->assertSessionHasErrors(['pin']);

    // 3. Nhập đúng PIN -> Mở khóa và redirect về trang trình chiếu
    $resCorrectPin = $this->post(route('live_wall.verify_pin', $invitation->slug), [
        'pin' => '8888',
    ]);
    $resCorrectPin->assertRedirect(route('live_wall.show', $invitation->slug));

    // 4. Khi thiệp bị Admin khóa -> verifyPin trả view locked không gây TypeError
    $invitation->update([
        'custom_config' => [
            'is_locked' => true,
            'lock_reason' => 'Khóa theo lệnh kiểm duyệt',
        ],
    ]);

    $resLockedVerify = $this->post(route('live_wall.verify_pin', $invitation->slug), [
        'pin' => '8888',
    ]);
    $resLockedVerify->assertStatus(200);
});
