<?php

use App\Models\User;
use App\Models\Wedding;
use App\Services\MediaLibraryService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->user = User::factory()->create([
        'role' => 'user',
    ]);
});

test('guest cannot access admin media library', function () {
    $this->get(route('admin.media.index'))->assertRedirect(route('admin.login'));
});

test('regular user cannot access admin media library', function () {
    $this->actingAs($this->user)->get(route('admin.media.index'))->assertRedirect(route('admin.login'));
});

test('admin can access media library and view storage statistics', function () {
    // Tạo sẵn một số tệp mẫu trong storage giả lập
    Storage::disk('public')->put('client/images/logo.png', 'fake image content');
    Storage::disk('public')->put('client/musics/romantic.mp3', 'fake mp3 content');

    $response = $this->actingAs($this->admin)->get(route('admin.media.index'));

    $response->assertStatus(200);
    $response->assertSee('Thư Viện Phương Tiện');
    $response->assertSee('Tổng Dung Lượng');
    $response->assertSee('Kho Nhạc Nền (MP3)');
});

test('admin can filter media by category and search keyword', function () {
    Storage::disk('public')->put('client/musics/wedding_song.mp3', 'song content');
    Storage::disk('public')->put('client/images/wedding_banner.webp', 'banner content');

    $response = $this->actingAs($this->admin)->get(route('admin.media.index', [
        'category' => 'musics',
        'search' => 'wedding_song',
    ]));

    $response->assertStatus(200);
    $response->assertSee('wedding_song.mp3');
});

test('admin can upload new media file to selected directory', function () {
    $fakeImage = UploadedFile::fake()->image('banner_atelier.jpg', 800, 600);

    $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
        'file' => $fakeImage,
        'folder' => 'client/images',
    ]);

    $response->assertSessionHas('success');

    // Kiểm tra tệp đã lưu trong thư mục
    $files = Storage::disk('public')->files('client/images');
    expect(count($files))->toBeGreaterThan(0);
});

test('upload validates file format and size', function () {
    $badFile = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

    $response = $this->actingAs($this->admin)->post(route('admin.media.upload'), [
        'file' => $badFile,
    ]);

    $response->assertSessionHasErrors('file');
});

test('admin can delete media file from storage', function () {
    Storage::disk('public')->put('client/images/test_to_delete.png', 'content to delete');
    expect(Storage::disk('public')->exists('client/images/test_to_delete.png'))->toBeTrue();

    $response = $this->actingAs($this->admin)->delete(route('admin.media.destroy'), [
        'path' => base64_encode('client/images/test_to_delete.png'),
    ]);

    $response->assertSessionHas('success');
    expect(Storage::disk('public')->exists('client/images/test_to_delete.png'))->toBeFalse();
});

test('orphaned media scanner detects unreferenced files and allows purge', function () {
    // 1. Tệp được tham chiếu trong database
    $referencedFile = 'weddings/active_wedding_photo.jpg';
    Storage::disk('public')->put($referencedFile, 'wedding photo content');

    Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Chú Rể Test',
        'bride_name' => 'Cô Dâu Test',
        'wedding_date' => now()->toDateString(),
        'cover_photo_url' => '/storage/'.$referencedFile,
    ]);

    // 2. Tệp mồ côi (không nằm trong database)
    $orphanFile = 'weddings/orphaned_abandoned_photo.jpg';
    Storage::disk('public')->put($orphanFile, 'abandoned content');

    // Quét phát hiện
    $scanData = MediaLibraryService::detectOrphanedFiles();
    $orphanPaths = array_column($scanData['orphans'], 'path');

    expect($orphanPaths)->toContain($orphanFile);
    expect($orphanPaths)->not->toContain($referencedFile);

    // Màn hình quét tệp mồ côi
    $response = $this->actingAs($this->admin)->get(route('admin.media.orphans'));
    $response->assertStatus(200);
    $response->assertSee('orphaned_abandoned_photo.jpg');

    // Dọn dẹp hàng loạt tệp mồ côi
    $purgeRes = $this->actingAs($this->admin)->post(route('admin.media.purge_orphans'), [
        'paths' => [base64_encode($orphanFile)],
    ]);

    $purgeRes->assertRedirect(route('admin.media.orphans'));
    $purgeRes->assertSessionHas('success');

    // Tệp mồ côi đã bị xóa, tệp có tham chiếu vẫn an toàn
    expect(Storage::disk('public')->exists($orphanFile))->toBeFalse();
    expect(Storage::disk('public')->exists($referencedFile))->toBeTrue();
});
