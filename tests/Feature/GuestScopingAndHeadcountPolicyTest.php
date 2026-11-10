<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestScopingAndHeadcountPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $stranger;

    private Invitation $invitation;

    private Wedding $wedding;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->stranger = User::factory()->create();

        $template = InvitationTemplate::create([
            'name' => 'Minimalist Cream',
            'slug' => 'minimalist-cream-'.uniqid(),
            'blade_view' => 'client.invitations.celestial.template',
            'thumbnail_url' => '/test/thumb.jpg',
            'is_active' => true,
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'template_id' => $template->id,
            'groom_name' => 'Minh Khang',
            'bride_name' => 'Hải Yến',
            'wedding_date' => now()->addMonths(3)->toDateString(),
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'title' => 'Thiệp cưới Minh Khang & Hải Yến',
            'slug' => 'minh-khang-hai-yen-'.uniqid(),
            'status' => 'published',
            'is_published' => true,
            'is_active' => true,
        ]);
    }

    public function test_owner_can_store_guest_with_max_attendees_and_event_scope(): void
    {
        $payload = [
            'name' => 'Bác Trần Văn Tuấn',
            'salutation' => 'Kính mời',
            'group_name' => 'Họ Nhà Trai',
            'expected_attendees' => 2,
            'max_attendees' => 3,
            'event_ids' => [1, 2],
            'phone' => '0901234567',
        ];

        $response = $this->actingAs($this->owner)
            ->post(route('guests.store', $this->invitation->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('guests', [
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Trần Văn Tuấn',
            'max_attendees' => 3,
        ]);

        $guest = Guest::where('name', 'Bác Trần Văn Tuấn')->firstOrFail();
        $this->assertEquals([1, 2], $guest->event_ids);
        $this->assertTrue($guest->isEventAllowed(1));
        $this->assertTrue($guest->isEventAllowed(2));
        $this->assertFalse($guest->isEventAllowed(99));
    }

    public function test_owner_can_update_guest_scoping_and_max_attendees(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bạn Nguyễn Văn Nam',
            'salutation' => 'Thân mời',
            'group_name' => 'Bạn Đại Học',
            'expected_attendees' => 1,
            'guest_token' => 'token-nam-123456',
        ]);

        $updatePayload = [
            'name' => 'Bạn Nguyễn Văn Nam & Người Yêu',
            'salutation' => 'Thân mời',
            'group_name' => 'Bạn Đại Học',
            'expected_attendees' => 2,
            'max_attendees' => 2,
            'event_ids' => [3],
        ];

        $response = $this->actingAs($this->owner)
            ->put(route('guests.update', $guest->id), $updatePayload);

        $response->assertRedirect();
        $guest->refresh();

        $this->assertEquals(2, $guest->max_attendees);
        $this->assertEquals([3], $guest->event_ids);
        $this->assertEquals(2, $guest->getAllowedMaxAttendees());
    }

    public function test_owner_can_revoke_and_regenerate_guest_token(): void
    {
        $oldToken = 'token-original-99999';
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Chị Lê Thị Mai',
            'salutation' => 'Thân mời',
            'group_name' => 'Đồng Nghiệp',
            'guest_token' => $oldToken,
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('guests.regenerate_token', $guest->id));

        $response->assertRedirect();
        $guest->refresh();

        $this->assertNotEquals($oldToken, $guest->guest_token);
        $this->assertTrue($guest->isTokenRevoked($oldToken));
        $this->assertContains($oldToken, $guest->revoked_tokens);
    }

    public function test_stranger_cannot_regenerate_guest_token(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Chị Lê Thị Mai',
            'guest_token' => 'token-secure-11111',
        ]);

        $response = $this->actingAs($this->stranger)
            ->post(route('guests.regenerate_token', $guest->id));

        $response->assertStatus(404);
    }

    public function test_guest_with_revoked_token_is_blocked_from_rsvp(): void
    {
        $oldToken = 'revoked-token-abcxyz';
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Anh Hoàng Tuấn',
            'guest_token' => 'new-active-token-777',
            'revoked_tokens' => [$oldToken],
        ]);

        $rsvpPayload = [
            'guest_name' => 'Anh Hoàng Tuấn',
            'attending_status' => 'attending',
            'adults_count' => 1,
            'children_count' => 0,
        ];

        $response = $this->postJson(route('rsvp.submit', [
            'slug' => $this->invitation->slug,
            'token' => $oldToken,
        ]), $rsvpPayload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'is_revoked' => true,
        ]);
        $response->assertJsonFragment([
            'message' => 'Đường link thiệp mời này đã được chủ tiệc thu hồi và cấp lại mã mới. Quý khách vui lòng liên hệ cô dâu chú rể để nhận đường link thiệp mới nhất.',
        ]);
    }

    public function test_guest_with_revoked_token_accessing_invitation_gets_410(): void
    {
        $oldToken = 'revoked-token-access-999';
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách Cũ',
            'guest_token' => 'new-active-token-888',
            'revoked_tokens' => [$oldToken],
        ]);

        $response = $this->get(route('invitation.show', [
            'slug' => $this->invitation->slug,
            'token' => $oldToken,
        ]));

        $response->assertStatus(410);
    }

    public function test_guest_cannot_rsvp_exceeding_max_attendees(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bạn Quốc Bảo',
            'guest_token' => 'token-bao-policy',
            'expected_attendees' => 1,
            'max_attendees' => 2, // Chỉ cho phép tối đa 2 người (+1)
        ]);

        // Gửi 2 người lớn + 1 trẻ em = 3 người -> Vượt quá 2 người
        $rsvpPayload = [
            'guest_name' => 'Bạn Quốc Bảo',
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 1,
        ];

        $response = $this->postJson(route('rsvp.submit', [
            'slug' => $this->invitation->slug,
            'token' => $guest->guest_token,
        ]), $rsvpPayload);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'headcount_exceeded' => true,
        ]);
        $this->assertStringContainsString('vượt quá số suất thiệp mời cho phép (tối đa 2 suất)', $response->json('message'));
    }

    public function test_guest_can_rsvp_within_max_attendees(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bạn Thùy Trang',
            'guest_token' => 'token-trang-ok',
            'expected_attendees' => 1,
            'max_attendees' => 2,
        ]);

        // Gửi đúng 2 người lớn + 0 trẻ em = 2 người <= 2
        $rsvpPayload = [
            'guest_name' => 'Bạn Thùy Trang',
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 0,
            'dietary_requirements' => 'Không ăn cay',
        ];

        $response = $this->postJson(route('rsvp.submit', [
            'slug' => $this->invitation->slug,
            'token' => $guest->guest_token,
        ]), $rsvpPayload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('guest_responses', [
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'adults_count' => 2,
            'children_count' => 0,
        ]);
    }

    public function test_guest_cannot_rsvp_for_unauthorized_event_scope(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Sáu (Chỉ dự Vu Quy)',
            'guest_token' => 'token-bac-sau-scope',
            'event_ids' => [10], // Chỉ được mời sự kiện 10
        ]);

        $rsvpPayload = [
            'guest_name' => 'Bác Sáu (Chỉ dự Vu Quy)',
            'attending_status' => 'attending',
            'adults_count' => 1,
            'event_id' => 20, // Gửi nhầm sự kiện 20
        ];

        $response = $this->postJson(route('rsvp.submit', [
            'slug' => $this->invitation->slug,
            'token' => $guest->guest_token,
        ]), $rsvpPayload);

        $response->assertStatus(422);
        $this->assertStringContainsString('không thuộc phạm vi sự kiện này', $response->json('message'));
    }

    public function test_guest_without_scoping_can_rsvp_normally(): void
    {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bạn Tự Do',
            'guest_token' => 'token-tu-do',
            'max_attendees' => null, // Không giới hạn
            'event_ids' => null, // Mọi sự kiện
        ]);

        $rsvpPayload = [
            'guest_name' => 'Bạn Tự Do',
            'attending_status' => 'attending',
            'adults_count' => 4,
            'children_count' => 2,
        ];

        $response = $this->postJson(route('rsvp.submit', [
            'slug' => $this->invitation->slug,
            'token' => $guest->guest_token,
        ]), $rsvpPayload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('guest_responses', [
            'guest_id' => $guest->id,
            'adults_count' => 4,
            'children_count' => 2,
        ]);
    }
}
