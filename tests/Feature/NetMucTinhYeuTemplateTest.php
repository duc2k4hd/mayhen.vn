<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('active template can be seeded, viewed, and previewed', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->first();
    expect($template)->not->toBeNull();
    expect($template->name)->toBe('Hạnh Phúc Mang Tên Chúng Mình');

    // 1. Xem trang chi tiết mẫu
    $responseDetail = $this->get('/mau-thiep/hanh-phuc-mang-ten-chung-minh');
    $responseDetail->assertStatus(200);
    $responseDetail->assertSee('Hạnh Phúc Mang Tên Chúng Mình');
    $responseDetail->assertSee('Tạo Thiệp Với Mẫu Này');

    // 2. Xem trang preview mẫu
    $responsePreview = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh');
    $responsePreview->assertStatus(200);
    $responsePreview->assertSee('mayhen_invitation_ladi_wraper', false);
    $responsePreview->assertSee('mayhen_invitation_wax_seal', false);
    $responsePreview->assertSee('mayhen_invitation_family_section', false);
    $responsePreview->assertSee('mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/styles.css', false);
    $responsePreview->assertSee('mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/script.js', false);
});

test('editor studio renders schema-driven form and exact template iframe preview', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $user = User::factory()->create();

    // Truy cập trang editor với mẫu đang hoạt động
    $response = $this->actingAs($user)->get('/tao-thiep?template=hanh-phuc-mang-ten-chung-minh');
    $response->assertStatus(200);

    // Kiểm tra nhúng chính xác iframe mẫu thiệp
    $response->assertSee('mayhen_editor_preview_iframe', false);
    $response->assertSee('/xem-thu/hanh-phuc-mang-ten-chung-minh?embed=1&mode=editor', false);

    // Kiểm tra các section theo chuẩn hóa hiện hành
    $response->assertSee('Trang Bìa');
    $response->assertSee('Gia Đình');
    $response->assertSee('Album Ảnh');
    $response->assertSee('Lịch Trình');
    $response->assertSee('Mừng Cưới');

    // Kiểm tra các trường dữ liệu đặc thù
    $response->assertSee('wedding[extra_data][bride_party_address]', false);
    $response->assertSee('wedding[extra_data][ceremony_groom_address]', false);
});

test('invitation created with an active template renders dynamic user data', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Nguyễn Minh Quân',
        'bride_name' => 'Trần Ngọc Mai',
        'groom_parents' => 'Ông Nguyễn Văn An & Bà Lê Thị Bình',
        'bride_parents' => 'Ông Trần Văn Cường & Bà Phạm Thị Dung',
        'wedding_date' => '2026-12-25 11:00:00',
        'story' => 'Hai trái tim một nhịp đập, bắt đầu hành trình trọn đời.',
        'cover_photo_url' => '/test-cover.jpg',
        'music_url' => '/test-music.mp3',
        'extra_data' => [
            'hero_title' => 'Lễ Thành Hôn',
            'bride_party_address' => 'Nhà Hàng White Palace, 194 Hoàng Văn Thụ, TP.HCM',
            'groom_parents_address' => 'Trung Tâm Tiệc Cưới GEM Center, Quận 1, TP.HCM',
            'dresscode_note' => 'Trắng, Kem hoặc Be nhạt',
        ],
    ]);

    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'quan-mai-wedding-2026',
        'view_count' => 0,
        'status' => 'published',
    ]);

    // Truy cập xem thiệp của cặp đôi qua slug
    $response = $this->get("/thiep/{$invitation->slug}");
    $response->assertStatus(200);
    $response->assertSee('Nguyễn Minh Quân');
    $response->assertSee('Trần Ngọc Mai');
    $response->assertSee('Nhà Hàng White Palace');
    $response->assertSee('GEM Center');
    $response->assertSee('Trắng, Kem hoặc Be nhạt');
});

