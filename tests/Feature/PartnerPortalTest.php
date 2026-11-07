<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first() ?? InvitationTemplate::create([
        'name' => 'Giữa Muôn Ngàn Vì Sao',
        'slug' => 'giua-muon-ngan-vi-sao',
        'description' => 'Test Template for Studio Partner',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.giua-muon-ngan-vi-sao.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->partnerUser = User::factory()->create([
        'role' => 'partner',
    ]);

    $this->partner = Partner::create([
        'user_id' => $this->partnerUser->id,
        'business_name' => 'Élysee Wedding Studio',
        'slug' => 'elysee-wedding-studio',
        'partner_code' => 'MH-PARTNER-ELYSEE',
        'phone' => '0912345678',
        'email' => 'contact@elysee.vn',
        'tier' => 'gold',
        'commission_rate' => 25.00,
        'wallet_balance' => 5000000.00,
        'status' => 'active',
        'custom_config' => [
            'branding' => [
                'footer_text' => 'Đồng hành cùng Élysee Wedding Studio',
                'show_badge' => true,
            ],
        ],
    ]);
});

test('người dùng có thể truy cập trang đăng ký đối tác studio', function () {
    $response = $this->get(route('partner.register'));
    $response->assertStatus(200);
    $response->assertSee('Gia Nhập Cổng Đối Tác');
    $response->assertSee('Chiết Khấu 20% - 30%');
});

test('người dùng đăng ký thành công trở thành đối tác studio', function () {
    $newUser = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($newUser)->post(route('partner.register.store'), [
        'business_name' => 'The Planners Vietnam',
        'phone' => '0988776655',
        'email' => 'planner@theplanners.vn',
        'address' => 'Tầng 5, Tòa Nhà Cưới, Hà Nội',
        'website' => 'https://theplanners.vn',
        'partner_type' => 'wedding_planner',
    ]);

    $response->assertRedirect(route('partner.dashboard'));
    $this->assertDatabaseHas('partners', [
        'business_name' => 'The Planners Vietnam',
        'email' => 'planner@theplanners.vn',
        'user_id' => $newUser->id,
        'status' => 'active',
    ]);

    expect($newUser->fresh()->role)->toBe('partner');
});

test('đối tác truy cập được bảng điều khiển trung tâm B2B', function () {
    $response = $this->actingAs($this->partnerUser)->get(route('partner.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Élysee Wedding Studio');
    $response->assertSee('MH-PARTNER-ELYSEE');
    $response->assertSee('5.000.000'); // Số dư
});

test('người dùng thường chưa phải đối tác bị điều hướng tới trang đăng ký', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $response = $this->actingAs($customer)->get(route('partner.dashboard'));
    $response->assertRedirect(route('partner.register'));
});

test('đối tác bị tạm khóa không được phép truy cập', function () {
    $this->partner->update(['status' => 'suspended']);

    $response = $this->actingAs($this->partnerUser)->get(route('partner.dashboard'));
    $response->assertStatus(403);
});

test('studio có thể khởi tạo đám cưới mới cho khách hàng', function () {
    $response = $this->actingAs($this->partnerUser)->post(route('partner.weddings.store'), [
        'template_id' => $this->template->id,
        'groom_name' => 'Anh Dũng',
        'bride_name' => 'Thu Hà',
        'wedding_date' => now()->addDays(45)->format('Y-m-d'),
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => 'Phú Nhuận, TP.HCM',
        'client_notes' => 'Khách hàng VIP gói chụp ảnh Paris',
    ]);

    $wedding = Wedding::where('groom_name', 'Anh Dũng')->first();
    expect($wedding)->not->toBeNull()
        ->and($wedding->partner_id)->toBe($this->partner->id)
        ->and($wedding->handover_code)->not->toBeNull()
        ->and(strlen($wedding->handover_code))->toBe(6)
        ->and($wedding->client_notes)->toBe('Khách hàng VIP gói chụp ảnh Paris');

    $invitation = $wedding->invitations()->first();
    expect($invitation)->not->toBeNull();

    $response->assertRedirect(route('invitation.create', ['edit' => $invitation->slug]));
});

