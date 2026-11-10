<?php

use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('hac bach trao duyen template renders preview successfully', function () {
    $response = $this->get('/xem-thu/hac-bach-trao-duyen');
    $response->assertStatus(200);
    $response->assertSee('Hà Kiên');
    $response->assertSee('Phạm Xôn');
    $response->assertSee('mayhen_invitation_ladjihe38udhd_wrapper');
});

test('hac bach trao duyen template satisfies live sync data contract', function () {
    $response = $this->get('/xem-thu/hac-bach-trao-duyen');
    $response->assertStatus(200);

    $syncTargets = [
        'groom_name',
        'bride_name',
        'wedding_date',
        'groom_father',
        'groom_mother',
        'bride_father',
        'bride_mother',
        'party_time',
        'party_venue',
        'event_party_address',
        'groom_bank_account',
        'bride_bank_account',
    ];

    foreach ($syncTargets as $target) {
        $response->assertSee('data-sync-target="'.$target.'"', false);
    }
});

test('hac bach trao duyen template handles studio embed mode defense', function () {
    $response = $this->get('/xem-thu/hac-bach-trao-duyen?embed=1');
    $response->assertStatus(200);
    $response->assertSee('mayhen_invitation_ladjihe38udhd_embed_mode');
    $response->assertSee('display: none !important;', false);
});

test('hac bach trao duyen template serves local css and js assets securely', function () {
    $cssResponse = $this->get('/mau-thiep/hac-bach-trao-duyen/assets/styles.css');
    $cssResponse->assertStatus(200);
    $cssResponse->assertHeader('content-type', 'text/css; charset=UTF-8');

    $jsResponse = $this->get('/mau-thiep/hac-bach-trao-duyen/assets/script.js');
    $jsResponse->assertStatus(200);
    $jsResponse->assertHeader('content-type', 'application/javascript; charset=UTF-8');
});

test('hac bach trao duyen template schema json is valid and contains standard sections', function () {
    $schemaPath = resource_path('views/client/invitations/hac-bach-trao-duyen/schema.json');
    expect(file_exists($schemaPath))->toBeTrue();

    $content = json_decode(file_get_contents($schemaPath), true);
    expect($content)->toBeArray()
        ->and($content['slug'])->toBe('hac-bach-trao-duyen')
        ->and($content['supported_sections'])->toContain('hero', 'story', 'parents', 'countdown_calendar', 'events', 'timeline', 'dresscode', 'album', 'gift', 'ending');
});

test('hac bach trao duyen template adheres to zero couple photos in signature and rsvp rule', function () {
    $templateBlade = file_get_contents(resource_path('views/client/invitations/hac-bach-trao-duyen/template.blade.php'));

    // Tách riêng section signature và rsvp
    preg_match('/<section[^>]*id="signature"[^>]*>(.*?)<\/section>/s', $templateBlade, $sigMatch);
    preg_match('/<section[^>]*id="rsvp"[^>]*>(.*?)<\/section>/s', $templateBlade, $rsvpMatch);

    expect($sigMatch)->not->toBeEmpty();
    expect($rsvpMatch)->not->toBeEmpty();

    $sigContent = $sigMatch[1];
    $rsvpContent = $rsvpMatch[1];

    // Không được chứa các ảnh chân dung cưới trong phần signature và rsvp
    expect($sigContent)->not->toContain('groom_photo_url');
    expect($sigContent)->not->toContain('bride_photo_url');
    expect($sigContent)->not->toContain('hero_photo_url');
    expect($sigContent)->not->toContain('save_date_photo_url');

    expect($rsvpContent)->not->toContain('groom_photo_url');
    expect($rsvpContent)->not->toContain('bride_photo_url');
    expect($rsvpContent)->not->toContain('hero_photo_url');
    expect($rsvpContent)->not->toContain('save_date_photo_url');
});
