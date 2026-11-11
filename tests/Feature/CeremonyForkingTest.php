<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\CeremonyForkService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Fork Ceremony',
        'slug' => 'mau-test-fork-ceremony-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Trần Thế Vinh',
        'bride_name' => 'Nguyễn Phương Thảo',
        'wedding_date' => now()->addMonths(1)->toDateString(),
        'venue_name' => 'Trung Tâm Hội Nghị Asiana Plaza',
        'venue_address' => '45-47 Phan Đăng Lưu, Bình Thạnh, TP.HCM',
        'extra_data' => [
            'groom_father' => 'Trần Văn Hùng',
            'groom_mother' => 'Nguyễn Thị Mai',
            'bride_father' => 'Nguyễn Văn Nam',
            'bride_mother' => 'Lê Thị Lan',
            'bride_party_venue' => 'Tư gia Nhà Gái — TP. Đà Nẵng',
            'bride_party_address' => '120 Bạch Đằng, Hải Châu, Đà Nẵng',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'the-vinh-phuong-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Thế Vinh & Phương Thảo',
        'is_published' => true,
        'custom_config' => [
            'theme_color' => '#C5A880',
            'font_family' => 'Playfair Display',
        ],
    ]);
});

test('khách vãng lai chưa đăng nhập bị chuyển hướng khi truy cập API options hoặc fork', function () {
    $this->get(route('account.ceremony.fork_options', $this->invitation->id))
        ->assertRedirect(route('login'));

    $this->post(route('account.ceremony.fork', $this->invitation->id), ['mode' => 'bride_side'])
        ->assertRedirect(route('login'));
});

test('người dùng khác không có quyền thao tác trên thiệp của người khác (403)', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('account.ceremony.fork_options', $this->invitation->id))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('account.ceremony.fork', $this->invitation->id), ['mode' => 'bride_side'])
        ->assertForbidden();
});

test('chủ sở hữu lấy thành công danh sách tùy chọn nhân bản kèm dữ liệu tiệc cưới', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('account.ceremony.fork_options', $this->invitation->id));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'invitation' => [
                'id' => $this->invitation->id,
                'slug' => $this->invitation->slug,
            ],
            'wedding' => [
                'groom_name' => 'Trần Thế Vinh',
                'bride_name' => 'Nguyễn Phương Thảo',
            ],
        ]);

    $modes = $response->json('modes');
    expect($modes)->toBeArray()->toHaveCount(4);
});

test('nhân bản thiệp thành công cho Nhà Gái (Lễ Vu Quy) với cấu hình ưu tiên cô dâu', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.ceremony.fork', $this->invitation->id), [
            'mode' => 'bride_side',
            'separate_project' => 1,
            'venue_name' => 'Sảnh Tiệc Kim Cương — Đà Nẵng',
            'venue_address' => '200 Nguyễn Văn Linh, Đà Nẵng',
            'wedding_date' => now()->addDays(20)->toDateString(),
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $newInvId = $response->json('invitation.id');
    $newInvitation = Invitation::with('wedding')->findOrFail($newInvId);

    expect($newInvitation->title)->toContain('Thiệp Vu Quy')
        ->and($newInvitation->title)->toContain('Nguyễn Phương Thảo & Trần Thế Vinh')
        ->and($newInvitation->wedding_id)->not->toBe($this->wedding->id) // Tách dự án riêng
        ->and($newInvitation->wedding->venue_name)->toBe('Sảnh Tiệc Kim Cương — Đà Nẵng')
        ->and($newInvitation->wedding->extra_data['ceremony_type'])->toBe('vu_quy')
        ->and($newInvitation->wedding->extra_data['display_order_couple'])->toBe('bride_first')
        ->and($newInvitation->wedding->extra_data['display_order_family'])->toBe('bride_first')
        ->and($newInvitation->custom_config['ceremony_fork']['mode'])->toBe('bride_side')
        ->and($newInvitation->custom_config['ceremony_fork']['forked_from_id'])->toBe($this->invitation->id);
});

test('nhân bản thiệp thành công cho Nhà Trai (Lễ Thành Hôn)', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.ceremony.fork', $this->invitation->id), [
            'mode' => 'groom_side',
            'separate_project' => 0, // Dùng chung wedding gốc
        ]);

    $response->assertOk()
        ->assertJson(['success' => true]);

    $newInvId = $response->json('invitation.id');
    $newInvitation = Invitation::with('wedding')->findOrFail($newInvId);

    expect($newInvitation->title)->toContain('Thiệp Thành Hôn')
        ->and($newInvitation->wedding_id)->toBe($this->wedding->id) // Chung wedding
        ->and($newInvitation->custom_config['ceremony_fork']['mode'])->toBe('groom_side');
});

test('nhân bản thiệp thành công cho Tiệc Báo Hỷ và Bản Sao Lưu Dự Phòng', function () {
    // 1. Tiệc Báo Hỷ
    $resParty = CeremonyForkService::fork($this->wedding, $this->invitation, [
        'mode' => 'party_side',
        'separate_project' => false,
    ]);
    expect($resParty['invitation']->title)->toContain('Thiệp Báo Hỷ')
        ->and($resParty['invitation']->custom_config['ceremony_fork']['mode'])->toBe('party_side');

    // 2. Bản sao lưu
    $resBackup = CeremonyForkService::fork($this->wedding, $this->invitation, [
        'mode' => 'backup_copy',
        'separate_project' => false,
    ]);
    expect($resBackup['invitation']->title)->toContain('(Bản Sao)')
        ->and($resBackup['invitation']->custom_config['ceremony_fork']['mode'])->toBe('backup_copy');
});

