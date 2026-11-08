<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\PortableWeddingArchiveService;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Rose Elegance',
        'slug' => 'rose-elegance-'.uniqid(),
        'blade_view' => 'client.invitations.rose.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quốc Hưng',
        'bride_name' => 'Hải Yến',
        'wedding_date' => '2026-12-15',
        'venue_name' => 'The Adora Grand View',
        'venue_address' => '431 Hoàng Văn Thụ, Tân Bình, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hung-yen-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);

    // Tạo lời chúc mẫu
    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hai Hoàng',
        'message' => 'Chúc hai cháu trăm năm đầu bạc răng long, con đàn cháu đống!',
        'reply' => 'Dạ chúng con xin cảm ơn Bác Hai rất nhiều ạ!',
        'is_approved' => true,
    ]);

    // Tạo khách mời mẫu
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Anh Dũng',
        'salutation' => 'Anh',
        'group_name' => 'Đồng Nghiệp',
        'table_name' => 'Bàn 05',
        'expected_attendees' => 2,
        'is_sent' => true,
        'checked_in_at' => now(),
    ]);

    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => 'Nguyễn Anh Dũng',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);
});

it('blocks unauthorized users from downloading offline archive (IDOR defense)', function () {
    // Chưa đăng nhập -> 404
    $this->get(route('memory.offline_archive', $this->invitation->id))
        ->assertNotFound();

    // Tài khoản khác -> 404
    $this->actingAs($this->otherUser)
        ->get(route('memory.offline_archive', $this->invitation->id))
        ->assertNotFound();
});

it('downloads wedding archive zip for authorized owner with proper headers', function () {
    $response = $this->actingAs($this->user)
        ->get(route('memory.offline_archive', $this->invitation->id));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/zip');

    $disposition = $response->headers->get('Content-Disposition');
    expect($disposition)->toContain('attachment')
        ->and($disposition)->toContain('Ky_Niem_Hon_Nhan_');
});

it('generates zip archive containing index.html, json, csv, readme and sha256 manifest', function () {
    $service = app(PortableWeddingArchiveService::class);
    $result = $service->generateArchive($this->invitation);

    expect(file_exists($result['zip_path']))->toBeTrue();
    expect($result['zip_filename'])->toContain($this->invitation->slug);

    $zip = new ZipArchive;
    $opened = $zip->open($result['zip_path']);
    expect($opened)->toBeTrue();

    // 1. Kiểm tra index.html
    $htmlContent = $zip->getFromName('index.html');
    expect($htmlContent)->not->toBeEmpty();
    expect($htmlContent)->toContain('Quốc Hưng &amp; Hải Yến');
    expect($htmlContent)->toContain('Bác Hai Hoàng');
    expect($htmlContent)->toContain('The Adora Grand View');

    // 2. Kiểm tra CSV khách mời
    $csvContent = $zip->getFromName('danh_sach_khach_moi_va_rsvp.csv');
    expect($csvContent)->not->toBeEmpty();
    expect($csvContent)->toContain('Nguyễn Anh Dũng');
    expect($csvContent)->toContain('Bàn 05');
    expect($csvContent)->toContain('Đã điểm danh');

    // 3. Kiểm tra JSON lời chúc
    $jsonContent = $zip->getFromName('loi_chuc_va_chu_ky.json');
    $wishesData = json_decode($jsonContent, true);
    expect($wishesData)->toBeArray();
    expect($wishesData[0]['name'])->toBe('Bác Hai Hoàng');
    expect($wishesData[0]['reply'])->toContain('chúng con xin cảm ơn');

    // 4. Kiểm tra file README
    $readmeContent = $zip->getFromName('HUONG_DAN_LUU_TRU_KY_NIEM.txt');
    expect($readmeContent)->toContain('MÂY HẸN (MAYHEN.VN)');
    expect($readmeContent)->toContain('Quốc Hưng & Hải Yến');

    // 5. Kiểm tra manifest và mã SHA-256
    $manifestContent = $zip->getFromName('wedding_manifest.json');
    $manifestData = json_decode($manifestContent, true);
    expect($manifestData['version'])->toBe('2.0-offline');
    expect($manifestData['wedding']['groom_name'])->toBe('Quốc Hưng');
    expect($manifestData['checksums_sha256']['index.html'])->toBe(hash('sha256', $htmlContent));
    expect($manifestData['checksums_sha256']['loi_chuc_va_chu_ky.json'])->toBe(hash('sha256', $jsonContent));
    expect($manifestData['checksums_sha256']['danh_sach_khach_moi_va_rsvp.csv'])->toBe(hash('sha256', $csvContent));

    $zip->close();

    // Dọn dẹp file test
    if (file_exists($result['zip_path'])) {
        unlink($result['zip_path']);
    }
});
