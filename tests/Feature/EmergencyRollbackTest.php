<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\InvitationTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Rollback',
        'slug' => 'mau-rollback-'.uniqid(),
        'description' => 'Test Template Rollback',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->owner->id,
        'groom_name' => 'Thế Anh',
        'bride_name' => 'Mỹ Duyên',
        'wedding_date' => '2026-12-10',
        'venue_name' => 'White Palace Phạm Văn Đồng',
        'venue_address' => '108 Phạm Văn Đồng, Hiệp Bình Chánh',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'the-anh-my-duyen-'.uniqid(),
        'is_published' => true,
        'revision_id' => 1,
        'custom_config' => [
            'color_theme' => 'ruby',
        ],
    ]);
});

test('updating invitation automatically captures previous published snapshot', function () {
    $templateService = app(InvitationTemplateService::class);

    // Cặp đôi sửa ngày cưới từ 2026-12-10 sang 2026-12-25 và đổi địa điểm
    $updatedInvitation = $templateService->saveInvitation(
        $this->owner,
        $this->template->id,
        [
            'wedding' => [
                'groom_name' => 'Thế Anh',
                'bride_name' => 'Mỹ Duyên',
                'wedding_date' => '2026-12-25',
                'venue_name' => 'Gem Center',
                'venue_address' => '8 Nguyễn Bỉnh Khiêm, Đa Kao',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'emerald',
                ],
            ],
        ],
        $this->invitation->id,
        $this->invitation->revision_id
    );

    $updatedInvitation->refresh();
    $this->wedding->refresh();

    // Dữ liệu mới đã được áp dụng
    expect($this->wedding->wedding_date->format('Y-m-d'))->toBe('2026-12-25');
    expect($this->wedding->venue_name)->toBe('Gem Center');

    // Snapshot bản cũ được bảo lưu trọn vẹn trong custom_config
    $snapshot = $updatedInvitation->custom_config['_previous_published_snapshot'] ?? null;
    expect($snapshot)->not->toBeNull();
    expect($snapshot['wedding']['wedding_date'])->toBe('2026-12-10');
    expect($snapshot['wedding']['venue_name'])->toBe('White Palace Phạm Văn Đồng');
    expect($snapshot['custom_config']['color_theme'])->toBe('ruby');
});

test('emergency rollback restores wedding and configuration to previous snapshot successfully', function () {
    $templateService = app(InvitationTemplateService::class);

    // 1. Cập nhật thiệp lần đầu để tạo snapshot
    $templateService->saveInvitation(
        $this->owner,
        $this->template->id,
        [
            'wedding' => [
                'groom_name' => 'Thế Anh',
                'bride_name' => 'Mỹ Duyên',
                'wedding_date' => '2026-12-25',
                'venue_name' => 'Gem Center',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'emerald',
                ],
            ],
        ],
        $this->invitation->id,
        $this->invitation->revision_id
    );

    // 2. Cặp đôi phát hiện sai sót và bấm Khôi Phục Khẩn Cấp (Emergency Rollback)
    $response = $this->actingAs($this->owner)->post(route('account.invitations.rollback', $this->invitation->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $this->wedding->refresh();

    // Dữ liệu ngày cưới và địa điểm đã được khôi phục về bản trước (2026-12-10 tại White Palace)
    expect($this->wedding->wedding_date->format('Y-m-d'))->toBe('2026-12-10');
    expect($this->wedding->venue_name)->toBe('White Palace Phạm Văn Đồng');
    expect($this->invitation->custom_config['color_theme'])->toBe('ruby');
    expect($this->invitation->revision_id)->toBe(3); // Tăng revision an toàn
});

test('stranger cannot rollback invitation of another user', function () {
    $response = $this->actingAs($this->otherUser)->post(route('account.invitations.rollback', $this->invitation->id));

    $response->assertNotFound();
});

test('emergency rollback preserves draft_config without erasing ongoing studio drafts', function () {
    $templateService = app(InvitationTemplateService::class);

    // 1. Cập nhật thiệp lần đầu để tạo snapshot
    $templateService->saveInvitation(
        $this->owner,
        $this->template->id,
        [
            'wedding' => [
                'groom_name' => 'Thế Anh',
                'bride_name' => 'Mỹ Duyên',
                'wedding_date' => '2026-12-25',
                'venue_name' => 'Gem Center',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'emerald',
                ],
            ],
        ],
        $this->invitation->id,
        $this->invitation->revision_id
    );

    // 2. Cặp đôi đang mở Studio và có bản nháp chưa xuất bản
    $this->invitation->update([
        'draft_config' => [
            'unsaved_notes' => 'Ghi chú nháp dở dang tại Studio',
        ],
    ]);

    // 3. Thực hiện rollback
    $response = $this->actingAs($this->owner)->post(route('account.invitations.rollback', $this->invitation->id));
    $response->assertRedirect();

    $this->invitation->refresh();

    // Bản công khai đã về snapshot cũ (ruby), nhưng bản nháp draft_config vẫn được bảo toàn nguyên vẹn
    expect($this->invitation->custom_config['color_theme'])->toBe('ruby');
    expect($this->invitation->draft_config)->not->toBeNull();
    expect($this->invitation->draft_config['unsaved_notes'])->toBe('Ghi chú nháp dở dang tại Studio');
});

test('emergency rollback supports redo swap allowing user to toggle between versions safely', function () {
    $templateService = app(InvitationTemplateService::class);

    // 1. Cập nhật thiệp để tạo snapshot V1
    $templateService->saveInvitation(
        $this->owner,
        $this->template->id,
        [
            'wedding' => [
                'groom_name' => 'Thế Anh',
                'bride_name' => 'Mỹ Duyên',
                'wedding_date' => '2026-12-25',
                'venue_name' => 'Gem Center',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'emerald',
                ],
            ],
        ],
        $this->invitation->id,
        $this->invitation->revision_id
    );

    // 2. Rollback lần 1 -> Về bản V1 (White Palace / ruby)
    $this->actingAs($this->owner)->post(route('account.invitations.rollback', $this->invitation->id));
    $this->wedding->refresh();
    $this->invitation->refresh();
    expect($this->wedding->venue_name)->toBe('White Palace Phạm Văn Đồng');
    expect($this->invitation->custom_config['color_theme'])->toBe('ruby');

    // 3. Người dùng nhận ra bản V2 mới đúng, bấm Rollback lần 2 (Redo Swap) -> Quay lại Gem Center / emerald
    $this->actingAs($this->owner)->post(route('account.invitations.rollback', $this->invitation->id));
    $this->wedding->refresh();
    $this->invitation->refresh();
    expect($this->wedding->venue_name)->toBe('Gem Center');
    expect($this->invitation->custom_config['color_theme'])->toBe('emerald');
});

test('emergency rollback api returns json response when requested with json header', function () {
    $templateService = app(InvitationTemplateService::class);

    $templateService->saveInvitation(
        $this->owner,
        $this->template->id,
        [
            'wedding' => [
                'groom_name' => 'Thế Anh',
                'bride_name' => 'Mỹ Duyên',
                'wedding_date' => '2026-12-25',
                'venue_name' => 'Gem Center',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'emerald',
                ],
            ],
        ],
        $this->invitation->id,
        $this->invitation->revision_id
    );

    $response = $this->actingAs($this->owner)->postJson(route('account.invitations.rollback', $this->invitation->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Đã khôi phục thành công thiệp cưới về phiên bản an toàn trước đó!',
    ]);
    expect($response->json('revision_id'))->toBeGreaterThan(1);
});
