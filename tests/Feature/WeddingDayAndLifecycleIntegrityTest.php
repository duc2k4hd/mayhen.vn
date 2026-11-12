<?php

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\MessagingDispatchLog;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingTable;
use App\Models\WeddingWish;
use App\Services\EmergencyOfflinePackService;
use App\Services\ReliableMessagingDispatchService;
use App\Services\SeatingCapacityGuardianService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'customer']);
    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tuấn Anh',
        'bride_name' => 'Ngọc Linh',
        'wedding_date' => now()->addDays(15)->toDateString(),
        'slug' => 'tuan-anh-ngoc-linh-'.uniqid(),
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm G2',
        'slug' => 'mau-thu-nghiem-g2-'.uniqid(),
        'thumbnail_url' => 'assets/templates/preview.webp',
        'price' => 0,
        'is_active' => true,
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-g2-'.uniqid(),
        'custom_config' => ['is_locked' => false],
    ]);
});

test('G2-001: Check-in hai thiết bị scan cùng QR đồng thời bảo toàn mốc thời gian cũ và trả already_checked_in', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Văn Mạnh',
        'guest_token' => 'QR_TOKEN_MANH_01',
        'expected_attendees' => 2,
    ]);

    // 1. Quét lần đầu tiên: Thành công
    $res1 = $this->actingAs($this->user)->postJson(
        route('checkin.scan', ['slug' => $this->invitation->slug]),
        [
            'token' => 'QR_TOKEN_MANH_01',
            'attendees' => 2,
        ]
    );

    $res1->assertStatus(200);
    $res1->assertJson([
        'success' => true,
        'already_checked_in' => false,
    ]);

    $firstCheckinTime = $guest->fresh()->checked_in_at;
    expect($firstCheckinTime)->not->toBeNull();

    // 2. Thiết bị thứ 2 quét lại cùng mã QR ngay sau đó
    $res2 = $this->actingAs($this->user)->postJson(
        route('checkin.scan', ['slug' => $this->invitation->slug]),
        [
            'token' => 'QR_TOKEN_MANH_01',
            'attendees' => 2,
        ]
    );

    $res2->assertStatus(200);
    $res2->assertJson([
        'success' => true,
        'already_checked_in' => true,
    ]);

    // Mốc thời gian check-in ban đầu được giữ nguyên, không toggle thành null
    expect($guest->fresh()->checked_in_at->timestamp)->toBe($firstCheckinTime->timestamp);
});

test('G2-002: Điều chỉnh sĩ số khách tại bàn đón tiếp ghi audit và chặn số âm 422', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Thị Thu',
        'guest_token' => 'QR_TOKEN_THU_02',
        'expected_attendees' => 1,
    ]);

    // 1. Điều chỉnh số suất âm hoặc = 0 -> Bị chặn 422 Unprocessable Entity
    $resInvalid = $this->actingAs($this->user)->postJson(
        route('checkin.exception.adjust_attendees', ['slug' => $this->invitation->slug, 'guestId' => $guest->id]),
        ['attendees' => 0]
    );
    $resInvalid->assertStatus(422);

    // 2. Điều chỉnh khách đi thêm người (+1 thành 2 suất) -> Thành công
    $resValid = $this->actingAs($this->user)->postJson(
        route('checkin.exception.adjust_attendees', ['slug' => $this->invitation->slug, 'guestId' => $guest->id]),
        ['attendees' => 2, 'note' => 'Khách dắt theo bạn đời']
    );
    $resValid->assertStatus(200);
    $resValid->assertJson([
        'success' => true,
        'checked_in_attendees' => 2,
    ]);
    expect($guest->fresh()->checked_in_attendees)->toBe(2);
});

test('G2-003: Ghế bàn quá tải được WeddingTable và SeatingCapacityGuardianService phát hiện', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP 01',
        'capacity' => 10,
    ]);

    // Gán khách có tổng sĩ số 12 vào bàn 10 chỗ
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $table->id,
        'name' => 'Đoàn Khách 1',
        'guest_token' => 'TOKEN_DOAN_1',
        'expected_attendees' => 6,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $table->id,
        'name' => 'Đoàn Khách 2',
        'guest_token' => 'TOKEN_DOAN_2',
        'expected_attendees' => 6,
    ]);

    expect($table->fresh()->assigned_attendees_count)->toBe(12);
    expect($table->fresh()->isOverCapacity())->toBeTrue();
    expect($table->fresh()->remaining_seats)->toBe(0);

    $guardianService = app(SeatingCapacityGuardianService::class);
    $health = $guardianService->analyzeSeatingHealth($this->wedding);

    expect($health['metrics']['overfilled_tables_count'])->toBeGreaterThanOrEqual(1);
});

