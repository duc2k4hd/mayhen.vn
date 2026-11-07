<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->otherUser = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Keepsake',
        'slug' => 'mau-keepsake-'.uniqid(),
        'description' => 'Test Template Keepsake Book',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->owner->id,
        'groom_name' => 'Hải Đăng',
        'bride_name' => 'Thảo My',
        'wedding_date' => now()->subDays(5),
        'venue_name' => 'Riverside Palace',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hai-dang-thao-my-'.uniqid(),
        'is_published' => true,
        'custom_config' => [
            'memory_vault' => [
                'enabled' => true,
                'thank_you_letter' => 'Cảm ơn tất cả mọi người đã đến chung vui cùng chúng mình trong ngày trọng đại.',
            ],
        ],
    ]);

    // Tạo các lời chúc mẫu
    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hai Long An',
        'message' => 'Chúc hai cháu trăm năm hạnh phúc, đầu bạc răng long.',
        'is_approved' => true,
    ]);

    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bạn Thân Đại Học',
        'message' => 'Mãi bên nhau hạnh phúc nhé hai bạn yêu!',
        'reply' => 'Cảm ơn mày nhiều nha!',
        'is_approved' => true,
    ]);

    // Lời chúc chưa duyệt không được xuất hiện trong sách
    WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Vãng Lai Spam',
        'message' => 'Spam comment không duyệt',
        'is_approved' => false,
    ]);
});

test('wedding owner can view editorial keepsake book pdf layout', function () {
    $response = $this->actingAs($this->owner)->get(route('memory.keepsake_book', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('KỶ YẾU LƯU BÚT NGÀY CƯỚI');
    $response->assertSee('Hải Đăng');
    $response->assertSee('Thảo My');
    $response->assertSee('Cảm ơn tất cả mọi người đã đến chung vui');
    $response->assertSee('Bác Hai Long An');
    $response->assertSee('Chúc hai cháu trăm năm hạnh phúc');
    $response->assertSee('Bạn Thân Đại Học');
    $response->assertSee('Cảm ơn mày nhiều nha!');
    $response->assertDontSee('Spam comment không duyệt');
});

test('unauthorized user cannot view keepsake book of another wedding', function () {
    $response = $this->actingAs($this->otherUser)->get(route('memory.keepsake_book', $this->invitation->id));

    $response->assertNotFound();
});

test('guest session with owned slug can view keepsake book', function () {
    $response = $this->withSession(['owned_invitation_slugs' => [$this->invitation->slug]])
        ->get(route('memory.keepsake_book', $this->invitation->id));

    $response->assertOk();
    $response->assertSee('Hải Đăng');
    $response->assertSee('Thảo My');
});
