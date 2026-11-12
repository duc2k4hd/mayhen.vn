<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Signature',
        'slug' => 'mau-test-signature-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Quốc Bảo',
        'bride_name' => 'Thanh Hà',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Park Hyatt Saigon',
        'venue_address' => '2 Lam Son Square, District 1, HCMC',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'bao-ha-'.uniqid(),
        'title' => 'Thiệp Cưới Quốc Bảo & Thanh Hà',
        'is_published' => true,
    ]);

    $this->wish = WeddingWish::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Nguyễn Văn An',
        'message' => 'Chúc hai bạn trăm năm hạnh phúc!',
        'signature_url' => 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=',
        'is_approved' => true,
        'is_pinned' => false,
    ]);
});

test('chủ thiệp có thể truy cập Studio Kho Lưu Trữ Bút Tích Chữ Ký Tay', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.signature_archive.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Lưu Giữ Bút Tích Chữ Ký Tay Quan Khách');
    $response->assertSee('Quốc Bảo');
    $response->assertSee('Thanh Hà');
});

test('người dùng khác bị từ chối 403 khi truy cập Kho Chữ Ký của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.signature_archive.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật chữ ký tay và chủ đề tranh ký tên mosaic', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.signature_archive.update_couple', $this->invitation->id), [
            'groom_signature_data' => 'data:image/svg+xml;base64,PHN2ZyBncm9vbT48L3N2Zz4=',
            'bride_signature_data' => 'data:image/svg+xml;base64,PHN2ZyBicmlkZT48L3N2Zz4=',
            'mosaic_theme' => 'cream_gold',
            'mosaic_title' => 'Bức Tranh Ký Tên Trăm Năm',
            'mosaic_quote' => 'Tình yêu là lời hứa vĩnh cửu.',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('chủ thiệp có thể ghim và bỏ ghim chữ ký quan khách trang trọng', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.signature_archive.toggle_pin', [
            'id' => $this->invitation->id,
            'wishId' => $this->wish->id,
        ]));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'is_pinned' => true,
        ]);

    expect($this->wish->fresh()->is_pinned)->toBeTrue();
});

test('chủ thiệp có thể gỡ bỏ chữ ký khỏi kho lưu trữ nhưng bảo toàn lời chúc', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('account.signature_archive.destroy', [
            'id' => $this->invitation->id,
            'wishId' => $this->wish->id,
        ]));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $freshWish = $this->wish->fresh();
    expect($freshWish->signature_url)->toBeNull();
    expect($freshWish->message)->toBe('Chúc hai bạn trăm năm hạnh phúc!');
});

test('mọi người có thể xem toàn màn hình Bức Tranh Ký Tên Mosaic Wall và nạp feed', function () {
    $mosaicResponse = $this->get(route('signature_archive.mosaic', $this->invitation->id));
    $mosaicResponse->assertStatus(200);

    $feedResponse = $this->getJson(route('signature_archive.feed', $this->invitation->id));
    $feedResponse->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'kpis',
            'config',
            'signatures',
        ]);
});
