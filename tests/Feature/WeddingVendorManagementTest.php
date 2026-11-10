<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingVendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingVendorManagementTest extends TestCase
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
            'groom_name' => 'Tuấn Anh',
            'bride_name' => 'Mai Phương',
            'wedding_date' => now()->addMonths(6)->toDateString(),
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $template->id,
            'title' => 'Thiệp cưới Tuấn Anh & Mai Phương',
            'slug' => 'tuan-anh-mai-phuong-'.uniqid(),
            'status' => 'draft',
        ]);
    }

    public function test_guest_cannot_access_vendor_planner(): void
    {
        $response = $this->get(route('account.planner.index', ['id' => $this->invitation->id, 'tab' => 'vendors']));
        $response->assertRedirect(route('login'));
    }

    public function test_stranger_cannot_access_owner_vendor_planner(): void
    {
        $response = $this->actingAs($this->stranger)
            ->get(route('account.planner.index', ['id' => $this->invitation->id, 'tab' => 'vendors']));

        $response->assertStatus(404);
    }

    public function test_owner_can_view_vendor_tab(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('account.planner.index', ['id' => $this->invitation->id, 'tab' => 'vendors']));

        $response->assertStatus(200);
        $response->assertSee('Tổng Nhà Cung Cấp');
        $response->assertSee('Tổng Giá Trị Hợp Đồng');
        $response->assertSee('Khởi Tạo 8 Đối Tác Mẫu');
    }

    public function test_owner_can_seed_default_vendors(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('account.planner.vendors.seed_defaults', $this->invitation->id));

        $response->assertRedirect();
        $this->assertDatabaseCount('wedding_vendors', 8);

        $this->assertDatabaseHas('wedding_vendors', [
            'wedding_id' => $this->wedding->id,
            'category' => 'venue',
            'name' => 'Trung Tâm Hội Nghị & Tiệc Cưới',
        ]);
    }

    public function test_owner_can_create_new_vendor(): void
    {
        $payload = [
            'name' => 'White Palace Hoàng Văn Thụ',
            'category' => 'venue',
            'contact_person' => 'Chị Phương (Sale Manager)',
            'phone' => '0909123456',
            'email' => 'booking@whitepalace.com.vn',
            'address' => '194 Hoàng Văn Thụ, Phú Nhuận, TP.HCM',
            'contract_status' => 'contract_signed',
            'total_amount' => 120000000,
            'deposit_paid' => 40000000,
            'payment_due_date' => now()->addMonths(2)->format('Y-m-d'),
            'service_timeline' => '17:00 - 21:30 Ngày 20/11/2026',
            'notes' => 'Tặng 1 bàn tiệc dùng thử và tháp ly rượu sâm panh',
        ];

        $response = $this->actingAs($this->owner)
            ->post(route('account.planner.vendors.store', $this->invitation->id), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('wedding_vendors', [
            'wedding_id' => $this->wedding->id,
            'name' => 'White Palace Hoàng Văn Thụ',
            'total_amount' => 120000000,
            'deposit_paid' => 40000000,
            'contract_status' => 'contract_signed',
        ]);
    }

    public function test_owner_can_update_vendor(): void
    {
        $vendor = WeddingVendor::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'TuArt Wedding Studio',
            'category' => 'photography',
            'contract_status' => 'contacting',
            'total_amount' => 25000000,
            'deposit_paid' => 0,
        ]);

        $updatePayload = [
            'name' => 'TuArt Wedding Studio VIP',
            'category' => 'photography',
            'contact_person' => 'Anh Tú',
            'phone' => '0988776655',
            'contract_status' => 'contract_signed',
            'total_amount' => 28000000,
            'deposit_paid' => 10000000,
        ];

        $response = $this->actingAs($this->owner)
            ->put(route('account.planner.vendors.update', ['id' => $this->invitation->id, 'vendorId' => $vendor->id]), $updatePayload);

        $response->assertRedirect();
        $this->assertDatabaseHas('wedding_vendors', [
            'id' => $vendor->id,
            'name' => 'TuArt Wedding Studio VIP',
            'total_amount' => 28000000,
            'deposit_paid' => 10000000,
            'contract_status' => 'contract_signed',
        ]);
    }

    public function test_owner_can_quick_update_vendor_deposit(): void
    {
        $vendor = WeddingVendor::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Váy Cưới Cee’s Bridal',
            'category' => 'attire',
            'contract_status' => 'contract_signed',
            'total_amount' => 30000000,
            'deposit_paid' => 10000000,
        ]);

        $response = $this->actingAs($this->owner)
            ->post(route('account.planner.vendors.deposit', ['id' => $this->invitation->id, 'vendorId' => $vendor->id]), [
                'deposit_paid' => 20000000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('wedding_vendors', [
            'id' => $vendor->id,
            'deposit_paid' => 20000000,
        ]);
    }

    public function test_owner_can_delete_vendor(): void
    {
        $vendor = WeddingVendor::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Band Nhạc Flamenco Cần Thơ',
            'category' => 'music_mc',
            'contract_status' => 'contacting',
            'total_amount' => 8000000,
            'deposit_paid' => 0,
        ]);

        $response = $this->actingAs($this->owner)
            ->delete(route('account.planner.vendors.destroy', ['id' => $this->invitation->id, 'vendorId' => $vendor->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('wedding_vendors', [
            'id' => $vendor->id,
        ]);
    }

    public function test_owner_can_export_vendors_csv(): void
    {
        WeddingVendor::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Decor Hoa Tươi May Flower',
            'category' => 'decor',
            'contact_person' => 'Chị Hằng',
            'phone' => '0933221100',
            'email' => 'mayflower@gmail.com',
            'contract_status' => 'contract_signed',
            'total_amount' => 45000000,
            'deposit_paid' => 20000000,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('account.planner.vendors.export', $this->invitation->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Decor Hoa Tươi May Flower', $response->getContent());
        $this->assertStringContainsString('45000000', $response->getContent());
    }
}
