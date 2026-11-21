<?php

use App\Models\InvitationTemplate;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

it('registers ban-nhac-khong-doan-ket in the database with luxury category and premium tier', function () {
    $template = InvitationTemplate::where('slug', 'ban-nhac-khong-doan-ket')->first();

    expect($template)->not->toBeNull()
        ->and($template->name)->toBe('Bản Nhạc Không Đoạn Kết')
        ->and($template->category)->toBe('luxury')
        ->and($template->is_active)->toBeTrue()
        ->and($template->default_config['tier'] ?? null)->toBe('premium');
});

it('passes 100/100 template integrity and preflight audit with zero external URLs and 10 canonical sections', function () {
    $integrity = InvitationTemplateService::inspectTemplateIntegrity('ban-nhac-khong-doan-ket');
    $preflight = InvitationTemplateService::runPreflightAudit('ban-nhac-khong-doan-ket');

    expect($integrity['score'])->toBe(100)
        ->and($integrity['is_canonical_valid'])->toBeTrue()
        ->and($integrity['canonical_passed_count'])->toBe(10)
        ->and($integrity['gallery_valid'])->toBeTrue()
        ->and($integrity['has_essential_sync'])->toBeTrue()
        ->and($integrity['extends_invitation'])->toBeTrue()
        ->and($integrity['has_embed_guard'])->toBeTrue()
        ->and($integrity['uses_bem_prefix'])->toBeTrue()
        ->and($integrity['is_local_assets_only'])->toBeTrue()
        ->and($integrity['external_violations'])->toBeEmpty()
        ->and($preflight['is_ready_for_release'])->toBeTrue()
        ->and($preflight['readiness_score'])->toBe(100)
        ->and($preflight['blockers'])->toBeEmpty();
});

it('renders detail, demo preview, curated mode, and studio embed mode with all 10 canonical sections and law #8 compliance', function () {
    $detail = $this->get('/mau-thiep/ban-nhac-khong-doan-ket');
    $detail->assertStatus(200);
    $detail->assertSee('Bản Nhạc Không Đoạn Kết');

    $response = $this->get('/xem-thu/ban-nhac-khong-doan-ket');
    $response->assertStatus(200)
        ->assertSee('Bản Nhạc Không Đoạn Kết', false)
        ->assertSee('mayhen_invitation_vinyl_wrap', false)
        ->assertSee('mayhen_invitation_gift_box', false)
        ->assertSee('data-mayhen-section="hero"', false)
        ->assertSee('data-mayhen-section="story"', false)
        ->assertSee('data-mayhen-section="parents"', false)
        ->assertSee('data-mayhen-section="countdown_calendar"', false)
        ->assertSee('data-mayhen-section="events"', false)
        ->assertSee('data-mayhen-section="timeline"', false)
        ->assertSee('data-mayhen-section="dresscode"', false)
        ->assertSee('data-mayhen-section="album"', false)
        ->assertSee('data-mayhen-section="gift"', false)
        ->assertSee('data-mayhen-section="ending"', false)
        ->assertSee('data-sync-target="groom_name"', false)
        ->assertSee('data-sync-target="bride_name"', false)
        ->assertSee('data-sync-target="music_url"', false);

    $html = $response->getContent();
    expect($html)->toContain('mayhen_invitation_sig_crest_svg')
        ->and($html)->toContain('mayhen_invitation_rsvp_emblem_svg')
        ->and($html)->not->toContain('IMG_KY_NEN')
        ->and($html)->not->toContain('IMG_KY')
        ->and($html)->not->toContain('IMG_XN')
        ->and($html)->not->toContain('builder.27biggroup.com')
        ->and($html)->not->toContain('ladicdn.com')
        ->and($html)->not->toContain('unsplash.com')
        ->and($html)->not->toContain('fonts.googleapis.com');

    $embedResponse = $this->get('/xem-thu/ban-nhac-khong-doan-ket?embed=1&mode=editor');
    $embedResponse->assertStatus(200)
        ->assertSee('mayhen_embed_mode', false)
        ->assertSee('data-is-embed="true"', false)
        ->assertSee('is-embed opened', false)
        ->assertSee('MAYHEN_BRIDGE_READY', false);

    $curatedResponse = $this->get('/xem-thu/ban-nhac-khong-doan-ket?curated=1');
    $curatedResponse->assertStatus(200)
        ->assertSee('data-mayhen-section="hero"', false);
});

it('serves template static assets (styles.css, script.js, thumbnail.webp, wedding-song.mp3, images, ornaments, fonts) with HTTP 200', function () {
    $css = $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/styles.css');
    $css->assertStatus(200);
    $css->assertHeader('content-type', 'text/css; charset=UTF-8');

    $js = $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/script.js');
    $js->assertStatus(200);
    $js->assertHeader('content-type', 'application/javascript; charset=UTF-8');

    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/thumbnail.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/wedding-song.mp3')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/images/hero-couple.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/images/vinyl-label.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/ornaments/a-dia.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/ornaments/a-quat.svg')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/fonts/w21-font-01.ttf')->assertStatus(200);
    $this->get('/mau-thiep/ban-nhac-khong-doan-ket/assets/fonts/w21-font-04.woff2')->assertStatus(200);
});

it('creates a public wedding invitation and handles rsvp, wishes, and signature APIs', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('slug', 'ban-nhac-khong-doan-ket')->firstOrFail();

    $invitation = app(InvitationTemplateService::class)->saveInvitation($user, $template->id, [
        'wedding' => [
            'groom_name' => 'Ngọc Khôi',
            'bride_name' => 'Minh Thư',
            'wedding_date' => '2026-12-27',
            'extra_data' => [
                'groom_bank_name' => 'Vietcombank',
                'groom_bank_account' => '1029384756',
                'groom_bank_holder' => 'TRAN NGOC KHOI',
                'party_venue' => 'Gem Center – Sảnh Sol',
                'venue_name' => 'Gem Center – Sảnh Sol',
            ],
        ],
    ]);
    $invitation->update(['is_published' => true, 'published_at' => now()]);

    $publicRes = $this->get('/thiep/'.$invitation->slug);
    $publicRes->assertStatus(200)
        ->assertSee('Ngọc Khôi')
        ->assertSee('Minh Thư')
        ->assertSee('Gem Center');

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug]), [
        'guest_name' => 'Anh Hoàng Nam',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
        'wishes' => 'Chúc hai bạn mãi hòa nhịp như bản nhạc không đoạn kết!',
    ])->assertStatus(200);

    $this->postJson(route('invitation.wish.store', ['slug' => $invitation->slug]), [
        'name' => 'Chị Thảo Vy',
        'message' => 'Trăm năm hạnh phúc, tình yêu luôn ngọt ngào!',
    ])->assertStatus(201);

    $samplePng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    $this->postJson(route('invitation.signature.save', ['id' => $invitation->id]), [
        'guest_name' => 'Bạn Gia Huy',
        'signature_data' => $samplePng,
    ])->assertStatus(200)->assertJsonPath('success', true);
});