test('user can upload an image to wedding storage', function () {
    $this->seed(InvitationTemplateSeeder::class);
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('my_wedding_photo.jpg', 800, 600);

    $response = $this->actingAs($user)->postJson('/tao-thiep/upload-asset', [
        'template_slug' => 'hanh-phuc-mang-ten-chung-minh',
        'file' => $file,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $data = $response->json();
    expect($data['file_url'])->toContain('/storage/weddings/photos/');

    Storage::disk('public')->assertExists($data['storage_path']);
    Storage::disk('public')->delete($data['storage_path']);
});

test('each wedding has its own independent guestbook database with wish submissions and isolation', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();

    // 1. Tạo đám cưới A (Đức & Phượng)
    $weddingA = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tràng Đức',
        'bride_name' => 'Ngọc Phượng',
        'wedding_date' => '2027-03-07 07:00:00',
    ]);
    $invitationA = Invitation::create([
        'wedding_id' => $weddingA->id,
        'template_id' => $template->id,
        'slug' => 'duc-phuong-wedding',
        'status' => 'published',
    ]);

    // 2. Tạo đám cưới B (Minh & Lan)
    $weddingB = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Quốc Minh',
        'bride_name' => 'Mai Lan',
        'wedding_date' => '2027-04-12 11:00:00',
    ]);
    $invitationB = Invitation::create([
        'wedding_id' => $weddingB->id,
        'template_id' => $template->id,
        'slug' => 'minh-lan-wedding',
        'status' => 'published',
    ]);

    // 3. Khách mời gửi lời chúc vào đám cưới A
    $responseA = $this->postJson("/thiep/{$invitationA->slug}/wishes", [
        'name' => 'Bạn thân Cấp 3',
        'message' => 'Chúc Đức và Phượng trăm năm hạnh phúc, sớm đón quý tử nha!',
    ]);
    $responseA->assertStatus(201);
    $responseA->assertJson([
        'success' => true,
        'wish' => [
            'name' => 'Bạn thân Cấp 3',
            'message' => 'Chúc Đức và Phượng trăm năm hạnh phúc, sớm đón quý tử nha!',
        ],
    ]);

    // 4. Khách mời gửi lời chúc vào đám cưới B
    $responseB = $this->postJson("/thiep/{$invitationB->slug}/wishes", [
        'name' => 'Đồng nghiệp Công ty',
        'message' => 'Chúc mừng hạnh phúc Minh & Lan, mãi mãi yêu thương!',
    ]);
    $responseB->assertStatus(201);
    $responseB->assertJson([
        'success' => true,
        'wish' => [
            'name' => 'Đồng nghiệp Công ty',
        ],
    ]);

    // 5. Kiểm tra tính độc lập (Isolation) của cơ sở dữ liệu
    expect($weddingA->wishes()->count())->toBe(1);
    expect($weddingB->wishes()->count())->toBe(1);

    // Khi xem thiệp A: thấy lời chúc của thiệp A, KHÔNG thấy lời chúc của thiệp B
    $viewA = $this->get("/thiep/{$invitationA->slug}");
    $viewA->assertStatus(200);
    $viewA->assertSee('Bạn thân Cấp 3');
    $viewA->assertSee('Chúc Đức và Phượng trăm năm hạnh phúc');
    $viewA->assertDontSee('Đồng nghiệp Công ty');

    // Khi xem thiệp B: thấy lời chúc của thiệp B, KHÔNG thấy lời chúc của thiệp A
    $viewB = $this->get("/thiep/{$invitationB->slug}");
    $viewB->assertStatus(200);
    $viewB->assertSee('Đồng nghiệp Công ty');
    $viewB->assertSee('Chúc mừng hạnh phúc Minh & Lan');
    $viewB->assertDontSee('Bạn thân Cấp 3');

    // 6. Test demo wish trên trang xem thử template preview
    $responseDemo = $this->postJson('/xem-thu/hanh-phuc-mang-ten-chung-minh/wishes', [
        'name' => 'Người xem thử',
        'message' => 'Lời chúc kiểm thử trên giao diện demo',
    ]);
    $responseDemo->assertStatus(201);
    $responseDemo->assertJson([
        'success' => true,
    ]);
});

