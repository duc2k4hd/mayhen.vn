<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\SeatingCapacityGuardianService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Royal Romance',
        'slug' => 'royal-romance-'.uniqid(),
        'blade_view' => 'client.invitations.royal.template',
        'thumbnail_url' => 'https://mayhen.vn/assets/thumb.jpg',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Bảo Long',
        'bride_name' => 'Ngọc Trân',
        'wedding_date' => now()->addDays(15),
        'venue_name' => 'White Palace Phạm Văn Đồng',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'long-tran-wedding-'.uniqid(),
        'is_published' => true,
        'is_active' => true,
    ]);
});

it('analyzes seating health and detects overfilled and underfilled tables', function () {
    $tableOver = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Bạn Cấp 3',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $tableUnder = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Khách Vãng Lai',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $tableOptimal = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Công Ty Chú Rể',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    // Gán 12 khách cho tableOver (quá tải)
    for ($i = 1; $i <= 6; $i++) {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'table_id' => $tableOver->id,
            'name' => "Bạn Học {$i}",
            'expected_attendees' => 2,
        ]);
    }

    // Gán 2 khách cho tableUnder (dưới 50% sức chứa)
    Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'table_id' => $tableUnder->id,
        'name' => 'Khách Lẻ A',
        'expected_attendees' => 2,
    ]);

    // Gán 10 khách cho tableOptimal (hoàn hảo)
    for ($i = 1; $i <= 5; $i++) {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'table_id' => $tableOptimal->id,
            'name' => "Đồng Nghiệp {$i}",
            'expected_attendees' => 2,
        ]);
    }

    $service = app(SeatingCapacityGuardianService::class);
    $health = $service->analyzeSeatingHealth($this->wedding, $this->invitation);

    expect($health['metrics']['total_tables'])->toBe(3)
        ->and($health['metrics']['overfilled_tables_count'])->toBe(1)
        ->and($health['metrics']['underfilled_tables_count'])->toBe(1)
        ->and($health['metrics']['optimal_tables_count'])->toBe(1)
        ->and($health['metrics']['total_assigned'])->toBe(24);

    $overIssue = collect($health['issues'])->firstWhere('severity', 'critical');
    expect($overIssue)->not->toBeNull()
        ->and($overIssue['table_name'])->toBe('Bàn Bạn Cấp 3')
        ->and($overIssue['message'])->toContain('thừa 2 người');
});

it('detects mixed generation seating conflicts between senior family and young friends', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Ghép 01',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'table_id' => $table->id,
        'name' => 'Bác Trần Văn Phú',
        'group_name' => 'Gia Đình Họ Hàng Nhà Trai',
        'expected_attendees' => 2,
    ]);

    Guest::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'table_id' => $table->id,
        'name' => 'Nguyễn Minh Tuấn',
        'group_name' => 'Bạn Đại Học Hội Quẩy',
        'expected_attendees' => 4,
    ]);

    $service = app(SeatingCapacityGuardianService::class);
    $health = $service->analyzeSeatingHealth($this->wedding, $this->invitation);

    $tableAnalysis = collect($health['table_analyses'])->firstWhere('id', $table->id);
    expect($tableAnalysis['has_mixed_generations'])->toBeTrue();

    $genIssue = collect($health['issues'])->firstWhere('severity', 'info');
    expect($genIssue)->not->toBeNull()
        ->and($genIssue['table_name'])->toBe('Bàn Ghép 01')
        ->and($genIssue['message'])->toContain('Người Lớn Tuổi / Họ Hàng');
});

it('forecasts recommended official tables and 10 percent buffer tables', function () {
    // Tạo 45 khách xác nhận tham dự
    for ($i = 1; $i <= 9; $i++) {
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'name' => "Khách Mời {$i}",
            'expected_attendees' => 5,
        ]);

        GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $guest->id,
            'guest_name' => "Khách Mời {$i}",
            'attending_status' => 'attending',
            'adults_count' => 5,
            'children_count' => 0,
        ]);
    }

    $service = app(SeatingCapacityGuardianService::class);
    $health = $service->analyzeSeatingHealth($this->wedding, $this->invitation);

    // 45 khách -> 5 bàn chính thức, 1 bàn buffer
    expect($health['metrics']['recommended_official_tables'])->toBe(5)
        ->and($health['metrics']['recommended_buffer_tables'])->toBe(1);
});

it('renders seating guardian card on seating planner view', function () {
    $this->actingAs($this->user);

    WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP Gia Đình',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $response = $this->get(route('seating.index', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Seating Capacity Guardian')
        ->assertSee('/100 Điểm')
        ->assertSee('DỰ BÁO BÀN CHÍNH THỨC')
        ->assertSee('BÀN DỰ PHÒNG AN TOÀN');
});

