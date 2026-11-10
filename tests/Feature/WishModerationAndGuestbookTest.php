<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-wish-template'],
        [
            'name' => 'Test Wish Template',
            'description' => 'Test template for wishes',
            'category' => 'modern',
            'blade_view' => 'client.invitations.template-1',
            'thumbnail_url' => '/assets/img/demo.jpg',
            'is_active' => true,
        ]
    );

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Nguyễn Văn Nam',
        'bride_name' => 'Trần Thị Hoa',
        'wedding_date' => now()->addDays(20)->toDateString(),
        'venue_name' => 'White Palace',
        'venue_address' => '108 Phạm Văn Đồng, TP.HCM',
        'extra_data' => [
            'wish_moderation_mode' => 'auto',
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dam-cuoi-nam-hoa-'.uniqid(),
        'title' => 'Thiệp cưới Nam & Hoa',
        'version_type' => 'free',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('public guest can submit wish with auto moderation mode and it is approved immediately', function () {
    $response = $this->postJson(route('invitation.wish.store', $this->invitation->slug), [
        'name' => 'Bác Ba Hàng Xóm',
        'message' => 'Chúc hai cháu trăm năm hạnh phúc, răng long đầu bạc!',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'wish' => [
                'name' => 'Bác Ba Hàng Xóm',
                'is_approved' => true,
                'is_private' => false,
            ],
        ]);

    $this->assertDatabaseHas('wedding_wishes', [
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Ba Hàng Xóm',
        'is_approved' => true,
        'is_private' => false,
    ]);
});

test('public guest wish is pending when wedding moderation mode is manual', function () {
    $this->wedding->setWishModerationMode('manual');

    $response = $this->postJson(route('invitation.wish.store', $this->invitation->slug), [
        'name' => 'Chú Tư',
        'message' => 'Chúc mừng đôi uyên ương!',
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'wish' => [
                'name' => 'Chú Tư',
                'is_approved' => false,
            ],
        ]);

    $this->assertDatabaseHas('wedding_wishes', [
        'wedding_id' => $this->wedding->id,
        'name' => 'Chú Tư',
        'is_approved' => false,
    ]);
});

test('private wish is never published publicly and has is_private set to true', function () {
    $response = $this->postJson(route('invitation.wish.store', $this->invitation->slug), [
        'name' => 'Bạn Thân Bí Mật',
        'message' => 'Món quà bất ngờ tớ để dưới gối nhé!',
        'is_private' => true,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'wish' => [
                'name' => 'Bạn Thân Bí Mật',
                'is_approved' => false,
                'is_private' => true,
            ],
        ]);

    $this->assertDatabaseHas('wedding_wishes', [
        'wedding_id' => $this->wedding->id,
        'name' => 'Bạn Thân Bí Mật',
        'is_approved' => false,
        'is_private' => true,
    ]);

    // getWishes API công khai không được trả về lời chúc này
    $getList = $this->getJson(route('invitation.wish.index', $this->invitation->slug));
    $getList->assertOk();
    expect($getList->json('wishes'))->toBeEmpty();
});

test('public guest can submit wish with signature canvas and guest token', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Anh Lê Hoàng',
        'phone' => '0912345678',
        'group_name' => 'Bạn Đại Học',
        'guest_token' => 'guest-secret-token-123',
    ]);

    // Giả lập PNG 1x1 pixel base64
    $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    $response = $this->postJson(route('invitation.wish.store', $this->invitation->slug), [
        'name' => 'Anh Lê Hoàng',
        'message' => 'Mừng ngày vui của người anh em!',
        'guest_token' => 'guest-secret-token-123',
        'signature_data' => $fakeBase64,
    ]);

    $response->assertStatus(201);

    $wish = WeddingWish::where('wedding_id', $this->wedding->id)->first();
    expect($wish->guest_id)->toBe($guest->id);
    expect($wish->signature_url)->not->toBeNull();
});

test('getWishes prioritizes pinned wishes on top and orders by latest', function () {
    $normalWish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Thường',
        'message' => 'Chúc mừng nhé!',
        'is_approved' => true,
        'is_pinned' => false,
        'is_private' => false,
        'created_at' => now()->subMinutes(10),
    ]);

    $pinnedWish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Trưởng Họ (Ghim)',
        'message' => 'Lời chúc đặc biệt nhất gia tộc!',
        'is_approved' => true,
        'is_pinned' => true,
        'is_private' => false,
        'created_at' => now()->subMinutes(30),
    ]);

    $response = $this->getJson(route('invitation.wish.index', $this->invitation->slug));
    $response->assertOk();

    $wishes = $response->json('wishes');
    expect(count($wishes))->toBe(2);
    // Lời chúc ghim phải ở vị trí đầu tiên dù được tạo trước
    expect($wishes[0]['id'])->toBe($pinnedWish->id);
    expect($wishes[0]['is_pinned'])->toBeTrue();
    expect($wishes[1]['id'])->toBe($normalWish->id);
});

test('owner can toggle pin a wish', function () {
    $wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Cô Giáo Cũ',
        'message' => 'Chúc mừng học trò cưng!',
        'is_approved' => true,
        'is_pinned' => false,
    ]);

    $this->actingAs($this->user)
        ->post(route('account.wishes.toggle_pin', [$this->invitation->id, $wish->id]))
        ->assertRedirect();

    expect($wish->fresh()->is_pinned)->toBeTrue();

    // Toggle lần 2 để bỏ ghim
    $this->actingAs($this->user)
        ->post(route('account.wishes.toggle_pin', [$this->invitation->id, $wish->id]))
        ->assertRedirect();

    expect($wish->fresh()->is_pinned)->toBeFalse();
});

test('owner can update wish moderation mode between auto and manual', function () {
    $this->actingAs($this->user)
        ->post(route('account.wishes.update_moderation_mode', $this->invitation->id), [
            'moderation_mode' => 'manual',
        ])
        ->assertRedirect();

    expect($this->wedding->fresh()->getWishModerationMode())->toBe('manual');

    $this->actingAs($this->user)
        ->post(route('account.wishes.update_moderation_mode', $this->invitation->id), [
            'moderation_mode' => 'auto',
        ])
        ->assertRedirect();

    expect($this->wedding->fresh()->getWishModerationMode())->toBe('auto');
});

test('owner can reply to a wish and export guestbook CSV with new fields', function () {
    $wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bác Hùng',
        'message' => 'Chúc hai cháu sớm có quý tử!',
        'is_approved' => true,
        'is_pinned' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('account.wishes.reply', [$this->invitation->id, $wish->id]), [
            'reply' => 'Dạ chúng cháu cảm ơn bác nhiều ạ!',
        ])
        ->assertRedirect();

    expect($wish->fresh()->reply)->toBe('Dạ chúng cháu cảm ơn bác nhiều ạ!');

    // Test Export CSV
    $csvResponse = $this->actingAs($this->user)
        ->get(route('account.wishes.export', $this->invitation->id));

    $csvResponse->assertOk();
    $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('stranger cannot moderate wishes of another wedding', function () {
    $stranger = User::factory()->create();

    $wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Khách Lạ',
        'message' => 'Hello',
        'is_approved' => true,
    ]);

    $this->actingAs($stranger)
        ->post(route('account.wishes.toggle_pin', [$this->invitation->id, $wish->id]))
        ->assertStatus(403);

    $this->actingAs($stranger)
        ->post(route('account.wishes.update_moderation_mode', $this->invitation->id), [
            'moderation_mode' => 'manual',
        ])
        ->assertStatus(403);
});
