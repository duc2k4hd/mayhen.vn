<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\InvitationFontRegistryService;
use App\Services\MediaLibraryService;
use App\Services\VisualEditorSanitizerService;
use App\Services\WeddingStorageService;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
    Storage::fake('public');
});

test('sanitizer clamps values, strips XSS, blocks ephemeral URLs, and supports deterministic resets', function () {
    $raw = [
        'global_style' => [
            'heading_font_id' => 'editorial-serif',
            'body_font_id' => 'evil-font); background:url(javascript:alert(1))',
            'accent_font_id' => 'indulge-script',
        ],
        'instance_styles' => [
            'hero__groom_name__0' => [
                'scope' => 'instance',
                'font_id' => 'romantic-calligraphy',
                'font_size_px' => 500, // clamped to 96
                'line_height' => 0.1, // clamped to 0.9
                'letter_spacing_em' => 2.5, // clamped to 0.5
                'text_align' => 'center',
                'color' => '#5A1822',
                'opacity' => 5, // clamped to 10
            ],
            'bad<script>id' => [
                'font_size_px' => 24,
            ],
        ],
        'image_overrides' => [
            'hero__cover_photo_url__0' => [
                'focal_x' => -20,
                'focal_y' => 150,
                'zoom' => 9.5,
                'opacity' => 90,
            ],
        ],
        'instance_animations' => [
            'hero__groom_name__0' => [
                'preset' => 'soft-rise',
                'duration_ms' => 99999,
                'delay_ms' => -50,
                'trigger' => 'on_load',
                'repeat' => 'once',
            ],
            'hero__bride_name__0' => [
                'preset' => 'unknown-preset',
            ],
        ],
        'overlays' => [
            [
                'id' => 'ov_test_text',
                'type' => 'overlay_text',
                'section_id' => 'hero',
                'text' => '<script>alert("xss")</script>Trăm Năm Hạnh Phúc',
                'x' => 1.5,
                'y' => -0.5,
                'w' => 0.4,
                'h' => 0.12,
                'rotation_deg' => 15,
                'z_index' => 20,
                'style' => ['font_id' => 'wedding-signature', 'color' => '#8B1E2D'],
                'motion' => ['preset' => 'gentle-zoom', 'duration_ms' => 700],
            ],
            [
                'id' => 'ov_test_frame',
                'type' => 'overlay_frame',
                'section_id' => 'story',
                'asset_id' => 'frame-arch',
                'image_url' => 'blob:http://127.0.0.1:8000/ephemeral-123',
                'x' => 0.5,
                'y' => 0.5,
                'w' => 0.3,
                'h' => 0.4,
            ],
        ],
    ];

    $clean = VisualEditorSanitizerService::sanitize($raw);

    expect($clean['global_style'])->toBe([
        'heading_font_id' => 'editorial-serif',
        'accent_font_id' => 'indulge-script',
    ])
        ->and($clean['instance_styles'])->not->toHaveKey('bad<script>id')
        ->and($clean['instance_styles']['hero__groom_name__0']['font_size_px'])->toBe(96)
        ->and($clean['instance_styles']['hero__groom_name__0']['line_height'])->toBe(0.9)
        ->and($clean['instance_styles']['hero__groom_name__0']['letter_spacing_em'])->toBe(0.5)
        ->and($clean['instance_styles']['hero__groom_name__0']['opacity'])->toBe(10)
        ->and($clean['image_overrides']['hero__cover_photo_url__0'])->toBe([
            'focal_x' => 0.0,
            'focal_y' => 100.0,
            'zoom' => 3.0,
            'opacity' => 90,
        ])
        ->and($clean['instance_animations'])->not->toHaveKey('hero__bride_name__0')
        ->and($clean['instance_animations']['hero__groom_name__0']['duration_ms'])->toBe(4000)
        ->and($clean['instance_animations']['hero__groom_name__0']['delay_ms'])->toBe(0)
        ->and($clean['overlays'][0]['text'])->toBe('alert("xss")Trăm Năm Hạnh Phúc')
        ->and($clean['overlays'][0]['x'])->toBe(0.98)
        ->and($clean['overlays'][0]['y'])->toBe(0.02)
        ->and($clean['overlays'][1]['image_url'])->toBe('');

    // Resetting an instance style via null removes it cleanly instead of shallow merge resurrecting it
    $afterReset = VisualEditorSanitizerService::sanitizeAndMerge($clean, [
        'instance_styles' => [
            'hero__groom_name__0' => null,
        ],
        'global_style' => [
            'heading_font_id' => 'template_default',
        ],
    ]);

    expect($afterReset['instance_styles'])->not->toHaveKey('hero__groom_name__0')
        ->and($afterReset['global_style'])->not->toHaveKey('heading_font_id')
        ->and($afterReset['global_style']['accent_font_id'])->toBe('indulge-script');
});

