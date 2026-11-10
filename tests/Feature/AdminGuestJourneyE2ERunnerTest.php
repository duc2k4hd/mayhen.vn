<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Models\WeddingWish;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_runner_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_runner_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_runner_test@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'ngay-tinh-yeu-dom-hoa'],
        [
            'name' => 'Ngày Tình Yêu Đơm Hoa',
            'category' => 'modern',
            'thumbnail_url' => '/mau-thiep/ngay-tinh-yeu-dom-hoa/assets/images/hero-couple.webp',
            'is_active' => true,
            'is_featured' => false,
            'current_version' => 'v1.0.0',
            'release_status' => 'published',
            'rollout_percentage' => 100,
        ]
    );

    $this->wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'wedding_date' => now()->addDays(30),
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM',
        'music_url' => '/assets/client/audio/wedding-song.mp3',
        'cover_photo_url' => '/assets/client/images/sample-hero.webp',
        'extra_data' => [
            'ceremony_type' => 'Lễ Thành Hôn',
            'event_party_address' => 'White Palace, TP.HCM',
            'groom_father' => 'Nguyễn Văn An',
            'groom_mother' => 'Trần Thị Bình',
            'bride_father' => 'Lê Văn Cường',
            'bride_mother' => 'Phạm Thị Dung',
            'gallery' => [
                'sample-1.webp',
                'sample-2.webp',
            ],
        ],
    ]);

    $this->table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP Danh Dự 01',
        'capacity' => 10,
        'assigned_seats' => 2,
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'minh-tuan-va-thu-ha-rehearsal',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('guest and customer cannot run guest journey e2e rehearsal', function () {
    $this->postJson(route('admin.invitations.run_rehearsal', $this->invitation->id))
        ->assertStatus(401);

    $this->actingAs($this->customer)
        ->postJson(route('admin.invitations.run_rehearsal', $this->invitation->id))
        ->assertStatus(403);
});

test('admin and staff can view rehearsal screen with e2e runner button and preview', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.invitations.rehearsal', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Diễn Tập Hành Trình Khách Mời');
    $response->assertSee('Bắt Đầu Diễn Tập Tự Động (Run E2E Runner)');
    $response->assertSee('Zero DB Pollution Guarantee');
    $response->assertSee('Giai Đoạn 1: Mở Thiệp');
    $response->assertSee('Giai Đoạn 5: Quét Mã QR');
});

test('runner executes all 5 stages successfully and guarantees zero database pollution', function () {
    // 1. Ghi nhận số lượng bản ghi ban đầu
    $initialGuestsCount = Guest::where('wedding_id', $this->wedding->id)->count();
    $initialResponsesCount = GuestResponse::where('wedding_id', $this->wedding->id)->count();
    $initialWishesCount = WeddingWish::where('wedding_id', $this->wedding->id)->count();

    // 2. Chạy E2E Runner
    $response = $this->actingAs($this->admin)->postJson(route('admin.invitations.run_rehearsal', $this->invitation->id));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'invitation_id' => $this->invitation->id,
                'slug' => $this->invitation->slug,
                'overall_status' => 'passed',
                'stages_passed' => 5,
                'total_stages' => 5,
                'db_pollution_bytes' => 0,
                'certificate' => 'WEDDING_READY_100_PERCENT',
            ],
        ]);

    $data = $response->json('data');
    expect($data['stages'])->toHaveCount(5);
    expect($data['stages'][0]['status'])->toBe('passed');
    expect($data['stages'][1]['status'])->toBe('passed');
    expect($data['stages'][2]['status'])->toBe('passed');
    expect($data['stages'][3]['status'])->toBe('passed');
    expect($data['stages'][4]['status'])->toBe('passed');

    // 3. XÁC MINH TUYỆT ĐỐI ZERO DB POLLUTION
    expect(Guest::where('wedding_id', $this->wedding->id)->count())->toBe($initialGuestsCount);
    expect(GuestResponse::where('wedding_id', $this->wedding->id)->count())->toBe($initialResponsesCount);
    expect(WeddingWish::where('wedding_id', $this->wedding->id)->count())->toBe($initialWishesCount);
});

test('runner flags issues when invitation is draft or missing couple details', function () {
    // Đưa thiệp về draft và xóa tên cô dâu chú rể
    $this->invitation->update(['is_published' => false]);
    $this->wedding->update([
        'groom_name' => '',
        'bride_name' => '',
    ]);

    $response = $this->actingAs($this->staff)->postJson(route('admin.invitations.run_rehearsal', $this->invitation->id));

    $response->assertStatus(200);
    $data = $response->json('data');

    // Stage 1 và Stage 2 bị failed/warning
    expect($data['stages'][0]['status'])->toBe('failed');
    expect($data['stages'][1]['status'])->toBe('warning');
    expect($data['certificate'])->toBe('REHEARSAL_NEEDS_ATTENTION');
});
