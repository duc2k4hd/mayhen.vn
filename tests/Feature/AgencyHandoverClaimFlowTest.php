<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->partnerUser = User::factory()->create(['role' => 'partner']);
    $this->partner = Partner::create([
        'user_id' => $this->partnerUser->id,
        'business_name' => 'Élysee Wedding Planner',
        'slug' => 'elysee-wedding-planner',
        'partner_code' => 'MH-ELYSEE-01',
        'phone' => '0912345678',
        'email' => 'partner@elysee.vn',
        'tier' => 'gold',
        'status' => 'active',
    ]);

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first() ?? InvitationTemplate::create([
        'name' => 'Giữa Muôn Ngàn Vì Sao',
        'slug' => 'giua-muon-ngan-vi-sao',
        'description' => 'Mẫu thiệp thử nghiệm',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.giua-muon-ngan-vi-sao.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->customer = User::factory()->create(['role' => 'customer', 'name' => 'Nguyễn Văn A']);
});

test('studio khởi tạo thiệp cưới tự động sinh mã bàn giao 6 ký tự và hạn sử dụng 30 ngày', function () {
    $response = $this->actingAs($this->partnerUser)->post(route('partner.weddings.store'), [
        'template_id' => $this->template->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Phương Thảo',
        'wedding_date' => now()->addDays(90)->format('Y-m-d'),
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận',
        'client_notes' => 'Gói thiết kế cao cấp cho VIP',
    ]);

    $response->assertRedirect();

    $wedding = Wedding::where('partner_id', $this->partner->id)->latest()->first();
    expect($wedding)->not->toBeNull();
    expect($wedding->handover_code)->toHaveLength(6);
    expect($wedding->isHandoverCodeExpired())->toBeFalse();

    $expiresAt = $wedding->getHandoverExpiresAt();
    expect($expiresAt)->not->toBeNull();
    expect($expiresAt->isFuture())->toBeTrue();
    // Hạn khoảng 30 ngày (29 đến 30 ngày)
    expect(now()->diffInDays($expiresAt))->toBeGreaterThanOrEqual(29);
});

test('cặp đôi claim thành công chuyển giao quyền sở hữu, xóa mã bàn giao và đồng bộ session', function () {
    // 1. Studio tạo đám cưới
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Phương Thảo',
        'wedding_date' => now()->addDays(60),
    ]);
    $code = $wedding->generateHandoverCode(30);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'gia-huy-phuong-thao-handover',
        'is_published' => false,
    ]);

    // 2. Cặp đôi claim mã bàn giao
    $response = $this->actingAs($this->customer)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), [
            'handover_code' => strtolower($code), // Case-insensitive test
        ]);

    $response->assertRedirect(route('account.invitations'))
        ->assertSessionHas('success');

    // 3. Kiểm tra DB
    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->customer->id);
    expect($wedding->handover_code)->toBeNull();
    expect($wedding->extra_data['handover_claimed_at'])->not->toBeNull();

    $history = $wedding->extra_data['handover_history'];
    expect($history)->toBeArray();
    expect($history[0]['handed_from_user_id'])->toBe($this->partnerUser->id);
    expect($history[0]['claimed_by_user_id'])->toBe($this->customer->id);

    // 4. Kiểm tra session sở hữu thiệp
    $sessionSlugs = session('owned_invitation_slugs', []);
    expect($sessionSlugs)->toContain('gia-huy-phuong-thao-handover');
});

test('chặn replay attack khi mã bàn giao đã được sử dụng một lần', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Quốc Anh',
        'bride_name' => 'Nhã Uyên',
        'wedding_date' => now()->addDays(45),
    ]);
    $code = $wedding->generateHandoverCode(30);

    // Người dùng thứ nhất claim thành công
    $this->actingAs($this->customer)
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $code]);

    // Người dùng thứ hai cố tình claim lại mã cũ (Replay Attack)
    $secondUser = User::factory()->create(['role' => 'customer']);
    $response = $this->actingAs($secondUser)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $code]);

    $response->assertRedirect(route('account.invitations'))
        ->assertSessionHas('error', 'Mã bàn giao không chính xác hoặc đã được sử dụng. Vui lòng liên hệ Studio của bạn để kiểm tra.');

    // Chủ sở hữu đám cưới vẫn là customer thứ nhất
    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->customer->id);
});

