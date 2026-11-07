<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeCapsule;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Security Matrix',
        'slug' => 'mau-sec-matrix-'.uniqid(),
        'description' => 'Security Test Template',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.hac-bach-trao-duyen.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->weddingA = Wedding::create([
        'user_id' => $this->userA->id,
        'groom_name' => 'Quốc Cường',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => now()->addDays(20),
    ]);

    $this->invitationA = Invitation::create([
        'wedding_id' => $this->weddingA->id,
        'template_id' => $this->template->id,
        'slug' => 'cuong-thao-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'checkin_pin' => '123456',
        ],
    ]);

    $this->guestA = Guest::create([
        'wedding_id' => $this->weddingA->id,
        'name' => 'Khách Của User A',
        'expected_attendees' => 2,
    ]);
});

test('user B cannot view or modify user A guests (Multi-tenant IDOR protection)', function () {
    $this->actingAs($this->userB);

    // 1. User B cố tình truy cập trang khách mời của User A -> Trả về 404 (chống dò IDOR)
    $response = $this->get(route('guests.index', $this->invitationA->id));
    $response->assertNotFound();

    // 2. User B cố tình sửa khách mời của User A -> Trả về 404 (chống dò IDOR)
    $updateResponse = $this->put(route('guests.update', $this->guestA->id), [
        'name' => 'Hack Name',
        'expected_attendees' => 5,
    ]);
    $updateResponse->assertNotFound();

    // 3. User B cố tình xóa khách mời của User A -> Trả về 404 (chống dò IDOR)
    $deleteResponse = $this->delete(route('guests.destroy', $this->guestA->id));
    $deleteResponse->assertNotFound();

    // Dữ liệu User A vẫn giữ nguyên
    $this->guestA->refresh();
    expect($this->guestA->name)->toBe('Khách Của User A');
});

test('receptionist PIN portal blocks unauthorized access', function () {
    // Chưa xác thực PIN -> Hiển thị form mã PIN tiếp tân
    $response = $this->get(route('checkin.portal', $this->invitationA->slug));
    $response->assertOk();
    $response->assertSee('Mở Cổng Tiếp Tân');

    // Nhập sai PIN -> Bị từ chối
    $verifyFail = $this->post(route('checkin.verifyPin', $this->invitationA->slug), [
        'pin' => '000000',
    ]);
    $verifyFail->assertSessionHas('error');

    // Nhập đúng PIN -> Mở khóa thành công
    $verifySuccess = $this->post(route('checkin.verifyPin', $this->invitationA->slug), [
        'pin' => '123456',
    ]);
    $verifySuccess->assertRedirect(route('checkin.portal', $this->invitationA->slug));
});

test('sealed time capsule content is strictly hidden from public api before unlock', function () {
    $capsule = WeddingTimeCapsule::create([
        'wedding_id' => $this->weddingA->id,
        'invitation_id' => $this->invitationA->id,
        'sender_type' => 'couple',
        'sender_name' => 'Quốc Cường',
        'recipient_name' => 'Thu Thảo',
        'title' => 'Bí Mật Tương Lai',
        'content' => 'NỘI DUNG TUYỆT MẬT KHÔNG ĐƯỢC LỘ',
        'milestone_years' => 5,
        'unlock_date' => now()->addYears(5),
        'is_sealed' => true,
        'seal_color' => 'ruby',
    ]);

    // Khách vãng lai xem trang Hộp Thời Gian
    $response = $this->get(route('time_capsule.public', $this->invitationA->slug));
    $response->assertOk();
    $response->assertSee('Bí Mật Tương Lai');
    // Nội dung tuyệt mật phải không bao giờ xuất hiện trong mã HTML
    $response->assertDontSee('NỘI DUNG TUYỆT MẬT KHÔNG ĐƯỢC LỘ');
});

test('partner handover code can only be claimed once and atomically transfers ownership', function () {
    $partnerUser = User::factory()->create();
    $partner = Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'Studio Hoàng Gia',
        'slug' => 'studio-hoang-gia-'.uniqid(),
        'partner_code' => 'STU-'.uniqid(),
        'phone' => '0912345678',
        'email' => 'studio@example.com',
        'status' => 'active',
    ]);

    $wedding = Wedding::create([
        'user_id' => $partnerUser->id,
        'partner_id' => $partner->id,
        'handover_code' => 'HG9999',
        'groom_name' => 'Hoàng Phúc',
        'bride_name' => 'Bảo Trân',
        'wedding_date' => now()->addDays(60),
    ]);

    // Khách hàng User A claim thiệp bằng mã
    $this->actingAs($this->userA);
    $claimResponse = $this->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'HG9999',
    ]);

    $claimResponse->assertRedirect(route('account.invitations'));
    $claimResponse->assertSessionHas('success');

    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->userA->id);
    expect($wedding->handover_code)->toBeNull();

    // Người thứ hai (User B) cố claim lại mã đó -> Thất bại
    $this->actingAs($this->userB);
    $secondClaim = $this->post(route('account.claim_partner_invitation'), [
        'handover_code' => 'HG9999',
    ]);

    $secondClaim->assertSessionHas('error');
    // Quyền sở hữu vẫn thuộc User A
    $wedding->refresh();
    expect($wedding->user_id)->toBe($this->userA->id);
});
