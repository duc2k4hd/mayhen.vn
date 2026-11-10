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
use App\Services\AuditLogger;
use App\Services\OperationsReplayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOperationsReplayTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Replay',
            'email' => 'superadmin_replay@mayhen.vn',
            'role' => 'admin',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff Replay Ops',
            'email' => 'staff_replay@mayhen.vn',
            'role' => 'staff',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Nguyễn Văn Minh',
            'email' => 'nguyenminh@mayhen.vn',
            'phone' => '0987654321',
            'role' => 'customer',
        ]);
    }

    /**
     * Test 1: Assemble unified multi-source timeline with PII masked by default.
     */
    public function test_operations_replay_assembles_multisource_timeline(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Văn Minh',
            'bride_name' => 'Thanh Thảo',
            'wedding_date' => Carbon::today()->addDays(20),
            'venue_name' => 'Capella Gallery Hall',
        ]);

        $plan = Plan::create([
            'name' => 'Gói Trọn Vẹn',
            'slug' => 'pro-replay',
            'price' => 299000,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'plan_id' => $plan->id,
            'order_code' => 'MH_REPLAY_101',
            'amount' => 299000,
            'status' => 'paid',
            'paid_at' => now()->subDays(3),
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Romantic Silk',
            'slug' => 'romantic-silk-replay',
            'thumbnail_url' => '/assets/silk.jpg',
            'is_active' => true,
        ]);

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $template->id,
            'slug' => 'van-minh-thanh-thao-replay',
            'is_published' => true,
            'view_count' => 45,
        ]);

        $guest = Guest::create([
            'wedding_id' => $wedding->id,
            'name' => 'Trần Hoàng Long',
            'phone' => '0909123456',
            'checked_in_at' => now(),
        ]);

        GuestResponse::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => 'Trần Hoàng Long',
            'attending_status' => 'attending',
            'adults_count' => 2,
        ]);

        $replay = OperationsReplayService::assembleTimeline('wedding', $wedding->id, true);

        $this->assertEquals('wedding', $replay['scope_type']);
        $this->assertGreaterThanOrEqual(4, $replay['total_steps']);
        $this->assertTrue($replay['is_masked']);

        // Verify PII is masked
        $events = collect($replay['timeline']);
        $weddingCreatedEvent = $events->firstWhere('event_type', 'wedding_profile_created');
        $this->assertNotNull($weddingCreatedEvent);
        $this->assertStringContainsString('***', $weddingCreatedEvent['actor']);
    }

    /**
     * Test 2: Super Admin can unmask PII with Safe Support Lens and audit trail.
     */
    public function test_super_admin_can_unmask_pii_with_audit_trail(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Hải Đăng',
            'bride_name' => 'Mai Phương',
            'wedding_date' => Carbon::today()->addDays(15),
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.intelligence.replay.show', [
            'type' => 'wedding',
            'id' => $wedding->id,
            'unmask' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('PII UNMASKED');
        $response->assertSee('nguyenminh@mayhen.vn');

        // Check audit log
        $logs = AuditLogger::getRecentLogs(20, 'unmask_replay_pii');
        $this->assertNotEmpty($logs);
        $this->assertEquals((string) $wedding->id, $logs[0]['target_id']);
    }

    /**
     * Test 3: Staff cannot unmask PII even if unmask query is passed.
     */
    public function test_staff_cannot_unmask_pii(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Đức Thịnh',
            'bride_name' => 'Kim Oanh',
            'wedding_date' => Carbon::today()->addDays(25),
        ]);

        $response = $this->actingAs($this->staff)->get(route('admin.intelligence.replay.show', [
            'type' => 'wedding',
            'id' => $wedding->id,
            'unmask' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('PII PROTECTION: ON');
        $response->assertDontSee('nguyenminh@mayhen.vn'); // Should be masked as ng***h@mayhen.vn
    }

    /**
     * Test 4: Anomaly detector flags urgent incident and prolonged gaps.
     */
    public function test_operations_replay_anomaly_detection(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Quốc Huy',
            'bride_name' => 'Ngọc Trâm',
            'wedding_date' => Carbon::today()->addDays(10),
            'created_at' => now()->subDays(5),
        ]);

        // Urgent ticket created
        SupportTicket::create([
            'user_id' => $this->customer->id,
            'ticket_code' => 'TK-ANOMALY-001',
            'subject' => 'Sự cố khẩn cấp sảnh tiệc',
            'body' => 'Báo lỗi hệ thống',
            'category' => 'general',
            'priority' => 'urgent',
            'status' => 'open',
        ]);

        $replay = OperationsReplayService::assembleTimeline('wedding', $wedding->id, true);

        $this->assertNotEmpty($replay['anomalies']);
        $anomalyTypes = collect($replay['anomalies'])->pluck('type')->toArray();
        $this->assertContains('urgent_incident', $anomalyTypes);
    }

    /**
     * Test 5: Replay index lookup screen renders successfully.
     */
    public function test_operations_replay_index_screen(): void
    {
        $response = $this->actingAs($this->staff)->get(route('admin.intelligence.replay.index'));

        $response->assertStatus(200);
        $response->assertSee('Read-Only Operations Replay');
        $response->assertSee('Buồng Phóng Replay');
    }

    /**
     * Test 6: Regular customer is forbidden from operations replay.
     */
    public function test_customer_forbidden_from_replay(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.intelligence.replay.index'));
        $response->assertRedirect(route('admin.login'));
    }
}
