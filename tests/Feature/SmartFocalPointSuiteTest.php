<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Focal Point',
        'slug' => 'mau-test-focal-point-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hữu Thắng',
        'bride_name' => 'Ngọc Linh',
        'cover_photo_url' => '/assets/client/images/sample-wedding-1.webp',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Pullman Saigon Centre',
        'venue_address' => '148 Tran Hung Dao, Dist 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thang-linh-'.uniqid(),
        'title' => 'Thiệp Cưới Hữu Thắng & Ngọc Linh',
        'is_published' => true,
    ]);
});

test('chủ thiệp có thể truy cập Studio Căn Chỉnh Tiêu Cự & Bố Cục Ảnh Cưới', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.focal_point.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Căn Chỉnh Tiêu Cự &amp; Bố Cục Ảnh Cưới', false);
});

test('người dùng khác bị 403 khi truy cập Focal Point Studio của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.focal_point.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật tọa độ tiêu cự x và y cho ảnh cưới', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.focal_point.update', $this->invitation->id), [
            'image_key' => 'hero_cover',
            'x' => 45.5,
            'y' => 30.2,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'focal_point' => [
                'x' => 46,
                'y' => 30,
            ],
        ]);

    $config = $this->invitation->fresh()->custom_config;
    expect($config['focal_points']['hero_cover'])->toBeArray();
});

test('validation từ chối tọa độ tiêu cự âm hoặc vượt quá 100', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.focal_point.update', $this->invitation->id), [
            'image_key' => 'hero_cover',
            'x' => -5,
            'y' => 150,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['x', 'y']);
});

test('cặp đôi có thể đặt lại tiêu cự ảnh về trung tâm 50% 50%', function () {
    $this->invitation->update([
        'custom_config' => [
            'focal_points' => [
                'hero_cover' => ['x' => 20, 'y' => 80],
            ],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('account.focal_point.reset', $this->invitation->id), [
            'image_key' => 'hero_cover',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'focal_point' => [
                'x' => 50,
                'y' => 50,
            ],
        ]);

    $config = $this->invitation->fresh()->custom_config;
    expect($config['focal_points']['hero_cover']['x'])->toBe(50);
    expect($config['focal_points']['hero_cover']['y'])->toBe(50);
});
