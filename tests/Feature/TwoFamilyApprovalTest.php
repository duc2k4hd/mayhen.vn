<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\TwoFamilyApprovalService;

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
        'groom_name' => 'Bảo Long',
        'bride_name' => 'Tuyết Mai',
        'groom_parents' => 'Ông: Nguyễn Văn An — Bà: Lê Thị Lan',
        'bride_parents' => 'Ông: Trần Văn Bình — Bà: Phạm Thị Cúc',
        'wedding_date' => '2026-12-28',
        'venue_name' => 'Asiana Plaza Bình Thạnh',
        'venue_address' => '45-47 Phan Đăng Lưu, Bình Thạnh, TP.HCM',
        'extra_data' => [
            'ceremony_groom_time' => '10:30',
            'ceremony_groom_date' => '28/12/2026',
            'ceremony_bride_time' => '08:00',
            'ceremony_bride_date' => '28/12/2026',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'long-mai-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);
});

it('generates unique review tokens for groom family and bride family', function () {
    $service = app(TwoFamilyApprovalService::class);
    $data = $service->getOrCreateApprovalTokens($this->invitation);

    expect($data['groom_family']['token'])->toStartWith('gf_')
        ->and($data['bride_family']['token'])->toStartWith('bf_')
        ->and($data['all_approved'])->toBeFalse();

    expect($data['groom_family']['review_url'])->toContain('/gia-dinh-duyet/'.$data['groom_family']['token'])
        ->and($data['bride_family']['review_url'])->toContain('/gia-dinh-duyet/'.$data['bride_family']['token']);
});

it('displays family review page for parents without requiring user login', function () {
    $service = app(TwoFamilyApprovalService::class);
    $tokens = $service->getOrCreateApprovalTokens($this->invitation);
    $groomToken = $tokens['groom_family']['token'];

    $response = $this->get(route('family_approval.review', $groomToken));

    $response->assertOk()
        ->assertSee('Bảo Long &amp; Tuyết Mai', false)
        ->assertSee('Nhà Trai (Bố Mẹ Chú Rể)')
        ->assertSee('Nguyễn Văn An')
        ->assertSee('Asiana Plaza Bình Thạnh')
        ->assertSee('Bố Mẹ Đồng Ý &amp; Chúc Phúc', false);
});

it('records parents approval and blessing note successfully', function () {
    $service = app(TwoFamilyApprovalService::class);
    $tokens = $service->getOrCreateApprovalTokens($this->invitation);
    $brideToken = $tokens['bride_family']['token'];

    $response = $this->post(route('family_approval.submit', $brideToken), [
        'decision' => 'approved',
        'note' => 'Bố mẹ nhà gái đã xem kỹ thông tin. Chúc hai con hạnh phúc viên mãn!',
    ]);

    $response->assertRedirect();

    $freshInvitation = $this->invitation->fresh();
    $approvals = $freshInvitation->custom_config['family_approvals'];

    expect($approvals['bride_family']['status'])->toBe('approved')
        ->and($approvals['bride_family']['blessing_note'])->toContain('Chúc hai con hạnh phúc viên mãn!')
        ->and($approvals['bride_family']['approved_at'])->not->toBeNull();
});

it('records parents feedback note successfully for couple to revise', function () {
    $service = app(TwoFamilyApprovalService::class);
    $tokens = $service->getOrCreateApprovalTokens($this->invitation);
    $groomToken = $tokens['groom_family']['token'];

    $response = $this->postJson(route('family_approval.submit', $groomToken), [
        'decision' => 'feedback',
        'note' => 'Giờ làm lễ đón dâu bên nhà trai cần đổi sang 10:45 sáng cho đẹp giờ hoàng đạo.',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'feedback',
                'feedback' => 'Giờ làm lễ đón dâu bên nhà trai cần đổi sang 10:45 sáng cho đẹp giờ hoàng đạo.',
            ],
        ]);

    $freshInvitation = $this->invitation->fresh();
    $approvals = $freshInvitation->custom_config['family_approvals'];

    expect($approvals['groom_family']['status'])->toBe('feedback')
        ->and($approvals['groom_family']['feedback'])->toContain('10:45 sáng');
});

it('blocks unauthorized access to family approval status API for non-owners (IDOR defense)', function () {
    // Chưa đăng nhập -> redirect/401
    $this->getJson(route('family_approval.status', $this->invitation->id))
        ->assertUnauthorized();

    // Người dùng khác -> 404
    $this->actingAs($this->otherUser)
        ->getJson(route('family_approval.status', $this->invitation->id))
        ->assertNotFound();

    // Chủ sở hữu -> 200
    $this->actingAs($this->user)
        ->getJson(route('family_approval.status', $this->invitation->id))
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'groom_family',
                'bride_family',
                'all_approved',
            ],
        ]);
});
