<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingPrivacyConsentService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Velvet Ruby',
        'slug' => 'velvet-ruby-'.uniqid(),
        'blade_view' => 'client.invitations.ruby.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Minh Trí',
        'bride_name' => 'Thục Quyên',
        'wedding_date' => now()->addDays(25),
        'venue_name' => 'The Reverie Saigon',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'tri-quyen-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);
});

it('renders privacy and consent center for wedding owner and calculates compliance score', function () {
    $this->actingAs($this->user);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Trần Văn Phú',
        'phone' => '0901234567',
    ]);

    $response = $this->get(route('privacy.show', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Trung Tâm Minh Bạch Quyền Riêng Tư')
        ->assertSee('Điểm Đánh Giá Bảo Vệ Dữ Liệu Cá Nhân')
        ->assertSee('Bảng Kiểm Định Tuân Thủ')
        ->assertSee('/100');
});

it('updates privacy settings and validates input rules', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('privacy.update', $this->invitation->id), [
        'bank_visibility' => 'named_guests_only',
        'wishes_visibility' => 'couple_only',
        'photo_drop_moderation' => 'require_approval',
        'phone_retention_days' => 30,
    ]);

    $response->assertRedirect(route('privacy.show', $this->invitation->id))
        ->assertSessionHas('success');

    $this->invitation->refresh();
    $settings = $this->invitation->custom_config['privacy_settings'] ?? [];

    expect($settings['bank_visibility'])->toBe('named_guests_only')
        ->and($settings['wishes_visibility'])->toBe('couple_only')
        ->and($settings['phone_retention_days'])->toBe(30);
});

it('masks sensitive bank account for public visitors when bank_visibility is named_guests_only', function () {
    $service = app(WeddingPrivacyConsentService::class);

    $service->updatePrivacySettings($this->invitation, [
        'bank_visibility' => 'named_guests_only',
    ]);

    $rawData = [
        'groom_bank_account' => '1029384756',
        'groom_bank_name' => 'Vietcombank',
        'bride_bank_account' => '9876543210',
        'bride_bank_name' => 'Techcombank',
    ];

    // Khách vãng lai xem -> số tài khoản bị che 4 số cuối
    $filteredPublic = $service->filterSensitiveWeddingData($this->invitation, $rawData, isNamedGuest: false);
    expect($filteredPublic['groom_bank_account'])->toBe('•••• •••• 4756')
        ->and($filteredPublic['bride_bank_account'])->toBe('•••• •••• 3210')
        ->and($filteredPublic['bank_masked'])->toBeTrue();

    // Khách có thiệp định danh cá nhân -> nhìn thấy đầy đủ
    $filteredNamed = $service->filterSensitiveWeddingData($this->invitation, $rawData, isNamedGuest: true);
    expect($filteredNamed['groom_bank_account'])->toBe('1029384756')
        ->and($filteredNamed['bride_bank_account'])->toBe('9876543210')
        ->and($filteredNamed['bank_masked'])->toBeFalse();
});

it('returns json privacy audit report through api endpoint with idor defense', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('privacy.audit', $this->invitation->id));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'report' => [
                'privacy_score',
                'status',
                'status_label',
                'checks',
                'statistics' => [
                    'total_guests',
                    'guests_with_phone',
                    'total_responses',
                    'public_wishes_count',
                ],
            ],
        ]);

    // Thử truy cập bằng tài khoản khác -> 404 (IDOR defense)
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $forbidden = $this->getJson(route('privacy.audit', $this->invitation->id));
    $forbidden->assertNotFound();
});

