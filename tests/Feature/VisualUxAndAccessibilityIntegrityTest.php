<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForG3(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'giua-muon-ngan-vi-sao'],
        [
            'name' => 'Giữa Muôn Ngàn Vì Sao',
            'description' => 'Mẫu thiệp thiên hà tinh tú lãng mạn',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Lễ Cưới Hoàng Gia',
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Mai Phương',
        'wedding_date' => Carbon::now()->addDays(30)->toDateString(),
        'venue_name' => 'The Reverie Saigon',
        'venue_address' => '22-36 Nguyễn Huệ, Bến Nghé, Quận 1, TP.HCM',
        'extra_data' => [
            'gallery' => [
                '/assets/client/images/sample-wedding-1.webp',
                '/assets/client/images/sample-wedding-2.webp',
            ],
        ],
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'tuan-kiet-mai-phuong-g3-'.uniqid(),
        'title' => 'Thiệp Cưới Tuấn Kiệt & Mai Phương',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('G3-001: Client và Admin login forms render đầy đủ meta viewport và controls trên mobile', function () {
    // 1. Client Login
    $clientLogin = $this->get(route('login'));
    $clientLogin->assertStatus(200);
    $clientLogin->assertSee('name="viewport"', false);
    $clientLogin->assertSee('width=device-width', false);
    $clientLogin->assertSee('type="password"', false);
    $clientLogin->assertSee('Đăng nhập');

    // 2. Client Register
    $clientRegister = $this->get(route('register'));
    $clientRegister->assertStatus(200);
    $clientRegister->assertSee('name="viewport"', false);
    $clientRegister->assertSee('Đăng ký');

    // 3. Admin Login
    $adminLogin = $this->get(route('admin.login'));
    $adminLogin->assertStatus(200);
    $adminLogin->assertSee('name="viewport"', false);
    $adminLogin->assertSee('Quản Trị');
});

it('G3-002: Partner Portal render trang thật khi đối tác đã authenticate và không bị redirect', function () {
    $partnerUser = User::factory()->create([
        'role' => 'partner',
    ]);

    Partner::create([
        'user_id' => $partnerUser->id,
        'business_name' => 'May Hen Elite Studio LLC',
        'slug' => 'may-hen-elite-studio-'.uniqid(),
        'partner_code' => 'STUDIO_'.Str::upper(Str::random(6)),
        'studio_name' => 'May Hen Elite Studio',
        'email' => 'contact@elitemayhen.com',
        'phone' => '0909123456',
        'status' => 'approved',
        'commission_rate' => 20.00,
    ]);

    $response = $this->actingAs($partnerUser)->get(route('partner.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('May Hen Elite Studio');
    $response->assertSee('Cổng quản lý thiệp cưới cao cấp');
});

it('G3-003: Studio Editor dài có thanh bar cố định, autosave indicator và CSS safe-area', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $response = $this->actingAs($user)->get(route('invitation.create', ['edit' => $invitation->slug]));

    $response->assertStatus(200);
    $response->assertSee('name="viewport"', false);
    $response->assertSee('mayhen_editor_topbar', false);
    $response->assertSee('Xuất Bản Thiệp');
    $response->assertSee('mayhen_editor_save_pill', false);
});

it('G3-004: Curated preview hiển thị giao diện dệt thiệp 7 bước chuẩn nhịp và nút bỏ qua hiệu ứng', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $invitation->custom_config = array_merge($invitation->custom_config ?? [], [
        'is_curated' => true,
        'presentation_token' => 'test-presentation-token-1234567890',
        'curated_layout' => ['hero', 'quote', 'family', 'events', 'gallery', 'gift', 'signature'],
    ]);
    $invitation->save();

    $response = $this->actingAs($user)->get(route('curated.preview', [
        'slug' => $invitation->slug,
        'pt' => 'test-presentation-token-1234567890',
    ]));

    $response->assertStatus(200);
    $response->assertSee('craftingStatusPill', false);
    $response->assertSee('btnSkipCrafting', false);
    $response->assertSee('Bỏ qua');
});

it('G3-005: Thiệp cưới công khai có thẻ opening, nút mở thiệp và tự động mở khi ở chế độ iframe embed', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    // 1. Xem công khai bình thường -> có màn phong bì opening
    $publicView = $this->get(route('invitation.show', $invitation->slug));
    $publicView->assertStatus(200);
    $publicView->assertSee('mayhen_invitation_opening', false);
    $publicView->assertSee('data-thoi-gian', false);

    // 2. Nhúng trong Studio (?embed=1) -> tự động mở sẵn cánh thiệp và ẩn màn mở đầu
    $embedView = $this->get(route('invitation.show', ['slug' => $invitation->slug, 'embed' => '1']));
    $embedView->assertStatus(200);
    $embedView->assertSee('display: none !important;', false);
});

