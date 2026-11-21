<?php

use App\Models\InvitationTemplate;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

it('registers tinh-yeu-qua-tung-dong-thu in the database with luxury category and premium tier', function () {
    $template = InvitationTemplate::where('slug', 'tinh-yeu-qua-tung-dong-thu')->first();

    expect($template)->not->toBeNull()
        ->and($template->name)->toBe('Tình yêu qua từng dòng thư')
        ->and($template->category)->toBe('luxury')
        ->and($template->is_active)->toBeTrue()
        ->and($template->default_config['tier'] ?? null)->toBe('premium');
});

it('passes 100/100 template integrity and preflight audit with zero external URLs and 10 canonical sections', function () {
    $integrity = InvitationTemplateService::inspectTemplateIntegrity('tinh-yeu-qua-tung-dong-thu');
    $preflight = InvitationTemplateService::runPreflightAudit('tinh-yeu-qua-tung-dong-thu');

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
    $detail = $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu');
    $detail->assertStatus(200);
    $detail->assertSee('Tình yêu qua từng dòng thư');

    $response = $this->get('/xem-thu/tinh-yeu-qua-tung-dong-thu');
    $response->assertStatus(200)
        ->assertSee('Tình yêu qua từng dòng thư', false)
        ->assertSee('Nhật Báo Tình Yêu', false)
        ->assertSee('Rạp Hạnh Phúc', false)
        ->assertSee('Đài Tình Yêu', false)
        ->assertSee('BƯU THIẾP · CARTE POSTALE', false)
        ->assertSee('BƯU ĐIỆN SÀI GÒN · PAR AVION', false)
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
    expect($html)->toContain('mayhen_invitation_stamp_graphic')
        ->and($html)->toContain('class="sg-kho-hinh" width="0" height="0"')
        ->and($html)->toContain('class="ladi-wraper sg sg-trang-bao"')
        ->and($html)->toContain('class="nua tren nua-tren"')
        ->and($html)->toContain('class="nua duoi nua-duoi"')
        ->and($html)->not->toContain('id="IMG_KY"')
        ->and($html)->not->toContain('builder.27biggroup.com')
        ->and($html)->not->toContain('ladicdn.com')
        ->and($html)->not->toContain('unsplash.com');

    $embedResponse = $this->get('/xem-thu/tinh-yeu-qua-tung-dong-thu?embed=1&mode=editor');
    $embedResponse->assertStatus(200)
        ->assertSee('mayhen_embed_mode', false)
        ->assertSee('data-is-embed="1"', false)
        ->assertSee('MAYHEN_BRIDGE_READY', false);
});

it('serves template static assets (styles.css, script.js, thumbnail.webp, wedding-song.mp3, images, fonts) with HTTP 200', function () {
    $css = $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/styles.css');
    $css->assertStatus(200);
    $css->assertHeader('content-type', 'text/css; charset=UTF-8');

    $js = $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/script.js');
    $js->assertStatus(200);
    $js->assertHeader('content-type', 'application/javascript; charset=UTF-8');

    $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/thumbnail.webp')->assertStatus(200);
    $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/wedding-song.mp3')->assertStatus(200);
    $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/images/hero-main.webp')->assertStatus(200);
    $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/images/invite-main.webp')->assertStatus(200);
    $this->get('/mau-thiep/tinh-yeu-qua-tung-dong-thu/assets/fonts/sg-font-01.woff2')->assertStatus(200);
});

it('creates a public wedding invitation and handles rsvp, wishes, and signature APIs', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('slug', 'tinh-yeu-qua-tung-dong-thu')->firstOrFail();

    $invitation = app(InvitationTemplateService::class)->saveInvitation($user, $template->id, [
        'wedding' => [
            'groom_name' => 'Thành Đạt',
            'bride_name' => 'Ngọc Hân',
            'wedding_date' => '2026-12-27',
            'extra_data' => [
                'groom_bank_name' => 'Vietcombank',
                'groom_bank_account' => '1029384756',
                'groom_bank_holder' => 'LE THANH DAT',
                'party_venue' => 'Nhà hàng Hương Xưa – Sảnh Bến Thành',
                'venue_name' => 'Nhà hàng Hương Xưa – Sảnh Bến Thành',
            ],
        ],
    ]);
    $invitation->update(['is_published' => true, 'published_at' => now()]);

    $publicRes = $this->get('/thiep/'.$invitation->slug);
    $publicRes->assertStatus(200)
        ->assertSee('Thành Đạt')
        ->assertSee('Ngọc Hân')
        ->assertSee('Nhà hàng Hương Xưa');

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug]), [
        'guest_name' => 'Anh Minh Khôi',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
        'wishes' => 'Chúc mừng hai bạn trăm năm hạnh phúc!',
    ])->assertStatus(200);

    $this->postJson(route('invitation.wish.store', ['slug' => $invitation->slug]), [
        'name' => 'Chị Lan Hương',
        'message' => 'Chúc đôi uyên ương mãi hạnh phúc như tờ Nhật Báo Tình Yêu!',
    ])->assertStatus(201);

    $samplePng = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    $this->postJson(route('invitation.signature.save', ['id' => $invitation->id]), [
        'guest_name' => 'Bạn Quốc Bảo',
        'signature_data' => $samplePng,
    ])->assertStatus(200)->assertJsonPath('success', true);
});
