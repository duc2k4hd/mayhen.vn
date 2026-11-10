<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GuestSmartCsvImportAndSegmentationTest extends TestCase
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
            'groom_name' => 'Hoàng Phúc',
            'bride_name' => 'Ngọc Linh',
            'wedding_date' => '2026-11-20',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Mẫu Thử Nghiệm CSV',
            'slug' => 'mau-thu-nghiem-csv',
            'blade_view' => 'client.invitations.celestial.template',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'hoang-phuc-ngoc-linh-csv',
            'is_published' => true,
        ]);
    }

    public function test_user_can_download_standard_csv_template(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('guests.csv_template', $this->invitation->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="mau_danh_sach_khach_moi_mayhen.csv"');

        $content = $response->getContent();
        // Kiểm tra UTF-8 BOM chống vỡ font Excel
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Họ và Tên', $content);
        $this->assertStringContainsString('Danh Xưng', $content);
        $this->assertStringContainsString('Nhóm Khách', $content);
        $this->assertStringContainsString('Số Lượng Mời', $content);
        $this->assertStringContainsString('Số Điện Thoại', $content);
        $this->assertStringContainsString('Bàn Tiệc', $content);
    }

    public function test_user_can_import_csv_with_comma_delimiter(): void
    {
        $csvContent = "\xEF\xBB\xBF"."Họ và Tên,Danh Xưng,Nhóm Khách,Số Lượng Mời,Số Điện Thoại,Bàn Tiệc,Ghi Chú\n".
            "Bác Hoàng Văn Nam,Kính mời Bác,Họ Nhà Trai,2,0912345678,Bàn 01,Trưởng họ\n".
            "Cô Trần Thị Bích,Kính mời Cô,Họ Nhà Trai,1,0987654321,Bàn 02,Khách quý\n";

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post(route('guests.import_csv', $this->invitation->id), [
                'csv_file' => $file,
                'duplicate_mode' => 'skip',
                'default_salutation' => 'Thân mời',
                'default_group' => 'Bạn Bè',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Hoàng Văn Nam',
            'salutation' => 'Kính mời Bác',
            'group_name' => 'Họ Nhà Trai',
            'expected_attendees' => 2,
            'phone' => '0912345678',
            'table_name' => 'Bàn 01',
            'check_in_note' => 'Trưởng họ',
        ]);

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $this->wedding->id,
            'name' => 'Cô Trần Thị Bích',
            'phone' => '0987654321',
        ]);

        // Kiểm tra Audit Log
        $logs = AuditLogger::getRecentLogs(10, 'GUEST_CSV_IMPORTED');
        $this->assertNotEmpty($logs);
        $this->assertEquals('wedding', $logs[0]['target_type']);
        $this->assertEquals((string) $this->wedding->id, $logs[0]['target_id']);
    }

    public function test_user_can_import_csv_with_semicolon_delimiter_and_phone_normalization(): void
    {
        // Delimiter ; phổ biến từ Excel tiếng Việt và SĐT có đầu +84
        $csvContent = "Họ và Tên;Danh Xưng;Nhóm Khách;Số Lượng Mời;Số Điện Thoại;Bàn Tiệc\n".
            "Anh Tuấn Anh;Thân mời;Bạn Cấp 3;2;+84901234567;Bàn VIP\n";

        $file = UploadedFile::fake()->createWithContent('guests_excel.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post(route('guests.import_csv', $this->invitation->id), [
                'csv_file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $this->wedding->id,
            'name' => 'Anh Tuấn Anh',
            'phone' => '0901234567', // Được chuẩn hóa từ +84901234567 thành 0901234567
            'group_name' => 'Bạn Cấp 3',
        ]);
    }

    public function test_csv_import_handles_duplicate_skip_mode(): void
    {
        // Tạo khách trước
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Chị Kim Oanh',
            'salutation' => 'Thương mời',
            'group_name' => 'Họ Nhà Gái',
            'phone' => '0933333333',
            'expected_attendees' => 1,
            'guest_token' => 'token_oanh_123',
        ]);

        $csvContent = "Họ và Tên,Danh Xưng,Nhóm Khách,Số Lượng Mời,Số Điện Thoại\n".
            "Chị Kim Oanh,Thân mời,Họ Nhà Gái,4,0933333333\n";

        $file = UploadedFile::fake()->createWithContent('duplicate.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post(route('guests.import_csv', $this->invitation->id), [
                'csv_file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Số khách vẫn là 1 và thông tin cũ không đổi
        $guest = Guest::where('phone', '0933333333')->first();
        $this->assertEquals(1, $guest->expected_attendees);
        $this->assertEquals('Thương mời', $guest->salutation);
    }

    public function test_csv_import_filters_profane_rows(): void
    {
        $csvContent = "Họ và Tên,Danh Xưng,Nhóm Khách,Số Lượng Mời,Số Điện Thoại\n".
            "Khách Hợp Lệ,Thân mời,Bạn Bè,1,0911223344\n".
            "Kẻ fuck xấu xa,Thân mời,Bạn Bè,1,0999888777\n";

        $file = UploadedFile::fake()->createWithContent('profane.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post(route('guests.import_csv', $this->invitation->id), [
                'csv_file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('guests', [
            'wedding_id' => $this->wedding->id,
            'name' => 'Khách Hợp Lệ',
        ]);

        $this->assertDatabaseMissing('guests', [
            'wedding_id' => $this->wedding->id,
            'phone' => '0999888777',
        ]);
    }

    public function test_csv_import_handles_duplicate_update_mode(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Chị Kim Oanh',
            'salutation' => 'Thương mời',
            'group_name' => 'Họ Nhà Gái',
            'phone' => '0933333333',
            'expected_attendees' => 1,
            'guest_token' => 'token_oanh_456',
        ]);

        $csvContent = "Họ và Tên,Danh Xưng,Nhóm Khách,Số Lượng Mời,Số Điện Thoại,Bàn Tiệc,Ghi Chú\n".
            "Chị Kim Oanh,Kính mời Chị,Họ Nhà Gái,3,0933333333,Bàn VIP 09,Đã cập nhật số người\n";

        $file = UploadedFile::fake()->createWithContent('duplicate_update.csv', $csvContent);

        $response = $this->actingAs($this->user)
            ->post(route('guests.import_csv', $this->invitation->id), [
                'csv_file' => $file,
                'duplicate_mode' => 'update',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Thông tin đã được update
        $guest = Guest::where('phone', '0933333333')->first();
        $this->assertEquals(3, $guest->expected_attendees);
        $this->assertEquals('Kính mời Chị', $guest->salutation);
        $this->assertEquals('Bàn VIP 09', $guest->table_name);
        $this->assertEquals('Đã cập nhật số người', $guest->check_in_note);
    }

    public function test_guest_segmentation_filter_by_side_and_status(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Bác Hùng Nhà Trai',
            'group_name' => 'Họ Nhà Trai',
            'guest_token' => 'token_groom_1',
            'open_count' => 5,
        ]);

        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Cô Mai Nhà Gái',
            'group_name' => 'Họ Nhà Gái',
            'guest_token' => 'token_bride_1',
            'open_count' => 0,
        ]);

        // Lọc theo Nhà Trai
        $groomResponse = $this->actingAs($this->user)
            ->get(route('guests.index', ['id' => $this->invitation->id, 'side' => 'groom']));
        $groomResponse->assertOk();
        $groomResponse->assertSee('Bác Hùng Nhà Trai');
        $groomResponse->assertDontSee('Cô Mai Nhà Gái');

        // Lọc theo Nhà Gái
        $brideResponse = $this->actingAs($this->user)
            ->get(route('guests.index', ['id' => $this->invitation->id, 'side' => 'bride']));
        $brideResponse->assertOk();
        $brideResponse->assertSee('Cô Mai Nhà Gái');
        $brideResponse->assertDontSee('Bác Hùng Nhà Trai');

        // Lọc theo trạng thái đã mở thiệp (opened)
        $openedResponse = $this->actingAs($this->user)
            ->get(route('guests.index', ['id' => $this->invitation->id, 'status' => 'opened']));
        $openedResponse->assertOk();
        $openedResponse->assertSee('Bác Hùng Nhà Trai');
        $openedResponse->assertDontSee('Cô Mai Nhà Gái');
    }
}
