<?php

use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('giua muon ngan vi sao preview page loads successfully with default data', function () {
    $response = $this->get('/xem-thu/giua-muon-ngan-vi-sao');
    $response->assertStatus(200);

    $content = $response->getContent();

    expect($content)->toContain('Minh Tuấn');
    expect($content)->toContain('Thu Hà');
    expect($content)->toContain('Trung tâm Tiệc cưới Ngân Hà');
    expect($content)->toContain('Giữa Muôn Ngàn Vì Sao');

    // Kiểm tra các thuộc tính data-sync-target quan trọng
    expect($content)->toContain('data-sync-target="groom_name"');
    expect($content)->toContain('data-sync-target="bride_name"');
    expect($content)->toContain('data-sync-target="groom_father"');
    expect($content)->toContain('data-sync-target="groom_mother"');
    expect($content)->toContain('data-sync-target="bride_father"');
    expect($content)->toContain('data-sync-target="bride_mother"');
    expect($content)->toContain('data-sync-target="event_party_address"');
    expect($content)->toContain('data-sync-target="groom_bank_account"');
    expect($content)->toContain('data-sync-target="bride_bank_account"');
});

test('giua muon ngan vi sao static assets load successfully', function () {
    $cssResponse = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/styles.css');
    $cssResponse->assertStatus(200);
    expect(realpath($cssResponse->getFile()->getPathname()))->toBe(realpath(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/styles.css')));

    $jsResponse = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/script.js');
    $jsResponse->assertStatus(200);
    expect(realpath($jsResponse->getFile()->getPathname()))->toBe(realpath(resource_path('views/client/invitations/giua-muon-ngan-vi-sao/script.js')));
    expect(filesize($jsResponse->getFile()->getPathname()))->toBeGreaterThan(50000);

    $thumbResponse = $this->get('/mau-thiep/giua-muon-ngan-vi-sao/assets/thumbnail.webp');
    $thumbResponse->assertStatus(200);
});

test('giua muon ngan vi sao embed mode adds defense class', function () {
    $response = $this->get('/xem-thu/giua-muon-ngan-vi-sao?embed=1');
    $response->assertStatus(200);

    $content = $response->getContent();
    expect($content)->toContain('mayhen_invitation_embed_mode');
    expect($content)->toContain('window.__MAYHEN_IS_EMBED = true;');
});

test('giua muon ngan vi sao schema json is valid and matches specification', function () {
    $schemaPath = resource_path('views/client/invitations/giua-muon-ngan-vi-sao/schema.json');
    expect(file_exists($schemaPath))->toBeTrue();

    $schema = json_decode(file_get_contents($schemaPath), true);
    expect($schema)->toBeArray();
    expect($schema)->toHaveKeys(['name', 'slug', 'sections']);

    $sectionIds = array_column($schema['sections'], 'id');
    expect($sectionIds)->toContain('hero');
    expect($sectionIds)->toContain('story');
    expect($sectionIds)->toContain('parents');
    expect($sectionIds)->toContain('countdown_calendar');
    expect($sectionIds)->toContain('events');
    expect($sectionIds)->toContain('timeline');
    expect($sectionIds)->toContain('dresscode');
    expect($sectionIds)->toContain('album');
    expect($sectionIds)->toContain('gift');
    expect($sectionIds)->toContain('ending');
});