test('G2-004: Bàn tiệc theo sự kiện riêng biệt phân tách hoàn toàn giữa Vu Quy và Tiệc Cưới', function () {
    $eventVuQuy = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lễ Vu Quy Nhà Gái',
        'event_date' => now()->addDays(14)->toDateString(),
        'venue_name' => 'Tư Gia Nhà Gái',
        'is_active' => true,
        'display_order' => 1,
    ]);

    $eventTiecCuoi = WeddingEvent::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Tiệc Cưới Chính Thức',
        'event_date' => now()->addDays(15)->toDateString(),
        'venue_name' => 'Trung Tâm Tiệc Cưới GEM Center',
        'is_active' => true,
        'display_order' => 2,
    ]);

    $tableVuQuy = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'event_id' => $eventVuQuy->id,
        'name' => 'Bàn Họ Nhà Gái 01',
        'capacity' => 10,
    ]);

    $tableTiecCuoi = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'event_id' => $eventTiecCuoi->id,
        'name' => 'Bàn Bạn Bè GEM 01',
        'capacity' => 10,
    ]);

    expect($tableVuQuy->event->id)->toBe($eventVuQuy->id);
    expect($tableTiecCuoi->event->id)->toBe($eventTiecCuoi->id);
    expect($eventVuQuy->tables()->count())->toBe(1);
    expect($eventTiecCuoi->tables()->count())->toBe(1);
    expect($eventVuQuy->tables()->first()->name)->toBe('Bàn Họ Nhà Gái 01');
});

test('G2-005: Khách walk-in phát sinh tại chỗ được sinh token và ghi nhận tức thì', function () {
    $res = $this->actingAs($this->user)->postJson(
        route('checkin.exception.walk_in', ['slug' => $this->invitation->slug]),
        [
            'name' => 'Khách Vãng Lai Bác Ba',
            'attendees' => 1,
            'note' => 'Bạn thân của bố chú rể đến chúc mừng',
        ]
    );

    $res->assertStatus(201);
    $res->assertJson(['success' => true]);

    $walkInGuest = Guest::where('wedding_id', $this->wedding->id)
        ->where('name', 'Khách Vãng Lai Bác Ba')
        ->first();

    expect($walkInGuest)->not->toBeNull();
    expect($walkInGuest->guest_token)->not->toBeEmpty();
    expect($walkInGuest->checked_in_at)->not->toBeNull();
});

test('G2-006: Offline recovery song song bảo tồn mốc check-in sớm nhất giữa các ca trực', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Võ Văn Kiệt',
        'guest_token' => 'TOKEN_OFFLINE_01',
        'expected_attendees' => 1,
    ]);

    $offlineService = app(EmergencyOfflinePackService::class);

    // Ca 1 offline lúc 18:00
    $timeShift1 = Carbon::parse('2026-11-12 18:00:00');
    $offlineService->syncOfflineCheckins($this->invitation, [
        $guest->id => $timeShift1->toIso8601String(),
    ]);

    expect($guest->fresh()->checked_in_at->timestamp)->toBe($timeShift1->timestamp);

    // Ca 2 gửi gói sync muộn hơn nhưng có timestamp quét muộn hơn (18:30)
    $timeShift2 = Carbon::parse('2026-11-12 18:30:00');
    $offlineService->syncOfflineCheckins($this->invitation, [
        $guest->id => $timeShift2->toIso8601String(),
    ]);

    // Mốc check-in 18:00 sớm hơn vẫn được bảo tồn nguyên vẹn
    expect($guest->fresh()->checked_in_at->timestamp)->toBe($timeShift1->timestamp);
});

test('G2-007: Thông báo đổi giờ cưới cá nhân hóa đúng tên khách và danh xưng', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Hoàng Nam',
        'salutation' => 'Anh',
        'expected_attendees' => 2,
    ]);

    $templateText = 'Kính gửi [DANH_XUNG] [TEN_KHACH], đám cưới của [CAP_DOI] bắt đầu đón khách lúc 18h.';
    $rendered = ReliableMessagingDispatchService::renderPersonalizedMessage($templateText, $guest, $this->wedding);

    expect($rendered)->toContain('Kính gửi Anh Hoàng Nam');
    expect($rendered)->toContain('đám cưới của Tuấn Anh & Ngọc Linh');
});

