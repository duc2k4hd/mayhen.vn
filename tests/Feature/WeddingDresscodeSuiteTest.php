<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingDresscodeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForDresscode(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-template-dresscode'],
        [
            'name' => 'Mẫu Thử Dresscode',
            'description' => 'Mô tả mẫu',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Hôn Lễ Hoàng Gia',
        'groom_name' => 'Đức Minh',
        'bride_name' => 'Thùy Chi',
        'wedding_date' => Carbon::now()->addDays(25)->toDateString(),
        'venue_name' => 'Riverside Palace',
        'venue_address' => '360D Bến Vân Đồn, Quận 4, TP.HCM',
        'extra_data' => [
            'dresscode_colors' => ['#FAF8F5', '#EFE8DD', '#9C7A4A'],
            'dresscode_note' => 'Gợi ý tone màu trang nhã.',
        ],
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'duc-minh-thuy-chi-dresscode-'.uniqid(),
        'title' => 'Thiệp Cưới Đức Minh & Thùy Chi',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('chặn truy cập cấu hình dresscode nếu chưa đăng nhập hoặc không phải chủ sở hữu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    // Chưa đăng nhập -> Chuyển hướng sang login
    $response = $this->get(route('account.dresscode.index', $invitation->id));
    $response->assertRedirect(route('login'));

    // Đăng nhập bằng user khác -> 403 Forbidden
    $otherUser = User::factory()->create();
    $response = $this->actingAs($otherUser)->get(route('account.dresscode.index', $invitation->id));
    $response->assertStatus(403);
});

it('cho phép chủ sở hữu mở màn hình studio dresscode thành công', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    $response = $this->actingAs($owner)->get(route('account.dresscode.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertViewIs('client.pages.account.dresscode.index');
    $response->assertSee('Bảng Màu Trang Phục &amp; Quy Chuẩn Dresscode', false);
    $response->assertSee('Đức Minh &amp; Thùy Chi', false);
    $response->assertSee('Quiet Luxury Neutral', false);
    $response->assertSee('Thanh Lịch');
});

it('cập nhật thành công cấu hình bảng màu dresscode và đồng bộ dữ liệu đám cưới', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    $postData = [
        'enabled' => true,
        'title' => 'Quy Chuẩn Trang Phục Hôn Lễ',
        'subtitle' => 'Tone màu be lụa và nâu ấm cúng',
        'preset_key' => 'botanical_garden_sage',
        'attire_style' => 'semi_formal',
        'colors' => [
            [
                'hex' => '#9AA899',
                'name' => 'Xanh Xô Thơm',
                'description' => 'Đầm maxi nhẹ nhàng',
            ],
            [
                'hex' => '#748E8A',
                'name' => 'Xanh Olive',
                'description' => 'Áo sơ mi hoặc suit nam',
            ],
            [
                'hex' => '#F5F3EF',
                'name' => 'Trắng Kem',
                'description' => 'Trang phục sáng màu',
            ],
        ],
        'note' => 'Kính mong quý khách mặc đúng tone màu gợi ý để khung hình kỷ niệm thật đẹp.',
        'gentlemen_guide' => 'Suit nam, sơ mi sáng màu, giày da lịch sự.',
        'ladies_guide' => 'Đầm dạ hội hoặc váy hoa nhã nhặn.',
        'avoid_colors_note' => 'Không mặc áo thun hoặc quần short.',
    ];

    $response = $this->actingAs($owner)->post(route('account.dresscode.update', $invitation->id), $postData);

    $response->assertRedirect(route('account.dresscode.index', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    $config = WeddingDresscodeService::resolveDresscodeConfig($invitation);

    expect($config['enabled'])->toBeTrue()
        ->and($config['title'])->toBe('Quy Chuẩn Trang Phục Hôn Lễ')
        ->and($config['preset_key'])->toBe('botanical_garden_sage')
        ->and(count($config['colors']))->toBe(3)
        ->and($config['colors'][0]['hex'])->toBe('#9AA899')
        ->and($config['gentlemen_guide'])->toBe('Suit nam, sơ mi sáng màu, giày da lịch sự.');

    // Kiểm tra đồng bộ ngược vào wedding->extra_data
    $invitation->wedding->refresh();
    expect($invitation->wedding->extra_data['dresscode_colors'])->toBe(['#9AA899', '#748E8A', '#F5F3EF'])
        ->and($invitation->wedding->extra_data['dresscode_note'])->toBe('Kính mong quý khách mặc đúng tone màu gợi ý để khung hình kỷ niệm thật đẹp.');
});

it('chặn cập nhật bảng màu nếu mã màu hex không hợp lệ', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    $invalidData = [
        'enabled' => true,
        'title' => 'Dresscode',
        'preset_key' => 'quiet_luxury_neutral',
        'attire_style' => 'semi_formal',
        'colors' => [
            [
                'hex' => 'not-a-hex-color',
                'name' => 'Sai Mã Màu',
            ],
        ],
    ];

    $response = $this->actingAs($owner)->post(route('account.dresscode.update', $invitation->id), $invalidData);
    $response->assertSessionHasErrors(['colors.0.hex']);
});

it('tải lên ảnh moodboard trang phục tiệc cưới thành công qua ajax', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    $fakeImage = UploadedFile::fake()->image('moodboard-outfit.jpg', 800, 600);

    $response = $this->actingAs($owner)->postJson(route('account.dresscode.upload_moodboard', $invitation->id), [
        'moodboard_file' => $fakeImage,
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $url = $response->json('url');
    expect($url)->not->toBeEmpty();

    $invitation->refresh();
    $config = WeddingDresscodeService::resolveDresscodeConfig($invitation);
    expect($config['moodboard_image_url'])->toBe($url);
});

it('chặn tải lên file moodboard nếu không phải định dạng ảnh', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    $fakeFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $response = $this->actingAs($owner)->postJson(route('account.dresscode.upload_moodboard', $invitation->id), [
        'moodboard_file' => $fakeFile,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['moodboard_file']);
});

it('render component dresscode-palette với đầy đủ swatch màu và hướng dẫn trang phục', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForDresscode($owner);

    WeddingDresscodeService::updateDresscode($invitation, [
        'enabled' => true,
        'title' => 'Quy Chuẩn Dress Code Hoàng Gia',
        'subtitle' => 'Trang phục dạ tiệc sang trọng',
        'attire_style' => 'formal_black_tie',
        'preset_key' => 'royal_velvet_black_tie',
        'colors' => [
            ['hex' => '#85182A', 'name' => 'Đỏ Rượu Vang', 'description' => 'Tone đỏ nhung'],
            ['hex' => '#C5A880', 'name' => 'Vàng Champagne', 'description' => 'Tone ánh kim'],
        ],
        'note' => 'Xin vui lòng không mặc trang phục quá sặc sỡ.',
        'gentlemen_guide' => 'Tuxedo hoặc suit đen sang trọng.',
        'ladies_guide' => 'Đầm dạ hội dáng dài chạm gót.',
        'avoid_colors_note' => 'Tránh màu trắng tinh trùng với cô dâu.',
    ]);

    $view = $this->blade("@include('client.components.dresscode-palette', ['invitation' => \$invitation])", [
        'invitation' => $invitation,
    ]);

    $view->assertSee('Quy Chuẩn Dress Code Hoàng Gia');
    $view->assertSee('Trang phục dạ tiệc sang trọng');
    $view->assertSee('Đỏ Rượu Vang');
    $view->assertSee('#85182A');
    $view->assertSee('Vàng Champagne');
    $view->assertSee('#C5A880');
    $view->assertSee('Dành Cho Quý Nam (Gentlemen)');
    $view->assertSee('Tuxedo hoặc suit đen sang trọng.');
    $view->assertSee('Dành Cho Quý Nữ (Ladies)');
    $view->assertSee('Đầm dạ hội dáng dài chạm gót.');
    $view->assertSee('Tránh màu trắng tinh trùng với cô dâu.');
});
