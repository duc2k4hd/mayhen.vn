<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGift;
use App\Models\WeddingTimeCapsule;
use App\Models\WeddingWish;
use App\Services\PersonalThankYouService;
use Carbon\Carbon;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('gift management crud and financial statistics calculation', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Gia Bảo',
        'bride_name' => 'Thảo My',
        'wedding_date' => '2026-11-25',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-gia-bao-thao-my-'.uniqid(),
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Nguyễn Văn Nam',
        'guest_token' => 'token-nam-'.uniqid(),
    ]);

    // 1. Thêm khoản mừng cưới tiền mặt
    $resStore1 = $this->actingAs($owner)->post(route('account.gifts.store', $invitation->id), [
        'sender_name' => 'Nguyễn Văn Nam',
        'sender_group' => 'Bạn Đại Học',
        'amount' => 1000000,
        'payment_method' => 'cash',
        'guest_id' => $guest->id,
        'notes' => 'Chúc hai bạn trăm năm hạnh phúc!',
    ]);
    $resStore1->assertSessionHas('success');

    // 2. Thêm khoản mừng cưới chuyển khoản VietQR
    $resStore2 = $this->actingAs($owner)->post(route('account.gifts.store', $invitation->id), [
        'sender_name' => 'Trần Thị Lan',
        'sender_group' => 'Đồng Nghiệp',
        'amount' => 2000000,
        'payment_method' => 'bank_transfer',
        'notes' => 'Mừng cưới Lan chuyển khoản',
    ]);
    $resStore2->assertSessionHas('success');

    expect($wedding->gifts()->count())->toBe(2);

    // 3. Xem danh sách và kiểm tra tính toán tài chính
    $resIndex = $this->actingAs($owner)->get(route('account.gifts.index', $invitation->id));
    $resIndex->assertStatus(200);

    $viewData = $resIndex->original->getData();
    expect($viewData['totalAmount'])->toBe(3000000)
        ->and($viewData['cashAmount'])->toBe(1000000)
        ->and($viewData['bankAmount'])->toBe(2000000)
        ->and($viewData['avgAmount'])->toBe(1500000)
        ->and($viewData['totalGiftsCount'])->toBe(2)
        ->and($viewData['acknowledgedCount'])->toBe(0);

    // 4. Đánh dấu đã gửi lời cảm ơn (Toggle Acknowledge)
    $gift1 = $wedding->gifts()->where('sender_name', 'Nguyễn Văn Nam')->first();
    $resAck = $this->actingAs($owner)->post(route('account.gifts.toggle_ack', [$invitation->id, $gift1->id]));
    $resAck->assertSessionHas('success');

    $gift1->refresh();
    expect($gift1->is_acknowledged)->toBeTrue();

    // 5. Cập nhật khoản mừng cưới
    $resUpdate = $this->actingAs($owner)->put(route('account.gifts.update', [$invitation->id, $gift1->id]), [
        'sender_name' => 'Nguyễn Văn Nam (Đã cập nhật)',
        'sender_group' => 'Bạn Thân',
        'amount' => 1500000,
        'payment_method' => 'cash',
        'notes' => 'Thêm 500k mừng riêng',
        'is_acknowledged' => true,
    ]);
    $resUpdate->assertSessionHas('success');

    $gift1->refresh();
    expect($gift1->sender_name)->toBe('Nguyễn Văn Nam (Đã cập nhật)')
        ->and($gift1->amount)->toBe(1500000)
        ->and($gift1->sender_group)->toBe('Bạn Thân');

    // 6. Xóa khoản mừng cưới khỏi sổ
    $resDestroy = $this->actingAs($owner)->delete(route('account.gifts.destroy', [$invitation->id, $gift1->id]));
    $resDestroy->assertSessionHas('success');

    expect($wedding->gifts()->count())->toBe(1);
});

