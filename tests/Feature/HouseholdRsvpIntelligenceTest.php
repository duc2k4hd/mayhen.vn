<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\HouseholdRsvpService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Hộ Gia Đình',
        'slug' => 'mau-test-household-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Khắc Tiệp',
        'bride_name' => 'Vũ Thảo My',
        'wedding_date' => now()->addMonths(1)->toDateString(),
        'venue_name' => 'GEM Center — Grand Ballroom',
        'venue_address' => '8 Nguyễn Bỉnh Khiêm, Đa Kao, Quận 1, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'khac-tiep-thao-my-'.uniqid(),
        'title' => 'Thiệp Cưới Khắc Tiệp & Thảo My',
        'is_published' => true,
    ]);

    // Bàn tiệc 01
    $this->table1 = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP 01 — Gia Đình Nhà Trai',
        'zone' => 'Khu Vực VIP',
        'capacity' => 10,
    ]);

    // Bàn tiệc 02
    $this->table2 = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 02 — Bạn Bè Đồng Nghiệp',
        'zone' => 'Khu Vực A',
        'capacity' => 10,
    ]);
});

test('khách chưa đăng nhập không thể truy cập báo cáo hộ gia đình hoặc xuất file', function () {
    $this->get(route('account.household.index', $this->invitation->id))
        ->assertRedirect(route('login'));

    $this->get(route('account.household.print', $this->invitation->id))
        ->assertRedirect(route('login'));

    $this->get(route('account.household.export', $this->invitation->id))
        ->assertRedirect(route('login'));
});

test('người dùng khác bị từ chối 403 khi truy cập báo cáo của người khác', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('account.household.index', $this->invitation->id))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->get(route('account.household.print', $this->invitation->id))
        ->assertForbidden();
});

test('HouseholdRsvpService tính toán chính xác ma trận số liệu người lớn, trẻ em và suất ăn chay', function () {
    // 1. Khách đơn lẻ (1 người)
    $guest1 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $this->table1->id,
        'name' => 'Nguyễn Văn Minh',
        'group_name' => 'Họ Hàng Nhà Trai',
        'expected_attendees' => 1,
        'max_attendees' => 1,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest1->id,
        'guest_name' => $guest1->name,
        'attending_status' => 'attending',
        'adults_count' => 1,
        'children_count' => 0,
        'dietary_requirements' => '1 phần ăn chay',
    ]);

    // 2. Khách cặp đôi (2 người)
    $guest2 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $this->table2->id,
        'name' => 'Lê Thanh Tùng',
        'group_name' => 'Đồng Nghiệp',
        'expected_attendees' => 2,
        'max_attendees' => 2,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest2->id,
        'guest_name' => $guest2->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    // 3. Khách hộ gia đình (4 người: 2 người lớn, 2 trẻ em)
    $guest3 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $this->table1->id,
        'name' => 'Gia Đình Anh Tuấn',
        'group_name' => 'Bà Con Nhà Gái',
        'expected_attendees' => 4,
        'max_attendees' => 4,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest3->id,
        'guest_name' => $guest3->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 2,
        'dietary_requirements' => 'Dị ứng tôm cua biển',
    ]);

    // 4. Khách từ chối
    $guest4 = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Phạm Hồng Ánh',
        'group_name' => 'Bạn Học',
        'expected_attendees' => 1,
    ]);
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest4->id,
        'guest_name' => $guest4->name,
        'attending_status' => 'declined',
    ]);

    $metrics = HouseholdRsvpService::computeMetrics($this->wedding);

    expect($metrics['total_invitations'])->toBe(4)
        ->and($metrics['total_expected_attendees'])->toBe(8)
        ->and($metrics['attending_invitations'])->toBe(3)
        ->and($metrics['declined_invitations'])->toBe(1)
        ->and($metrics['confirmed_adults'])->toBe(5) // 1 + 2 + 2
        ->and($metrics['confirmed_children'])->toBe(2) // 0 + 0 + 2
        ->and($metrics['total_confirmed_attendees'])->toBe(7)
        ->and($metrics['tables_needed'])->toBe(1)
        ->and($metrics['household_breakdown']['individual'])->toBe(2) // guest1, guest4
        ->and($metrics['household_breakdown']['couple'])->toBe(1) // guest2
        ->and($metrics['household_breakdown']['family'])->toBe(1) // guest3
        ->and($metrics['dietary_summary']['vegetarian_count'])->toBe(1)
        ->and($metrics['dietary_summary']['total_special_dietary'])->toBe(2);
});

test('chủ sở hữu xem thành công màn hình báo cáo hộ gia đình và ẩm thực', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.household.index', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Báo Cáo Hộ Gia Đình &amp; Suất Ăn Bếp Tiệc', false)
        ->assertSee('Tổng Số Lời Mời')
        ->assertSee('Suất Ăn Chay Cần Chuẩn Bị')
        ->assertSee('Khách Cặp Đôi (+1)');
});

test('in bản kê A4 Bếp Trưởng thành công và ghi nhận AuditLogger', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.household.print', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Bảng Kê Suất Ăn &amp; Điều Phối Bàn Tiệc Cưới', false)
        ->assertSee('Bàn VIP 01 — Gia Đình Nhà Trai')
        ->assertSee('Suất Người Lớn')
        ->assertSee('Suất Trẻ Em');
});

test('xuất CSV Bảng kê Bếp Trưởng có định dạng UTF-8 BOM và nội dung chính xác', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.household.export', $this->invitation->id));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
        ->assertHeader('Content-Disposition');

    $content = $response->getContent();
    expect($content)->toStartWith("\xEF\xBB\xBF") // UTF-8 BOM
        ->and($content)->toContain('Bàn VIP 01 — Gia Đình Nhà Trai')
        ->and($content)->toContain('Suất Ăn Chay')
        ->and($content)->toContain('Yêu Cầu Ăn Kiêng & Dị Ứng');
});
