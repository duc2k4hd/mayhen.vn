<?php

use App\Models\Plan;

beforeEach(function () {
    Plan::updateOrCreate(['slug' => 'pro'], [
        'name' => 'Trọn Vẹn (Wedding Pro)',
        'price' => 299000,
        'duration_days' => 730,
        'max_invitations' => 3,
        'is_active' => true,
    ]);
});

it('renders sandbox preview for supported features like seating and checkin', function () {
    $responseSeating = $this->get(route('premium.preview', 'seating'));

    $responseSeating->assertOk()
        ->assertSee('Chế Độ Xem Thử Tính Năng Cao Cấp')
        ->assertSee('Sơ Đồ Bố Trí Bàn Tiệc &amp; Sức Chứa Sảnh Cưới', false)
        ->assertSee('Bàn VIP 01');

    $responseCheckin = $this->get(route('premium.preview', 'checkin'));

    $responseCheckin->assertOk()
        ->assertSee('Nhật Ký Quét QR Điểm Danh Lễ Tân Thời Gian Thực')
        ->assertSee('Bác Lê Hoàng Long');
});

it('renders sandbox preview for live-wall and memory-vault', function () {
    $responseLiveWall = $this->get(route('premium.preview', 'live-wall'));

    $responseLiveWall->assertOk()
        ->assertSee('Giao Diện Màn Hình Chiếu Trực Tiếp')
        ->assertSee('Chúc mừng đôi uyên ương trăm năm hạnh phúc!');

    $responseVault = $this->get(route('premium.preview', 'memory-vault'));

    $responseVault->assertOk()
        ->assertSee('Gói Kỷ Niệm Ngoại Tuyến Độc Lập')
        ->assertSee('wedding_manifest.json');
});

it('returns 404 for unsupported preview features', function () {
    $response = $this->get(route('premium.preview', 'unsupported-xyz'));

    $response->assertNotFound();
});

