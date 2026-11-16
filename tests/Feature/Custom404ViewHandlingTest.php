<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('truy cập route không tồn tại trả về mã 404 và render đúng giao diện thật errors.404', function () {
    $response = $this->get('/duong-dan-hoan-toan-khong-ton-tai-'.uniqid());

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Ôi, tấm thiệp <em>đi lạc</em> mất rồi!', false);
    $response->assertSee('Về Trang Chủ');
    $response->assertSee('Khám Phá Mẫu Thiệp');
    $response->assertSee(route('home'));
    $response->assertSee(route('templates.index'));
    $response->assertDontSee('NotFoundHttpException');
    $response->assertDontSee('Stack trace');
});

test('controller gọi abort(404) khi xem bài blog không tồn tại sẽ render giao diện thật errors.404', function () {
    $response = $this->get('/cam-nang-cuoi/bai-viet-khong-he-ton-tai');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Ôi, tấm thiệp <em>đi lạc</em> mất rồi!', false);
    $response->assertSee('Về Trang Chủ');
});

test('controller gọi abort(404) khi xem mẫu thiệp không tồn tại sẽ render giao diện thật errors.404', function () {
    $response = $this->get('/mau-thiep/mau-thiep-chua-tung-co');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Ôi, tấm thiệp <em>đi lạc</em> mất rồi!', false);
    $response->assertSee('Về Trang Chủ');
});

test('controller gọi abort(404) khi xem tài liệu docs không tồn tại sẽ render giao diện thật errors.404', function () {
    $response = $this->get('/docs/client/bai-viet-tai-lieu-khong-co');

    $response->assertStatus(404);
    $response->assertSee('404');
    $response->assertSee('Ôi, tấm thiệp <em>đi lạc</em> mất rồi!', false);
    $response->assertSee('Về Trang Chủ');
});

test('yêu cầu dạng JSON hoặc API gặp 404 vẫn trả về phản hồi JSON chuẩn', function () {
    $response = $this->getJson('/api/endpoint-khong-ton-tai');

    $response->assertStatus(404);
    $response->assertHeader('Content-Type', 'application/json');
});