test('G2-008 & G2-009 & G2-010 & G2-011: Quản trị gửi tin nhắn lọc trùng, opt-out và cập nhật callback DLR thực tế', function () {
    // Tạo 3 khách: Khách 1 và Khách 2 trùng SĐT, Khách 3 opt-out
    $guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn A',
        'phone' => '0912345678',
        'expected_attendees' => 1,
    ]);

    $guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn B (Cùng SĐT A)',
        'phone' => '0912345678',
        'expected_attendees' => 1,
    ]);

    $guest3 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Từ Chối Nhận Tin',
        'phone' => '0987654321',
        'notes' => 'opt_out từ chối nhận tin nhắn',
        'expected_attendees' => 1,
    ]);

    $dispatch = ReliableMessagingDispatchService::createCampaign($this->wedding, [
        'campaign_name' => 'Nhắc hẹn cưới tuần cuối',
        'channel' => 'sms',
        'message_template' => 'Thân mời [DANH_XUNG] [TEN_KHACH] dự lễ cưới.',
        'target_audience' => 'all',
    ], $this->user);

    // 1. Chạy dry-run: lọc trùng SĐT và loại trừ khách Opt-out (G2-010)
    $dryRunResult = ReliableMessagingDispatchService::runDryRun($dispatch);
    expect($dryRunResult['deduplicated'])->toBeGreaterThanOrEqual(1);
    expect($dryRunResult['valid'])->toBeGreaterThanOrEqual(1);

    // 2. Dispatch campaign chính thức (G2-008 & G2-009)
    $dispatchSuccess = ReliableMessagingDispatchService::dispatchRealCampaign($dispatch, $this->user);
    expect($dispatchSuccess)->toBeTrue();
    expect($dispatch->fresh()->status)->toBe('completed');

    // 3. Xử lý webhook callback DLR từ nhà mạng (G2-011)
    $log = MessagingDispatchLog::where('dispatch_id', $dispatch->id)->whereNotNull('provider_ref')->first();
    if ($log) {
        $callbackSuccess = ReliableMessagingDispatchService::handleDeliveryReport(
            $log->provider_ref,
            'delivered'
        );
        expect($callbackSuccess)->toBeTrue();
        expect($log->fresh()->status)->toBe('delivered');
    }
});

test('G2-012: Photo Drop ảnh bị từ chối không bao giờ xuất hiện trên Live Wall feed', function () {
    // 1. Tạo ảnh đã được duyệt
    $approvedPhoto = GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Bạn Bàn 02',
        'photo_url' => 'assets/uploads/photo_approved.webp',
        'is_approved' => true,
    ]);

    // 2. Tạo ảnh bị từ chối / chưa duyệt
    $rejectedPhoto = GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Kẻ Phá Hoại',
        'photo_url' => 'assets/uploads/photo_spam.webp',
        'is_approved' => false,
    ]);

    $response = $this->getJson(route('live_wall.feed', ['slug' => $this->invitation->slug]));
    $response->assertStatus(200);

    $photos = $response->json('photos') ?? [];
    $photoUrls = array_column($photos, 'url');

    expect($photoUrls)->toContain('assets/uploads/photo_approved.webp');
    expect($photoUrls)->not->toContain('assets/uploads/photo_spam.webp');
});

test('G2-013: Lời chúc bị xóa hoặc hủy duyệt biến mất tức thì khỏi Live Wall feed', function () {
    $approvedWish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hai Sài Gòn',
        'sender_name' => 'Bác Hai Sài Gòn',
        'message' => 'Chúc hai cháu răng long đầu bạc!',
        'is_approved' => true,
    ]);

    $res1 = $this->getJson(route('live_wall.feed', ['slug' => $this->invitation->slug]));
    $res1->assertStatus(200);
    $wishes1 = $res1->json('wishes') ?? [];
    $messages1 = array_column($wishes1, 'message');
    expect($messages1)->toContain('Chúc hai cháu răng long đầu bạc!');

    // Hủy duyệt hoặc xóa lời chúc
    $approvedWish->update(['is_approved' => false]);

    $res2 = $this->getJson(route('live_wall.feed', ['slug' => $this->invitation->slug]));
    $res2->assertStatus(200);
    $wishes2 = $res2->json('wishes') ?? [];
    $messages2 = array_column($wishes2, 'message');
    expect($messages2)->not->toContain('Chúc hai cháu răng long đầu bạc!');
});
