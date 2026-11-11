<?php

use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\RsvpEnforcementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForUser(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-template-enforcement'],
        [
            'name' => 'Mẫu Thử Hạn Chót',
            'description' => 'Mô tả mẫu',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Lễ Cưới Hoàng Gia',
        'groom_name' => 'Bảo Nam',
        'bride_name' => 'Khánh Linh',
        'wedding_date' => Carbon::now()->addDays(20)->toDateString(),
        'venue_name' => 'Trung Tâm Hội Nghị Grand Palace',
        'venue_address' => 'Số 142 Võ Thị Sáu, Quận 3, TP.HCM',
        'extra_data' => [
            'groom_phone' => '0901112233',
            'bride_phone' => '0904445566',
        ],
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'bao-nam-khanh-linh-enforcement-'.uniqid(),
        'title' => 'Thiệp Cưới Bảo Nam & Khánh Linh',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('chặn truy cập cấu hình hạn chót nếu chưa đăng nhập hoặc không phải chủ sở hữu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    // Chưa đăng nhập -> Chuyển hướng sang login
    $response = $this->get(route('account.rsvp_enforcement.index', $invitation->id));
    $response->assertRedirect(route('login'));

    // Đăng nhập bằng user khác -> 403 Forbidden
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('account.rsvp_enforcement.index', $invitation->id));
    $response->assertStatus(403);
});

it('cho phép chủ sở hữu mở màn hình cấu hình hạn chót và khảo sát rsvp', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    $response = $this->actingAs($owner)->get(route('account.rsvp_enforcement.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertViewIs('client.pages.account.rsvp_enforcement.index');
    $response->assertSee('Hạn Chót RSVP, Xe Đưa Đón &amp; Khẩu Vị Tiệc Cưới', false);
    $response->assertSee('Bảo Nam &amp; Khánh Linh', false);
});

it('cập nhật thành công cấu hình hạn chót và khảo sát xe đưa đón cùng thực đơn', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    $deadlineDate = Carbon::now()->addDays(10)->format('Y-m-d');

    $postData = [
        'deadline' => $deadlineDate,
        'is_manually_closed' => false,
        'lock_after_deadline' => true,
        'closed_notice' => 'Gia đình đã chốt số lượng mâm cỗ với nhà hàng. Xin vui lòng liên hệ hotline.',
        'contact_phone' => '0988776655',
        'shuttle_bus_enabled' => true,
        'shuttle_bus_title' => 'Xe Buýt Đưa Đón Khách Resort',
        'shuttle_bus_description' => 'Có xe 29 chỗ đón tại 2 điểm trung tâm.',
        'pickup_points' => [
            [
                'id' => 'p1',
                'name' => 'Nhà Hát Thành Phố',
                'address' => 'Số 07 Lam Sơn, Quận 1',
                'departure_time' => '15:30',
                'coordinator_name' => 'Anh Hoàng',
                'coordinator_phone' => '0911223344',
            ],
            [
                'id' => 'p2',
                'name' => 'Bến Xe Miền Đông Mới',
                'address' => 'Xa lộ Hà Nội, Thủ Đức',
                'departure_time' => '16:00',
                'coordinator_name' => 'Anh Long',
                'coordinator_phone' => '0955667788',
            ],
        ],
        'dietary_survey_enabled' => true,
        'dietary_survey_title' => 'Khảo Sát Thực Đơn Tiệc Cưới',
        'dietary_survey_description' => 'Xin vui lòng cho gia đình biết nếu bạn dùng món chay.',
    ];

    $response = $this->actingAs($owner)->post(route('account.rsvp_enforcement.update', $invitation->id), $postData);

    $response->assertRedirect(route('account.rsvp_enforcement.index', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    $config = RsvpEnforcementService::resolveConfig($invitation);

    expect($config['deadline'])->toBe($deadlineDate)
        ->and($config['is_manually_closed'])->toBeFalse()
        ->and($config['lock_after_deadline'])->toBeTrue()
        ->and($config['contact_phone'])->toBe('0988776655')
        ->and($config['shuttle_bus']['enabled'])->toBeTrue()
        ->and(count($config['shuttle_bus']['pickup_points']))->toBe(2)
        ->and($config['shuttle_bus']['pickup_points'][0]['name'])->toBe('Nhà Hát Thành Phố')
        ->and($config['dietary_survey']['enabled'])->toBeTrue();
});

it('kiểm tra RsvpEnforcementService::isRsvpOpen hoạt động chính xác theo các điều kiện hạn chót', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    // 1. Mặc định chưa quá hạn -> isRsvpOpen = true
    RsvpEnforcementService::saveConfig($invitation, [
        'deadline' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'lock_after_deadline' => true,
        'is_manually_closed' => false,
    ]);
    expect(RsvpEnforcementService::isRsvpOpen($invitation))->toBeTrue();

    // 2. Quá hạn và lock_after_deadline = true -> isRsvpOpen = false
    RsvpEnforcementService::saveConfig($invitation, [
        'deadline' => Carbon::now()->subDays(2)->format('Y-m-d'),
        'lock_after_deadline' => true,
        'is_manually_closed' => false,
    ]);
    expect(RsvpEnforcementService::isRsvpOpen($invitation))->toBeFalse();

    // 3. Quá hạn nhưng không khóa tự động (lock_after_deadline = false) -> isRsvpOpen = true
    RsvpEnforcementService::saveConfig($invitation, [
        'deadline' => Carbon::now()->subDays(2)->format('Y-m-d'),
        'lock_after_deadline' => false,
        'is_manually_closed' => false,
    ]);
    expect(RsvpEnforcementService::isRsvpOpen($invitation))->toBeTrue();

    // 4. Khóa thủ công (is_manually_closed = true) -> luôn false
    RsvpEnforcementService::saveConfig($invitation, [
        'deadline' => Carbon::now()->addDays(5)->format('Y-m-d'),
        'is_manually_closed' => true,
    ]);
    expect(RsvpEnforcementService::isRsvpOpen($invitation))->toBeFalse();
});

it('chặn khách gửi RSVP với thông báo lịch sự khi form đã đóng', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    // Khóa form thủ công
    RsvpEnforcementService::saveConfig($invitation, [
        'is_manually_closed' => true,
        'closed_notice' => 'Cổng xác nhận tham dự đã khép lại chu đáo.',
        'contact_phone' => '0901234567',
    ]);

    $postData = [
        'guest_name' => 'Nguyễn Văn An',
        'phone' => '0909998877',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ];

    $response = $this->postJson(route('rsvp.submit', $invitation->slug), $postData);

    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'is_closed' => true,
    ]);
    $response->assertJsonPath('contact_phone', '0901234567');
});

