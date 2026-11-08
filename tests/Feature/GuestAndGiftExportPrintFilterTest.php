<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAndGiftExportPrintFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Wedding $wedding;

    private Invitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->wedding = Wedding::create([
            'user_id' => $this->user->id,
            'groom_name' => 'Thành Nam',
            'bride_name' => 'Thu Hương',
            'wedding_date' => '2026-11-20',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Velvet Ruby',
            'slug' => 'velvet-ruby',
            'blade_view' => 'client.invitations.velvet-ruby.template',
            'thumbnail_url' => '/assets/client/images/templates/velvet-ruby.jpg',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'thanh-nam-thu-huong-filter',
            'is_published' => true,
        ]);
    }

    public function test_guests_export_csv_respects_group_and_status_filters(): void
    {
        // Khách 1: Nhóm Bạn bè, đã xác nhận tham dự
        $guest1 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Hoàng Nam',
            'group_name' => 'Bạn Bè',
            'table_name' => 'Bàn 01',
            'expected_attendees' => 2,
            'guest_token' => 'token-test-1',
        ]);
        GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest1->id,
            'guest_name' => 'Hoàng Nam',
            'attending_status' => 'attending',
            'adults_count' => 2,
        ]);

        // Khách 2: Nhóm Họ Nhà Gái, từ chối tham gia
        $guest2 = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Sáu',
            'group_name' => 'Họ Nhà Gái',
            'table_name' => 'Bàn 02',
            'expected_attendees' => 1,
            'guest_token' => 'token-test-2',
        ]);
        GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest2->id,
            'guest_name' => 'Bác Sáu',
            'attending_status' => 'declined',
        ]);

        // Khách 3: Nhóm Bạn bè, chưa phản hồi (pending)
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Trần Văn Cường',
            'group_name' => 'Bạn Bè',
            'table_name' => 'Bàn 01',
            'expected_attendees' => 1,
            'guest_token' => 'token-test-3',
        ]);

        // 1. Xuất không lọc -> Đủ cả 3 khách
        $respAll = $this->actingAs($this->user)->get(route('guests.export', $this->invitation->id));
        $respAll->assertOk();
        $contentAll = $respAll->streamedContent();
        $this->assertStringContainsString('Hoàng Nam', $contentAll);
        $this->assertStringContainsString('Bác Sáu', $contentAll);
        $this->assertStringContainsString('Trần Văn Cường', $contentAll);

        // 2. Xuất lọc theo nhóm "Họ Nhà Gái"
        $respGroup = $this->actingAs($this->user)->get(route('guests.export', [
            'id' => $this->invitation->id,
            'group' => 'Họ Nhà Gái',
        ]));
        $respGroup->assertOk();
        $contentGroup = $respGroup->streamedContent();
        $this->assertStringContainsString('Bác Sáu', $contentGroup);
        $this->assertStringNotContainsString('Hoàng Nam', $contentGroup);
        $this->assertStringNotContainsString('Trần Văn Cường', $contentGroup);

        // 3. Xuất lọc theo trạng thái "attending"
        $respAttending = $this->actingAs($this->user)->get(route('guests.export', [
            'id' => $this->invitation->id,
            'status' => 'attending',
        ]));
        $respAttending->assertOk();
        $contentAttending = $respAttending->streamedContent();
        $this->assertStringContainsString('Hoàng Nam', $contentAttending);
        $this->assertStringNotContainsString('Bác Sáu', $contentAttending);
        $this->assertStringNotContainsString('Trần Văn Cường', $contentAttending);

        // 4. Xuất lọc theo từ khóa tìm kiếm "Cường"
        $respSearch = $this->actingAs($this->user)->get(route('guests.export', [
            'id' => $this->invitation->id,
            'q' => 'Cường',
        ]));
        $respSearch->assertOk();
        $contentSearch = $respSearch->streamedContent();
        $this->assertStringContainsString('Trần Văn Cường', $contentSearch);
        $this->assertStringNotContainsString('Hoàng Nam', $contentSearch);
    }

    public function test_guests_print_page_respects_filters(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Nguyễn Minh Quân',
            'group_name' => 'Đồng Nghiệp',
            'table_name' => 'Bàn VIP',
            'expected_attendees' => 2,
            'guest_token' => 'token-print-1',
        ]);

        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Đặng Thị Lan',
            'group_name' => 'Họ Nhà Trai',
            'table_name' => 'Bàn 03',
            'expected_attendees' => 1,
            'guest_token' => 'token-print-2',
        ]);

        // Lọc theo bàn VIP
        $response = $this->actingAs($this->user)->get(route('guests.print', [
            'id' => $this->invitation->id,
            'table' => 'Bàn VIP',
        ]));

        $response->assertOk()
            ->assertSee('Nguyễn Minh Quân')
            ->assertDontSee('Đặng Thị Lan')
            ->assertSee('Bàn: Bàn VIP');
    }

    public function test_other_user_cannot_export_or_print_guests(): void
    {
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('guests.export', $this->invitation->id))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('guests.print', $this->invitation->id))
            ->assertNotFound();
    }

    public function test_gifts_export_csv_respects_method_and_acknowledgement_filters(): void
    {
        WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'sender_name' => 'Cô Ba Hải Phòng',
            'sender_group' => 'Họ Hàng',
            'amount' => 2000000,
            'payment_method' => 'bank_transfer',
            'is_acknowledged' => true,
        ]);

        WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'sender_name' => 'Bạn Thân Tuấn',
            'sender_group' => 'Bạn Bè',
            'amount' => 1000000,
            'payment_method' => 'cash',
            'is_acknowledged' => false,
        ]);

        // 1. Xuất không lọc -> Có cả 2
        $respAll = $this->actingAs($this->user)->get(route('account.gifts.export', $this->invitation->id));
        $respAll->assertOk();
        $contentAll = $respAll->streamedContent();
        $this->assertStringContainsString('Cô Ba Hải Phòng', $contentAll);
        $this->assertStringContainsString('Bạn Thân Tuấn', $contentAll);

        // 2. Xuất lọc theo hình thức cash (tiền mặt)
        $respCash = $this->actingAs($this->user)->get(route('account.gifts.export', [
            'id' => $this->invitation->id,
            'method' => 'cash',
        ]));
        $respCash->assertOk();
        $contentCash = $respCash->streamedContent();
        $this->assertStringContainsString('Bạn Thân Tuấn', $contentCash);
        $this->assertStringNotContainsString('Cô Ba Hải Phòng', $contentCash);

        // 3. Xuất lọc theo trạng thái cảm ơn (yes)
        $respAck = $this->actingAs($this->user)->get(route('account.gifts.export', [
            'id' => $this->invitation->id,
            'ack' => 'yes',
        ]));
        $respAck->assertOk();
        $contentAck = $respAck->streamedContent();
        $this->assertStringContainsString('Cô Ba Hải Phòng', $contentAck);
        $this->assertStringNotContainsString('Bạn Thân Tuấn', $contentAck);
    }

    public function test_gifts_print_page_respects_filters(): void
    {
        WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'sender_name' => 'Chú Bảy Cần Thơ',
            'sender_group' => 'Bà Con Xa',
            'amount' => 5000000,
            'payment_method' => 'bank_transfer',
            'is_acknowledged' => true,
        ]);

        WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'sender_name' => 'Đồng Nghiệp Marketing',
            'sender_group' => 'Đồng Nghiệp',
            'amount' => 500000,
            'payment_method' => 'cash',
            'is_acknowledged' => false,
        ]);

        // Lọc in ấn theo từ khóa "Cần Thơ"
        $response = $this->actingAs($this->user)->get(route('account.gifts.print', [
            'id' => $this->invitation->id,
            'q' => 'Cần Thơ',
        ]));

        $response->assertOk()
            ->assertSee('Chú Bảy Cần Thơ')
            ->assertDontSee('Đồng Nghiệp Marketing')
            ->assertSee('5.000.000');
    }

    public function test_other_user_cannot_export_or_print_gifts(): void
    {
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('account.gifts.export', $this->invitation->id))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('account.gifts.print', $this->invitation->id))
            ->assertNotFound();
    }
}