test('each wedding has independent rsvp responses for attending and declining', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();

    // 1. Tạo 2 đám cưới khác nhau
    $weddingA = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Thu Hà',
        'wedding_date' => '2027-05-20 11:00:00',
    ]);
    $invitationA = Invitation::create([
        'wedding_id' => $weddingA->id,
        'template_id' => $template->id,
        'slug' => 'nam-ha-wedding',
        'status' => 'published',
    ]);

    $weddingB = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tuấn Anh',
        'bride_name' => 'Hương Giang',
        'wedding_date' => '2027-06-15 18:00:00',
    ]);
    $invitationB = Invitation::create([
        'wedding_id' => $weddingB->id,
        'template_id' => $template->id,
        'slug' => 'anh-giang-wedding',
        'status' => 'published',
    ]);

    // 2. Khách A xác nhận CÓ THAM DỰ vào đám cưới A
    $responseA = $this->postJson("/thiep/{$invitationA->slug}/rsvp", [
        'guest_name' => 'Nguyễn Văn Hùng',
        'phone' => '0912345678',
        'attending_status' => 'attending',
        'adults_count' => 2,
    ]);
    $responseA->assertStatus(200);
    $responseA->assertJson(['success' => true]);

    // 3. Khách B xác nhận KHÔNG THAM DỰ vào đám cưới B
    $responseB = $this->postJson("/thiep/{$invitationB->slug}/rsvp", [
        'guest_name' => 'Lê Thị Thu',
        'phone' => '0987654321',
        'attending_status' => 'declined',
        'adults_count' => 0,
        'wishes' => 'Bận công tác xa không về kịp, chúc hai em hạnh phúc!',
    ]);
    $responseB->assertStatus(200);
    $responseB->assertJson(['success' => true]);

    // 4. Kiểm tra dữ liệu được lưu đúng và độc lập
    expect($weddingA->guestResponses()->count())->toBe(1);
    expect($weddingB->guestResponses()->count())->toBe(1);

    $savedResponseA = $weddingA->guestResponses()->first();
    expect($savedResponseA->guest_name)->toBe('Nguyễn Văn Hùng');
    expect($savedResponseA->phone)->toBe('0912345678');
    expect($savedResponseA->attending_status)->toBe('attending');
    expect($savedResponseA->adults_count)->toBe(2);

    $savedResponseB = $weddingB->guestResponses()->first();
    expect($savedResponseB->guest_name)->toBe('Lê Thị Thu');
    expect($savedResponseB->phone)->toBe('0987654321');
    expect($savedResponseB->attending_status)->toBe('declined');
    expect($savedResponseB->wishes)->toBe('Bận công tác xa không về kịp, chúc hai em hạnh phúc!');

    // Lời chúc từ RSVP của khách B cũng tự động lưu vào wedding_wishes của đám cưới B
    expect($weddingB->wishes()->count())->toBe(1);
    expect($weddingA->wishes()->count())->toBe(0);

    // 5. Kiểm tra route demo RSVP trên trang xem thử preview
    $responseDemo = $this->postJson('/xem-thu/hanh-phuc-mang-ten-chung-minh/rsvp', [
        'guest_name' => 'Khách xem thử',
        'phone' => '0900000000',
        'attending_status' => 'attending',
        'adults_count' => 1,
    ]);
    $responseDemo->assertStatus(200);
    $responseDemo->assertJson(['success' => true]);
});

test('user cannot rsvp more than once from the same ip for the same wedding', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Chí Kiên',
        'bride_name' => 'Khánh Linh',
        'wedding_date' => '2027-08-08 11:00:00',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'kien-linh-wedding',
        'status' => 'published',
    ]);

    $clientIp = '192.168.1.100';

    // 1. Gửi RSVP lần đầu từ IP 192.168.1.100 -> Thành công
    $response1 = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->postJson("/thiep/{$invitation->slug}/rsvp", [
            'guest_name' => 'Nguyễn Minh Tuấn',
            'phone' => '0911222333',
            'attending_status' => 'attending',
            'adults_count' => 1,
        ]);
    $response1->assertStatus(200);
    $response1->assertJson(['success' => true]);

    // 2. Cố tình gửi lại RSVP từ cùng IP 192.168.1.100 -> Bị từ chối 422
    $response2 = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->postJson("/thiep/{$invitation->slug}/rsvp", [
            'guest_name' => 'Nguyễn Minh Tuấn (Gửi lại)',
            'phone' => '0911222333',
            'attending_status' => 'declined',
            'adults_count' => 0,
        ]);
    $response2->assertStatus(422);
    $response2->assertJson([
        'success' => false,
        'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
    ]);

    // Tổng số response vẫn chỉ là 1
    expect($wedding->guestResponses()->count())->toBe(1);

    // 3. Khách từ IP khác vẫn có thể gửi bình thường
    $responseOtherIp = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.101'])
        ->postJson("/thiep/{$invitation->slug}/rsvp", [
            'guest_name' => 'Trần Văn Nam',
            'phone' => '0944555666',
            'attending_status' => 'declined',
            'adults_count' => 0,
        ]);
    $responseOtherIp->assertStatus(200);
    expect($wedding->guestResponses()->count())->toBe(2);

    // 4. Khi IP đã RSVP truy cập trang thiệp, giao diện khóa nút chọn
    $viewResponse = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->get("/thiep/{$invitation->slug}");
    $viewResponse->assertStatus(200);
    $viewResponse->assertDontSee('Đã xác nhận: Có tham dự');
});