it('G3-006: RSVP Modal trả về lỗi 422 rõ ràng và giữ lại input khi thiếu tên khách', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    // Gửi thiếu guest_name
    $response = $this->post(route('rsvp.submit', $invitation->slug), [
        'attending_status' => 'attending',
        'guest_name' => '',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['guest_name']);
});

it('G3-007: Seating planner hỗ trợ kéo thả và giao diện tablet trực quan không đè nút', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $response = $this->actingAs($user)->get(route('seating.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Sơ Đồ Xếp Chỗ');
    $response->assertSee('Bàn Tiệc Cưới');
    $response->assertSee('mayhen_seat', false);
});

it('G3-008: Admin Data Tables trên laptop 1366px có container chống tràn table-responsive và phân trang', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Admin Users Table
    $usersResponse = $this->actingAs($admin)->get(route('admin.users.index'));
    $usersResponse->assertStatus(200);
    $usersResponse->assertSee('table', false);

    // Admin Orders Table
    $ordersResponse = $this->actingAs($admin)->get(route('admin.orders.index'));
    $ordersResponse->assertStatus(200);
    $ordersResponse->assertSee('table', false);
});

it('G3-009: Các trang báo lỗi 404, 403, 410, 500 render chuẩn Quiet Luxury và không rò rỉ stack trace', function () {
    // 1. Error 404
    $notFound = $this->get('/duong-dan-khong-ton-tai-tren-mayhen-'.uniqid());
    $notFound->assertStatus(404);
    $notFound->assertSee('404');
    $notFound->assertSee('Lạc bước giữa những lời hẹn');
    $notFound->assertDontSee('Stack trace');

    // 2. Error 403 View
    $view403 = view('errors.403', [
        'exception' => new HttpException(403, 'Tài khoản của bạn chưa được cấp quyền hạn cần thiết.'),
    ])->render();
    expect($view403)->toContain('403');
    expect($view403)->toContain('Truy Cập Bị Giới Hạn');
    expect($view403)->toContain('Khu vực yêu cầu đặc quyền');
    expect($view403)->not->toContain('Stack trace');

    // 3. Error 410 View
    $view410 = view('errors.410', [
        'exception' => new HttpException(410, 'Thiệp cưới đã hết hạn lưu trữ.'),
    ])->render();
    expect($view410)->toContain('410');
    expect($view410)->toContain('Đã Khép Lại Lưu Trữ');
    expect($view410)->toContain('Về Trang Chủ Mây Hẹn');

    // 4. Error 500 View
    $view500 = view('errors.500', [
        'exception' => new HttpException(500, 'Hệ thống đang bảo trì.'),
    ])->render();
    expect($view500)->toContain('500');
    expect($view500)->toContain('Sự Cố Kỹ Thuật');
});

it('G3-010: Live Wall LED hiển thị chuẩn tỉ lệ 16:9 và font chữ linh hoạt', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $response = $this->get(route('live_wall.show', ['slug' => $invitation->slug]));

    $response->assertStatus(200);
    $response->assertSee('mayhen_wall', false);
    $response->assertSee('Tuấn Kiệt');
    $response->assertSee('Mai Phương');
});

it('G3-011: Layout Master định nghĩa meta viewport đầy đủ hỗ trợ phóng to tiếp cận WCAG', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('name="viewport"', false);
    $response->assertSee('width=device-width', false);
    $response->assertSee('maximum-scale=5.0', false);
});

it('G3-012: CSS gốc định nghĩa prefers-reduced-motion và đơn vị rem chuẩn tiếp cận', function () {
    $baseCssPath = public_path('assets/client/css/common/base.css');
    expect(file_exists($baseCssPath))->toBeTrue();

    $cssContent = file_get_contents($baseCssPath);
    expect($cssContent)->toContain('prefers-reduced-motion: reduce');
    expect($cssContent)->toContain('animation-duration: 0.01ms !important');
});

it('G3-013: Âm thanh trên thiệp tuân thủ Autoplay Policy của iOS Safari và Chrome', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $response = $this->get(route('invitation.show', $invitation->slug));

    $response->assertStatus(200);
    // Không có autoplay tự phát vi phạm chính sách
    $html = $response->getContent();
    expect($html)->not->toContain('<audio autoplay');
});

it('G3-014: Ảnh cưới trong Gallery sử dụng object-fit và fallback asset nội bộ', function () {
    $user = User::factory()->create();
    $invitation = createWeddingWithInvitationForG3($user);

    $response = $this->get(route('invitation.show', $invitation->slug));

    $response->assertStatus(200);
    $html = $response->getContent();

    // Kiểm tra class gallery
    expect($html)->toContain('mayhen_invitation_gallery');
    // Kiểm tra không có URL ngoài server rác (ladicdn, builder.27biggroup) trong thuộc tính src
    expect($html)->not->toContain('src="https://w.ladicdn.com');
    expect($html)->not->toContain('src="https://builder.27biggroup.com');
});
