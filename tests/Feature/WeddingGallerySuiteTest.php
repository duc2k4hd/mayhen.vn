<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingGalleryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createWeddingWithInvitationForGallery(User $user): Invitation
{
    $template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-template-gallery'],
        [
            'name' => 'Mẫu Thử Gallery',
            'description' => 'Mô tả mẫu',
            'thumbnail_url' => '/images/sample.jpg',
            'layout_type' => 'single_page',
            'is_active' => true,
        ]
    );

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'title' => 'Lễ Cưới Hoàng Gia Mây Hẹn',
        'groom_name' => 'Hoàng Khang',
        'bride_name' => 'Mỹ Duyên',
        'wedding_date' => Carbon::now()->addDays(25)->toDateString(),
        'venue_name' => 'Park Hyatt Saigon Ballroom',
        'venue_address' => '2 Lam Son Square, District 1, TP.HCM',
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
        'slug' => 'hoang-khang-my-duyen-gallery-'.uniqid(),
        'title' => 'Thiệp Cưới Hoàng Khang & Mỹ Duyên',
        'is_published' => true,
        'custom_config' => [],
    ]);
}

it('chặn truy cập cấu hình album ảnh cưới nếu chưa đăng nhập hoặc không phải chủ sở hữu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    // 1. Khách vãng lai -> Redirect sang trang đăng nhập
    $response = $this->get(route('account.gallery.index', $invitation->id));
    $response->assertRedirect(route('login'));

    // 2. Người dùng khác -> 403 Forbidden
    $intruder = User::factory()->create();
    $this->actingAs($intruder)
        ->get(route('account.gallery.index', $invitation->id))
        ->assertStatus(403);

    $this->actingAs($intruder)
        ->post(route('account.gallery.update', $invitation->id), [
            'title' => 'Album giả mạo',
            'layout_style' => 'editorial_masonry',
            'photos' => [
                ['url' => '/images/fake.jpg', 'caption' => 'Fake'],
            ],
        ])
        ->assertStatus(403);
});

it('cho phép chủ sở hữu mở màn hình Studio quản trị Album 10 ảnh cưới nghệ thuật', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    $response = $this->actingAs($owner)->get(route('account.gallery.index', $invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Album 10 Ảnh Cưới Nghệ Thuật &amp; Bố Cục Tạp Chí', false);
    $response->assertSee('Hoàng Khang &amp; Mỹ Duyên', false);
    $response->assertSee('Tạp Chí Nghệ Thuật (Editorial Masonry)');
    $response->assertSee('Lướt Ngang 3D (Coverflow Carousel)');
    $response->assertSee('Lưới Cổ Điển Sang Trọng (Classic Luxury Grid)');
    $response->assertSee('Slot 01');
    $response->assertSee('Slot 10');
});

it('cho phép tải lên ảnh mới qua AJAX vào kho lưu trữ Album thiệp cưới', function () {
    Storage::fake('public');
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    $file = UploadedFile::fake()->image('wedding_moment.jpg', 1200, 1600);

    $response = $this->actingAs($owner)
        ->postJson(route('account.gallery.upload_photo', $invitation->id), [
            'photo' => $file,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Tải ảnh cưới lên thành công!',
    ]);

    $url = $response->json('url');
    expect($url)->toContain('/storage/invitations/'.$invitation->id.'/gallery/');
});

it('cập nhật thành công danh sách 10 ảnh, bố cục tạp chí và đồng bộ vào wedding extra_data', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    $newPhotos = [];
    for ($i = 0; $i < 10; $i++) {
        $newPhotos[] = [
            'url' => "/storage/invitations/{$invitation->id}/gallery/photo_{$i}.webp",
            'caption' => 'Khoảnh khắc ngọt ngào số '.($i + 1),
            'alt' => 'Ảnh cưới số '.($i + 1),
        ];
    }

    $payload = [
        'enabled' => '1',
        'layout_style' => 'carousel_coverflow',
        'title' => 'Thước Phim Tình Yêu Vĩnh Cửu',
        'subtitle' => 'Những giây phút đong đầy hạnh phúc bên người thương',
        'lightbox_enabled' => '1',
        'photos' => $newPhotos,
    ];

    $response = $this->actingAs($owner)
        ->post(route('account.gallery.update', $invitation->id), $payload);

    $response->assertRedirect(route('account.gallery.index', $invitation->id));
    $response->assertSessionHas('success');

    $invitation->refresh();
    $config = WeddingGalleryService::resolveGalleryConfig($invitation);

    expect($config['title'])->toBe('Thước Phim Tình Yêu Vĩnh Cửu');
    expect($config['layout_style'])->toBe('carousel_coverflow');
    expect($config['lightbox_enabled'])->toBeTrue();
    expect($config['photos'])->toHaveCount(10);
    expect($config['photos'][0]['caption'])->toBe('Khoảnh khắc ngọt ngào số 1');

    // Đồng bộ vào bảng weddings extra_data['gallery']
    $invitation->wedding->refresh();
    $extraGallery = $invitation->wedding->extra_data['gallery'] ?? [];
    expect($extraGallery)->toHaveCount(10);
    expect($extraGallery[0])->toBe("/storage/invitations/{$invitation->id}/gallery/photo_0.webp");
});

it('kiểm tra validation khi cập nhật album với phong cách bố cục không hợp lệ hoặc thiếu dữ liệu', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    $response = $this->actingAs($owner)
        ->post(route('account.gallery.update', $invitation->id), [
            'title' => '',
            'layout_style' => 'invalid_layout_style',
            'photos' => [],
        ]);

    $response->assertSessionHasErrors(['title', 'layout_style', 'photos']);
});

it('render Blade component wedding-gallery-suite với đầy đủ các ảnh và markup Lightbox', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    WeddingGalleryService::updateGallery($invitation, [
        'enabled' => true,
        'layout_style' => 'editorial_masonry',
        'title' => 'Những Khoảnh Khắc Bất Tận',
        'subtitle' => 'Ghi lại tình yêu dưới ánh hoàng hôn',
        'lightbox_enabled' => true,
        'photos' => array_map(function ($idx) {
            return [
                'url' => "/images/wedding_photo_{$idx}.jpg",
                'caption' => "Khoảnh khắc hẹn ước số {$idx}",
                'alt' => "Ảnh {$idx}",
            ];
        }, range(0, 9)),
    ]);

    $html = view('client.components.wedding-gallery-suite', ['invitation' => $invitation])->render();

    expect($html)->toContain('Những Khoảnh Khắc Bất Tận');
    expect($html)->toContain('mayhen_invitation_gallery--editorial_masonry');
    expect($html)->toContain('mayhen_invitation_lightbox');
    expect($html)->toContain('data-sync-target="gallery.0"');
    expect($html)->toContain('data-sync-target="gallery.9"');
    expect($html)->toContain('Khoảnh khắc hẹn ước số 0');
});

it('không render component khi tính năng album ảnh bị tắt (enabled = false)', function () {
    $owner = User::factory()->create();
    $invitation = createWeddingWithInvitationForGallery($owner);

    WeddingGalleryService::updateGallery($invitation, [
        'enabled' => false,
        'layout_style' => 'classic_grid',
        'photos' => WeddingGalleryService::getDefaultPhotos($invitation),
    ]);

    $html = view('client.components.wedding-gallery-suite', ['invitation' => $invitation])->render();

    expect(trim($html))->toBeEmpty();
});