it('lưu thành công thông tin xe đưa đón và khẩu vị khi khách gửi RSVP hợp lệ', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    // Mở form, bật xe đưa đón và khảo sát thực đơn
    RsvpEnforcementService::saveConfig($invitation, [
        'deadline' => Carbon::now()->addDays(15)->format('Y-m-d'),
        'is_manually_closed' => false,
        'shuttle_bus_enabled' => true,
        'dietary_survey_enabled' => true,
    ]);

    $postData = [
        'guest_name' => 'Trần Thị Mai',
        'phone' => '0933221100',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
        'shuttle_bus_needed' => true,
        'shuttle_pickup_point' => 'Nhà Hát Thành Phố',
        'shuttle_passengers' => 3,
        'menu_preference' => 'vegetarian',
        'dietary_requirements' => 'Ăn chay trường, dị ứng đậu phộng',
        'wishes' => 'Chúc hai bạn trăm năm hạnh phúc!',
    ];

    $response = $this->postJson(route('rsvp.submit', $invitation->slug), $postData);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $savedResponse = GuestResponse::where('wedding_id', $invitation->wedding_id)
        ->where('guest_name', 'Trần Thị Mai')
        ->first();

    expect($savedResponse)->not->toBeNull()
        ->and($savedResponse->attending_status)->toBe('attending')
        ->and($savedResponse->shuttle_bus_needed)->toBeTrue()
        ->and($savedResponse->shuttle_pickup_point)->toBe('Nhà Hát Thành Phố')
        ->and($savedResponse->shuttle_passengers)->toBe(3)
        ->and($savedResponse->menu_preference)->toBe('vegetarian')
        ->and($savedResponse->dietary_requirements)->toContain('Ăn chay trường');
});

it('xuất thành công tệp CSV danh sách xe đưa đón và thực đơn bếp trưởng với UTF-8 BOM', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForUser($owner);

    // Tạo khách đăng ký xe đưa đón và thực đơn chay
    GuestResponse::create([
        'wedding_id' => $invitation->wedding_id,
        'guest_name' => 'Lê Hoàng Minh',
        'phone' => '0944556677',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
        'shuttle_bus_needed' => true,
        'shuttle_pickup_point' => 'Nhà Hát Thành Phố',
        'shuttle_passengers' => 2,
        'menu_preference' => 'vegetarian',
        'dietary_requirements' => 'Không ăn hành tỏi',
    ]);

    // 1. Xuất CSV Xe Đưa Đón
    $shuttleCsv = $this->actingAs($owner)->get(route('account.rsvp_enforcement.export_shuttle', $invitation->id));
    $shuttleCsv->assertStatus(200);
    $shuttleCsv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($shuttleCsv->content())->toStartWith("\xEF\xBB\xBF")
        ->and($shuttleCsv->content())->toContain('Lê Hoàng Minh')
        ->and($shuttleCsv->content())->toContain('Nhà Hát Thành Phố');

    // 2. Xuất CSV Thực Đơn Bếp Trưởng
    $dietaryCsv = $this->actingAs($owner)->get(route('account.rsvp_enforcement.export_dietary', $invitation->id));
    $dietaryCsv->assertStatus(200);
    $dietaryCsv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($dietaryCsv->content())->toStartWith("\xEF\xBB\xBF")
        ->and($dietaryCsv->content())->toContain('Lê Hoàng Minh')
        ->and($dietaryCsv->content())->toContain('vegetarian')
        ->and($dietaryCsv->content())->toContain('Không ăn hành tỏi');
});
