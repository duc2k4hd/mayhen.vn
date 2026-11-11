<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingTimelineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForTimeline(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-template-timeline'],
        [
            'name' => 'Mẫu Thử Timeline',
            'description' => 'Mô tả mẫu',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Lễ Thành Hôn Hoàng Gia',
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Mai Phương',
        'wedding_date' => Carbon::now()->addDays(30)->toDateString(),
        'venue_name' => 'The Reverie Saigon',
        'venue_address' => 'Số 22-36 Nguyễn Huệ, Bến Nghé, Quận 1, TP.HCM',
        'extra_data' => [
            'timeline' => [
                ['time' => '17:30', 'title' => 'Đón Khách'],
                ['time' => '18:30', 'title' => 'Cử Hành Hôn Lễ'],
            ],
        ],
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'tuan-kiet-mai-phuong-timeline-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Kiệt & Mai Phương',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('chặn truy cập cấu hình timeline nếu chưa đăng nhập hoặc không phải chủ sở hữu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    // Chưa đăng nhập -> Chuyển hướng sang login
    $response = $this->get(route('account.timeline.index', $invitation->id));
    $response->assertRedirect(route('login'));

    // Đăng nhập bằng user khác -> 403 Forbidden
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('account.timeline.index', $invitation->id));
    $response->assertStatus(403);
});

it('cho phép chủ sở hữu mở màn hình studio timeline ngày cưới thành công', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    $response = $this->actingAs($owner)->get(route('account.timeline.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertViewIs('client.pages.account.timeline.index');
    $response->assertSee('Lịch Trình Hôn Lễ &amp; Thêm Vào Lịch Một Chạm', false);
    $response->assertSee('Tuấn Kiệt &amp; Mai Phương', false);
    $response->assertSee('Tiệc Cưới Tối Khách Sạn', false);
    $response->assertSee('Sunset Beach', false);
});

it('cập nhật thành công cấu hình mốc thời gian ngày cưới và đồng bộ dữ liệu đám cưới', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    $postData = [
        'enabled' => true,
        'title' => 'Chương Trình Lễ Cưới Trọng Đại',
        'subtitle' => 'Kính mời quý khách theo dõi các mốc sự kiện',
        'scenario_key' => 'sunset_beach',
        'calendar_reminder_hours' => 3,
        'items' => [
            [
                'time' => '16:00',
                'title' => 'Đón Khách Tại Bãi Cát Ven Biển',
                'location' => 'Bãi cỏ hướng biển Resort',
                'description' => 'Thưởng thức nước dừa tươi và chụp ảnh kỷ niệm.',
                'icon' => 'cocktail',
            ],
            [
                'time' => '17:00',
                'title' => 'Nghi Thức Trao Lời Hẹn Ước Hoàng Hôn',
                'location' => 'Vòm hoa bãi cát',
                'description' => 'Khoảnh khắc trao nhẫn thiêng liêng lúc hoàng hôn.',
                'icon' => 'rings',
            ],
            [
                'time' => '18:30',
                'title' => 'Dạ Tiệc Hải Sản Dưới Ánh Sao',
                'location' => 'Khuôn viên hồ bơi',
                'description' => 'Khai tiệc chiêu đãi và nâng ly chúc phúc.',
                'icon' => 'feast',
            ],
        ],
    ];

    $response = $this->actingAs($owner)->post(route('account.timeline.update', $invitation->id), $postData);

    $response->assertRedirect(route('account.timeline.index', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    $config = WeddingTimelineService::resolveTimelineConfig($invitation);

    expect($config['enabled'])->toBeTrue()
        ->and($config['title'])->toBe('Chương Trình Lễ Cưới Trọng Đại')
        ->and($config['calendar_reminder_hours'])->toBe(3)
        ->and(count($config['items']))->toBe(3)
        ->and($config['items'][0]['time'])->toBe('16:00')
        ->and($config['items'][0]['title'])->toBe('Đón Khách Tại Bãi Cát Ven Biển')
        ->and($config['items'][0]['icon'])->toBe('cocktail');

    // Kiểm tra đồng bộ vào wedding->extra_data['timeline']
    $invitation->wedding->refresh();
    expect(count($invitation->wedding->extra_data['timeline']))->toBe(3)
        ->and($invitation->wedding->extra_data['timeline'][0]['title'])->toBe('Đón Khách Tại Bãi Cát Ven Biển');
});

it('chặn cập nhật timeline nếu thiếu thông tin bắt buộc', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    $invalidData = [
        'enabled' => true,
        'title' => '',
        'scenario_key' => 'evening_banquet',
        'items' => [
            [
                'time' => '',
                'title' => '',
            ],
        ],
    ];

    $response = $this->actingAs($owner)->post(route('account.timeline.update', $invitation->id), $invalidData);
    $response->assertSessionHasErrors(['title', 'items.0.time', 'items.0.title']);
});

it('cho phép tải tệp ics chuẩn rfc 5545 từ trang quản trị và route công khai', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    // 1. Tải từ trang quản trị
    $responseAdmin = $this->actingAs($owner)->get(route('account.timeline.download_ics', $invitation->id));
    $responseAdmin->assertStatus(200);
    $responseAdmin->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    expect($responseAdmin->content())->toContain('BEGIN:VCALENDAR')
        ->and($responseAdmin->content())->toContain('BEGIN:VEVENT')
        ->and($responseAdmin->content())->toContain('Tuấn Kiệt & Mai Phương')
        ->and($responseAdmin->content())->toContain('The Reverie Saigon')
        ->and($responseAdmin->content())->toContain('END:VCALENDAR');

    // 2. Khách mời tải từ route public
    $responsePublic = $this->get(route('invitation.timeline.download_ics', $invitation->slug));
    $responsePublic->assertStatus(200);
    $responsePublic->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    expect($responsePublic->content())->toContain('BEGIN:VCALENDAR')
        ->and($responsePublic->content())->toContain('TRIGGER:-P1D');
});

