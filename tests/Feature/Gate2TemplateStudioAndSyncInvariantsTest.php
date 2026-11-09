<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\InvitationTemplateService;
use Carbon\Carbon;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Gate2TemplateStudioAndSyncInvariantsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(InvitationTemplateSeeder::class);

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Nguyễn Minh Tuấn',
            'bride_name' => 'Trần Thu Hà',
            'groom_parents' => 'Ông Nguyễn Văn Hùng & Bà Lê Thị Thu',
            'bride_parents' => 'Ông Trần Quốc Bảo & Bà Phạm Thị Mai',
            'wedding_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
            'venue_name' => 'Trung Tâm Hội Nghị Quốc Gia',
            'venue_address' => 'Đại lộ Thăng Long, Mễ Trì, Nam Từ Liêm, Hà Nội',
            'story' => 'Chúng mình đã cùng nhau đi qua 7 năm thanh xuân và quyết định về chung một nhà.',
            'cover_photo_url' => '/assets/demo/cover.webp',
            'extra_data' => [
                'party_time' => '18:00',
                'wedding_lunar_date' => 'Nhằm ngày 12 tháng 11 năm Bính Ngọ',
            ],
        ]);

        $initialTemplate = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->firstOrFail();

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $initialTemplate->id,
            'slug' => 'tuan-ha-wedding',
            'is_published' => true,
            'expires_at' => Carbon::now()->addYear(),
            'revision_id' => 1,
            'custom_config' => [
                'color_theme' => 'ruby',
            ],
        ]);
    }

    public function test_inv38_switching_template_preserves_100_percent_wedding_data_and_saves_rollback_snapshot(): void
    {
        $service = app(InvitationTemplateService::class);
        $newTemplate = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();

        // Chuyển mẫu thiệp sang Hạnh Phúc Mang Tên Chúng Mình
        $updatedInvitation = $service->saveInvitation($this->owner, $newTemplate->id, [
            'wedding' => [
                'groom_name' => 'Nguyễn Minh Tuấn',
                'bride_name' => 'Trần Thu Hà',
            ],
            'invitation' => [
                'custom_config' => [
                    'color_theme' => 'sage',
                ],
            ],
        ], $this->invitation->id, 1);

        $this->assertEquals($newTemplate->id, $updatedInvitation->template_id);
        $this->assertEquals(2, $updatedInvitation->revision_id);

        // Kiểm chứng bất biến INV-38: Dữ liệu đám cưới trong weddings không hề bị suy suyển
        $this->wedding->refresh();
        $this->assertEquals('Nguyễn Minh Tuấn', $this->wedding->groom_name);
        $this->assertEquals('Trần Thu Hà', $this->wedding->bride_name);
        $this->assertEquals('Ông Nguyễn Văn Hùng & Bà Lê Thị Thu', $this->wedding->groom_parents);
        $this->assertEquals('Trung Tâm Hội Nghị Quốc Gia', $this->wedding->venue_name);
        $this->assertEquals('Chúng mình đã cùng nhau đi qua 7 năm thanh xuân và quyết định về chung một nhà.', $this->wedding->story);
        $this->assertEquals('18:00', $this->wedding->extra_data['party_time']);

        // Snapshot bản trước được lưu trữ tự động an toàn
        $config = $updatedInvitation->custom_config;
        $this->assertArrayHasKey('_previous_published_snapshot', $config);
        $this->assertEquals('giua-muon-ngan-vi-sao', InvitationTemplate::find($config['_previous_published_snapshot']['template_id'])->slug);
    }

    public function test_inv34_and_inv36_all_templates_support_embed_mode_and_handshake(): void
    {
        $templates = InvitationTemplate::where('is_active', true)->get();

        foreach ($templates as $t) {
            $response = $this->get("/xem-thu/{$t->slug}?embed=1&mode=editor");
            $response->assertStatus(200);

            $html = $response->getContent();

            // 1. Phải có class embed mode
            $this->assertStringContainsString('mayhen_embed_mode', $html);

            // 2. Phải có handshake 2 chiều MAYHEN_BRIDGE_READY và MAYHEN_SYNC_INPUT
            $this->assertStringContainsString('MAYHEN_BRIDGE_READY', $html);
            $this->assertStringContainsString('MAYHEN_SYNC_INPUT', $html);

            // 3. Tiêu đề xem thử phải tuân thủ chuẩn INV-39
            $this->assertStringContainsString('Xem thử:', $html);
            $this->assertStringContainsString($t->name, html_entity_decode($html));
        }
    }

    public function test_inv37_signature_and_rsvp_sections_strictly_prohibit_couple_and_stock_person_photos(): void
    {
        $templates = InvitationTemplate::where('is_active', true)->get();

        foreach ($templates as $t) {
            $response = $this->get("/xem-thu/{$t->slug}");
            $response->assertStatus(200);

            $html = $response->getContent();

            // Tìm block Signature & RSVP trong HTML
            if (preg_match('/<section[^>]*class="[^"]*(?:signature|rsvp)[^"]*"[^>]*>(.*?)<\/section>/is', $html, $matches)) {
                $sectionHtml = $matches[0];
                // Tuyệt đối không chứa ảnh chân dung cặp đôi trong phần ký tên và rsvp
                $this->assertStringNotContainsString('hero-couple', $sectionHtml);
                $this->assertStringNotContainsString('groom-portrait', $sectionHtml);
                $this->assertStringNotContainsString('bride-portrait', $sectionHtml);
                $this->assertStringNotContainsString('unsplash.com', $sectionHtml);
            }
        }
    }

    public function test_inv33_live_sync_targets_exist_for_core_dynamic_fields(): void
    {
        $standardTemplates = InvitationTemplate::where('is_active', true)
            ->where('category', '!=', 'curated')
            ->get();

        $coreTargets = [
            'groom_name',
            'bride_name',
            'groom_father',
            'bride_father',
            'wedding_date_digits',
            'event_party_address',
        ];

        foreach ($standardTemplates as $t) {
            $response = $this->get("/xem-thu/{$t->slug}?embed=1&mode=editor");
            $response->assertStatus(200);
            $html = $response->getContent();

            foreach ($coreTargets as $target) {
                $this->assertStringContainsString("data-sync-target=\"{$target}\"", $html, "Template [{$t->slug}] thiếu data-sync-target='{$target}'");
            }
        }
    }
}
