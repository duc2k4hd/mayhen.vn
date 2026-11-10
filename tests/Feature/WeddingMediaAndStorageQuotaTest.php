<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingStorageService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Storage Test Template',
        'slug' => 'storage-test-template-'.uniqid(),
        'blade_view' => 'client.invitations.celestial.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb-default.jpg',
        'default_config' => ['tier' => 'free'],
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Vy',
        'wedding_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
        'venue_name' => 'GEM Center',
        'venue_address' => '8 Nguyễn Bỉnh Khiêm, Quận 1',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-vy-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'tier' => 'free',
        ],
    ]);
});

test('owner can view media library and storage quota cockpit', function () {
    $this->actingAs($this->user);

    // Tạo 1 file ảnh giả lập thuộc thiệp
    $photoPath = 'weddings/photos/nam_vy_cover.jpg';
    Storage::disk('public')->put($photoPath, 'fake-image-content-for-testing');

    $this->wedding->update([
        'cover_photo_url' => asset('storage/'.$photoPath),
    ]);

    $response = $this->get(route('account.wedding_media.index', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Kho Tệp Media');
    $response->assertSee('Quản Lý Dung Lượng Lưu Trữ Cưới');
    $response->assertSee('Tình Trạng Dung Lượng Bộ Nhớ');
    $response->assertSee('50 MB'); // Hạn mức gói Free
    $response->assertSee('nam_vy_cover.jpg');
});

test('quota api returns real-time storage metrics', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('account.wedding_media.quota', $this->invitation->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'limit_mb' => 50,
                'is_exceeded' => false,
            ],
        ]);
});

test('uploading asset via studio fails with 422 when quota is exceeded', function () {
    $this->actingAs($this->user);

    // Mock service báo hết dung lượng
    $mockService = Mockery::mock(WeddingStorageService::class);
    $mockService->shouldReceive('canUpload')->andReturn(false);
    $mockService->shouldReceive('getStorageUsage')->andReturn([
        'used_mb' => 50.1,
        'limit_mb' => 50,
        'used_bytes' => 52500000,
        'limit_bytes' => 52428800,
        'remaining_mb' => 0,
        'percent' => 100,
        'is_exceeded' => true,
        'total_files_count' => 15,
    ]);
    app()->instance(WeddingStorageService::class, $mockService);

    $file = UploadedFile::fake()->image('wedding_huge_photo.jpg', 2000, 2000);

    $response = $this->postJson(route('invitation.uploadAsset'), [
        'file' => $file,
        'invitation_id' => $this->invitation->id,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'quota_exceeded' => true,
        ]);
});

test('owner can safely delete media file to release storage', function () {
    $this->actingAs($this->user);

    $filePath = 'weddings/photos/delete_me.jpg';
    Storage::disk('public')->put($filePath, 'image-data');
    expect(Storage::disk('public')->exists($filePath))->toBeTrue();

    $this->wedding->update([
        'extra_data' => [
            'gallery' => [
                asset('storage/'.$filePath),
            ],
        ],
    ]);

    $response = $this->delete(route('account.wedding_media.destroy', $this->invitation->id), [
        'storage_path' => $filePath,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Kiểm tra file vật lý đã bị xóa
    expect(Storage::disk('public')->exists($filePath))->toBeFalse();

    // Kiểm tra gallery của wedding đã được gỡ link
    $this->wedding->refresh();
    $gallery = $this->wedding->extra_data['gallery'] ?? [];
    expect($gallery)->toBeEmpty();
});

test('path traversal attempt in delete media file is strictly rejected', function () {
    $this->actingAs($this->user);

    $response = $this->delete(route('account.wedding_media.destroy', $this->invitation->id), [
        'storage_path' => '../../config/database.php',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('non-owner cannot access media library of another couple (tenant isolation 404)', function () {
    $this->actingAs($this->otherUser);

    $response = $this->get(route('account.wedding_media.index', $this->invitation->id));

    $response->assertNotFound();
});
