<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingLocationAdvisoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForLocation(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-template-location'],
        [
            'name' => 'Mẫu Thử Location',
            'description' => 'Mô tả mẫu',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Lễ Cưới Hoàng Gia Mây Hẹn',
        'groom_name' => 'Bảo Nam',
        'bride_name' => 'Ánh Dương',
        'wedding_date' => Carbon::now()->addDays(20)->toDateString(),
        'venue_name' => 'Gem Center Grand Ballroom',
        'venue_address' => 'Số 8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1, TP.HCM',
        'extra_data' => [
            'groom_phone' => '0901234567',
        ],
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'bao-nam-anh-duong-loc-'.uniqid(),
        'title' => 'Thiệp Cưới Bảo Nam & Ánh Dương',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('chặn truy cập cấu hình bản đồ và thời tiết nếu chưa đăng nhập hoặc không phải chủ sở hữu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    // 1. Chưa đăng nhập -> Chuyển hướng sang login
    $response = $this->get(route('account.location_advisory.index', $invitation->id));
    $response->assertRedirect(route('login'));

    // 2. Người dùng khác -> 403 Forbidden
    $intruder = User::factory()->create();
    $this->actingAs($intruder)
        ->get(route('account.location_advisory.index', $invitation->id))
        ->assertStatus(403);

    $this->actingAs($intruder)
        ->post(route('account.location_advisory.update', $invitation->id), [
            'title' => 'Tiêu đề sửa đổi',
            'venue_name' => 'Địa điểm giả mạo',
            'venue_address' => 'Địa chỉ giả mạo',
            'condition_key' => 'sunny_clear',
        ])
        ->assertStatus(403);
});

it('cho phép chủ sở hữu mở màn hình Studio cấu hình bản đồ và thời tiết ngày cưới', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    $response = $this->actingAs($owner)->get(route('account.location_advisory.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Chỉ Dẫn Địa Điểm, Bãi Đỗ Xe &amp; Thời Tiết Ngày Cưới', false);
    $response->assertSee('Gem Center Grand Ballroom');
    $response->assertSee('Nắng Ấm &amp; Trời Quang Đãng', false);
    $response->assertSee('Gió Biển Nghỉ Dưỡng Mát Lành');
});

it('cập nhật thành công cấu hình bản đồ, GPS, bãi xe và thời tiết ngày cưới', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    $payload = [
        'enabled' => '1',
        'title' => 'Chỉ Dẫn Di Chuyển & Đón Tiếp',
        'subtitle' => 'Kính mời quý khách xem thông tin đường đi và đỗ xe thuận tiện',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM',
        'map_url' => 'https://maps.app.goo.gl/whitepalace194',
        'latitude' => 10.798123,
        'longitude' => 106.669456,
        'reception_phone' => '0988776655',
        'reception_coordinator' => 'Trưởng Ban Lễ Tân Hoàng Khang',
        'car_parking' => 'Hầm B2 sảnh Albatross, nhân viên hướng dẫn vào luồng ưu tiên',
        'bike_parking' => 'Bãi xe máy tầng hầm B1, vé quét mã QR tự động',
        'accessibility_note' => 'Thang máy cuốn trực tiếp từ hầm giữ xe lên sảnh tiệc tầng 2',
        'weather_enabled' => '1',
        'condition_key' => 'sunset_golden',
        'temperature_celsius' => 29,
        'advisory_note' => 'Tiệc cưới đón hoàng hôn sân vườn tuyệt đẹp, quý khách nên đến sớm 15 phút để chụp ảnh lưu niệm.',
    ];

    $response = $this->actingAs($owner)
        ->post(route('account.location_advisory.update', $invitation->id), $payload);

    $response->assertRedirect(route('account.location_advisory.index', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    $config = WeddingLocationAdvisoryService::resolveAdvisoryConfig($invitation);

    expect($config['title'])->toBe('Chỉ Dẫn Di Chuyển & Đón Tiếp');
    expect($config['venue_name'])->toBe('Trung Tâm Hội Nghị White Palace');
    expect($config['latitude'])->toEqual(10.798123);
    expect($config['longitude'])->toEqual(106.669456);
    expect($config['parking']['car_parking'])->toContain('Hầm B2 sảnh Albatross');
    expect($config['weather']['condition_key'])->toBe('sunset_golden');
    expect($config['weather']['temperature_celsius'])->toBe(29);
    expect($config['weather']['condition_name'])->toBe('Hoàng Hôn Nắng Vàng (Golden Hour)');

    // Đồng bộ ngược vào bảng wedding
    $invitation->wedding->refresh();
    expect($invitation->wedding->venue_name)->toBe('Trung Tâm Hội Nghị White Palace');
    expect($invitation->wedding->venue_address)->toBe('194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM');
});

it('kiểm tra validation dữ liệu bắt buộc và toạ độ hợp lệ', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    // 1. Thiếu thông tin bắt buộc
    $response = $this->actingAs($owner)
        ->post(route('account.location_advisory.update', $invitation->id), [
            'title' => '',
            'venue_name' => '',
            'venue_address' => '',
            'condition_key' => '',
        ]);

    $response->assertSessionHasErrors(['title', 'venue_name', 'venue_address', 'condition_key']);

    // 2. Toạ độ nằm ngoài giới hạn địa lý
    $response2 = $this->actingAs($owner)
        ->post(route('account.location_advisory.update', $invitation->id), [
            'title' => 'Chỉ Dẫn',
            'venue_name' => 'Sảnh Tiệc',
            'venue_address' => 'Địa chỉ',
            'condition_key' => 'sunny_clear',
            'latitude' => 120.5, // > 90 độ
            'longitude' => -250.0, // < -180 độ
        ]);

    $response2->assertSessionHasErrors(['latitude', 'longitude']);
});

it('sinh chính xác các đường dẫn điều hướng bản đồ Google Maps, Apple Maps, Grab và Hotline taxi', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    WeddingLocationAdvisoryService::updateAdvisoryConfig($invitation, [
        'venue_name' => 'Park Hyatt Saigon',
        'venue_address' => '2 Lam Son Square, District 1, Ho Chi Minh City',
        'latitude' => 10.7765,
        'longitude' => 106.7032,
        'condition_key' => 'breezy_cool',
    ]);

    $navLinks = WeddingLocationAdvisoryService::generateNavigationLinks($invitation);

    expect($navLinks['google_maps'])->toContain('10.7765,106.7032');
    expect($navLinks['apple_maps'])->toContain('10.7765,106.7032');
    expect($navLinks['grab'])->toContain('drop_off_latitude=10.7765');
    expect($navLinks['grab'])->toContain('drop_off_longitude=106.7032');
    expect($navLinks['taxi_mailinh'])->toBe('tel:1055');
    expect($navLinks['taxi_vinasun'])->toBe('tel:02838272727');
});

