<?php

use App\Models\InvitationTemplate;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

it('registers ban-sonata-mau-hanh-phuc in the database with romantic category and pro tier', function () {
    $template = InvitationTemplate::where('slug', 'ban-sonata-mau-hanh-phuc')->first();

    expect($template)->not->toBeNull()
        ->and($template->name)->toBe('Bản Sonata Màu Hạnh Phúc')
        ->and($template->category)->toBe('romantic')
        ->and($template->is_active)->toBeTrue()
        ->and($template->default_config['tier'] ?? null)->toBe('pro');
});

it('passes 100/100 template integrity and preflight audit with zero external URLs and 10 canonical sections', function () {
    $integrity = InvitationTemplateService::inspectTemplateIntegrity('ban-sonata-mau-hanh-phuc');
    $preflight = InvitationTemplateService::runPreflightAudit('ban-sonata-mau-hanh-phuc');

    expect($integrity['score'])->toBe(100)
        ->and($integrity['is_canonical_valid'])->toBeTrue()
        ->and($integrity['canonical_passed_count'])->toBe(10)
        ->and($integrity['gallery_valid'])->toBeTrue()
        ->and($integrity['has_essential_sync'])->toBeTrue()
        ->and($integrity['extends_invitation'])->toBeTrue()
        ->and($integrity['uses_bem_prefix'])->toBeTrue()
        ->and($integrity['is_local_assets_only'])->toBeTrue()
        ->and($integrity['external_violations'])->toBeEmpty()
        ->and($preflight['is_ready_for_release'])->toBeTrue()
        ->and($preflight['readiness_score'])->toBe(100)
        ->and($preflight['blockers'])->toBeEmpty();
});

it('renders detail, demo preview, and studio embed mode with all 10 canonical sections and law #8 compliance', function () {
    $detail = $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc');
    $detail->assertStatus(200);
    $detail->assertSee('Bản Sonata Màu Hạnh Phúc');

    $response = $this->get('/xem-thu/ban-sonata-mau-hanh-phuc');
    $response->assertStatus(200)
        ->assertSee('Bản Sonata Màu Hạnh Phúc', false)
        ->assertSee('Hà Kiên', false)
        ->assertSee('Phạm Xôn', false)
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
    expect($html)->toContain('mayhen_invitation_sig_heart_svg')
        ->and($html)->toContain('mayhen_invitation_rsvp_botanical_crest')
        ->and($html)->not->toContain('builder.27biggroup.com')
        ->and($html)->not->toContain('ladicdn.com')
        ->and($html)->not->toContain('unsplash.com');

    $embedResponse = $this->get('/xem-thu/ban-sonata-mau-hanh-phuc?embed=1&mode=editor');
    $embedResponse->assertStatus(200)
        ->assertSee('mayhen_embed_mode', false)
        ->assertSee('data-is-embed="1"', false)
        ->assertSee('MAYHEN_BRIDGE_READY', false);
});

it('serves template static assets (styles.css, script.js, thumbnail.webp, wedding-song.mp3, ornaments, fonts) with HTTP 200', function () {
    $css = $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/styles.css');
    $css->assertStatus(200);
    $css->assertHeader('content-type', 'text/css; charset=UTF-8');

    $js = $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/script.js');
    $js->assertStatus(200);
    $js->assertHeader('content-type', 'application/javascript; charset=UTF-8');

    $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/thumbnail.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/wedding-song.mp3')->assertStatus(200);
    $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/images/hero-main.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/ornaments/a-giay.webp')->assertStatus(200);
    $this->get('/mau-thiep/ban-sonata-mau-hanh-phuc/assets/fonts/w18-hastegi.otf')->assertStatus(200);
});

it('creates a public wedding invitation and handles rsvp, wishes, and signature APIs', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('slug', 'ban-sonata-mau-hanh-phuc')->firstOrFail();

    $invitation = app(InvitationTemplateService::class)->saveInvitation($user, $template->id, [
        'wedding' => [
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Minh Anh',
            'wedding_date' => '2027-01-16',
            'extra_data' => [
                'groom_bank_name' => 'Techcombank',
                'groom_bank_account' => '190388889999',
                'groom_account_name' => 'LE HOANG NAM',
                'party_venue' => 'Trung Tâm Tiệc Cưới Hồ Tây',
            ],
        ],
    ]);
    $invitation->update(['is_published' => true, 'published_at' => now()]);

    $publicRes = $this->get('/thiep/'.$invitation->slug);
    $publicRes->assertStatus(200)
        ->assertSee('Hoàng Nam')
        ->assertSee('Minh Anh')
        ->assertSee('Trung Tâm Tiệc Cưới Hồ Tây');

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug]), [
        'guest_name' => 'Anh Hoàng Minh',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
        'wishes' => 'Chúc mừng hai bạn!',
    ])->assertStatus(200);

    $this->postJson(route('invitation.wish.store', ['slug' => $invitation->slug]), [
        'name' => 'Chị Thu Hà',
        'message' => 'Chúc đôi bạn trăm năm hạnh phúc!',
    ])->assertStatus(201);

    $samplePng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    $this->postJson(route('invitation.signature.save', ['id' => $invitation->id]), [
        'guest_name' => 'Bạn Khánh Linh',
        'signature_data' => $samplePng,
    ])->assertStatus(200)->assertJsonPath('success', true);
});