test('draft visual_editor stays isolated from public view until published and restores in Studio preview', function () {
    $user = User::factory()->create();
    $template = InvitationTemplate::where('slug', 'vom-troi-may-ao-cuoi')->firstOrFail();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Minh Tiến',
        'bride_name' => 'Thu Nguyệt',
        'wedding_date' => '2027-03-14',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'minh-tien-thu-nguyet-v3-'.uniqid(),
        'revision_id' => 0,
        'is_published' => true,
        'published_at' => now(),
        'custom_config' => [
            'visual_editor' => [
                'version' => 1,
                'global_style' => ['heading_font_id' => 'editorial-serif'],
                'overlays' => [],
            ],
        ],
    ]);

    // 1. Autosave draft visual_editor (adds sticker + changes heading font to royal-serif)
    $autosaveResp = $this->actingAs($user)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 0,
        'draft_config' => [
            'visual_editor' => [
                'global_style' => ['heading_font_id' => 'royal-serif'],
                'instance_styles' => [
                    'parents__groom_name__0' => ['font_size_px' => 42, 'color' => '#8B1E2D'],
                ],
                'overlays' => [
                    [
                        'id' => 'ov_draft_seal',
                        'type' => 'overlay_sticker',
                        'section_id' => 'hero',
                        'asset_id' => 'wax-seal-champagne',
                        'x' => 0.8,
                        'y' => 0.2,
                        'w' => 0.18,
                        'h' => 0.18,
                    ],
                ],
            ],
        ],
    ]);

    $autosaveResp->assertOk()->assertJson([
        'success' => true,
        'new_revision_id' => 1,
    ]);

    // 2. Public guest visiting /thiep/{slug} sees published editorial-serif, NOT draft ov_draft_seal, and NO editor-bridge even with ?embed=1&mode=editor
    auth()->logout();
    $publicResp = $this->get(route('invitation.show', ['slug' => $invitation->slug, 'embed' => 1, 'mode' => 'editor']));
    $publicResp->assertOk();
    $publicHtml = $publicResp->getContent();
    expect($publicHtml)->toContain('MayhenV3EditorialSerif')
        ->and($publicHtml)->not->toContain('ov_draft_seal')
        ->and($publicHtml)->not->toContain('iframe-canvas-agent.js')
        ->and($publicHtml)->not->toContain('MAYHEN_BRIDGE_READY')
        ->and($publicHtml)->not->toContain('fonts.googleapis.com');

    // 3. Owner reloading Studio preview iframe sees draft royal-serif + ov_draft_seal + iframe-canvas-agent.js
    $previewResp = $this->actingAs($user)->get(route('templates.preview', [
        'slug' => 'vom-troi-may-ao-cuoi',
        'edit' => $invitation->slug,
        'embed' => 1,
        'mode' => 'editor',
    ]));
    $previewResp->assertOk();
    $previewHtml = $previewResp->getContent();
    expect($previewHtml)->toContain('MayhenV3RoyalSerif')
        ->and($previewHtml)->toContain('ov_draft_seal')
        ->and($previewHtml)->toContain('iframe-canvas-agent.js');

    // 4. Owner publishes via POST /tao-thiep -> custom_config now carries royal-serif & ov_draft_seal on public /thiep/{slug}
    $publishResp = $this->actingAs($user)->postJson(route('invitation.store'), [
        'template_id' => $template->id,
        'invitation_id' => $invitation->id,
        'revision_id' => 1,
        'wedding' => [
            'groom_name' => 'Minh Tiến',
            'bride_name' => 'Thu Nguyệt',
            'wedding_date' => '2027-03-14',
        ],
    ]);
    $publishResp->assertOk()->assertJson(['success' => true]);

    auth()->logout();
    $publicAfterResp = $this->get(route('invitation.show', $invitation->slug));
    $publicAfterResp->assertOk();
    expect($publicAfterResp->getContent())
        ->toContain('MayhenV3RoyalSerif')
        ->toContain('ov_draft_seal');
});

