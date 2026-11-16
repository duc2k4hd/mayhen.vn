<?php

use App\Models\InvitationTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_custom_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->template = InvitationTemplate::create([
        'name' => 'Bản Giao Hưởng Mùa Thu',
        'slug' => 'ban-giao-huong-mua-thu',
        'category' => 'luxury',
        'thumbnail_url' => '/mau-thiep/ban-giao-huong-mua-thu/assets/images/thumb.webp',
        'is_active' => true,
        'is_featured' => true,
        'default_config' => [
            'groom_name' => 'Nguyễn Minh Quân',
            'bride_name' => 'Trần Thị Thu Hà',
            'wedding_date' => '2026-11-20',
            'heading_font' => 'Playfair Display',
            'music_url' => '/assets/audio/a-thousand-years.mp3',
        ],
    ]);
});

test('admin can update full showcase configuration including fonts, music, couple and 10 gallery photos', function () {
    $response = $this->actingAs($this->admin)->put(route('admin.templates.update', $this->template->id), [
        'name' => 'Bản Giao Hưởng Mùa Thu Tuyệt Mỹ',
        'category' => 'luxury',
        'tier' => 'pro',
        'description' => 'Mẫu thiệp sang trọng bậc nhất mùa cưới 2026',
        'heading_font' => 'Cormorant Garamond',
        'body_font' => 'Lora',
        'script_font' => 'Alex Brush',
        'music_url' => '/assets/audio/canon-in-d.mp3',
        'groom_name' => 'Phạm Hoàng Long',
        'bride_name' => 'Lê Diệu Linh',
        'wedding_date' => '2026-12-25',
        'party_venue' => 'White Palace Hoàng Văn Thụ',
        'groom_bank_name' => 'Vietcombank',
        'groom_bank_account' => '001100223344',
        'groom_bank_owner' => 'PHAM HOANG LONG',
        'gallery_url' => [
            '/assets/images/gallery-01.jpg',
            '/assets/images/gallery-02.jpg',
            '/assets/images/gallery-03.jpg',
            '/assets/images/gallery-04.jpg',
            '/assets/images/gallery-05.jpg',
            '/assets/images/gallery-06.jpg',
            '/assets/images/gallery-07.jpg',
            '/assets/images/gallery-08.jpg',
            '/assets/images/gallery-09.jpg',
            '/assets/images/gallery-10.jpg',
        ],
    ]);

    $response->assertRedirect(route('admin.templates.index'));
    $response->assertSessionHas('success');

    $fresh = $this->template->fresh();
    expect($fresh->name)->toBe('Bản Giao Hưởng Mùa Thu Tuyệt Mỹ');
    expect($fresh->tier)->toBe('pro');
    expect($fresh->default_config['heading_font'])->toBe('Cormorant Garamond');
    expect($fresh->default_config['body_font'])->toBe('Lora');
    expect($fresh->default_config['script_font'])->toBe('Alex Brush');
    expect($fresh->default_config['music_url'])->toBe('/assets/audio/canon-in-d.mp3');
    expect($fresh->default_config['groom_name'])->toBe('Phạm Hoàng Long');
    expect($fresh->default_config['bride_name'])->toBe('Lê Diệu Linh');
    expect($fresh->default_config['wedding_date'])->toBe('2026-12-25');
    expect($fresh->default_config['groom_bank_account'])->toBe('001100223344');
    expect(count($fresh->default_config['gallery']))->toBe(10);
    expect($fresh->default_config['gallery'][9])->toBe('/assets/images/gallery-10.jpg');
});

test('admin uploading new file deletes previous file from storage disk to prevent storage bloat', function () {
    Storage::fake('public');

    // 1. First upload: create a mock file in storage
    $initialFile = UploadedFile::fake()->image('first-hero.jpg', 600, 600);
    $initialPath = $initialFile->storeAs('templates/ban-giao-huong-mua-thu/photos', 'first-hero.jpg', 'public');
    $initialStorageUrl = '/storage/'.$initialPath;

    Storage::disk('public')->assertExists($initialPath);

    // Update template config with initial storage path
    $this->template->update([
        'default_config' => array_merge($this->template->default_config ?? [], [
            'hero_cover_url' => $initialStorageUrl,
        ]),
    ]);

    // 2. Second upload: upload a replacement file
    $replacementFile = UploadedFile::fake()->image('second-hero.jpg', 800, 800);

    $response = $this->actingAs($this->admin)->put(route('admin.templates.update', $this->template->id), [
        'name' => $this->template->name,
        'category' => $this->template->category,
        'tier' => 'pro',
        'hero_cover_file' => $replacementFile,
    ]);

    $response->assertRedirect(route('admin.templates.index'));

    // Old file in storage must be deleted
    Storage::disk('public')->assertMissing($initialPath);

    // New file must be present in storage and recorded in default_config
    $fresh = $this->template->fresh();
    $newCoverUrl = $fresh->default_config['hero_cover_url'] ?? '';
    expect($newCoverUrl)->toStartWith('/storage/templates/ban-giao-huong-mua-thu/photos/');
    $newRelativePath = substr($newCoverUrl, strlen('/storage/'));
    Storage::disk('public')->assertExists($newRelativePath);
});