test('gift management tenant isolation prevents intruders from tampering with gift book', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Hải Đăng',
        'bride_name' => 'Thùy Dung',
        'wedding_date' => '2026-12-05',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-hai-dang-thuy-dung-'.uniqid(),
    ]);

    $gift = WeddingGift::create([
        'wedding_id' => $wedding->id,
        'sender_name' => 'Bác Hai',
        'amount' => 5000000,
        'payment_method' => 'cash',
    ]);

    // 1. Kẻ lạ truy cập sổ mừng -> 404
    $this->actingAs($intruder)->get(route('account.gifts.index', $invitation->id))->assertStatus(404);

    // 2. Kẻ lạ cố tình ghi thêm quà mừng -> 404
    $this->actingAs($intruder)->post(route('account.gifts.store', $invitation->id), [
        'sender_name' => 'Hacker',
        'amount' => 1000,
        'payment_method' => 'cash',
    ])->assertStatus(404);

    // 3. Kẻ lạ cố tình xóa quà mừng -> 404
    $this->actingAs($intruder)->delete(route('account.gifts.destroy', [$invitation->id, $gift->id]))->assertStatus(404);

    // 4. Chủ thiệp tạo thiệp vãng lai qua session (chưa đăng nhập tài khoản) vẫn được quản lý
    $sessionInvitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'thiep-session-gift-'.uniqid(),
    ]);

    $resSessionOwner = $this->withSession(['owned_invitation_slugs' => [$sessionInvitation->slug]])
        ->get(route('account.gifts.index', $sessionInvitation->id));
    $resSessionOwner->assertStatus(200);
});

test('gift csv export includes utf8 bom and formula sanitization', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Hữu Phước',
        'bride_name' => 'Thanh Trúc',
        'wedding_date' => '2026-12-12',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-huu-phuoc-thanh-truc-'.uniqid(),
    ]);

    WeddingGift::create([
        'wedding_id' => $wedding->id,
        'sender_name' => '+EXCEL|formula!injection',
        'sender_group' => 'Họ Hàng',
        'amount' => 2000000,
        'payment_method' => 'bank_transfer',
        'notes' => '=SUM(A1:A10)',
    ]);

    $response = $this->actingAs($owner)->get(route('account.gifts.export', $invitation->id));
    $response->assertStatus(200);

    $content = $response->streamedContent();

    // 1. Phải có UTF-8 BOM
    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue();

    // 2. Các ký tự công thức +, = phải được khử độc với dấu nháy đơn '
    expect($content)->toContain("'+EXCEL")
        ->and($content)->toContain("'=SUM");
});

