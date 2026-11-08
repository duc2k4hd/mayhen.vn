<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingGift;
use App\Models\WeddingWish;
use App\Services\CsvSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvFormulaInjectionDefenseTest extends TestCase
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
            'groom_name' => 'Gia Huy',
            'bride_name' => 'Bảo Trân',
            'wedding_date' => '2026-12-25',
        ]);

        $template = InvitationTemplate::create([
            'name' => 'Celestial Gold',
            'slug' => 'celestial-gold-'.uniqid(),
            'blade_view' => 'client.invitations.celestial-gold.template',
            'thumbnail_url' => '/assets/client/images/templates/celestial-gold.jpg',
            'is_active' => true,
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'slug' => 'gia-huy-bao-tran-'.uniqid(),
            'is_published' => true,
        ]);
    }

    public function test_csv_sanitizer_service_neutralizes_formula_injection_characters(): void
    {
        // 1. Các tiền tố công thức nguy hiểm phổ biến
        expect(CsvSanitizerService::sanitize('=1+1'))->toBe("'=1+1");
        expect(CsvSanitizerService::sanitize('+cmd|"/C calc"!A0'))->toBe("'+cmd|\"/C calc\"!A0");
        expect(CsvSanitizerService::sanitize('-SUM(A1:A10)'))->toBe("'-SUM(A1:A10)");
        expect(CsvSanitizerService::sanitize('@evil.com'))->toBe("'@evil.com");
        expect(CsvSanitizerService::sanitize(" \t=2+2"))->toBe("' \t=2+2");

        // 2. Các giá trị an toàn thông thường không bị biến đổi
        expect(CsvSanitizerService::sanitize('Nguyễn Văn A'))->toBe('Nguyễn Văn A');
        expect(CsvSanitizerService::sanitize(12345))->toBe(12345);
        expect(CsvSanitizerService::sanitize(null))->toBeNull();
        expect(CsvSanitizerService::sanitize(''))->toBe('');

        // 3. Vệ sinh toàn bộ mảng row
        $row = [1, '=cmd', 'Bình thường', '@test'];
        expect(CsvSanitizerService::row($row))->toBe([1, "'=cmd", 'Bình thường', "'@test"]);
    }

    public function test_wishes_export_csv_neutralizes_csv_formula_injection(): void
    {
        WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => '=HYPERLINK("http://attacker.com")',
            'message' => '+cmd|"/C calc"!A0 Chúc hai bạn trăm năm hạnh phúc',
            'is_approved' => true,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('account.wishes.export', $this->invitation->id));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Bắt nội dung CSV từ stream download
        $content = $response->streamedContent();

        // Kiểm tra tiền tố công thức đã được bao bọc an toàn bởi dấu nháy đơn
        expect($content)->toContain("'=HYPERLINK(\"\"http://attacker.com\"\")");
        expect($content)->toContain("'+cmd|\"\"/C calc\"\"!A0 Chúc hai bạn trăm năm hạnh phúc");
    }

    public function test_guests_export_csv_neutralizes_csv_formula_injection(): void
    {
        Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => '@AdminGroup',
            'salutation' => 'Bạn',
            'group_name' => '-SUM(B1:B10)',
            'expected_attendees' => 2,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('guests.export', $this->invitation->id));

        $response->assertOk();
        $content = $response->streamedContent();

        expect($content)->toContain("'@AdminGroup");
        expect($content)->toContain("'-SUM(B1:B10)");
    }

    public function test_gifts_export_csv_neutralizes_csv_formula_injection(): void
    {
        WeddingGift::create([
            'wedding_id' => $this->wedding->id,
            'sender_name' => '=1337*2',
            'amount' => 500000,
            'notes' => '+exec("whoami")',
            'is_acknowledged' => true,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('account.gifts.export', $this->invitation->id));

        $response->assertOk();
        $content = $response->streamedContent();

        expect($content)->toContain("'=1337*2");
        expect($content)->toContain("'+exec(\"\"whoami\"\")");
    }
}
