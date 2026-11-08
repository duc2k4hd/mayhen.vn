<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Resilience Kit Test',
        'slug' => 'mau-resilience-'.uniqid(),
        'description' => 'Test Template Resilience Kit',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đức Duy',
        'bride_name' => 'Ngọc Mai',
        'wedding_date' => now()->addDays(15),
        'venue_name' => 'Trung Tâm Hội Nghị Asiana Plaza',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'duc-duy-ngoc-mai-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'checkin_pin' => '654321',
        ],
    ]);

    $this->table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 01 - VIP',
        'shape' => 'round',
        'capacity' => 10,
        'zone' => 'Khu VIP Sân Khấu',
        'display_order' => 1,
    ]);

    $this->guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn Minh',
        'salutation' => 'Anh',
        'group_name' => 'Bạn Đại Học',
        'table_name' => 'Bàn 01 - VIP',
        'table_id' => $this->table->id,
        'expected_attendees' => 2,
    ]);

    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $this->guest->id,
        'guest_name' => $this->guest->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);
});

test('unauthenticated visitor cannot access resilience kit', function () {
    $response = $this->get(route('account.invitations.resilience_kit', $this->invitation->id));
    $response->assertRedirect(route('login'));
});

test('other user cannot access owner resilience kit (404 protection)', function () {
    $this->actingAs($this->otherUser);
    $response = $this->get(route('account.invitations.resilience_kit', $this->invitation->id));
    $response->assertNotFound();
});

test('owner can access wedding day resilience kit and view all printed manifests', function () {
    $this->actingAs($this->user);
    $response = $this->get(route('account.invitations.resilience_kit', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Bộ Dự Phòng Sự Cố Ngày Cưới');
    $response->assertSee('Đức Duy');
    $response->assertSee('Ngọc Mai');
    $response->assertSee('654321'); // Mã PIN tiếp tân
    $response->assertSee('Bàn 01 - VIP');
    $response->assertSee('Nguyễn Văn Minh');
    $response->assertSee('In Tài Liệu Dự Phòng (A4)');
    $response->assertSee('Trung Tâm Hội Nghị Asiana Plaza');
});

test('resilience kit renders emergency runbook and pii phone masking accurately', function () {
    // Tạo khách có số điện thoại để kiểm tra masking
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Trần Thị Thu Hà',
        'salutation' => 'Chị',
        'phone' => '0912345678',
        'table_name' => 'Bàn 01 - VIP',
        'table_id' => $this->table->id,
        'expected_attendees' => 1,
    ]);

    $this->actingAs($this->user);
    $response = $this->get(route('account.invitations.resilience_kit', $this->invitation->id));

    $response->assertOk();
    // Kiểm tra quy trình 4 bước sự cố mạng
    $response->assertSee('Kế Hoạch Vận Hành Khi Sự Cố Mạng / Mất Điện Tại Tiệc Cưới');
    $response->assertSee('Bước 1: Tiếp đón bằng bản in');
    $response->assertSee('Bước 2: Phát 4G dự phòng');
    $response->assertSee('Bước 3: Sử dụng Mã PIN Tiếp Tân');
    $response->assertSee('Bước 4: Trợ giúp kỹ thuật');

    // Kiểm tra có cả bản đầy đủ và bản mask 4 số giữa SĐT
    $response->assertSee('0912345678');
    $response->assertSee('091****678');
    $response->assertSee('Ẩn 4 số giữa của SĐT');
});