test('wishes from the same ip are stored as separate submissions', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();

    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Bảo Long',
        'bride_name' => 'Thùy Chi',
        'wedding_date' => '2027-09-09 11:00:00',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'long-chi-wedding',
        'status' => 'published',
    ]);

    $clientIp = '10.10.10.20';

    // 1. Gửi lời chúc lần 1 -> Thành công tạo mới (201)
    $response1 = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->postJson("/thiep/{$invitation->slug}/wishes", [
            'name' => 'Hoàng Bách',
            'message' => 'Chúc hai bạn trăm năm viên mãn!',
        ]);
    $response1->assertStatus(201);
    $response1->assertJson([
        'success' => true,
        'is_update' => false,
    ]);
    expect($wedding->wishes()->count())->toBe(1);

    // 2. Lời chúc thứ hai từ cùng IP được tạo riêng
    $response2 = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->postJson("/thiep/{$invitation->slug}/wishes", [
            'name' => 'Hoàng Bách (Chỉnh sửa)',
            'message' => 'Chúc hai bạn trăm năm viên mãn, sớm có tin vui nhé!',
        ]);
    $response2->assertStatus(201);
    $response2->assertJson([
        'success' => true,
        'is_update' => false,
    ]);

    // Hai lời chúc được lưu độc lập
    expect($wedding->wishes()->count())->toBe(2);

    $this->assertDatabaseHas('wedding_wishes', ['wedding_id' => $wedding->id, 'name' => 'Hoàng Bách', 'message' => 'Chúc hai bạn trăm năm viên mãn!']);
    $this->assertDatabaseHas('wedding_wishes', ['wedding_id' => $wedding->id, 'name' => 'Hoàng Bách (Chỉnh sửa)', 'message' => 'Chúc hai bạn trăm năm viên mãn, sớm có tin vui nhé!']);

    // 3. Người khác từ IP khác gửi lời chúc -> Thành công tạo mới (201)
    $responseOther = $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.21'])
        ->postJson("/thiep/{$invitation->slug}/wishes", [
            'name' => 'Thu Hương',
            'message' => 'Chúc mừng hạnh phúc Long & Chi!',
        ]);
    $responseOther->assertStatus(201);
    $responseOther->assertJson(['is_update' => false]);
    expect($wedding->wishes()->count())->toBe(3);

    // 4. Không xác định quyền chỉnh sửa lời chúc dựa trên IP
    $viewResponse = $this->withServerVariables(['REMOTE_ADDR' => $clientIp])
        ->get("/thiep/{$invitation->slug}");
    $viewResponse->assertStatus(200);
    $viewResponse->assertDontSee('Chỉnh sửa lời chúc của bạn');
    $viewResponse->assertSee('Hoàng Bách (Chỉnh sửa)');
});

