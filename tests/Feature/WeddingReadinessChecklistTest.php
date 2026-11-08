<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingConsistencyGuardianService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Celestial Romance',
        'slug' => 'celestial-romance-'.uniqid(),
        'blade_view' => 'client.invitations.celestial.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Minh Triết',
        'bride_name' => 'Thu Thảo',
        'wedding_date' => '2026-12-12',
        'venue_name' => 'Trung Tâm Hội Nghị White Palace',
        'venue_address' => '194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP.HCM',
        'extra_data' => [
            'party_time' => '18:00',
            'event_party_map_url' => 'https://maps.app.goo.gl/abcdef123456',
            'groom_bank_account' => '0123456789',
            'groom_bank_name' => 'Vietcombank',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'triet-thao-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
        'custom_config' => [
            'checkin_pin' => '889977',
        ],
    ]);

    // Thêm khách mời có xếp bàn
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn A',
        'salutation' => 'Bạn',
        'group_name' => 'Bạn Chú Rể',
        'table_name' => 'Bàn VIP 01',
        'expected_attendees' => 2,
    ]);
});

test('it calculates full readiness score when all criteria are met', function () {
    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->calculateReadinessChecklist($this->wedding, $this->invitation);

    expect($result['score'])->toBe(100);
    expect($result['passed_count'])->toBe(7);
    expect($result['total_count'])->toBe(7);
    expect($result['status'])->toBe('ready');

    $keys = collect($result['items'])->pluck('key')->all();
    expect($keys)->toContain(
        'publish',
        'date_time_venue',
        'maps_navigation',
        'vietqr_banking',
        'guest_roster',
        'seating_planner',
        'resilience_kit'
    );
});

test('it identifies missing checklist items accurately', function () {
    // Tạo thiệp còn thiếu nhiều tiêu chí
    $incompleteWedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đức Minh',
        'bride_name' => 'Mai Anh',
        'wedding_date' => '2027-01-01',
        'venue_name' => null,   // Thiếu sảnh tiệc
        'venue_address' => null, // Thiếu địa chỉ
        'extra_data' => [],     // Thiếu giờ tiệc, thiếu maps, thiếu ngân hàng
    ]);

    $incompleteInvitation = Invitation::create([
        'wedding_id' => $incompleteWedding->id,
        'template_id' => $this->template->id,
        'slug' => 'minh-mai-wedding-'.uniqid(),
        'is_published' => false, // Bản nháp
        'custom_config' => [
            'checkin_pin' => '123456', // Mã mặc định
        ],
    ]);

    $guardian = app(WeddingConsistencyGuardianService::class);
    $result = $guardian->calculateReadinessChecklist($incompleteWedding, $incompleteInvitation);

    expect($result['score'])->toBe(0);
    expect($result['passed_count'])->toBe(0);
    expect($result['status'])->toBe('incomplete');

    $publishItem = collect($result['items'])->firstWhere('key', 'publish');
    expect($publishItem['passed'])->toBeFalse();

    $seatingItem = collect($result['items'])->firstWhere('key', 'seating_planner');
    expect($seatingItem['passed'])->toBeFalse();
});

test('owner can fetch readiness checklist via api', function () {
    $response = $this->actingAs($this->user)->getJson(route('invitation.readiness_check', $this->invitation->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'data' => [
            'score' => 100,
            'passed_count' => 7,
            'total_count' => 7,
            'status' => 'ready',
        ],
    ]);
});

test('stranger cannot fetch readiness checklist of another user via api', function () {
    $response = $this->actingAs($this->otherUser)->getJson(route('invitation.readiness_check', $this->invitation->id));

    $response->assertForbidden();
});

test('account invitations page renders readiness widget and checklist modal', function () {
    $response = $this->actingAs($this->user)->get(route('account.invitations'));

    $response->assertOk();
    $response->assertSee('mayhen_readiness_indicator', false);
    $response->assertSee('Xem 7 bước', false);
    $response->assertSee('100%', false);
    $response->assertSee('mayhen_readiness_modal', false);
});