it('render component wedding-timeline với đầy đủ mốc sự kiện và liên kết calendar', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForTimeline($owner);

    WeddingTimelineService::updateTimeline($invitation, [
        'enabled' => true,
        'title' => 'Lịch Trình Hôn Lễ Hoàng Gia',
        'subtitle' => 'Khoảnh khắc kết nối tình yêu',
        'scenario_key' => 'evening_banquet',
        'items' => [
            [
                'time' => '17:30',
                'title' => 'Đón Tiếp Quan Khách Quý',
                'location' => 'Sảnh Pha Lê Tầng 3',
                'description' => 'Thưởng thức cocktail và chụp ảnh tại Backdrop hoa tươi.',
                'icon' => 'cocktail',
            ],
            [
                'time' => '18:45',
                'title' => 'Nghi Lễ Trao Nhẫn Cưới Thiêng Liêng',
                'location' => 'Lễ đường chính',
                'description' => 'Khoảnh khắc nguyện thề trọn đời.',
                'icon' => 'rings',
            ],
        ],
    ]);

    $view = $this->blade("@include('client.components.wedding-timeline', ['invitation' => \$invitation])", [
        'invitation' => $invitation,
    ]);

    $view->assertSee('Lịch Trình Hôn Lễ Hoàng Gia');
    $view->assertSee('Khoảnh khắc kết nối tình yêu');
    $view->assertSee('17:30');
    $view->assertSee('Đón Tiếp Quan Khách Quý');
    $view->assertSee('Sảnh Pha Lê Tầng 3');
    $view->assertSee('18:45');
    $view->assertSee('Nghi Lễ Trao Nhẫn Cưới Thiêng Liêng');
    $view->assertSee('Thêm Hôn Lễ Vào Lịch (Add to Calendar)');
    $view->assertSee('Google Calendar');
    $view->assertSee('Apple Calendar (Tải file .ics)');
});
