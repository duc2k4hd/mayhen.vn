<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Gate4CommercialExperienceInvariantsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $stranger;

    private Wedding $wedding;

    private Invitation $invitation;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'name' => 'Trọn Kiếp Này Bên Nhau',
            'slug' => 'tron-kiep-nay-ben-nhau',
            'thumbnail_url' => '/assets/templates/tron-kiep.webp',
            'category' => 'luxury',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create([
            'name' => 'Minh Tuấn',
            'email' => 'minhtuan@example.com',
        ]);

        $this->stranger = User::factory()->create([
            'name' => 'Người Dùng Khác',
            'email' => 'stranger@example.com',
        ]);

        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'wedding_date' => Carbon::now()->addMonths(2),
            'music_url' => '/assets/audio/wedding-melody.mp3',
            'extra_data' => [
                'groom_phone' => '0901234567',
                'bride_phone' => '0987654321',
                'event_party_address' => 'Trung Tâm Tiệc Cưới Mây Hẹn, 123 Ba Tháng Hai, Q.10, TP.HCM',
                'event_party_map_url' => 'https://maps.google.com/?q=MayHen+Wedding',
                'groom_bank_account' => '19034567890123',
                'groom_bank_name' => 'Techcombank',
                'bride_bank_account' => '0071001234567',
                'bride_bank_name' => 'Vietcombank',
            ],
        ]);

        $this->invitation = Invitation::create([
            'user_id' => $this->owner->id,
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'minh-tuan-thu-ha-gate4',
            'is_published' => true,
            'published_at' => Carbon::now()->subDay(),
            'custom_config' => [
                'persona_messages' => [
                    'groups' => [
                        'Khách VIP' => [
                            'title' => 'Trân Trọng Đón Tiếp Khách Quý',
                            'content' => 'Gia đình chúng tôi rất vinh hạnh được đón tiếp Quý Khách tại Bàn VIP Danh Dự.',
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * INV-50 & TASK-VAL-01: Chế độ chữ lớn & hỗ trợ nhanh cho người lớn tuổi (Senior-Friendly Guest Mode).
     */
    public function test_senior_friendly_guest_mode_renders_widget_with_accessible_controls_and_quick_contacts(): void
    {
        $response = $this->get('/thiep/'.$this->invitation->slug);

        $response->assertOk();

        // Widget tồn tại với ID chuẩn và button có accessibility attributes
        $response->assertSee('id="mayhen_senior_widget"', false);
        $response->assertSee('id="mayhen_senior_toggle"', false);
        $response->assertSee('aria-label="Bật chế độ chữ lớn dễ đọc cho người lớn tuổi"', false);
        $response->assertSee('Chữ Lớn');

        // Nút hỗ trợ nhanh gọi điện thoại cho cô dâu chú rể
        $response->assertSee('href="tel:0901234567"', false);
        $response->assertSee('href="tel:0987654321"', false);

        // Nút xem chỉ đường Google Maps
        $response->assertSee('href="https://maps.google.com/?q=MayHen+Wedding"', false);

        // CSS class mayhen_senior_mode được khai báo để phóng to chữ và ẩn hiệu ứng hoa bay xao nhãng
        $response->assertSee('.mayhen_senior_mode');
        $response->assertSee('.mayhen_senior_mode .mayhen_petal');
    }

    /**
     * TASK-VAL-08: Thanh tổng duyệt trước khi gửi thiệp (Pre-Publish Rehearsal Bar)
     * - Khách vãng lai / Người lạ tuyệt đối KHÔNG được thấy thanh Rehearsal.
     * - Chỉ Chủ thiệp (Owner) mới kích hoạt được và mô phỏng được persona.
     * - Không tạo rác dữ liệu trong database (zero side-effects).
     */
    public function test_pre_publish_rehearsal_bar_is_strictly_restricted_to_owner_and_leaves_db_untouched(): void
    {
        // 1. Khách vãng lai gửi query ?rehearsal=1&rehearsal_group=Khách+VIP -> BỊ TỪ CHỐI / ẨN
        $guestResponse = $this->get('/thiep/'.$this->invitation->slug.'?rehearsal=1&rehearsal_group=Khách+VIP');
        $guestResponse->assertOk();
        $guestResponse->assertDontSee('CHẾ ĐỘ TỔNG DUYỆT');
        $guestResponse->assertDontSee('mayhen-rehearsal-topbar');

        // 2. Người dùng khác (không phải chủ sở hữu) gửi query -> BỊ TỪ CHỐI / ẨN
        $strangerResponse = $this->actingAs($this->stranger)
            ->get('/thiep/'.$this->invitation->slug.'?rehearsal=1&rehearsal_group=Khách+VIP');
        $strangerResponse->assertOk();
        $strangerResponse->assertDontSee('CHẾ ĐỘ TỔNG DUYỆT');

        // 3. Chính chủ sở hữu đăng nhập và kích hoạt Rehearsal
        $ownerResponse = $this->actingAs($this->owner)
            ->get('/thiep/'.$this->invitation->slug.'?rehearsal=1&rehearsal_group=Khách+VIP');
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('CHẾ ĐỘ TỔNG DUYỆT');
        $ownerResponse->assertSee('id="mayhen-rehearsal-topbar"', false);
        $ownerResponse->assertSee('Đang mô phỏng lời ngỏ & thẻ bàn của: <strong>Khách VIP</strong>', false);
        $ownerResponse->assertSee('Khách Quý (Khách VIP)');

        // 4. Bất biến kiểm chứng: Dữ liệu thật trong DB tuyệt đối KHÔNG bị ô nhiễm
        $this->assertEquals(0, Guest::where('invitation_id', $this->invitation->id)->count());
        $this->invitation->refresh();
        $this->assertEquals('minh-tuan-thu-ha-gate4', $this->invitation->slug);
    }

    /**
     * TASK-VAL-05: Sổ tay hướng dẫn vị trí & bãi đỗ xe thông minh (Interactive Parking Concierge).
     */
    public function test_parking_concierge_modal_displays_when_enabled_and_remains_hidden_when_disabled(): void
    {
        // 1. Khi chưa bật parking guide -> Nút mở hướng dẫn đỗ xe KHÔNG xuất hiện
        $initialResponse = $this->get('/thiep/'.$this->invitation->slug);
        $initialResponse->assertOk();
        $initialResponse->assertDontSee('id="mayhen-parking-btn-trigger"', false);

        // 2. Chủ thiệp cấu hình bãi đỗ xe và kích hoạt
        $config = $this->invitation->custom_config ?? [];
        $config['parking_guide'] = [
            'enabled' => true,
            'car_parking' => 'Tầng hầm B2 tòa nhà Mây Hẹn, cổng vào số 2',
            'motorbike_parking' => 'Bãi xe máy ngoài trời bên hông sảnh tiệc',
            'fee_note' => 'Miễn phí gửi xe có đóng dấu tại bàn lễ tân',
            'entrance_guide' => 'Đi thang máy Trục A lên tầng 3 sảnh Grand Ballroom',
            'map_tip' => 'Đường 1 chiều, vui lòng rẽ từ ngã tư Cách Mạng Tháng 8',
        ];
        $this->invitation->update(['custom_config' => $config]);

        // 3. Thiệp công khai hiển thị nút mở modal và toàn bộ thông tin chỉ dẫn
        $activeResponse = $this->get('/thiep/'.$this->invitation->slug);
        $activeResponse->assertOk();
        $activeResponse->assertSee('id="mayhen-parking-btn-trigger"', false);
        $activeResponse->assertSee('Bãi Đỗ Xe & Lối Vào', false);
        $activeResponse->assertSee('Tầng hầm B2 tòa nhà Mây Hẹn, cổng vào số 2');
        $activeResponse->assertSee('Bãi xe máy ngoài trời bên hông sảnh tiệc');
        $activeResponse->assertSee('Miễn phí gửi xe có đóng dấu tại bàn lễ tân');
        $activeResponse->assertSee('Đi thang máy Trục A lên tầng 3 sảnh Grand Ballroom');
    }

    /**
     * TASK-VAL-24 & LAW 91/2025/QH15: Quyền riêng tư & Bảo vệ dữ liệu cá nhân (Privacy Consent).
     */
    public function test_privacy_consent_management_and_guest_data_protection(): void
    {
        // 1. Người lạ hoặc khách vãng lai không có quyền truy cập trang cài đặt riêng tư của thiệp (404 chống enum)
        $unauthResponse = $this->actingAs($this->stranger)
            ->get(route('privacy.show', $this->invitation->id));
        $unauthResponse->assertNotFound();

        // 2. Chủ sở hữu truy cập thành công trang cài đặt riêng tư
        $ownerResponse = $this->actingAs($this->owner)
            ->get(route('privacy.show', $this->invitation->id));
        $ownerResponse->assertOk();
        $ownerResponse->assertSee('Quyền Riêng Tư');

        // 3. Chủ sở hữu lưu cấu hình bảo vệ số tài khoản mừng cưới và ngày lưu trữ SĐT
        $updateResponse = $this->actingAs($this->owner)
            ->post(route('privacy.update', $this->invitation->id), [
                'bank_visibility' => 'named_guests_only',
                'wishes_visibility' => 'public',
                'photo_drop_moderation' => 'require_approval',
                'phone_retention_days' => 30,
                'anonymous_rsvp_allowed' => 0,
            ]);
        $updateResponse->assertRedirect(route('privacy.show', $this->invitation->id));

        $this->invitation->refresh();
        $privacySettings = $this->invitation->custom_config['privacy_settings'] ?? [];
        $this->assertEquals('named_guests_only', $privacySettings['bank_visibility'] ?? '');
        $this->assertEquals(30, $privacySettings['phone_retention_days'] ?? 0);
    }

    /**
     * TASK-VAL-26: Cổng khởi đầu thành công sau thanh toán (Post-Payment First Success Portal).
     */
    public function test_post_payment_first_success_portal_provides_actionable_onboarding_guide(): void
    {
        $plan = Plan::create([
            'name' => 'Gói Đính Ước Vĩnh Cửu',
            'slug' => 'dinh-uoc-vinh-cuu',
            'price' => 399000,
            'duration_days' => 180,
            'max_invitations' => 3,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->owner->id,
            'plan_id' => $plan->id,
            'invitation_id' => $this->invitation->id,
            'order_code' => 'MHGATE4SUCC1',
            'amount' => 399000,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('checkout.first_success', $order->order_code));

        $response->assertOk();
        $response->assertSee('MHGATE4SUCC1');
        $response->assertSee('Xem &amp; Chia Sẻ Link Thiệp Cưới Chính Thức', false);
        $response->assertSee('Bố Trí Sơ Đồ Bàn Tiệc &amp; QR Điểm Danh Lễ Tân', false);
        $response->assertSee('Cấu Hình Bảo Vệ Số Tài Khoản Ngân Hàng', false);
        $response->assertSee($this->invitation->slug);
    }

    /**
     * INV-47 & INV-48: Chính sách âm thanh nền, Autoplay Policy Defense & Nút Play/Pause nổi.
     */
    public function test_audio_dock_and_autoplay_policy_defense_invariants(): void
    {
        $response = $this->get('/thiep/'.$this->invitation->slug);
        $response->assertOk();

        // 1. Thẻ audio phải có preload="none" để tiết kiệm băng thông và tuân thủ Autoplay Policy
        $response->assertSee('preload="none"', false);
        $response->assertSee('loop', false);

        // 2. Nút chuyển đổi âm thanh nổi có mặt trên giao diện
        $hasMusicButton = str_contains($response->getContent(), 'mayhen_invitation_music_btn') ||
                          str_contains($response->getContent(), 'data-mayhen-music-btn') ||
                          str_contains($response->getContent(), 'mayhen_curated_music_dock') ||
                          str_contains($response->getContent(), 'mayhen_curated_music_btn') ||
                          str_contains($response->getContent(), 'music-btn');

        $this->assertTrue($hasMusicButton, 'Music dock button must be present in invitation interface');
    }

    /**
     * INV-45: Khi đám cưới kết thúc, toàn bộ dữ liệu danh sách khách, số tiền mừng cưới, lời chúc và ảnh kỷ niệm được lưu giữ vĩnh viễn.
     */
    public function test_post_wedding_data_preservation_invariant_45(): void
    {
        // Giả lập đám cưới đã diễn ra 1 tháng trước
        $this->wedding->update([
            'wedding_date' => Carbon::now()->subMonth(),
        ]);

        // Tạo dữ liệu kỷ niệm: khách, lời chúc, ảnh tiệc cưới
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'invitation_id' => $this->invitation->id,
            'name' => 'Hoàng Nam',
            'phone' => '0912345678',
            'status' => 'attending',
            'headcount' => 2,
        ]);

        $wish = WeddingWish::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Hoàng Nam',
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
            'is_approved' => true,
        ]);

        $photo = GuestPhoto::create([
            'wedding_id' => $this->wedding->id,
            'uploader_name' => 'Hoàng Nam',
            'photo_url' => 'photos/nam-party.webp',
            'caption' => 'Khoảnh khắc nâng ly',
            'is_approved' => true,
        ]);

        // Thiệp vẫn truy cập bình thường hoặc ở chế độ kỷ niệm mà không mất mát dữ liệu
        $response = $this->get('/thiep/'.$this->invitation->slug);
        $response->assertOk();

        // Kiểm chứng tính toàn vẹn dữ liệu
        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Hoàng Nam',
            'phone' => '0912345678',
        ]);

        $this->assertDatabaseHas('wedding_wishes', [
            'id' => $wish->id,
            'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
        ]);

        $this->assertDatabaseHas('guest_photos', [
            'id' => $photo->id,
            'photo_url' => 'photos/nam-party.webp',
        ]);
    }
}