test('studio có thể xem và làm mới mã bàn giao thiệp cưới', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'handover_code' => 'ABC123',
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Bích Phương',
        'wedding_date' => now()->addDays(15),
    ]);

    $response = $this->actingAs($this->partnerUser)->get(route('partner.weddings.handover', $wedding->id));
    $response->assertStatus(200);
    $response->assertSee('ABC123');
    $response->assertSee('Quốc Bảo &amp; Bích Phương', false);

    // Đổi mã bàn giao
    $regenerateResponse = $this->actingAs($this->partnerUser)->post(route('partner.weddings.regenerate', $wedding->id));
    $regenerateResponse->assertRedirect();

    $updatedWedding = $wedding->fresh();
    expect($updatedWedding->handover_code)->not->toBe('ABC123')
        ->and(strlen($updatedWedding->handover_code))->toBe(6);
});

test('cặp đôi có thể nhận quyền quản lý thiệp bằng mã bàn giao', function () {
    $coupleUser = User::factory()->create(['role' => 'customer']);

    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id, // Lúc đầu thuộc tài khoản Studio
        'partner_id' => $this->partner->id,
        'handover_code' => 'XYZ999',
        'groom_name' => 'Trọng Hiếu',
        'bride_name' => 'Mai Anh',
        'wedding_date' => now()->addDays(20),
    ]);

    $response = $this->actingAs($coupleUser)->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'XYZ999',
    ]);

    $response->assertRedirect(route('account.invitations'));

    $freshWedding = $wedding->fresh();
    // Đã chuyển sang cặp đôi
    expect($freshWedding->user_id)->toBe($coupleUser->id)
        // Mã bàn giao đã được xóa để chống claim lại
        ->and($freshWedding->handover_code)->toBeNull()
        // Nhưng partner_id vẫn giữ nguyên để ghi nhận công sức studio
        ->and($freshWedding->partner_id)->toBe($this->partner->id);
});

test('studio có thể cập nhật thông tin và thương hiệu co-branding', function () {
    $file = UploadedFile::fake()->image('studio_logo.png', 200, 80);

    $response = $this->actingAs($this->partnerUser)->post(route('partner.profile.update'), [
        'business_name' => 'Élysee Luxury Studio',
        'phone' => '0933221100',
        'email' => 'contact@elysee.vn',
        'footer_text' => 'Thực hiện bởi Élysee Luxury Studio',
        'show_badge' => '1',
        'logo' => $file,
    ]);

    $response->assertRedirect();
    $this->partner->refresh();

    expect($this->partner->business_name)->toBe('Élysee Luxury Studio')
        ->and($this->partner->logo_url)->not->toBeNull()
        ->and($this->partner->custom_config['branding']['footer_text'])->toBe('Thực hiện bởi Élysee Luxury Studio');
});

test('studio có thể theo dõi danh sách hoa hồng chiết khấu', function () {
    PartnerCommission::create([
        'partner_id' => $this->partner->id,
        'amount' => 500000.00,
        'type' => 'commission',
        'status' => 'approved',
        'description' => 'Hoa hồng nâng cấp Gói Hoàng Gia',
    ]);

    $response = $this->actingAs($this->partnerUser)->get(route('partner.commissions.index'));
    $response->assertStatus(200);
    $response->assertSee('500.000 ₫');
    $response->assertSee('Hoa Hồng Thiệp');
});

test('thiệp cưới công khai hiển thị dòng chữ bảo chứng của studio đối tác', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Văn Nam',
        'bride_name' => 'Thu Trang',
        'wedding_date' => now()->addDays(10),
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'van-nam-thu-trang-'.uniqid(),
        'is_published' => true,
    ]);

    $response = $this->get(route('invitation.show', $invitation->slug));
    $response->assertStatus(200);
    $response->assertSee('Đồng hành cùng Élysee Wedding Studio');
});
