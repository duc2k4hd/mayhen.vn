<?php

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Photo Drop Test',
        'slug' => 'mau-photo-drop-'.uniqid(),
        'description' => 'Test Template for Photo Drop',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.hac-bach-trao-duyen.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Tố Như',
        'wedding_date' => now()->addDays(5),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'gia-huy-to-nhu-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'photo_drop' => [
                'enabled' => true,
                'auto_approve' => true,
            ],
        ],
    ]);
});

test('khách truy cập được trang photo drop công khai của đám cưới', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Minh',
        'phone' => '0901234567',
        'guest_token' => 'token-test-'.uniqid(),
    ]);

    $response = $this->get(route('photo_drop.show', [
        'slug' => $this->invitation->slug,
        'token' => $guest->guest_token,
    ]));

    $response->assertStatus(200);
    $response->assertSee('LIVE GUEST PHOTO DROP');
    $response->assertSee('Gia Huy &amp; Tố Như', false);
    $response->assertSee('Nguyễn Văn Minh');
});

test('khách có thể tải lên ảnh hợp lệ và ảnh được lưu trữ an toàn', function () {
    $file1 = UploadedFile::fake()->image('party1.jpg', 800, 600);
    $file2 = UploadedFile::fake()->image('party2.png', 1024, 768);

    $response = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
        'uploader_name' => 'Bác Năm Họ Nhà Gái',
        'caption' => 'Hai cháu đẹp đôi quá, mãi hạnh phúc nhé!',
        'photos' => [$file1, $file2],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'count' => 2,
    ]);

    $this->assertDatabaseCount('guest_photos', 2);
    $photo = GuestPhoto::first();
    expect($photo->wedding_id)->toBe($this->wedding->id)
        ->and($photo->uploader_name)->toBe('Bác Năm Họ Nhà Gái')
        ->and($photo->caption)->toBe('Hai cháu đẹp đôi quá, mãi hạnh phúc nhé!')
        ->and($photo->is_approved)->toBeTrue();

    // Xác nhận file đã được lưu trong storage disk public
    $relativePath = str_replace(Storage::disk('public')->url(''), '', $photo->photo_url);
    $relativePath = ltrim($relativePath, '/');
    Storage::disk('public')->assertExists($relativePath);
});

test('hệ thống từ chối tải lên file nguy hiểm hoặc không phải ảnh', function () {
    $badFile = UploadedFile::fake()->create('malicious.php', 100, 'text/x-php');

    $response = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
        'uploader_name' => 'Hacker',
        'photos' => [$badFile],
    ]);

    $response->assertStatus(422);
    $this->assertDatabaseCount('guest_photos', 0);
});

test('hệ thống chặn tải lên quá 10 ảnh trong một lần gửi', function () {
    $photos = [];
    for ($i = 0; $i < 11; $i++) {
        $photos[] = UploadedFile::fake()->image("pic_{$i}.jpg");
    }

    $response = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
        'uploader_name' => 'Khách Chụp Nhiều',
        'photos' => $photos,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['photos']);
});

test('hệ thống chặn tải ảnh khi cặp đôi đóng chức năng nhận ảnh', function () {
    $this->invitation->update([
        'custom_config' => [
            'photo_drop' => [
                'enabled' => false,
            ],
        ],
    ]);

    $file = UploadedFile::fake()->image('wedding.jpg');

    $response = $this->postJson(route('photo_drop.upload', $this->invitation->slug), [
        'uploader_name' => 'Bạn Học Cũ',
        'photos' => [$file],
    ]);

    $response->assertStatus(403);
    $response->assertJson([
        'success' => false,
        'message' => 'Hộp nhận ảnh kỷ niệm đã tạm thời đóng nhận thêm ảnh.',
    ]);
});

test('chủ sở hữu truy cập được trang quản lý album ảnh khách mời', function () {
    GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Chị Thư',
        'photo_url' => 'https://mayhen.vn/storage/test.jpg',
        'file_size' => 1024 * 500,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('photo_drop.manage', $this->invitation->id));
    $response->assertStatus(200);
    $response->assertSee('Album Khoảnh Khắc Do Khách Mời Chụp');
    $response->assertSee('Chị Thư');
    $response->assertSee('MÃ QR ĐẶT TẠI BÀN TIỆC');
});

test('người lạ không có quyền truy cập trang quản lý album ảnh của cặp đôi', function () {
    $stranger = User::factory()->create();

    $response = $this->actingAs($stranger)->get(route('photo_drop.manage', $this->invitation->id));
    $response->assertStatus(404);
});

test('cặp đôi có thể bật hoặc tắt trạng thái duyệt hiển thị của ảnh', function () {
    $photo = GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Anh Long',
        'photo_url' => 'https://mayhen.vn/storage/test.jpg',
        'file_size' => 1024 * 300,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->from(route('photo_drop.manage', $this->invitation->id))
        ->post(route('photo_drop.toggleApproval', [
            'id' => $this->invitation->id,
            'photoId' => $photo->id,
        ]));

    $response->assertRedirect(route('photo_drop.manage', $this->invitation->id));
    expect($photo->fresh()->is_approved)->toBeFalse();

    // Bật lại
    $this->actingAs($this->user)
        ->post(route('photo_drop.toggleApproval', [
            'id' => $this->invitation->id,
            'photoId' => $photo->id,
        ]));
    expect($photo->fresh()->is_approved)->toBeTrue();
});

test('cặp đôi có thể xóa ảnh và xóa file vật lý tương ứng', function () {
    $file = UploadedFile::fake()->image('to_delete.jpg');
    $path = $file->storeAs("guest_photos/{$this->wedding->id}", 'delete_me.jpg', 'public');

    $photo = GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Người Xóa',
        'photo_url' => Storage::disk('public')->url($path),
        'file_size' => $file->getSize(),
        'is_approved' => true,
    ]);

    Storage::disk('public')->assertExists($path);

    $response = $this->actingAs($this->user)
        ->from(route('photo_drop.manage', $this->invitation->id))
        ->delete(route('photo_drop.destroy', [
            'id' => $this->invitation->id,
            'photoId' => $photo->id,
        ]));

    $response->assertRedirect(route('photo_drop.manage', $this->invitation->id));
    $this->assertDatabaseMissing('guest_photos', ['id' => $photo->id]);
    Storage::disk('public')->assertMissing($path);
});

test('cặp đôi có thể tải xuống file ZIP đóng gói toàn bộ ảnh của khách', function () {
    $file1 = UploadedFile::fake()->image('mem1.jpg', 100, 100);
    $path1 = $file1->storeAs("guest_photos/{$this->wedding->id}", 'mem1.jpg', 'public');

    $file2 = UploadedFile::fake()->image('mem2.jpg', 100, 100);
    $path2 = $file2->storeAs("guest_photos/{$this->wedding->id}", 'mem2.jpg', 'public');

    GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Khách A',
        'photo_url' => Storage::disk('public')->url($path1),
        'file_size' => $file1->getSize(),
        'is_approved' => true,
    ]);

    GuestPhoto::create([
        'wedding_id' => $this->wedding->id,
        'uploader_name' => 'Khách B',
        'photo_url' => Storage::disk('public')->url($path2),
        'file_size' => $file2->getSize(),
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('photo_drop.downloadZip', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/zip');
});