test('chặn mã bàn giao đã hết hạn hiệu lực quá 30 ngày', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Thanh Phong',
        'bride_name' => 'Ngọc Diệp',
        'wedding_date' => now()->addDays(50),
    ]);
    $code = $wedding->generateHandoverCode(30);

    // Giả lập thời gian đã trôi qua 35 ngày (mã hết hạn)
    $extra = $wedding->extra_data;
    $extra['handover_expires_at'] = Carbon::now('Asia/Ho_Chi_Minh')->subDays(5)->toIso8601String();
    $wedding->update(['extra_data' => $extra]);

    expect($wedding->isHandoverCodeExpired())->toBeTrue();

    // Khách claim mã hết hạn
    $response = $this->actingAs($this->customer)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $code]);

    $response->assertRedirect(route('account.invitations'))
        ->assertSessionHas('error', 'Mã bàn giao này đã hết hạn hiệu lực (quá thời hạn 30 ngày). Vui lòng liên hệ Studio đối tác để được cấp mã mới.');

    // Thiệp vẫn thuộc về partner, chưa bị claim
    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->partnerUser->id);
    expect($wedding->handover_code)->toBe($code);
});

test('studio có thể đổi mã bàn giao mới và gia hạn thêm 30 ngày', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Minh Triết',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => now()->addDays(70),
    ]);
    $oldCode = $wedding->generateHandoverCode(30);

    $response = $this->actingAs($this->partnerUser)
        ->from(route('partner.weddings.handover', $wedding->id))
        ->post(route('partner.weddings.regenerate', $wedding->id));

    $response->assertRedirect(route('partner.weddings.handover', $wedding->id))
        ->assertSessionHas('success');

    $wedding->refresh();
    $newCode = $wedding->handover_code;
    expect($newCode)->not->toBe($oldCode);
    expect($newCode)->toHaveLength(6);
    expect($wedding->isHandoverCodeExpired())->toBeFalse();

    // Mã cũ không thể claim được nữa
    $responseOld = $this->actingAs($this->customer)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $oldCode]);

    $responseOld->assertSessionHas('error');

    // Mã mới claim thành công
    $responseNew = $this->actingAs($this->customer)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $newCode]);

    $responseNew->assertSessionHas('success');
    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->customer->id);
});

test('studio có thể thu hồi mã bàn giao để ngăn chặn claim', function () {
    $wedding = Wedding::create([
        'user_id' => $this->partnerUser->id,
        'partner_id' => $this->partner->id,
        'groom_name' => 'Đức Thịnh',
        'bride_name' => 'Mỹ Dung',
        'wedding_date' => now()->addDays(80),
    ]);
    $code = $wedding->generateHandoverCode(30);

    $response = $this->actingAs($this->partnerUser)
        ->from(route('partner.weddings.handover', $wedding->id))
        ->post(route('partner.weddings.revoke', $wedding->id));

    $response->assertRedirect(route('partner.weddings.handover', $wedding->id))
        ->assertSessionHas('success');

    $wedding->refresh();
    expect($wedding->handover_code)->toBeNull();
    expect($wedding->extra_data['handover_revoked_at'])->not->toBeNull();

    // Thử claim mã đã bị thu hồi
    $claimResponse = $this->actingAs($this->customer)
        ->from(route('account.invitations'))
        ->post(route('account.claim_partner_invitation'), ['handover_code' => $code]);

    $claimResponse->assertSessionHas('error', 'Mã bàn giao không chính xác hoặc đã được sử dụng. Vui lòng liên hệ Studio của bạn để kiểm tra.');
});

test('màn hình handover hiển thị trạng thái đã bàn giao khi khách đã nhận thiệp', function () {
    $wedding = Wedding::create([
        'user_id' => $this->customer->id, // Đã thuộc khách hàng
        'partner_id' => $this->partner->id,
        'groom_name' => 'Đức Thịnh',
        'bride_name' => 'Mỹ Dung',
        'wedding_date' => now()->addDays(80),
        'extra_data' => [
            'handover_claimed_at' => now()->toIso8601String(),
        ],
    ]);

    $response = $this->actingAs($this->partnerUser)
        ->get(route('partner.weddings.handover', $wedding->id));

    $response->assertStatus(200);
    $response->assertSee('Thiệp cưới đã được bàn giao thành công!');
    $response->assertDontSee('MÃ BÀN GIAO 6 KÝ TỰ');
});
