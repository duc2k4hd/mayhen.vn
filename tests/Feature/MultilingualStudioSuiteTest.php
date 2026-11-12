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
        'name' => 'Mẫu Test Đa Ngôn Ngữ',
        'slug' => 'mau-test-i18n-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Minh Triết',
        'bride_name' => 'Khánh Vy',
        'wedding_date' => now()->addMonths(4)->toDateString(),
        'venue_name' => 'Sheraton Saigon Hotel',
        'venue_address' => '88 Dong Khoi St, District 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'triet-vy-'.uniqid(),
        'title' => 'Thiệp Cưới Minh Triết & Khánh Vy',
        'is_published' => true,
    ]);
});

test('chủ thiệp có thể truy cập Studio Quản Trị Đa Ngôn Ngữ Thiệp Cưới', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.multilingual.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Thiệp Cưới Đa Ngôn Ngữ');
    $response->assertSee('Bản Dịch Quốc Tế');
});

test('người dùng khác bị 403 khi truy cập Studio Đa Ngôn Ngữ của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.multilingual.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật danh sách ngôn ngữ kích hoạt và ghi đè bản dịch', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.multilingual.update', $this->invitation->id), [
            'enabled_locales' => ['vi', 'en', 'ko'],
            'default_locale' => 'en',
            'overrides' => [
                'en' => [
                    'hero.wedding_of' => 'The Wedding Ceremony of',
                    'rsvp.title' => 'RSVP & Presence Confirmation',
                ],
            ],
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $config = $this->invitation->fresh()->custom_config;
    expect($config['i18n']['default_locale'])->toBe('en');
    expect($config['i18n']['enabled_locales'])->toContain('vi', 'en', 'ko');
    expect($config['i18n']['overrides']['en']['hero.wedding_of'])->toBe('The Wedding Ceremony of');
});

test('cặp đôi có thể đặt lại bản dịch tùy biến về từ điển mặc định Mây Hẹn', function () {
    // Thiết lập overrides trước
    $this->invitation->update([
        'custom_config' => [
            'i18n' => [
                'enabled_locales' => ['vi', 'en'],
                'default_locale' => 'vi',
                'overrides' => ['en' => ['hero.wedding_of' => 'Custom text']],
            ],
        ],
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('account.multilingual.reset', $this->invitation->id));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $config = $this->invitation->fresh()->custom_config;
    expect($config['i18n']['overrides'])->toBeEmpty();
});