test('uploadAsset blocks IDOR, locked invitations, spoofed files, and protects overlay images from orphan purge', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $template = InvitationTemplate::where('is_active', true)->firstOrFail();

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Quốc Khánh',
        'bride_name' => 'Bảo Ngọc',
        'wedding_date' => '2027-05-10',
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'quoc-khanh-bao-ngoc-'.uniqid(),
        'revision_id' => 0,
        'is_published' => true,
    ]);

    // 1. Intruder cannot upload to owner's invitation_id
    $fakeImg = UploadedFile::fake()->image('couple.jpg', 600, 800);
    $this->actingAs($intruder)->postJson(route('invitation.uploadAsset'), [
        'file' => $fakeImg,
        'invitation_id' => $invitation->id,
    ])->assertStatus(403);

    // 2. Spoofed non-image binary rejected with 422
    $spoofed = UploadedFile::fake()->createWithContent('evil.jpg', 'not-an-image-payload');
    $this->actingAs($owner)->postJson(route('invitation.uploadAsset'), [
        'file' => $spoofed,
        'invitation_id' => $invitation->id,
    ])->assertStatus(422);

    // 3. Owner uploads valid image and attaches to overlay_frame
    $validUpload = $this->actingAs($owner)->postJson(route('invitation.uploadAsset'), [
        'file' => UploadedFile::fake()->image('frame-photo.jpg', 600, 800),
        'invitation_id' => $invitation->id,
    ]);
    $validUpload->assertOk()->assertJson(['success' => true]);
    $relativeUrl = $validUpload->json('relative_path');
    $storagePath = $validUpload->json('storage_path');

    expect($relativeUrl)->toStartWith('/storage/weddings/photos/');
    Storage::disk('public')->assertExists($storagePath);

    $invitation->update([
        'custom_config' => [
            'visual_editor' => [
                'version' => 1,
                'overlays' => [
                    [
                        'id' => 'ov_frame_1',
                        'type' => 'overlay_frame',
                        'section_id' => 'hero',
                        'asset_id' => 'frame-arch',
                        'image_url' => $relativeUrl,
                        'x' => 0.5,
                        'y' => 0.5,
                        'w' => 0.3,
                        'h' => 0.4,
                    ],
                ],
            ],
        ],
    ]);

    // 4. Orphan detector and WeddingStorageService recognize the V3 overlay asset as active
    $orphanReport = MediaLibraryService::detectOrphanedFiles();
    $orphanPaths = array_column($orphanReport['orphans'], 'path');
    expect($orphanPaths)->not->toContain($storagePath);

    $mediaFiles = app(WeddingStorageService::class)->getMediaFiles($wedding, $invitation->fresh());
    expect(array_column($mediaFiles, 'storage_path'))->toContain($storagePath);

    // 5. Locked invitation blocks uploadAsset
    $invitation->update(['custom_config' => ['is_locked' => true]]);
    $this->actingAs($owner)->postJson(route('invitation.uploadAsset'), [
        'file' => UploadedFile::fake()->image('blocked.jpg', 400, 400),
        'invitation_id' => $invitation->id,
    ])->assertStatus(403);
});

