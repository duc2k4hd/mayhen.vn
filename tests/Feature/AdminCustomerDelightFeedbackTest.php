<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use App\Services\CustomerDelightService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerDelightFeedbackTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Delight',
            'email' => 'admin_delight@mayhen.vn',
            'role' => 'admin',
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Staff CSKH',
            'email' => 'staff_cskh@mayhen.vn',
            'role' => 'staff',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Cô Dâu Thảo My',
            'email' => 'thaomy@mayhen.vn',
            'phone' => '0912345678',
            'role' => 'customer',
        ]);
    }

    /**
     * Test 1: Record 5-star feedback and compute CSAT/NPS metrics.
     */
    public function test_record_positive_feedback_computes_csat_metrics(): void
    {
        $wedding = Wedding::create([
            'user_id' => $this->customer->id,
            'groom_name' => 'Hoàng Phúc',
            'bride_name' => 'Thảo My',
            'wedding_date' => Carbon::today()->addDays(30),
        ]);

        $fb = CustomerDelightService::recordFeedback([
            'user_id' => $this->customer->id,
            'wedding_id' => $wedding->id,
            'milestone' => 'after_publish',
            'rating' => 5,
            'category' => 'template_design',
            'comment' => 'Mẫu thiệp hiệu ứng hoa tuyết rất đẹp, thiệp lộng lẫy và mở mượt mà!',
        ], $this->customer);

        $this->assertEquals(5, $fb->rating);
        $this->assertEquals('delighted', $fb->sentiment);
        $this->assertNull($fb->linked_ticket_id);
        $this->assertEquals('resolved', $fb->action_status);

        $metrics = CustomerDelightService::getDelightMetrics();
        $this->assertEquals(1, $metrics['total_feedbacks']);
        $this->assertEquals(5.0, $metrics['average_score']);
        $this->assertEquals(100, $metrics['satisfaction_rate']);
        $this->assertEquals(100, $metrics['nps_score']);
        $this->assertEquals(0, $metrics['alerts_count']);
    }

    /**
     * Test 2: Low-rating feedback automatically creates an urgent Support Ticket.
     */
    public function test_low_rating_feedback_automatically_spawns_urgent_support_ticket(): void
    {
        $fb = CustomerDelightService::recordFeedback([
            'user_id' => $this->customer->id,
            'milestone' => 'after_publish',
            'rating' => 1,
            'category' => 'studio_editor',
            'comment' => 'Bị lỗi không tải được ảnh cưới lên, nhờ kỹ thuật hỗ trợ gấp!',
        ], $this->customer);

        $this->assertEquals(1, $fb->rating);
        $this->assertEquals('frustrated', $fb->sentiment);
        $this->assertNotNull($fb->linked_ticket_id);
        $this->assertEquals('pending_review', $fb->action_status);

        // Verify ticket properties
        $ticket = SupportTicket::find($fb->linked_ticket_id);
        $this->assertNotNull($ticket);
        $this->assertEquals('urgent', $ticket->priority);
        $this->assertEquals('open', $ticket->status);
        $this->assertStringContainsString('CSAT Cảnh Báo', $ticket->subject);

        $metrics = CustomerDelightService::getDelightMetrics();
        $this->assertEquals(1, $metrics['alerts_count']);
    }

    /**
     * Test 3: Staff can access Feedback Loop dashboard and filter by rating/milestone.
     */
    public function test_staff_can_view_feedback_loop_and_filter(): void
    {
        CustomerDelightService::recordFeedback([
            'user_id' => $this->customer->id,
            'milestone' => 'after_payment',
            'rating' => 4,
            'category' => 'pricing',
            'comment' => 'Gói cước hợp lý, kích hoạt nhanh.',
        ], $this->customer);

        CustomerDelightService::recordFeedback([
            'user_id' => $this->customer->id,
            'milestone' => 'post_wedding',
            'rating' => 2,
            'category' => 'customer_service',
            'comment' => 'Phản hồi lúc đêm muộn hơi chậm một chút.',
        ], $this->customer);

        $response = $this->actingAs($this->staff)->get(route('admin.intelligence.feedback_loop'));

        $response->assertStatus(200);
        $response->assertSee('Customer Delight Feedback Loop');
        $response->assertSee('Điểm CSAT Trung Bình');
        $response->assertSee('Gói cước hợp lý, kích hoạt nhanh.');
        $response->assertSee('Cô Dâu Thảo My');

        // Filter by rating 2
        $filterResponse = $this->actingAs($this->staff)->get(route('admin.intelligence.feedback_loop', ['rating' => 2]));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Phản hồi lúc đêm muộn');
        $filterResponse->assertDontSee('Gói cước hợp lý');
    }

    /**
     * Test 4: Staff resolves low rating feedback with an Action Plan note.
     */
    public function test_staff_resolves_feedback_with_action_plan_note(): void
    {
        $fb = CustomerDelightService::recordFeedback([
            'user_id' => $this->customer->id,
            'milestone' => 'after_publish',
            'rating' => 2,
            'category' => 'studio_editor',
            'comment' => 'Chưa rõ cách thêm số tài khoản ngân hàng.',
        ], $this->customer);

        $this->assertTrue($fb->requiresAction());

        $response = $this->actingAs($this->staff)->post(route('admin.intelligence.resolve_feedback', $fb->id), [
            'action_plan_note' => 'Đã gọi điện hướng dẫn cô dâu điền STK và bật mã VietQR tự động thành công.',
            'action_status' => 'resolved',
        ]);

        $response->assertSessionHas('success');

        $fb->refresh();
        $this->assertEquals('resolved', $fb->action_status);
        $this->assertEquals($this->staff->id, $fb->handled_by);
        $this->assertNotNull($fb->handled_at);
        $this->assertStringContainsString('Đã gọi điện hướng dẫn', $fb->action_plan_note);

        // Check metrics: alert resolved
        $metrics = CustomerDelightService::getDelightMetrics();
        $this->assertEquals(0, $metrics['alerts_count']);
    }

    /**
     * Test 5: Client API submit feedback.
     */
    public function test_client_api_feedback_submission(): void
    {
        $response = $this->actingAs($this->customer)->postJson(route('feedback.submit'), [
            'milestone' => 'after_publish',
            'rating' => 5,
            'category' => 'template_design',
            'comment' => 'Thao tác tạo thiệp rất nhanh chóng và tiện lợi!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('customer_feedbacks', [
            'user_id' => $this->customer->id,
            'rating' => 5,
            'sentiment' => 'delighted',
        ]);
    }

    /**
     * Test 6: Regular customer is forbidden from admin feedback screen.
     */
    public function test_customer_forbidden_from_admin_feedback_screen(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.intelligence.feedback_loop'));
        $response->assertRedirect(route('admin.login'));
    }
}
