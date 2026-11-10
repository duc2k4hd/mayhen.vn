<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\AuditLogger;
use App\Services\InvitationQualityPassportService;
use App\Services\InvitationTemplateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVNextInnovationBacklogTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $manager;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin_inno@mayhen.vn',
            'role' => 'admin',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Test',
            'email' => 'manager_inno@mayhen.vn',
            'role' => 'manager',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff Test',
            'email' => 'staff_inno@mayhen.vn',
            'role' => 'staff',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Nguyễn Văn Khách',
            'email' => 'customer_inno@mayhen.vn',
            'phone' => '0912345678',
            'role' => 'customer',
        ]);
    }

    /**
     * Test 1.1: Role-Aware Morning Briefing renders correctly for Super Admin & Manager.
     */
    public function test_morning_briefing_renders_for_super_admin(): void
    {
        $plan = Plan::create([
            'name' => 'Gói Hoàng Kim Test',
            'slug' => 'goi-hoang-kim-test',
            'price' => 399000,
            'is_active' => true,
        ]);

        Order::create([
            'user_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'order_code' => 'MH_BRIEF_001',
            'amount' => 399000,
            'status' => 'paid',
            'paid_at' => now()->subHours(2),
        ]);

        Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Hoàng Nam',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => Carbon::today(),
            'venue_name' => 'Sảnh Trắng',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('morningBrief');
        $brief = $response->viewData('morningBrief');

        $this->assertStringContainsString('Super Admin Test', $brief['greeting']);
        $this->assertEquals('admin', $brief['role']);
        $this->assertEquals(399000, $brief['revenue_24h']);
        $this->assertEquals(1, $brief['orders_24h']);
        $this->assertEquals(1, $brief['today_weddings_count']);
        $response->assertSee('Tổng Chỉ Huy Vận Hành (Super Admin)');
        $response->assertSee('399.000đ');
    }

    /**
     * Test 1.2: Role-Aware Morning Briefing adapts for Staff role.
     */
    public function test_morning_briefing_adapts_for_staff(): void
    {
        SupportTicket::create([
            'user_id' => $this->customer->id,
            'ticket_code' => 'TK-STAFF-001',
            'subject' => 'Hỗ trợ đổi font thiệp',
            'body' => 'Khách hàng nhờ đổi font tiêu đề thiệp cưới sang serif',
            'category' => 'invitation',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('morningBrief');
        $brief = $response->viewData('morningBrief');

        $this->assertEquals('staff', $brief['role']);
        $this->assertEquals(1, $brief['open_tickets_count']);
        $response->assertSee('Chuyên Viên Hỗ Trợ & Ngày Cưới (Staff)');
        $response->assertSee('1 yêu cầu');
    }

    /**
     * Test 2.1: Wedding Day Rescue Cockpit displays today's wedding and unassigned guests.
     */
    public function test_rescue_cockpit_displays_active_wedding_and_guests(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Minh Trí',
            'bride_name' => 'Lan Ngọc',
            'wedding_date' => Carbon::today(),
            'venue_name' => 'Sảnh Diamond',
        ]);

        $table = WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bàn VIP 01',
            'capacity' => 10,
            'zone' => 'Khu A',
            'display_order' => 1,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Vãng Lai 01',
            'phone' => '0988776655',
            'expected_attendees' => 2,
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.guests.rescue'));

        $response->assertStatus(200);
        $response->assertSee('Minh Trí');
        $response->assertSee('Lan Ngọc');
        $response->assertSee('Khách Vãng Lai 01');
        $response->assertSee('Bàn VIP 01');
    }

    /**
     * Test 2.2: Quick assign table assigns guest to table and logs audit.
     */
    public function test_quick_assign_table_for_walk_in_guest(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Hải Đăng',
            'bride_name' => 'Mai Anh',
            'wedding_date' => Carbon::today(),
        ]);

        $table = WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bàn 05 Sân Vườn',
            'capacity' => 10,
            'display_order' => 1,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bác Hai Họ Nhà Gái',
            'expected_attendees' => 1,
        ]);

        $this->assertNull($guest->table_id);

        $response = $this->actingAs($this->staff)->post(route('admin.guests.assign_table', $guest->id), [
            'table_id' => $table->id,
        ]);

        $response->assertSessionHas('success');
        $guest->refresh();
        $this->assertEquals($table->id, $guest->table_id);
        $this->assertEquals('Bàn 05 Sân Vườn', $guest->table_name);

        $logs = AuditLogger::getRecentLogs(20, 'rescue_assign_table');
        $this->assertNotEmpty($logs);
        $this->assertEquals((string) $guest->id, $logs[0]['target_id']);
    }

    /**
     * Test 2.3: Create emergency table creates overflow table with audit log.
     */
    public function test_create_emergency_table(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Tiến Dũng',
            'bride_name' => 'Bảo Trâm',
            'wedding_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->staff)->post(route('admin.guests.emergency_table', $wedding->id), [
            'name' => 'Bàn Dự Phòng Sảnh Chính',
            'capacity' => 12,
            'zone' => 'Khu Khẩn Cấp',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wedding_tables', [
            'wedding_id' => $wedding->id,
            'name' => 'Bàn Dự Phòng Sảnh Chính',
            'capacity' => 12,
            'zone' => 'Khu Khẩn Cấp',
        ]);

        $logs = AuditLogger::getRecentLogs(20, 'create_emergency_table');
        $this->assertNotEmpty($logs);
        $this->assertEquals('WeddingTable', $logs[0]['target_type']);
    }

    /**
     * Test 3.1: Export personal data (DSAR - Luật 91/2025/QH15) streams valid JSON.
     */
    public function test_export_personal_data_compliance_json(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Khách Chú Rể',
            'bride_name' => 'Khách Cô Dâu',
            'wedding_date' => Carbon::today()->addDays(20),
            'venue_name' => 'Khách Sạn 5 Sao',
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Mời A',
            'phone' => '0909000111',
            'expected_attendees' => 2,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.export_personal_data', [
            'id' => $this->customer->id,
            'dsar_reason' => 'Yêu cầu trích xuất dữ liệu cá nhân theo quy chuẩn Luật 91/2025/QH15',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertStringContainsString('Luật 91/2025/QH15', $data['compliance']);
        $this->assertEquals($this->customer->id, $data['personal_profile']['id']);
        $this->assertEquals('Nguyễn Văn Khách', $data['personal_profile']['name']);
        $this->assertEquals('customer_inno@mayhen.vn', $data['personal_profile']['email']);
        $this->assertCount(1, $data['weddings']);

        // Masking check (NXT-02)
        $this->assertStringContainsString('****', $data['weddings'][0]['guests'][0]['phone']);

        $logs = AuditLogger::getRecentLogs(20, 'export_personal_data_dsar');
        $this->assertNotEmpty($logs);
        $this->assertEquals((string) $this->customer->id, $logs[0]['target_id']);
    }

    /**
     * Test 3.2: Anonymize customer data masks PII and preserves accounting integrity.
     */
    public function test_anonymize_customer_pii_preserves_orders(): void
    {
        $plan = Plan::create([
            'name' => 'Gói Vĩnh Cửu',
            'slug' => 'goi-vinh-cuu-test',
            'price' => 599000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'order_code' => 'MH_ANONYMIZE_999',
            'amount' => 599000,
            'status' => 'paid',
            'paid_at' => now()->subDays(5),
        ]);

        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Anh Đức',
            'bride_name' => 'Chị Hoa',
            'groom_parents' => 'Ông Nam & Bà Lan',
            'bank_name' => 'Vietcombank',
            'bank_account_number' => '1234567890',
            'wedding_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.anonymize', $this->customer->id));

        $response->assertSessionHas('success');

        $this->customer->refresh();
        $this->assertStringContainsString('Khách Hàng Đã Ẩn Danh', $this->customer->name);
        $this->assertStringContainsString('@deleted.mayhen.vn', $this->customer->email);
        $this->assertEquals('0000000000', $this->customer->phone);
        $this->assertTrue($this->customer->is_locked);
        $this->assertStringContainsString('Luật 91/2025/QH15', $this->customer->lock_reason);

        // Wedding PII is cleared
        $wedding->refresh();
        $this->assertNull($wedding->bank_account_number);
        $this->assertNull($wedding->groom_parents);

        // Order is intact
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_code' => 'MH_ANONYMIZE_999',
            'amount' => 599000,
            'status' => 'paid',
        ]);

        $logs = AuditLogger::getRecentLogs(20, 'anonymize_user_data');
        $this->assertNotEmpty($logs);
        $this->assertEquals((string) $this->customer->id, $logs[0]['target_id']);
    }

    /**
     * Test 3.3: Self-anonymization and admin anonymization are strictly blocked.
     */
    public function test_anonymize_self_and_admin_blocked(): void
    {
        // 1. Cannot anonymize self
        $response1 = $this->actingAs($this->superAdmin)->post(route('admin.users.anonymize', $this->superAdmin->id));
        $response1->assertSessionHas('error');

        // 2. Cannot anonymize manager
        $response2 = $this->actingAs($this->superAdmin)->post(route('admin.users.anonymize', $this->manager->id));
        $response2->assertSessionHas('error');
    }

    /**
     * Test 4.1: Invitation Quality Passport (Innovation #2) audits wedding completeness and template.
     */
    public function test_invitation_quality_passport_inspection(): void
    {
        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold Test',
            'slug' => 'celestial-gold-test',
            'thumbnail_url' => '/assets/templates/celestial-test.jpg',
            'is_active' => true,
        ]);

        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Quốc Bảo',
            'bride_name' => 'Nhã Phương',
            'wedding_date' => Carbon::today()->addDays(10),
            'venue_name' => 'Trung Tâm Hội Nghị White Palace',
            'venue_address' => '194 Hoàng Văn Thụ, Phú Nhuận',
            'cover_photo_url' => '/storage/test/cover.jpg',
            'music_url' => '/storage/test/music.mp3',
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'quoc-bao-nha-phuong-passport',
            'is_published' => true,
        ]);

        $passport = InvitationQualityPassportService::inspect($invitation);

        $this->assertIsArray($passport);
        $this->assertGreaterThanOrEqual(40, $passport['score']);
        $this->assertNotEmpty($passport['checklist']);
        $this->assertEquals('Celestial Gold Test', $passport['template_name']);

        $response = $this->actingAs($this->staff)->get(route('admin.invitations.show', $invitation->id));
        $response->assertStatus(200);
        $response->assertSee('Hộ Chiếu Chất Lượng Thiệp Cưới');
        $response->assertSee($passport['grade_label']);
    }

    /**
     * Test 4.2: Proactive VIP Wedding Concierge Queue (Innovation #16) filters tickets for weddings in 48h.
     */
    public function test_vip_concierge_queue_filters_48h_weddings(): void
    {
        // 1. VIP Customer having wedding tomorrow (within 48h)
        $vipCustomer = User::factory()->create([
            'name' => 'Khách VIP Sát Giờ G',
            'email' => 'vip_48h@mayhen.vn',
            'role' => 'customer',
        ]);

        Wedding::create([
            'user_id' => $vipCustomer->id,
            'groom_name' => 'Tuấn Khang',
            'bride_name' => 'Mỹ Linh',
            'wedding_date' => Carbon::tomorrow(),
            'venue_name' => 'Khách Sạn Rex',
        ]);

        $vipTicket = SupportTicket::create([
            'user_id' => $vipCustomer->id,
            'ticket_code' => 'TK-VIP-048',
            'subject' => 'Khẩn cấp: Kiểm tra bản đồ chỉ đường cho khách',
            'body' => 'Ngày mai cưới rồi, nhờ admin hỗ trợ gấp link bản đồ',
            'category' => 'invitation',
            'priority' => 'urgent',
            'status' => 'open',
        ]);

        // 2. Normal customer having wedding in 30 days
        $normalCustomer = User::factory()->create([
            'name' => 'Khách Thông Thường',
            'email' => 'normal@mayhen.vn',
            'role' => 'customer',
        ]);

        Wedding::create([
            'user_id' => $normalCustomer->id,
            'groom_name' => 'Văn A',
            'bride_name' => 'Thị B',
            'wedding_date' => Carbon::today()->addDays(30),
        ]);

        $normalTicket = SupportTicket::create([
            'user_id' => $normalCustomer->id,
            'ticket_code' => 'TK-NORM-001',
            'subject' => 'Hỏi về gói dịch vụ vĩnh cửu',
            'body' => 'Tư vấn giúp em gói',
            'category' => 'billing',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        // Test VIP 48h filter
        $response = $this->actingAs($this->staff)->get(route('admin.support.index', ['queue' => 'vip_48h']));

        $response->assertStatus(200);
        $response->assertSee('TK-VIP-048');
        $response->assertSee('👑 VIP 48h');
        $response->assertDontSee('TK-NORM-001');
    }

    /**
     * Test 4.3: Operational Memory & Incident SOP Cards (Innovation #17) renders on ticket show page.
     */
    public function test_sop_cards_render_on_ticket_detail(): void
    {
        $ticket = SupportTicket::create([
            'user_id' => $this->customer->id,
            'ticket_code' => 'TK-SOP-001',
            'subject' => 'Lỗi không nghe được nhạc trên điện thoại',
            'body' => 'Em mở thiệp trên iPhone không thấy nhạc tự chạy',
            'category' => 'invitation',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.support.show', $ticket));

        $response->assertStatus(200);
        $response->assertSee('Sổ Tay Vận Hành');
        $response->assertSee('Âm thanh không tự phát trên iPhone Safari');
        $response->assertSee('Quên mã PIN sảnh tiệc');
    }

    /**
     * Test 5.1: Promise-to-Proof Monitor (Innovation #6) detects discrepancies between marketing and DB.
     */
    public function test_promise_to_proof_monitor_audit(): void
    {
        // Create 3 exact matching plans
        Plan::create(['name' => 'Gói Trải Nghiệm', 'slug' => 'free', 'price' => 0, 'is_active' => true]);
        Plan::create(['name' => 'Gói Hạnh Phúc', 'slug' => 'plus', 'price' => 149000, 'is_active' => true]);
        Plan::create(['name' => 'Gói Trọn Vẹn', 'slug' => 'pro', 'price' => 299000, 'is_active' => true]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.plans.index'));

        $response->assertStatus(200);
        $response->assertViewHas('promiseAudit');
        $audit = $response->viewData('promiseAudit');

        $this->assertTrue($audit['is_aligned']);
        $this->assertEmpty($audit['discrepancies']);
        $response->assertSee('100% Khớp Đúng Lời Hứa Marketing');

        // Now introduce a discrepancy (e.g. price mismatch)
        $proPlan = Plan::where('slug', 'pro')->first();
        $proPlan->update(['price' => 350000]);

        $response2 = $this->actingAs($this->superAdmin)->get(route('admin.plans.index'));
        $audit2 = $response2->viewData('promiseAudit');

        $this->assertFalse($audit2['is_aligned']);
        $this->assertNotEmpty($audit2['discrepancies']);
        $response2->assertSee('Phát Hiện Sai Lệch Giữa CSDL &amp; Landing Page', false);
    }

    /**
     * Test 5.2: Pre-Wedding Change Impact Simulator (Innovation #3) returns JSON impact assessment.
     */
    public function test_change_impact_simulator_assessment(): void
    {
        $template = InvitationTemplate::create([
            'name' => 'Serenity Gold',
            'slug' => 'serenity-gold',
            'thumbnail_url' => '/assets/serenity.jpg',
            'is_active' => true,
        ]);

        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Trọng Hiếu',
            'bride_name' => 'Bích Phương',
            'wedding_date' => Carbon::today()->addDays(5),
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'trong-hieu-bich-phuong-sim',
            'is_published' => true,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Bạn Thân Của Chú Rể',
        ]);

        GuestResponse::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => 'Bạn Thân Của Chú Rể',
            'attending_status' => 'attending',
            'adults_count' => 2,
        ]);

        // Simulate changing wedding date
        $response = $this->actingAs($this->staff)->getJson(route('admin.invitations.simulate_impact', [
            'id' => $invitation->id,
            'change_type' => 'wedding_date',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'severity' => 'high',
            'metrics' => [
                'total_guests' => 1,
                'confirmed_rsvps' => 1,
            ],
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['warnings']);
        $this->assertStringContainsString('Có 1 quan khách đã bấm xác nhận tham dự', $data['warnings'][0]);
    }

    /**
     * Test 5.3: Guest Journey Synthetic Rehearsal (Innovation #4) renders sandbox environment.
     */
    public function test_synthetic_rehearsal_sandbox_screen(): void
    {
        $template = InvitationTemplate::create([
            'name' => 'Royal Velvet',
            'slug' => 'royal-velvet',
            'thumbnail_url' => '/assets/royal.jpg',
            'is_active' => true,
        ]);

        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Hoàng Khang',
            'bride_name' => 'Ngọc Diệp',
            'wedding_date' => Carbon::today()->addDays(20),
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'hoang-khang-ngoc-diep-rehearsal',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.invitations.rehearsal', $invitation->id));

        $response->assertStatus(200);
        $response->assertSee('Diễn Tập Hành Trình Khách Mời');
        $response->assertSee('Zero DB Pollution Guarantee');
        $response->assertSee('Giai Đoạn 1: Mở Thiệp');
        $response->assertSee('Giai Đoạn 5: Quét Mã QR');
    }

    /**
     * Test 6.1: Template Visual DNA (Innovation #10) extracts typography, palette and material cues.
     */
    public function test_template_visual_dna_extraction(): void
    {
        $template = InvitationTemplate::create([
            'name' => 'Kỷ Niệm Vàng Son',
            'slug' => 'dau-ky-tram-nam',
            'thumbnail_url' => '/assets/dau-ky.jpg',
            'is_active' => true,
        ]);

        $dna = InvitationTemplateService::extractVisualDna('dau-ky-tram-nam');

        $this->assertIsArray($dna);
        $this->assertArrayHasKey('fonts', $dna);
        $this->assertArrayHasKey('palette', $dna);
        $this->assertArrayHasKey('theme', $dna);
        $this->assertArrayHasKey('has_paper_texture', $dna);
        $this->assertArrayHasKey('has_border', $dna);

        // Edit template page should render Visual DNA section
        $response = $this->actingAs($this->superAdmin)->get(route('admin.templates.edit', $template->id));

        $response->assertStatus(200);
        $response->assertSee('Template Visual DNA');
        $response->assertSee('Bảng Màu Tông Sắc Nhận Diện');
        $response->assertSee('Texture Giấy');
    }

    /**
     * Test 6.2: Delight Recovery Planner (Innovation #13) detects paid couples who have not published.
     */
    public function test_delight_recovery_planner_detects_stuck_couples(): void
    {
        $plan = Plan::create([
            'name' => 'Gói Trọn Vẹn Plus',
            'slug' => 'goi-tron-ven-plus-delight',
            'price' => 399000,
            'is_active' => true,
        ]);

        $stuckUser = User::factory()->create([
            'name' => 'Cặp Đôi Bị Kẹt',
            'email' => 'stuck_couple@mayhen.vn',
            'role' => 'customer',
        ]);

        Order::create([
            'user_id' => $stuckUser->id,
            'plan_id' => $plan->id,
            'order_code' => 'MH_DELIGHT_777',
            'amount' => 399000,
            'status' => 'paid',
            'paid_at' => now()->subDays(2),
        ]);

        $wedding = Wedding::create([
            'user_id' => $stuckUser->id,
            'groom_name' => 'Anh Nam',
            'bride_name' => 'Chị Mai',
            'wedding_date' => Carbon::today()->addDays(40),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Vintage Romance',
            'slug' => 'vintage-romance-delight',
            'thumbnail_url' => '/assets/vintage.jpg',
            'is_active' => true,
        ]);

        // Invitation is still draft (not published)
        Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'anh-nam-chi-mai-draft',
            'is_published' => false,
            'view_count' => 5,
        ]);

        // Regular customer is redirected to login (not allowed to access admin)
        $customerResponse = $this->actingAs($this->customer)->get(route('admin.intelligence.delight_recovery'));
        $customerResponse->assertRedirect(route('admin.login'));

        // Staff / Manager can view and support
        $response = $this->actingAs($this->manager)->get(route('admin.intelligence.delight_recovery'));

        $response->assertStatus(200);
        $response->assertSee('Delight Recovery Planner');
        $response->assertSee('Đã thanh toán nhưng chưa xuất bản thiệp');
        $response->assertSee('stuck_couple@mayhen.vn');
        $response->assertSee('Anh Nam &amp; Chị Mai', false);
    }

    /**
     * Test 6.3: Zero-Surprise Campaign Launch (Innovation #15) renders segment metrics and OG preview.
     */
    public function test_zero_surprise_campaign_preview(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Minh Triết',
            'bride_name' => 'Thùy Chi',
            'wedding_date' => Carbon::today()->addDays(15),
            'venue_name' => 'Riverside Palace',
            'cover_photo_url' => '/storage/covers/triet-chi.jpg',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Romantic Blush',
            'slug' => 'romantic-blush',
            'thumbnail_url' => '/assets/blush.jpg',
            'is_active' => true,
        ]);

        Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'minh-triet-thuy-chi',
            'is_published' => true,
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Đã Gửi Link',
            'phone' => '0912345678',
            'is_sent' => true,
        ]);

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Khách Chưa Gửi Link',
            'phone' => '0987654321',
            'is_sent' => false,
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.guests.campaign_preview', [
            'wedding_id' => $wedding->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Xem Trước Chiến Dịch Gửi Thiệp');
        $response->assertSee('Minh Triết &amp; Thùy Chi', false);
        $response->assertSee('50%'); // Send rate 1 / 2 = 50%
    }
}