test('duplicating an invitation regenerates unique overlay IDs and store enforces active & tier checks', function () {
    $user = User::factory()->create();
    $inactiveTpl = InvitationTemplate::create([
        'slug' => 'mau-tam-an-test',
        'name' => 'Mẫu Tạm Ẩn',
        'category' => 'minimal',
        'thumbnail_url' => '/images/test-inactive.jpg',
        'default_config' => ['tier' => 'free'],
        'is_active' => false,
    ]);

    // Inactive template blocked on POST /tao-thiep
    $this->actingAs($user)->postJson(route('invitation.store'), [
        'template_id' => $inactiveTpl->id,
        'groom_name' => 'Hoàng Minh',
        'bride_name' => 'Thanh Tâm',
        'wedding_date' => '2027-06-01',
    ])->assertStatus(404);

    // Pro template blocked for unauthenticated guest on POST /tao-thiep
    $proTpl = InvitationTemplate::where('slug', 'mayhen-curated')->firstOrFail();
    auth()->logout();
    $this->postJson(route('invitation.store'), [
        'template_id' => $proTpl->id,
        'groom_name' => 'Hoàng Minh',
        'bride_name' => 'Thanh Tâm',
        'wedding_date' => '2027-06-01',
    ])->assertStatus(403);

    // Duplicate regenerates overlay IDs
    $activeFree = InvitationTemplate::where('slug', 'vom-troi-may-ao-cuoi')->firstOrFail();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Hoàng Minh',
        'bride_name' => 'Thanh Tâm',
        'wedding_date' => '2027-06-01',
    ]);
    $original = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $activeFree->id,
        'slug' => 'hoang-minh-thanh-tam-orig-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'visual_editor' => [
                'version' => 1,
                'overlays' => [
                    ['id' => 'ov_original_fixed', 'type' => 'overlay_sticker', 'section_id' => 'hero', 'asset_id' => 'wedding-rings-gold', 'x' => 0.5, 'y' => 0.5, 'w' => 0.2, 'h' => 0.2],
                ],
            ],
        ],
    ]);

    $this->actingAs($user)->post(route('account.invitations.duplicate', $original->id))
        ->assertRedirect(route('account.invitations'));

    $clone = Invitation::where('id', '!=', $original->id)->where('wedding_id', $wedding->id)->firstOrFail();
    $cloneOverlayId = $clone->custom_config['visual_editor']['overlays'][0]['id'] ?? null;
    expect($cloneOverlayId)->not->toBeNull()
        ->and($cloneOverlayId)->not->toBe('ov_original_fixed');
});

test('all 7 active templates render Studio V3 preview and public invitation cleanly with zero Google Fonts CDN', function () {
    $user = User::factory()->create();
    $templates = InvitationTemplate::where('is_active', true)->get();
    expect($templates->count())->toBeGreaterThanOrEqual(7);

    foreach ($templates as $tpl) {
        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Thành Đạt',
            'bride_name' => 'Huyền Trang',
            'wedding_date' => '2027-08-18',
            'extra_data' => [
                'slogan' => 'Trăm năm tình viên mãn',
                'couple_quote' => 'Yêu người vừa ý, cưới người mình thương',
                'thank_you_title' => 'Trân Trọng Cảm Ơn',
                'thank_you_quote' => 'Sự hiện diện của bạn là niềm hạnh phúc lớn lao.',
            ],
        ]);

        $inv = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $tpl->id,
            'slug' => 'v3-check-'.$tpl->slug.'-'.uniqid(),
            'is_published' => true,
            'published_at' => now(),
            'custom_config' => [
                'is_curated_seven' => $tpl->slug === 'mayhen-curated',
                'visual_editor' => [
                    'version' => 1,
                    'global_style' => [
                        'heading_font_id' => 'philosopher-serif',
                        'body_font_id' => 'modern-sans',
                        'accent_font_id' => 'romantic-calligraphy',
                    ],
                ],
            ],
        ]);

        // Studio editor preview
        $previewResp = $this->actingAs($user)->get(route('templates.preview', [
            'slug' => $tpl->slug,
            'edit' => $inv->slug,
            'embed' => 1,
            'mode' => 'editor',
        ]));
        $previewResp->assertOk();
        expect($previewResp->getContent())
            ->toContain('invitation-fonts-v3.css')
            ->toContain('invitation-v3-runtime.css')
            ->toContain('MayhenV3Philosopher')
            ->not->toContain('fonts.googleapis.com');

        // Public guest view
        auth()->logout();
        $pubResp = $this->get(route('invitation.show', $inv->slug));
        $pubResp->assertOk();
        expect($pubResp->getContent())
            ->toContain('MayhenV3Philosopher')
            ->not->toContain('fonts.googleapis.com')
            ->not->toContain('iframe-canvas-agent.js');
    }

    expect(InvitationFontRegistryService::all())->toHaveCount(12);
});