test('guest creating invitation without login is recognized as owner via session, can reply wishes and view rsvps, but cannot submit new wishes or rsvp', function () {
    $this->seed(InvitationTemplateSeeder::class);

    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();

    // 1. Khách vãng lai tạo thiệp khi CHƯA đăng nhập
    $storeResponse = $this->post('/tao-thiep', [
        'template_id' => $template->id,
        'wedding' => [
            'groom_name' => 'Nguyễn Anh Tuấn',
            'bride_name' => 'Lê Mỹ Duyên',
            'wedding_date' => '2027-10-10',
        ],
    ]);
    $storeResponse->assertRedirect();

    // Kiểm tra session đã lưu slug thiệp
    $invitation = Invitation::latest('id')->first();
    expect(session('owned_invitation_slugs'))->toContain($invitation->slug);

    // 2. Kiểm tra hàm isOwner
    expect($invitation->isOwner())->toBeTrue();

    // 3. Khách mời khác gửi RSVP và Wish vào thiệp này
    $guestIp = '123.45.67.89';
    $this->withServerVariables(['REMOTE_ADDR' => $guestIp])
        ->flushSession() // giả lập khách mời khác không có session owner
        ->postJson("/thiep/{$invitation->slug}/rsvp", [
            'guest_name' => 'Bác Hùng Hàng Xóm',
            'phone' => '0988776655',
            'attending_status' => 'attending',
            'adults_count' => 2,
        ])->assertStatus(200);

    $this->withServerVariables(['REMOTE_ADDR' => $guestIp])
        ->flushSession()
        ->postJson("/thiep/{$invitation->slug}/wishes", [
            'name' => 'Bác Hùng Hàng Xóm',
            'message' => 'Chúc hai cháu trăm năm đầu bạc răng long!',
        ])->assertStatus(201);

    $wish = $invitation->wedding->wishes()->first();
    expect($wish)->not->toBeNull();

    // 4. Chủ nhân (với session owned_invitation_slugs) xem thiệp
    $ownerView = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->get("/thiep/{$invitation->slug}");
    $ownerView->assertStatus(200);
    $ownerView->assertSee('Nguyễn Anh Tuấn');
    $ownerView->assertSee('Lê Mỹ Duyên');

    // 5. Chủ nhân cố tình gửi RSVP -> BỊ CHẶN 403
    $ownerRsvpResponse = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->postJson("/thiep/{$invitation->slug}/rsvp", [
            'guest_name' => 'Chủ nhân tự gửi',
            'attending_status' => 'attending',
        ]);
    $ownerRsvpResponse->assertStatus(403);
    $ownerRsvpResponse->assertJson([
        'success' => false,
        'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
    ]);

    // 6. Chủ nhân cố tình gửi lời chúc mới -> BỊ CHẶN 403
    $ownerWishResponse = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->postJson("/thiep/{$invitation->slug}/wishes", [
            'name' => 'Chủ nhân tự gửi chúc',
            'message' => 'Tự chúc chính mình',
        ]);
    $ownerWishResponse->assertStatus(403);
    $ownerWishResponse->assertJson([
        'success' => false,
        'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể trả lời các lời chúc của khách mời.',
    ]);

    // 7. Chủ nhân TRẢ LỜI LỜI CHÚC của khách -> THÀNH CÔNG (200)
    $replyResponse = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->postJson("/thiep/{$invitation->slug}/wishes/{$wish->id}/reply", [
            'reply' => 'Dạ chúng cháu cảm ơn bác Hùng nhiều ạ!',
        ]);
    $replyResponse->assertStatus(200);
    $replyResponse->assertJson([
        'success' => true,
        'message' => 'Đã gửi phản hồi lời chúc thành công!',
        'reply' => 'Dạ chúng cháu cảm ơn bác Hùng nhiều ạ!',
    ]);

    // Kiểm tra DB đã cập nhật reply
    $wish->refresh();
    expect($wish->reply)->toBe('Dạ chúng cháu cảm ơn bác Hùng nhiều ạ!');
    expect($wish->replied_at)->not->toBeNull();

    // 8. Người dùng lạ không phải chủ nhân cố tình gọi API reply -> BỊ CHẶN 403
    $strangerReply = $this->flushSession()->postJson("/thiep/{$invitation->slug}/wishes/{$wish->id}/reply", [
        'reply' => 'Phản hồi trái phép',
    ]);
    $strangerReply->assertStatus(403);

    // 9. Chủ nhân XEM DANH SÁCH RSVP -> THÀNH CÔNG (200)
    $ownerRsvpList = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->getJson("/thiep/{$invitation->slug}/rsvp/responses");
    $ownerRsvpList->assertStatus(200);
    $ownerRsvpList->assertJson([
        'success' => true,
        'stats' => [
            'total' => 1,
            'attending' => 1,
            'attending_guests' => 2,
            'declined' => 0,
        ],
    ]);

    // 10. Người dùng lạ cố tình xem danh sách RSVP -> BỊ CHẶN 403
    $strangerRsvpList = $this->flushSession()->getJson("/thiep/{$invitation->slug}/rsvp/responses");
    $strangerRsvpList->assertStatus(403);

    // 11. Đăng nhập tài khoản -> thiệp trong session tự động gán quyền sở hữu sang User đăng nhập
    $user = User::factory()->create(['email' => 'tuan.duyen@gmail.com']);
    $loginResponse = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->post('/dang-nhap', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $invitation->refresh();
    expect($invitation->wedding->user_id)->toBe($user->id);
    expect($invitation->isOwner($user))->toBeTrue();
});

it('blocks profanity, insults and wedding curses in guestbook wishes', function () {
    $this->seed(InvitationTemplateSeeder::class);
    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Bảo Nam',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => '2027-10-10 10:00:00',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'nam-thao-wedding',
        'status' => 'published',
    ]);

    // 1. Chửi bậy viết tắt / lóng MXH
    $res1 = $this->postJson("/thiep/{$invitation->slug}/wishes", [
        'name' => 'Kẻ Phá Đám',
        'message' => 'Đám cưới cái đm gì, nhìn ngứa mắt vl!',
    ]);
    $res1->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // 2. Tục tĩu lách ký tự dấu chấm, dấu cách, leetspeak
    $res2 = $this->postJson("/thiep/{$invitation->slug}/wishes", [
        'name' => 'Hater',
        'message' => 'chuc 2 ban d.m d1t nhau som bo nhau',
    ]);
    $res2->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // 3. Trù ẻo đám cưới (ly dị, tang tóc)
    $res3 = $this->postJson("/thiep/{$invitation->slug}/wishes", [
        'name' => 'Khách Vô Duyên',
        'message' => 'Chúc hai bạn kết hôn rồi sớm ly dị nhé!',
    ]);
    $res3->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    // 4. Tên người gửi chứa từ chửi bậy / xúc phạm
    $res4 = $this->postJson("/thiep/{$invitation->slug}/wishes", [
        'name' => 'Đồ con chó đẻ',
        'message' => 'Chúc mừng hạnh phúc hai bạn!',
    ]);
    $res4->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    // 5. Lời chúc lịch sự, văn minh -> Thành công 201
    $res5 = $this->postJson("/thiep/{$invitation->slug}/wishes", [
        'name' => 'Nguyễn Thu Trang',
        'message' => 'Chúc hai bạn trăm năm hạnh phúc, đầu bạc răng long, mãi mãi bên nhau!',
    ]);
    $res5->assertStatus(201)
        ->assertJson(['success' => true]);
});