test('time capsule strictly conceals sealed content and prevents pre-mature unsealing', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Đức Thịnh',
        'bride_name' => 'Ái Vân',
        'wedding_date' => '2026-12-25',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-duc-thinh-ai-van-'.uniqid(),
        'is_published' => true,
    ]);

    // Tạo bức thư gửi tương lai mở vào 1 năm sau ngày cưới (2027)
    $capsule = WeddingTimeCapsule::create([
        'wedding_id' => $wedding->id,
        'invitation_id' => $invitation->id,
        'sender_type' => 'groom',
        'sender_name' => 'Đức Thịnh',
        'recipient_name' => 'Ái Vân',
        'title' => 'Gửi người vợ yêu quý tròn 1 năm ngày cưới',
        'content' => 'Bí mật tuyệt đối: Anh đã chuẩn bị chuyến đi du lịch Paris cho em.',
        'milestone_years' => 1,
        'unlock_date' => Carbon::parse('2027-12-25'),
        'is_sealed' => true,
        'seal_color' => 'ruby',
    ]);

    // 1. Xem công khai: content BẮT BUỘC phải là null vì đang niêm phong sáp
    $resPublic = $this->get(route('time_capsule.public_slug', $invitation->slug));
    $resPublic->assertStatus(200);

    $publicCapsules = $resPublic->original->getData()['capsules'];
    expect($publicCapsules[0]['content'])->toBeNull()
        ->and($publicCapsules[0]['is_sealed'])->toBeTrue()
        ->and($publicCapsules[0]['can_be_unlocked'])->toBeFalse();

    // 2. Chủ sở hữu cố tình mở niêm phong trước thời hạn -> bị từ chối 422 JSON hoặc báo lỗi
    $resUnsealEarly = $this->actingAs($owner)->postJson(route('account.time_capsule.unseal', [$invitation->id, $capsule->id]));
    $resUnsealEarly->assertStatus(422)
        ->assertJson(['success' => false]);

    $capsule->refresh();
    expect($capsule->is_sealed)->toBeTrue();

    // 3. Khi thời gian đã tới hạn mở khóa (giả lập unlock_date trong quá khứ)
    $capsule->update(['unlock_date' => Carbon::now()->subDay()]);
    $resUnsealSuccess = $this->actingAs($owner)->postJson(route('account.time_capsule.unseal', [$invitation->id, $capsule->id]));
    $resUnsealSuccess->assertStatus(200)
        ->assertJson([
            'success' => true,
            'content' => 'Bí mật tuyệt đối: Anh đã chuẩn bị chuyến đi du lịch Paris cho em.',
        ]);

    $capsule->refresh();
    expect($capsule->is_sealed)->toBeFalse()
        ->and($capsule->unlocked_at)->not->toBeNull();
});

test('memory vault keepsake book renders chronological approved wishes', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Bảo Trọng',
        'bride_name' => 'Ngọc Diễm',
        'wedding_date' => '2026-11-01',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-bao-trong-ngoc-diem-'.uniqid(),
    ]);

    // Tạo lời chúc: 1 đã duyệt, 1 chưa duyệt
    WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Bạn Thân Tuấn',
        'message' => 'Chúc mừng hạnh phúc hai bạn!',
        'is_approved' => true,
    ]);

    WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Spammer',
        'message' => 'Nội dung quảng cáo cá cược',
        'is_approved' => false,
    ]);

    $response = $this->actingAs($owner)->get(route('memory.keepsake_book', $invitation->id));
    $response->assertStatus(200);

    $wishes = $response->original->getData()['wishes'];
    expect($wishes->count())->toBe(1)
        ->and($wishes->first()->name)->toBe('Bạn Thân Tuấn');
});

test('personal thank you builder synthesizes context matching attending guests and gifts', function () {
    $owner = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Tấn Phát',
        'bride_name' => 'Mỹ Duyên',
        'wedding_date' => '2026-11-15',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-tan-phat-my-duyen-'.uniqid(),
    ]);

    $guest = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Nguyễn Thị Hoa',
        'salutation' => 'Cô',
        'group_name' => 'Họ Hàng Nhà Gái',
        'guest_token' => 'token-hoa-'.uniqid(),
    ]);

    // Khách có đi tiệc và có gửi quà mừng
    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => 'Nguyễn Thị Hoa',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ]);

    WeddingGift::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $guest->id,
        'sender_name' => 'Nguyễn Thị Hoa',
        'amount' => 2000000,
        'payment_method' => 'cash',
    ]);

    $service = app(PersonalThankYouService::class);
    $plan = $service->buildThankYouPlan($wedding, $invitation);

    expect($plan)->toBeArray()
        ->and($plan)->toHaveKey('messages')
        ->and($plan)->toHaveKey('stats')
        ->and($plan['stats']['total_guests'])->toBeGreaterThanOrEqual(1)
        ->and($plan['stats']['attended_count'])->toBe(1);

    $guestMessage = collect($plan['messages'])->firstWhere('guest_name', 'Nguyễn Thị Hoa');
    expect($guestMessage)->not->toBeNull()
        ->and($guestMessage['suggested_message'])->toContain('Cô')
        ->and($guestMessage['category'])->toBe('attended');
});
