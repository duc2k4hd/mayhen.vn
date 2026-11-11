<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\LoveStoryTimelineService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Love Story',
        'slug' => 'mau-test-love-story-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hải Đăng',
        'bride_name' => 'Nhật Hạ',
        'wedding_date' => now()->addMonths(3)->toDateString(),
        'venue_name' => 'InterContinental Saigon',
        'venue_address' => 'Corner Nguyen Du St. & Hai Ba Trung St., Dist 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dang-ha-'.uniqid(),
        'title' => 'Thiệp Cưới Hải Đăng & Nhật Hạ',
        'is_published' => true,
    ]);
});

test('cặp đôi có thể truy cập Studio quản trị Hành Trình Yêu', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.love_story.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Dòng Thời Gian Hành Trình Yêu');
    $response->assertSee('Ánh Mắt Đầu Tiên Chạm Nhau');
    $response->assertSee('Hành Trình Yêu Thương');
});

test('người dùng không thể truy cập Hành Trình Yêu của thiệp người khác', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.love_story.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật các cột mốc tình yêu và lưu vào cấu hình', function () {
    $payload = [
        'enabled' => '1',
        'title' => 'Chuyện Tình Mùa Thu Của Chúng Mình',
        'subtitle' => 'Từ giảng đường đại học đến lễ đường hôm nay',
        'milestones' => [
            [
                'id' => 'm1',
                'date_label' => 'Mùa Thu 2019',
                'title' => 'Lần Đầu Tiên Gặp Em Dưới Mưa',
                'description' => 'Cơn mưa bất chợt ở cổng trường đã đưa hai ta lại gần nhau.',
                'photo_url' => 'https://mayhen.vn/assets/photo1.webp',
                'icon' => 'sparkles',
            ],
            [
                'id' => 'm2',
                'date_label' => '24-12-2023',
                'title' => 'Cầu Hôn Dưới Tháp Eiffel',
                'description' => 'Giữa trời đông Paris hoa lệ, chiếc nhẫn đính ước đã tìm được chủ nhân.',
                'photo_url' => 'https://mayhen.vn/assets/photo2.webp',
                'icon' => 'ring',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('account.love_story.update', $this->invitation->id), $payload);

    $response->assertRedirect(route('account.love_story.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $config = LoveStoryTimelineService::resolveLoveStoryConfig($this->invitation);

    expect($config['enabled'])->toBeTrue();
    expect($config['title'])->toBe('Chuyện Tình Mùa Thu Của Chúng Mình');
    expect($config['subtitle'])->toBe('Từ giảng đường đại học đến lễ đường hôm nay');
    expect(count($config['milestones']))->toBe(2);
    expect($config['milestones'][0]['title'])->toBe('Lần Đầu Tiên Gặp Em Dưới Mưa');
    expect($config['milestones'][1]['icon'])->toBe('ring');
});

test('cặp đôi có thể tải ảnh kỷ niệm cho cột mốc tình yêu qua API', function () {
    $file = UploadedFile::fake()->image('first_date.jpg', 800, 600);

    $response = $this->actingAs($this->user)
        ->postJson(route('account.love_story.upload_photo', $this->invitation->id), [
            'photo' => $file,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $url = $response->json('url');
    expect($url)->toContain('invitations/'.$this->invitation->id.'/love-story/milestone_');
});

test('hệ thống chặn tải lên các tệp tin không phải ảnh', function () {
    $badFile = UploadedFile::fake()->create('script.php', 10, 'text/plain');

    $response = $this->actingAs($this->user)
        ->postJson(route('account.love_story.upload_photo', $this->invitation->id), [
            'photo' => $badFile,
        ]);

    $response->assertStatus(422);
});

test('component dòng thời gian hành trình yêu render chuẩn xác trên giao diện thiệp', function () {
    LoveStoryTimelineService::updateLoveStory($this->invitation, [
        'enabled' => true,
        'title' => 'Chặng Đường Hạnh Phúc',
        'subtitle' => 'Những khoảnh khắc không thể nào quên',
        'milestones' => [
            [
                'id' => 'test_m1',
                'date_label' => 'Mùa Đông 2020',
                'title' => 'Chuyến Đi Đà Lạt Đầu Tiên',
                'description' => 'Những sớm mai mờ sương và bàn tay ấm áp nắm chặt.',
                'photo_url' => 'https://mayhen.vn/assets/dalat.webp',
                'icon' => 'heart',
            ],
        ],
    ]);

    $this->invitation->refresh();

    $view = $this->view('client.components.love-story-timeline', [
        'invitation' => $this->invitation,
    ]);

    $rendered = (string) $view;
    expect($rendered)->toContain('mayhen_invitation_love_story_timeline');
    expect($rendered)->toContain('Chặng Đường Hạnh Phúc');
    expect($rendered)->toContain('Chuyến Đi Đà Lạt Đầu Tiên');
    expect($rendered)->toContain('Mùa Đông 2020');
    expect($rendered)->toContain('mayhen_timeline_spine');
});

test('service phân giải an toàn 4 mốc mặc định mang tên cặp đôi khi chưa có cấu hình', function () {
    $config = LoveStoryTimelineService::resolveLoveStoryConfig($this->invitation);

    expect($config['enabled'])->toBeTrue();
    expect($config['title'])->toBe('Hành Trình Yêu Thương');
    expect(count($config['milestones']))->toBe(4);
    expect($config['milestones'][0]['description'])->toContain('Hải Đăng');
    expect($config['milestones'][0]['description'])->toContain('Nhật Hạ');
});

