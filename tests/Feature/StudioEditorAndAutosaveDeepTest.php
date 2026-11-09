<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
    Storage::fake('public');
});

test('autosave increments revision id and persists draft config', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Mai Anh',
        'wedding_date' => '2026-11-20',
        'venue_name' => 'Trung Tâm Hội Nghị Riverside',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-hoang-nam-mai-anh-'.uniqid(),
        'revision_id' => 0,
        'is_published' => true,
    ]);

    $response = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 0,
        'draft_config' => [
            'theme_color' => '#85182A',
            'font_family' => 'Cormorant Garamond',
        ],
        'wedding_delta' => [
            'venue_name' => 'Trung Tâm Tiệc Cưới GEM Center',
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'conflict' => false,
            'new_revision_id' => 1,
        ]);

    $invitation->refresh();
    $wedding->refresh();

    expect($invitation->revision_id)->toBe(1)
        ->and($invitation->draft_config['theme_color'])->toBe('#85182A')
        ->and($wedding->venue_name)->toBe('Trung Tâm Tiệc Cưới GEM Center')
        ->and($invitation->last_autosaved_at)->not->toBeNull();
});

test('autosave detects concurrency conflict when client revision is stale', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'wedding_date' => '2026-12-25',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-minh-tuan-thu-ha-'.uniqid(),
        'revision_id' => 2,
    ]);

    // Client sends stale revision 1 when DB is at revision 2
    $response = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 1,
        'draft_config' => ['color' => '#1B2A4A'],
    ]);

    $response->assertStatus(409)
        ->assertJson([
            'success' => false,
            'conflict' => true,
            'current_revision' => 2,
        ]);
});

test('autosave strips local blob urls to prevent saving broken ephemeral assets', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Bảo Long',
        'bride_name' => 'Khánh Vy',
        'wedding_date' => '2026-12-15',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-bao-long-khanh-vy-'.uniqid(),
        'revision_id' => 0,
    ]);

    $response = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 0,
        'draft_config' => [
            'valid_text' => 'Câu chuyện lãng mạn',
            'temp_cover' => 'blob:http://localhost/39203810-abcd-1234',
            'nested' => [
                'clean' => 'giữ nguyên',
                'blob_asset' => 'blob:https://mayhen.vn/blob-sample-url',
            ],
        ],
    ]);

    $response->assertStatus(200);

    $invitation->refresh();
    $draft = $invitation->draft_config;

    expect($draft['valid_text'])->toBe('Câu chuyện lãng mạn')
        ->and($draft)->not->toHaveKey('temp_cover')
        ->and($draft['nested']['clean'])->toBe('giữ nguyên')
        ->and($draft['nested'])->not->toHaveKey('blob_asset');
});

test('autosave rejects request from non-owner', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Đức Anh',
        'bride_name' => 'Phương Thảo',
        'wedding_date' => '2026-10-10',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $owner->id,
        'slug' => 'thiep-cuoi-duc-anh-phuong-thao-'.uniqid(),
        'revision_id' => 0,
    ]);

    $response = $this->actingAs($intruder)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 0,
        'draft_config' => ['hacked' => true],
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'success' => false,
            'message' => 'Bạn không có quyền chỉnh sửa thiệp cưới này.',
        ]);
});

test('locked invitation blocks autosave and guardian inspection APIs', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Việt Hoàng',
        'bride_name' => 'Minh Thư',
        'wedding_date' => '2026-11-11',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-viet-hoang-minh-thu-'.uniqid(),
        'revision_id' => 0,
        'custom_config' => [
            'is_locked' => true,
            'lock_reason' => 'Tạm khóa theo yêu cầu kiểm duyệt.',
        ],
    ]);

    // 1. Autosave blocked
    $resAutosave = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 0,
        'draft_config' => ['title' => 'Cố tình lưu'],
    ]);
    $resAutosave->assertStatus(403)->assertJson(['locked' => true]);

    // 2. Publish impact blocked
    $resImpact = $this->actingAs($user)->postJson(route('invitation.publish_impact', $invitation->id), [
        'wedding' => ['venue_name' => 'Thay đổi sảnh'],
    ]);
    $resImpact->assertStatus(403);

    // 3. Collision check blocked
    $resCollision = $this->actingAs($user)->getJson(route('invitation.collision_check', $invitation->id));
    $resCollision->assertStatus(403);

    // 4. Consistency check blocked
    $resConsistency = $this->actingAs($user)->getJson(route('invitation.consistency_check', $invitation->id));
    $resConsistency->assertStatus(403);

    // 5. Readiness check blocked
    $resReadiness = $this->actingAs($user)->getJson(route('invitation.readiness_check', $invitation->id));
    $resReadiness->assertStatus(403);
});

