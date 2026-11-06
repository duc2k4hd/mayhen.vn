<?php

use App\Models\InvitationTemplate;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('all active templates render preview with editor bridge and data-sync-targets', function () {
    $slugs = InvitationTemplate::where('is_active', true)->pluck('slug');
    expect($slugs)->not->toBeEmpty();

    foreach ($slugs as $slug) {
        $response = $this->get("/xem-thu/{$slug}?embed=1&mode=editor");
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Phải chứa editor-bridge
        expect($content)->toContain('MAYHEN_SYNC_INPUT');
        expect($content)->toContain('MAYHEN_BRIDGE_READY');

        // 2. Phải có ít nhất 1 target tên cô dâu và chú rể
        expect($content)->toContain('data-sync-target="groom_name"');
        expect($content)->toContain('data-sync-target="bride_name"');
    }
});

test('hanh phuc has data-sync targets for names parents date and address', function () {
    $response = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh?embed=1&mode=editor');
    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('data-sync-target="groom_name"');
    expect($content)->toContain('data-sync-target="bride_name"');
    expect($content)->toContain('data-sync-target="groom_father"');
    expect($content)->toContain('data-sync-target="bride_father"');
    expect($content)->toContain('data-sync-target="event_party_address"');
    expect($content)->toContain('data-sync-target="wedding_date_digits"');
});

test('giua muon ngan vi sao has data-sync targets for names parents date and address', function () {
    $response = $this->get('/xem-thu/giua-muon-ngan-vi-sao?embed=1&mode=editor');
    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('data-sync-target="groom_name"');
    expect($content)->toContain('data-sync-target="bride_name"');
    expect($content)->toContain('data-sync-target="groom_father"');
    expect($content)->toContain('data-sync-target="bride_father"');
    expect($content)->toContain('data-sync-target="event_party_address"');
    expect($content)->toContain('data-sync-target="wedding_date_digits"');
});

test('hanh phuc renders its invitation wrapper when embedded in editor', function () {
    $response = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh?embed=1&mode=editor');
    $response->assertStatus(200);

    $content = $response->getContent();
    // Khung body khong bi khoa cuon hoac an noi dung
    expect($content)->not->toContain('body_class mayhen_invitation_wrapper mayhen_invitation_djcvhwiuo9w9_no-scroll');
    expect($content)->toContain('mayhen_invitation_ladi_wraper');
    expect($content)->toContain('display: block !important;');
});

test('hanh phuc embed mode opens doors and renders content with data sync targets', function () {
    $response = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh?embed=1&mode=editor');
    $response->assertStatus(200);

    $content = $response->getContent();
    expect($content)->toContain('mayhen_invitation_opening--opened');
    expect($content)->toContain('data-sync-target="groom_name"');
    expect($content)->toContain('data-sync-target="bride_name"');
    expect($content)->toContain('data-sync-target="groom_father"');
    expect($content)->toContain('data-sync-target="groom_mother"');
    expect($content)->toContain('data-sync-target="bride_father"');
    expect($content)->toContain('data-sync-target="bride_mother"');
    expect($content)->toContain('data-sync-target="event_party_address"');
    expect($content)->toContain('data-sync-target="party_time"');
    expect($content)->toContain('data-sync-target="party_venue"');
    expect($content)->toContain('data-sync-target="wedding_lunar_date"');
    expect($content)->toContain('data-sync-target="groom_photo_url"');
    expect($content)->toContain('data-sync-target="bride_photo_url"');
    expect($content)->toContain('data-sync-target="save_date_photo_url"');
    expect($content)->toContain('data-sync-target="wedding_date_digits"');
    expect($content)->toContain('data-sync-target="qr_bride_image"');
    expect($content)->toContain('data-mayhen-section="hero,couple"');
    expect($content)->toContain('data-mayhen-section="parents,groom_family,bride_family"');
    expect($content)->toContain('data-mayhen-section="album,gallery"');
    expect($content)->toContain('data-mayhen-section="timeline"');
    expect($content)->toContain('data-mayhen-section="gift"');
    expect($content)->toContain('mayhen_invitation_ladi_wraper');

    $schema = app(InvitationTemplateService::class)->getSchema('hanh-phuc-mang-ten-chung-minh');
    $fieldKeys = collect($schema['sections'])
        ->flatMap(fn (array $section): array => array_column($section['fields'], 'key'))
        ->all();

    expect($fieldKeys)
        ->toContain('wedding.extra_data.groom_photo_url')
        ->toContain('wedding.extra_data.bride_photo_url')
        ->toContain('wedding.extra_data.party_time')
        ->toContain('wedding.extra_data.party_venue')
        ->toContain('wedding.extra_data.event_party_address')
        ->toContain('wedding.extra_data.wedding_lunar_date')
        ->toContain('wedding.extra_data.qr_groom_image')
        ->toContain('wedding.extra_data.qr_bride_image');

    expect($content)->toContain('data-sync-target="qr_groom_image"');
});

test('editor creation page loads with valid schema for free templates', function () {
    $response = $this->get('/tao-thiep?template=hanh-phuc-mang-ten-chung-minh');
    $response->assertStatus(200);

    $content = $response->getContent();
    expect($content)->toContain('mayhen_editor_form');
    expect($content)->toContain('mayhen_editor_preview_iframe');
    expect($content)->toContain('mayhen_btn_device_mobile');
    expect($content)->toContain('mayhen_btn_device_desktop');
    expect($content)->toContain('mayhen_editor_center_stage');
});

test('editor creation page loads successfully for all active templates without error', function () {
    $user = User::factory()->create();
    $templates = InvitationTemplate::where('is_active', true)->get();
    expect($templates->count())->toBeGreaterThan(0);

    foreach ($templates as $t) {
        $response = $this->actingAs($user)->get('/tao-thiep?template='.$t->slug);
        $response->assertStatus(200);
    }
});
