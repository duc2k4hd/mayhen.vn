<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\PaperInvitationScan;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Scan Thiệp Giấy',
        'slug' => 'mau-test-scan-thiep-giay-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Tên Tạm Chú Rể',
        'bride_name' => 'Tên Tạm Cô Dâu',
        'wedding_date' => now()->addMonths(2)->toDateString(),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-scan-test-'.uniqid(),
        'status' => 'draft',
        'is_published' => false,
    ]);
});

test('guest cannot access paper scan page', function () {
    $response = $this->get(route('account.paper_scan.create', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('user cannot access paper scan of another user', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->get(route('account.paper_scan.create', $this->invitation->id));

    $response->assertStatus(404);
});

test('user can upload paper invitation image and parse data', function () {
    $file = UploadedFile::fake()->image('thiep_cuoi_in_truyen_thong.jpg', 1200, 800);

    $response = $this->actingAs($this->user)
        ->post(route('account.paper_scan.store', $this->invitation->id), [
            'image' => $file,
        ]);

    $this->assertDatabaseHas('paper_invitation_scans', [
        'user_id' => $this->user->id,
        'invitation_id' => $this->invitation->id,
        'status' => 'parsed',
    ]);

    $scan = PaperInvitationScan::where('invitation_id', $this->invitation->id)->first();
    expect($scan)->not->toBeNull();
    expect($scan->confidence_score)->toBeGreaterThan(50);
    expect($scan->parsed_data)->toHaveKey('groom_name');
    expect($scan->parsed_data)->toHaveKey('bride_name');

    Storage::disk('public')->assertExists($scan->image_path);

    $response->assertRedirect(route('account.paper_scan.review', [$this->invitation->id, $scan->id]));
    $response->assertSessionHas('success');
});

test('upload validates file format and size', function () {
    $pdfFile = UploadedFile::fake()->create('tai_lieu.pdf', 500, 'application/pdf');

    $response = $this->actingAs($this->user)
        ->post(route('account.paper_scan.store', $this->invitation->id), [
            'image' => $pdfFile,
        ]);

    $response->assertSessionHasErrors('image');
});

test('user can review and apply parsed data to wedding', function () {
    $scan = PaperInvitationScan::create([
        'user_id' => $this->user->id,
        'invitation_id' => $this->invitation->id,
        'wedding_id' => $this->wedding->id,
        'image_path' => 'paper_scans/sample.jpg',
        'parsed_data' => [
            'groom_name' => 'Nguyễn Minh Quân',
            'bride_name' => 'Lê Hoàng Yến',
        ],
        'confidence_score' => 95.0,
        'status' => 'parsed',
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('account.paper_scan.apply', [$this->invitation->id, $scan->id]), [
            'groom_name' => 'Nguyễn Minh Quân',
            'bride_name' => 'Lê Hoàng Yến',
            'groom_father' => 'Nguyễn Văn An',
            'groom_mother' => 'Trần Thị Bình',
            'bride_father' => 'Lê Văn Cường',
            'bride_mother' => 'Phạm Thị Dung',
            'wedding_date' => '2026-12-28',
            'party_time' => '11:30',
            'venue_name' => 'Trung Tâm Hội Nghị White Palace',
            'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận',
            'quote' => 'Trăm năm một chữ đồng lòng',
        ]);

    $response->assertRedirect(route('invitation.create', ['edit' => $this->invitation->slug]));
    $response->assertSessionHas('success');

    $this->wedding->refresh();
    expect($this->wedding->groom_name)->toBe('Nguyễn Minh Quân');
    expect($this->wedding->bride_name)->toBe('Lê Hoàng Yến');
    expect($this->wedding->venue_name)->toBe('Trung Tâm Hội Nghị White Palace');

    $extra = is_array($this->wedding->extra_data) ? $this->wedding->extra_data : json_decode($this->wedding->extra_data, true);
    expect($extra['groom_father'])->toBe('Nguyễn Văn An');
    expect($extra['bride_mother'])->toBe('Phạm Thị Dung');
    expect($extra['event_party_time'])->toBe('11:30');

    $scan->refresh();
    expect($scan->status)->toBe('applied');
    expect($scan->applied_at)->not->toBeNull();
});