it('returns json seating health check through api endpoint with ownership authorization', function () {
    $this->actingAs($this->user);

    WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn 01',
        'shape' => 'round',
        'capacity' => 10,
    ]);

    $response = $this->getJson(route('seating.health_check', $this->invitation->id));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'health' => [
                'health_score',
                'status',
                'status_label',
                'metrics' => [
                    'total_tables',
                    'total_capacity',
                    'total_assigned',
                    'total_unassigned',
                    'overall_occupancy_percent',
                    'overfilled_tables_count',
                    'underfilled_tables_count',
                    'optimal_tables_count',
                    'recommended_official_tables',
                    'recommended_buffer_tables',
                ],
                'table_analyses',
                'issues',
            ],
        ]);

    // Thử truy cập bằng tài khoản khác -> 404 (IDOR defense)
    $otherUser = User::factory()->create();
    $this->actingAs($otherUser);

    $forbidden = $this->getJson(route('seating.health_check', $this->invitation->id));
    $forbidden->assertNotFound();
});

it('warns couple when updating guest count or rsvp overloads assigned table capacity', function () {
    $this->actingAs($this->user);

    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn VIP Gia Đình',
        'shape' => 'round',
        'capacity' => 4,
    ]);

    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'table_id' => $table->id,
        'table_name' => $table->name,
        'name' => 'Bác Hai',
        'expected_attendees' => 2,
    ]);

    // 1. Cặp đôi sửa số người lên 6 (vượt sức chứa 4 ghế của bàn)
    $response = $this->put(route('guests.update', $guest->id), [
        'name' => 'Bác Hai (và các cháu)',
        'expected_attendees' => 6,
    ]);

    $response->assertSessionHas('warning');
    expect(session('warning'))->toContain('Bàn VIP Gia Đình')
        ->toContain('quá tải');

    // 2. Thử gọi qua JSON API
    $jsonRes = $this->putJson(route('guests.update', $guest->id), [
        'name' => 'Bác Hai (và các cháu)',
        'expected_attendees' => 6,
    ]);

    $jsonRes->assertOk();
    $jsonRes->assertJsonPath('is_table_overloaded', true);
    expect($jsonRes->json('warning'))->toContain('Bàn VIP Gia Đình');

    // 3. Cập nhật qua manual RSVP (thêm 5 người lớn)
    $rsvpRes = $this->postJson(route('guests.manual_rsvp', $guest->id), [
        'attending_status' => 'attending',
        'adults_count' => 5,
        'children_count' => 1,
    ]);

    $rsvpRes->assertOk();
    $rsvpRes->assertJsonPath('is_table_overloaded', true);
    expect($rsvpRes->json('warning'))->toContain('quá tải ghế sau khi cập nhật RSVP');
});

it('warns couple when adding a single guest that exceeds table capacity', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Bạn Thân',
        'shape' => 'round',
        'capacity' => 4,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('guests.store', $this->invitation->id), [
        'name' => 'Gia Đình Anh Tuấn',
        'salutation' => 'Anh',
        'group_name' => 'Bạn Bè',
        'expected_attendees' => 5, // Vượt quá 4 ghế của bàn
        'table_name' => 'Bàn Bạn Thân',
    ]);

    $response->assertRedirect(route('guests.index', $this->invitation->id));
    $response->assertSessionHas('success');
    $response->assertSessionHas('warning');
    expect(session('warning'))->toContain('Bàn Bạn Thân')
        ->toContain('quá tải ghế');
});

it('warns overload and filters profanity during batch guest import', function () {
    $table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Doanh Nhân',
        'shape' => 'round',
        'capacity' => 4,
    ]);

    $this->actingAs($this->user);

    $batchInput = "Nguyễn Văn A | Anh | Bạn Bè | 3 | 0911222333 | Bàn Doanh Nhân\n"
        ."Trần Thị B | Chị | Bạn Bè | 3 | 0944555666 | Bàn Doanh Nhân\n"
        .'Thằng đụ má | Bạn | Bạn Bè | 2 | | Bàn Doanh Nhân';

    $response = $this->post(route('guests.batch_store', $this->invitation->id), [
        'batch_input' => $batchInput,
        'default_salutation' => 'Thân mời',
        'default_group' => 'Bạn Bè',
    ]);

    $response->assertRedirect(route('guests.index', $this->invitation->id));
    $response->assertSessionHas('success');
    $response->assertSessionHas('warning');

    // Dòng chứa profanity bị lọc bỏ (chỉ tạo 2 khách)
    expect($this->wedding->guests()->count())->toBe(2);
    expect($this->wedding->guests()->pluck('name')->all())->toContain('Nguyễn Văn A', 'Trần Thị B');
    expect($this->wedding->guests()->where('name', 'like', '%đụ má%')->exists())->toBeFalse();

    // Cảnh báo quá tải được phát hiện
    expect(session('warning'))->toContain('Bàn Doanh Nhân')
        ->toContain('quá tải ghế');
});
