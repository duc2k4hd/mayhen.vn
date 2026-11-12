<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Photo Wish',
        'slug' => 'mau-test-photo-wish-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Phương Thảo',
        'wedding_date' => now()->addMonths(3)->toDateString(),
        'venue_name' => 'Lotte Hotel Saigon',
        'venue_address' => '2A-4A Ton Duc Thang, Dist 1, HCMC',
        'extra_data' => [
            'wish_moderation_mode' => 'manual', // Chế độ kiểm duyệt trước
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'huy-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Gia Huy & Phương Thảo',
        'is_published' => true,
    ]);
});

test('quan khách có thể gửi lời chúc đính kèm ảnh kỷ niệm (Photo Wish)', function () {
    $photo = UploadedFile::fake()->image('ky_niem.jpg', 800, 600);

    $response = $this->post(route('invitation.wish.store', ['slug' => $this->invitation->slug]), [
        'name' => 'Trần Văn Nam',
        'message' => 'Chúc mừng hạnh phúc hai bạn!',
        'photo' => $photo,
        'is_private' => false,
    ]);

    $response->assertStatus(201);

    $wish = WeddingWish::where('name', 'Trần Văn Nam')->first();
    expect($wish)->not->toBeNull();
    expect($wish->photo_url)->not->toBeNull();
    expect($wish->is_approved)->toBeFalse(); // Chế độ manual nên chờ duyệt
});

test('lời chúc chưa duyệt không xuất hiện trên feed công khai', function () {
    $wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lê Thị Mai',
        'message' => 'Trăm năm gắn kết!',
        'photo_url' => '/storage/wishes_photos/test.jpg',
        'is_approved' => false,
    ]);

    $feedResponse = $this->getJson(route('invitation.wish.index', ['slug' => $this->invitation->slug]));
    $feedResponse->assertStatus(200);
    $feedResponse->assertDontSee('Lê Thị Mai');
});

test('chủ thiệp có thể duyệt hiển thị lời chúc đính kèm ảnh trong Sổ Lưu Bút', function () {
    $wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Hoàng Khải',
        'message' => 'Hạnh phúc viên mãn nhé!',
        'photo_url' => '/storage/wishes_photos/khai.jpg',
        'is_approved' => false,
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('account.wishes.toggle_visibility', [
            'id' => $this->invitation->id,
            'wishId' => $wish->id,
        ]));

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    expect($wish->fresh()->is_approved)->toBeTrue();

    // Giờ lời chúc đã xuất hiện trên feed công khai
    $feedResponse = $this->getJson(route('invitation.wish.index', ['slug' => $this->invitation->slug]));
    $feedResponse->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Hoàng Khải',
        ]);
});