it('render Blade component wedding-location-advisory chính xác với đầy đủ thông tin', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    WeddingLocationAdvisoryService::updateAdvisoryConfig($invitation, [
        'enabled' => true,
        'title' => 'Chỉ Dẫn Đường Đi Đến Hôn Lễ',
        'subtitle' => 'Kính mời quý khách theo dõi sơ đồ và hướng dẫn di chuyển',
        'venue_name' => 'The Reverie Saigon Ball Room',
        'venue_address' => '22-36 Nguyễn Huệ, Bến Nghé, Quận 1',
        'car_parking' => 'Hầm B3 toà nhà Times Square',
        'weather_enabled' => true,
        'condition_key' => 'breezy_cool',
        'temperature_celsius' => 25,
        'advisory_note' => 'Gió mùa dịu êm, quý khách có thể mặc vest mỏng hoặc đầm lụa thoải mái.',
    ]);

    $html = view('client.components.wedding-location-advisory', ['invitation' => $invitation])->render();

    expect($html)->toContain('Chỉ Dẫn Đường Đi Đến Hôn Lễ');
    expect($html)->toContain('The Reverie Saigon Ball Room');
    expect($html)->toContain('22-36 Nguyễn Huệ, Bến Nghé, Quận 1');
    expect($html)->toContain('Google Maps');
    expect($html)->toContain('Apple Maps');
    expect($html)->toContain('Đặt Xe Grab');
    expect($html)->toContain('Hầm B3 toà nhà Times Square');
    expect($html)->toContain('25');
    expect($html)->toContain('Gió Mát Thu &amp; Dịu Nhẹ');
});

it('không render component khi tính năng bị vô hiệu hoá (enabled = false)', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForLocation($owner);

    WeddingLocationAdvisoryService::updateAdvisoryConfig($invitation, [
        'enabled' => false,
        'condition_key' => 'sunny_clear',
    ]);

    $html = view('client.components.wedding-location-advisory', ['invitation' => $invitation])->render();

    expect(trim($html))->toBeEmpty();
});