it('blocks profanity and malicious text in rsvp submissions', function () {
    $this->seed(InvitationTemplateSeeder::class);
    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Văn Đức',
        'bride_name' => 'Ngọc Ánh',
        'wedding_date' => '2027-11-11 11:00:00',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'rsvp-profanity-check',
        'status' => 'published',
    ]);

    // 1. Tên khách mời chứa từ ngữ chửi bậy
    $res1 = $this->postJson("/thiep/{$invitation->slug}/rsvp", [
        'guest_name' => 'thằng mặt lồn',
        'attending_status' => 'attending',
    ]);
    $res1->assertStatus(422)
        ->assertJsonValidationErrors(['guest_name']);

    // 2. Lời nhắn gửi cặp đôi chứa trù ẻo, chửi bới
    $res2 = $this->postJson("/thiep/{$invitation->slug}/rsvp", [
        'guest_name' => 'Lê Hoàng',
        'attending_status' => 'declined',
        'wishes' => 'Không đi đâu, cưới xong cắm sừng phản bội nhau thôi!',
    ]);
    $res2->assertStatus(422)
        ->assertJsonValidationErrors(['wishes']);

    // 3. Thông tin văn minh -> Thành công
    $res3 = $this->postJson("/thiep/{$invitation->slug}/rsvp", [
        'guest_name' => 'Bác Hai Sài Gòn',
        'phone' => '0903123456',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'wishes' => 'Gia đình bác sẽ có mặt đúng giờ để chúc phúc cho hai cháu!',
    ]);
    $res3->assertStatus(200)
        ->assertJson(['success' => true]);
});

it('blocks profanity in owner replies', function () {
    $this->seed(InvitationTemplateSeeder::class);
    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();
    $user = User::factory()->create();
    $wedding = Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Tuấn Anh',
        'bride_name' => 'Mai Phương',
        'wedding_date' => '2027-12-12 12:00:00',
    ]);
    $invitation = Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'reply-profanity-check',
        'status' => 'published',
    ]);

    $wish = WeddingWish::create([
        'wedding_id' => $wedding->id,
        'name' => 'Khách Quý',
        'message' => 'Chúc mừng hai bạn!',
        'is_approved' => true,
        'ip_address' => '127.0.0.1',
    ]);

    // Chủ thiệp phản hồi chứa từ ngữ tục tĩu -> Bị chặn 422
    $res1 = $this->actingAs($user)
        ->postJson("/thiep/{$invitation->slug}/wishes/{$wish->id}/reply", [
            'reply' => 'Cảm ơn con mẹ mày nhé đồ đĩ thõa',
        ]);
    $res1->assertStatus(422)
        ->assertJsonValidationErrors(['reply']);

    // Chủ thiệp phản hồi lịch sự -> Thành công 200
    $res2 = $this->actingAs($user)
        ->postJson("/thiep/{$invitation->slug}/wishes/{$wish->id}/reply", [
            'reply' => 'Vợ chồng mình xin chân thành cảm ơn tình cảm của bạn!',
        ]);
    $res2->assertStatus(200)
        ->assertJson(['success' => true]);
});