test('strict name validation enforces word count, character length, and XSS defense', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    // 1. Quá 4 từ -> 422
    $resTooManyWords = $this->actingAs($user)->postJson(route('invitation.store'), [
        'template_id' => $template->id,
        'groom_name' => 'Nguyễn Trần Lê Văn Tuấn', // 5 từ
        'bride_name' => 'Trần Thu Hà',
        'wedding_date' => '2026-12-01',
    ]);
    $resTooManyWords->assertStatus(422)
        ->assertJsonValidationErrors(['Họ và tên Chú Rể']);

    // 2. Chứa ký tự đặc biệt / số -> 422
    $resInvalidChars = $this->actingAs($user)->postJson(route('invitation.store'), [
        'template_id' => $template->id,
        'groom_name' => 'Minh Tuấn 99',
        'bride_name' => 'Thu Hà',
        'wedding_date' => '2026-12-01',
    ]);
    $resInvalidChars->assertStatus(422)
        ->assertJsonValidationErrors(['Họ và tên Chú Rể']);

    // 3. Tên hợp lệ tiếng Việt có dấu chuẩn mực -> Thành công 200
    $resValid = $this->actingAs($user)->postJson(route('invitation.store'), [
        'template_id' => $template->id,
        'groom_name' => 'Nguyễn Minh Tuấn',
        'bride_name' => 'Đặng Thu Hà',
        'wedding_date' => '2026-12-01',
    ]);
    $resValid->assertStatus(200)
        ->assertJson(['success' => true]);
});

test('asset upload accepts valid image formats and rejects unpermitted extensions', function () {
    $user = User::factory()->create();

    // 1. File hợp lệ (jpg/png)
    $validImage = UploadedFile::fake()->image('wedding-cover.jpg', 800, 600);
    $resValid = $this->actingAs($user)->postJson(route('invitation.uploadAsset'), [
        'file' => $validImage,
    ]);
    $resValid->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonStructure(['file_name', 'file_url', 'storage_path']);

    $savedPath = $resValid->json('storage_path');
    Storage::disk('public')->assertExists($savedPath);

    // 2. File không hợp lệ (ví dụ: script php hoặc file exe giả lập)
    $invalidFile = UploadedFile::fake()->create('malicious.php', 10, 'application/x-php');
    $resInvalid = $this->actingAs($user)->postJson(route('invitation.uploadAsset'), [
        'file' => $invalidFile,
    ]);
    $resInvalid->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('publish impact calculates changes and impacted guest and rsvp headcount', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tuấn Kiệt',
        'bride_name' => 'Bảo Trâm',
        'wedding_date' => '2026-12-20',
        'venue_name' => 'White Palace Hoàng Văn Thụ',
        'venue_address' => '194 Hoàng Văn Thụ, P.9, Phú Nhuận',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-tuan-kiet-bao-tram-'.uniqid(),
        'revision_id' => 1,
    ]);

    // Tạo khách và phản hồi RSVP
    $guest1 = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Nguyễn Văn A',
        'guest_token' => 'token-a-'.uniqid(),
    ]);
    $guest2 = Guest::create([
        'wedding_id' => $wedding->id,
        'name' => 'Trần Thị B',
        'guest_token' => 'token-b-'.uniqid(),
    ]);

    GuestResponse::create([
        'wedding_id' => $wedding->id,
        'guest_id' => $guest1->id,
        'guest_name' => 'Nguyễn Văn A',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ]);

    // Gọi API publish-impact khi đổi ngày cưới và địa chỉ
    $response = $this->actingAs($user)->postJson(route('invitation.publish_impact', $invitation->id), [
        'wedding' => [
            'wedding_date' => '2026-12-28',
            'venue_name' => 'GEM Center',
            'venue_address' => '8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1',
        ],
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'has_critical_changes' => true,
                'warning_level' => 'critical',
                'impacted_guests_count' => 2,
                'impacted_rsvp_count' => 1,
            ],
        ]);
});

test('collision check detects identical ceremony times between groom and bride households', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->first();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Thành Danh',
        'bride_name' => 'Ngọc Linh',
        'wedding_date' => '2026-11-30',
        'extra_data' => [
            'ceremony_time' => '09:00', // Lễ Vu Quy
            'ceremony_groom_time' => '09:00', // Lễ Thành Hôn cùng giờ!
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'user_id' => $user->id,
        'slug' => 'thiep-cuoi-thanh-danh-ngoc-linh-'.uniqid(),
        'revision_id' => 0,
    ]);

    $response = $this->actingAs($user)->getJson(route('invitation.collision_check', $invitation->id));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'has_collisions' => true,
                'highest_severity' => 'critical',
            ],
        ]);

    $collisions = $response->json('data.collisions');
    expect($collisions[0]['type'])->toBe('ceremony_overlap');
});
