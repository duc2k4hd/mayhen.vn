<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\CuratedInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CuratedInvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected CuratedInvitationService $curatedService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->curatedService = app(CuratedInvitationService::class);

        // Đảm bảo template mayhen-curated tồn tại
        InvitationTemplate::firstOrCreate(
            ['slug' => 'mayhen-curated'],
            [
                'name' => 'Mây Hẹn Curated (Thiết Kế Tự Phối)',
                'thumbnail_url' => '/assets/client/images/templates/curated-thumb.webp',
                'description' => 'Mẫu thiệp cưới cao cấp tự phối thông minh 7 section tiêu chuẩn.',
                'sections' => ['hero', 'story', 'parents', 'countdown', 'events', 'timeline', 'dresscode'],
                'category' => 'curated',
                'is_active' => true,
                'default_config' => [
                    'theme_color' => '#85182A',
                    'font_family' => 'Cormorant Garamond',
                    'tier' => 'pro',
                    'sections_count' => 7,
                ],
            ]
        );
    }

    /**
     * 1. Màn hình Wizard hiển thị đầy đủ danh sách phong cách và bảng màu chuẩn.
     */
    public function test_curated_wizard_page_can_be_rendered(): void
    {
        $response = $this->get(route('curated.create'));

        $response->assertStatus(200);
        $response->assertSee('Tạo Thiệp Cưới Độc Bản Dành Riêng Cho Bạn');
        $response->assertSee('AI CURATED INVITATION');

        // Kiểm tra các phong cách chuẩn
        $styles = $this->curatedService->getStyles();
        foreach (array_keys($styles) as $styleKey) {
            $response->assertSee('value="'.$styleKey.'"', false);
        }

        // Kiểm tra các bảng màu chuẩn
        $palettes = $this->curatedService->getPalettes();
        foreach (array_keys($palettes) as $paletteKey) {
            $response->assertSee('value="'.$paletteKey.'"', false);
        }

        // Kiểm tra khối tóm tắt và nút khởi tạo
        $response->assertSee('Tóm Tắt Bản Thiết Kế Dành Riêng Cho Hai Bạn');
        $response->assertSee('Tạo Thiệp Của Chúng Mình');
    }

    /**
     * 2. Chặn từ chối nếu người dùng chọn phong cách hoặc bảng màu ngoài danh mục chuẩn (Mục 2, 3).
     */
    public function test_cannot_generate_with_invalid_style_or_palette(): void
    {
        $response = $this->post(route('curated.generate'), [
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'style' => 'cyberpunk_neon', // Không hợp lệ
            'palette' => 'ruby',
        ]);

        $response->assertSessionHasErrors(['style']);

        $response2 = $this->post(route('curated.generate'), [
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'style' => 'romantic',
            'palette' => 'electric_purple', // Không hợp lệ
        ]);

        $response2->assertSessionHasErrors(['palette']);
    }

    /**
     * 3. Khởi tạo thiệp tự phối thành công, lưu layout ngẫu nhiên bền vững và nội dung AI (Mục 1, 4, 7, 10).
     */
    public function test_generate_curated_invitation_creates_valid_7_section_invitation(): void
    {
        $postData = [
            'groom_name' => 'Quang Vinh',
            'bride_name' => 'Mai Anh',
            'groom_father' => 'Ông Nguyễn Quang Huy',
            'groom_mother' => 'Bà Lê Thị Kim',
            'bride_father' => 'Ông Trần Văn Nam',
            'bride_mother' => 'Bà Hoàng Thị Cúc',
            'style' => 'editorial',
            'palette' => 'sage-green',
            'wedding_date' => '2026-11-20',
            'venue_name' => 'Capella Gallery Hall',
            'venue_address' => 'Số 24 Đường 3/2, Quận 10, TP.HCM',
        ];

        $response = $this->post(route('curated.generate'), $postData);

        $invitation = Invitation::whereHas('wedding', function ($query) {
            $query->where('groom_name', 'Quang Vinh')->where('bride_name', 'Mai Anh');
        })->first();

        $this->assertNotNull($invitation);
        $token = $invitation->custom_config['presentation_token'] ?? null;
        $this->assertNotEmpty($token);
        $response->assertRedirect(route('curated.preview', ['slug' => $invitation->slug, 'pt' => $token]));

        // Kiểm tra cấu hình bền vững
        $config = $invitation->custom_config;
        $this->assertEquals('sage-green', $config['color_theme']);
        $this->assertTrue($config['is_curated']);
        $this->assertIsArray($config['curated_layout']);
        $this->assertEquals('editorial', $config['curated_layout']['style']);
        $this->assertEquals('sage-green', $config['curated_layout']['palette']);

        // Phải có đủ 7 section
        $sections = $config['curated_layout']['sections'];
        $this->assertCount(7, $sections);
        $this->assertArrayHasKey('hero', $sections);
        $this->assertArrayHasKey('story', $sections);
        $this->assertArrayHasKey('parents', $sections);
        $this->assertArrayHasKey('countdown', $sections);
        $this->assertArrayHasKey('events', $sections);
        $this->assertArrayHasKey('timeline', $sections);
        $this->assertArrayHasKey('album', $sections);

        // Nội dung AI sinh có trích dẫn và lời ngỏ
        $wedding = $invitation->wedding;
        $this->assertNotEmpty($wedding->extra_data['couple_quote']);
        $this->assertNotEmpty($wedding->extra_data['story_title']);
    }

    /**
     * 4. Màn hình Live Crafting Preview hiển thị chính xác với đầy đủ công cụ điều khiển (Mục 8, 9).
     */
    public function test_curated_preview_page_can_be_rendered_by_owner(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Đức Minh',
            'bride_name' => 'Ngọc Linh',
            'wedding_date' => '2026-12-15',
            'venue_name' => 'GEM Center',
            'venue_address' => 'Số 8 Nguyễn Bỉnh Khiêm, Quận 1',
        ]);

        $layout = $this->curatedService->assembleCuratedLayout('romantic', 'ruby');

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'duc-minh-va-ngoc-linh-test',
            'title' => 'Thiệp Cưới Đức Minh & Ngọc Linh',
            'custom_config' => [
                'curated_layout' => $layout,
                'color_theme' => 'ruby',
                'is_curated' => true,
            ],
            'is_published' => true,
            'revision_id' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('curated.preview', ['slug' => $invitation->slug]));

        $response->assertStatus(200);
        $response->assertSee('Đức Minh &amp; Ngọc Linh', false);
        $response->assertSee('Phối lại bố cục 🎲');
        $response->assertSee('Xem lại dựng thiệp ↺');
        $response->assertSee('Đổi Bố Cục Từng Mục');
        $response->assertSee('Xuất bản thiệp 🚀');
    }

    /**
     * 5. Thiệp cưới tự phối render đúng 7 section tiêu chuẩn (bao gồm Album 10 ảnh, không render gift hay rsvp).
     */
    public function test_curated_invitation_renders_exactly_7_sections(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Tuấn Kiệt',
            'bride_name' => 'Phương Thảo',
            'wedding_date' => '2026-10-10',
            'venue_name' => 'White Palace',
            'venue_address' => 'Phạm Văn Đồng, Thủ Đức',
            'extra_data' => [
                'couple_quote' => 'Trăm năm một chữ tình.',
                'dresscode_note' => 'Trang phục trang nhã.',
            ],
        ]);

        $layout = $this->curatedService->assembleCuratedLayout('traditional', 'royal-navy');

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'tuan-kiet-va-phuong-thao-test',
            'title' => 'Thiệp Cưới Tuấn Kiệt & Phương Thảo',
            'custom_config' => [
                'curated_layout' => $layout,
                'color_theme' => 'royal-navy',
                'is_curated' => true,
            ],
            'is_published' => true,
        ]);

        $response = $this->get(route('invitation.show', ['slug' => $invitation->slug]));

        $response->assertStatus(200);

        // Phải có class chủ đề của bảng màu
        $response->assertSee('mayhen_curated_theme--royal-navy');

        // Phải có đầy đủ 7 section tiêu chuẩn (bao gồm section 8 trong SKILL là Album 10 ảnh)
        $response->assertSee('data-curated-section="hero"', false);
        $response->assertSee('data-curated-section="story"', false);
        $response->assertSee('data-curated-section="parents"', false);
        $response->assertSee('data-curated-section="countdown"', false);
        $response->assertSee('data-curated-section="events"', false);
        $response->assertSee('data-curated-section="timeline"', false);
        $response->assertSee('data-curated-section="album"', false);

        // KHÔNG được có dresscode, gift QR, rsvp hay wishes
        $response->assertDontSee('data-curated-section="dresscode"');
        $response->assertDontSee('data-mayhen-section="gift"');
        $response->assertDontSee('data-mayhen-section="rsvp"');
    }

    /**
     * 6. Phối lại toàn bộ bố cục (Remix All Layout) ngẫu nhiên mà TUYỆT ĐỐI GIỮ NGUYÊN BẢNG MÀU (Mục 3, 9).
     */
    public function test_remix_layout_preserves_chosen_palette(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Thành Đạt',
            'bride_name' => 'Khánh Vy',
            'wedding_date' => '2026-12-01',
        ]);

        $originalLayout = $this->curatedService->assembleCuratedLayout('minimal', 'warm-amber');

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'thanh-dat-va-khanh-vy-remix',
            'title' => 'Thiệp Cưới Thành Đạt & Khánh Vy',
            'custom_config' => [
                'curated_layout' => $originalLayout,
                'color_theme' => 'warm-amber',
                'is_curated' => true,
            ],
            'revision_id' => 1,
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('curated.remix', ['id' => $invitation->id]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $invitation->refresh();

        // Palette bắt buộc phải là warm-amber (không được đổi!)
        $this->assertEquals('warm-amber', $invitation->custom_config['curated_layout']['palette']);
        $this->assertEquals('warm-amber', $invitation->custom_config['color_theme']);
        $this->assertEquals(2, $invitation->revision_id);
    }

    /**
     * 7. Đổi riêng lẻ biến thể của một section hoạt động tuần tự (Mục 9).
     */
    public function test_switch_single_section_variant_works(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Hải Đăng',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => '2026-12-01',
        ]);

        $layout = [
            'style' => 'romantic',
            'palette' => 'ruby',
            'sections' => [
                'hero' => 'variant_a',
                'story' => 'variant_a',
                'parents' => 'variant_a',
                'countdown' => 'variant_a',
                'events' => 'variant_a',
                'timeline' => 'variant_a',
                'album' => 'variant_a',
            ],
        ];

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'hai-dang-va-thanh-thao-switch',
            'title' => 'Thiệp Cưới Hải Đăng & Thanh Thảo',
            'custom_config' => [
                'curated_layout' => $layout,
                'color_theme' => 'ruby',
                'is_curated' => true,
            ],
            'revision_id' => 1,
            'is_published' => true,
        ]);

        // Đổi hero sang kiểu kế tiếp (variant_b)
        $response = $this->actingAs($user)->postJson(route('curated.switch_variant'), [
            'invitation_id' => $invitation->id,
            'section' => 'hero',
            'direction' => 'next',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'layout' => [
                'sections' => [
                    'hero' => 'variant_b',
                ],
            ],
        ]);

        $invitation->refresh();
        $this->assertEquals('variant_b', $invitation->custom_config['curated_layout']['sections']['hero']);
    }

    /**
     * 8. Layout ngẫu nhiên được lưu bền vững vào database, reload không random lại (Mục 10).
     */
    public function test_curated_layout_persists_across_reloads(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Bảo Long',
            'bride_name' => 'Kim Oanh',
            'wedding_date' => '2026-12-01',
        ]);

        $fixedLayout = [
            'style' => 'editorial',
            'palette' => 'dusty-rose',
            'sections' => [
                'hero' => 'variant_c',
                'story' => 'variant_b',
                'parents' => 'variant_a',
                'countdown' => 'variant_c',
                'events' => 'variant_b',
                'timeline' => 'variant_a',
                'album' => 'variant_c',
            ],
        ];

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'bao-long-va-kim-oanh-persist',
            'title' => 'Thiệp Cưới Bảo Long & Kim Oanh',
            'custom_config' => [
                'curated_layout' => $fixedLayout,
                'color_theme' => 'dusty-rose',
                'is_curated' => true,
            ],
            'is_published' => true,
        ]);

        // Truy cập nhiều lần
        $this->get(route('invitation.show', ['slug' => $invitation->slug]));
        $this->get(route('invitation.show', ['slug' => $invitation->slug]));

        $invitation->refresh();
        $this->assertEquals($fixedLayout, $invitation->custom_config['curated_layout']);
    }

    /**
     * 9. Người dùng lạ không thể tự ý remix hoặc đổi biến thể thiệp của người khác (Bảo mật).
     */
    public function test_unauthorized_user_cannot_remix_or_switch_variant(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $owner->id,
            'groom_name' => 'Chủ Nhân',
            'bride_name' => 'Hợp Pháp',
            'wedding_date' => '2026-12-01',
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'chu-nhan-va-hop-phap',
            'title' => 'Thiệp Cưới',
            'custom_config' => [
                'curated_layout' => $this->curatedService->assembleCuratedLayout('romantic', 'ruby'),
                'is_curated' => true,
            ],
            'is_published' => true,
        ]);

        // Người lạ thử remix -> 404 (do authorizeInvitationOwner abort)
        $response = $this->actingAs($stranger)->postJson(route('curated.remix', ['id' => $invitation->id]));
        $response->assertStatus(404);

        // Khách vãng lai không có session -> 404
        $response2 = $this->postJson(route('curated.switch_variant'), [
            'invitation_id' => $invitation->id,
            'section' => 'hero',
            'direction' => 'next',
        ]);
        $response2->assertStatus(404);
    }

    /**
     * 10. Cơ chế One-Time Presentation Token: Chỉ phát hoạt cảnh dựng thiệp lần đầu, reload F5 hiện ngay thành phẩm.
     */
    public function test_one_time_presentation_token_only_animates_on_first_visit(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => 'Thế Vinh',
            'bride_name' => 'Lan Chi',
            'wedding_date' => '2026-11-20',
        ]);

        $token = 'test-presentation-token-12345';

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'the-vinh-va-lan-chi-token',
            'title' => 'Thiệp Cưới Thế Vinh & Lan Chi',
            'custom_config' => [
                'curated_layout' => $this->curatedService->assembleCuratedLayout('romantic', 'ruby'),
                'is_curated' => true,
                'presentation_token' => $token,
                'first_presented_at' => null,
            ],
            'is_published' => true,
        ]);

        // Lần đầu mở với token hợp lệ: iframe có query crafting=1
        $response1 = $this->actingAs($user)->get(route('curated.preview', [
            'slug' => $invitation->slug,
            'pt' => $token,
        ]));
        $response1->assertStatus(200);
        $response1->assertSee('?crafting=1');

        $invitation->refresh();
        $this->assertNotNull($invitation->custom_config['first_presented_at']);
        $this->assertNull($invitation->custom_config['presentation_token']);

        // Lần thứ hai mở lại (Reload F5 / mở lại link cũ còn param pt): KHÔNG CÒN crafting=1
        $response2 = $this->actingAs($user)->get(route('curated.preview', [
            'slug' => $invitation->slug,
            'pt' => $token,
        ]));
        $response2->assertStatus(200);
        $response2->assertDontSee('?crafting=1');
    }

    /**
     * 11. AI Content Engine: Gọi provider khi có API key và fallback biên tập Mây Hẹn khi không có key hoặc lỗi (Mục 8).
     */
    public function test_ai_content_engine_uses_provider_when_configured_and_falls_back_gracefully(): void
    {
        // Kịch bản A: Khi có API Key và AI phản hồi thành công
        Config::set('services.curated_ai.api_key', 'test-openai-key-secret');

        $sequence = Http::fakeSequence()
            ->push([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'couple_quote' => 'Tình yêu là khi hai ánh mắt giao nhau và cùng nhìn về một hướng.',
                                'story_title' => 'Khúc Ca Duyên Nợ',
                                'story_subtitle' => 'Từng Nhịp Bước Bình Yên',
                                'story_content' => 'Từ những ngày đầu gặp gỡ đến khoảnh khắc hẹn ước trăm năm, tình yêu của chúng mình luôn được nuôi dưỡng bằng sự bao dung và chân thành.',
                                'dresscode_note' => 'Tone màu pastel trang nhã.',
                            ]),
                        ],
                    ],
                ],
            ], 200)
            ->push(['error' => 'Internal Server Error'], 500);

        Http::fake([
            'api.openai.com/*' => $sequence,
        ]);

        $aiResult = $this->curatedService->generateCuratedContent([
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Thùy Dung',
        ], 'romantic');

        $this->assertEquals('ai_generated', $aiResult['content_source']);
        $this->assertEquals('Khúc Ca Duyên Nợ', $aiResult['story_title']);
        $this->assertStringContainsString('Tình yêu là khi', $aiResult['couple_quote']);

        // Kịch bản B: Khi AI gặp lỗi 500 hoặc timeout, hệ thống tự động fallback biên tập an toàn không làm hỏng trải nghiệm
        $fallbackResult = $this->curatedService->generateCuratedContent([
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Thùy Dung',
        ], 'romantic');

        $this->assertEquals('editorial_fallback', $fallbackResult['content_source']);
        $this->assertNotEmpty($fallbackResult['couple_quote']);
        $this->assertNotEmpty($fallbackResult['story_content']);
    }

    /**
     * 12. Phòng vệ dữ liệu trống: Countdown và Map hiển thị placeholder trang nhã khi chưa có ngày cưới hoặc sảnh tiệc (UI-09).
     */
    public function test_countdown_and_events_render_graceful_placeholders_when_date_or_venue_omitted(): void
    {
        $user = User::factory()->create();
        $template = InvitationTemplate::where('slug', 'mayhen-curated')->first();

        // Kịch bản A: Chỉ nhập tên, chưa có ngày cưới và địa điểm
        $responseA = $this->actingAs($user)->post(route('curated.generate'), [
            'groom_name' => 'Văn Hậu',
            'bride_name' => 'Hải My',
            'style' => 'editorial',
            'palette' => 'sage-green',
        ]);

        $responseA->assertSessionHasNoErrors();
        $responseA->assertRedirect();
        $invitationA = Invitation::latest('id')->first();

        $renderA = $this->get(route('invitation.show', ['slug' => $invitationA->slug]));
        $renderA->assertStatus(200);
        $renderA->assertSee('✦ Bổ sung ngày cưới để xem đếm ngược ✦');
        $renderA->assertSee('Chưa có định vị bản đồ sảnh tiệc');
        $renderA->assertDontSee('data-countdown=', false);

        // Kịch bản B: Đã cung cấp đầy đủ ngày cưới và địa điểm sảnh tiệc
        $responseB = $this->actingAs($user)->post(route('curated.generate'), [
            'groom_name' => 'Văn Hậu',
            'bride_name' => 'Hải My',
            'style' => 'editorial',
            'palette' => 'sage-green',
            'wedding_date' => '2026-12-25',
            'venue_name' => 'Khách Sạn Lotte Hà Nội',
            'venue_address' => '54 Liễu Giai, Ba Đình, Hà Nội',
        ]);

        $responseB->assertSessionHasNoErrors();
        $responseB->assertRedirect();
        $invitationB = Invitation::latest('id')->first();

        $renderB = $this->get(route('invitation.show', ['slug' => $invitationB->slug]));
        $renderB->assertStatus(200);
        $renderB->assertSee('data-countdown="2026-12-25', false);
        $renderB->assertSee('Khách Sạn Lotte Hà Nội');
        $renderB->assertSee('maps.google.com/maps?q=Kh%C3%A1ch+S%E1%BA%A1n+Lotte+H%C3%A0+N%E1%BB%99i');
    }
}
