<?php

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTable;

beforeEach(function () {
    config([
        'services.sepay.webhook_secret' => 'test_sepay_webhook_secret_key_123456',
        'services.sepay.bank_code' => 'ICB',
        'services.sepay.bank_name' => 'VietinBank',
        'services.sepay.bank_account' => '103870000000',
        'services.sepay.bank_account_name' => 'MAY HEN ATELIER',
    ]);

    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Concurrency',
        'slug' => 'mau-concurrency-'.uniqid(),
        'description' => 'Test concurrency',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.hac-bach-trao-duyen.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Đạt',
        'bride_name' => 'Khánh Linh',
        'wedding_date' => now()->addDays(15),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thanh-dat-khanh-linh-'.uniqid(),
        'is_published' => true,
        'expires_at' => now()->addDays(30),
        'custom_config' => [
            'checkin_pin' => '888888',
        ],
    ]);

    $this->table = WeddingTable::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Bàn Hoàng Gia 01',
        'shape' => 'round',
        'capacity' => 12,
        'zone' => 'Khu A',
        'display_order' => 1,
    ]);
});

test('concurrent rapid qr scans on the same guest token guarantee idempotency and zero double-counting', function () {
    $guest = Guest::create([
        'wedding_id' => $this->wedding->id,
        'name' => 'Lâm Chí Dĩnh',
        'expected_attendees' => 2,
        'table_id' => $this->table->id,
        'table_name' => $this->table->name,
    ]);

    // Khách RSVP 2 người
    GuestResponse::create([
        'wedding_id' => $this->wedding->id,
        'guest_id' => $guest->id,
        'guest_name' => $guest->name,
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 0,
    ]);

    $token = $guest->guest_token;
    $this->actingAs($this->user);

    // Mô phỏng 5 lần quét dồn dập (ví dụ: double tap hoặc 2 lễ tân quét đồng thời)
    $results = [];
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $token,
        ]);
        $results[] = $response->json();
    }

    // Lần đầu tiên phải là success = true, already_checked_in = false
    expect($results[0]['success'])->toBeTrue();
    expect($results[0]['already_checked_in'])->toBeFalse();
    expect($results[0]['guest']['checked_in_attendees'])->toBe(2);

    // 4 lần sau phải nhận diện already_checked_in = true, không tăng người
    for ($i = 1; $i < 5; $i++) {
        expect($results[$i]['success'])->toBeTrue();
        expect($results[$i]['already_checked_in'])->toBeTrue();
    }

    $freshGuest = $guest->fresh();
    expect($freshGuest->checked_in_attendees)->toBe(2);
});

test('batch simultaneous checkins across table guests preserve exact table capacity without deadlock', function () {
    $guests = [];
    for ($i = 1; $i <= 6; $i++) {
        $g = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => "Khách Bàn $i",
            'expected_attendees' => 2,
            'table_id' => $this->table->id,
            'table_name' => $this->table->name,
        ]);
        GuestResponse::create([
            'wedding_id' => $this->wedding->id,
            'guest_id' => $g->id,
            'guest_name' => $g->name,
            'attending_status' => 'attending',
            'adults_count' => 2,
            'children_count' => 0,
        ]);
        $guests[] = $g;
    }

    $this->actingAs($this->user);

    // Quét điểm danh lần lượt toàn bộ 6 khách (tổng cộng 12 ghế = đúng capacity)
    foreach ($guests as $g) {
        $res = $this->postJson(route('checkin.scan', $this->invitation->slug), [
            'token' => $g->guest_token,
        ]);
        $res->assertOk()->assertJsonPath('success', true);
    }

    // Kiểm tra sĩ số bàn tiệc khớp chính xác 12
    expect($this->table->fresh()->assigned_attendees_count)->toBe(12);
    expect($this->table->fresh()->remaining_seats)->toBe(0);
});

test('burst webhook payments with identical transaction id are strictly idempotent', function () {
    $plan = Plan::create([
        'name' => 'Gói Hoàng Kim Pro',
        'slug' => 'goi-hoang-kim-pro-'.uniqid(),
        'price' => 599000,
        'duration_days' => 180,
        'max_invitations' => 1,
        'is_active' => true,
    ]);

    $order = Order::create([
        'user_id' => $this->user->id,
        'invitation_id' => $this->invitation->id,
        'plan_id' => $plan->id,
        'order_code' => 'MHTESTBURS01',
        'amount' => 599000,
        'status' => 'pending',
    ]);

    $secretKey = config('services.sepay.webhook_secret');
    $timestamp = now()->timestamp;
    $rawTxId = 'TXN_BURST_'.uniqid();

    $payload = [
        'id' => 998811,
        'gateway' => 'VietinBank',
        'transactionDate' => date('Y-m-d H:i:s'),
        'accountNumber' => '103870000000',
        'subAccount' => null,
        'code' => null,
        'content' => 'Thanh toan don hang MHTESTBURS01',
        'transferType' => 'in',
        'description' => 'MHTESTBURS01',
        'transferAmount' => 599000,
        'referenceCode' => $rawTxId,
        'referenceNumber' => $rawTxId,
        'accumulated' => 599000,
    ];

    $rawContent = json_encode($payload);
    $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawContent, $secretKey);
    $headers = [
        'Content-Type' => 'application/json',
        'X-SePay-Timestamp' => (string) $timestamp,
        'X-SePay-Signature' => $signature,
    ];

    // Gửi lần 1: Kích hoạt đơn hàng
    $res1 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($headers), $rawContent);
    $res1->assertStatus(200);

    $order->refresh();
    expect($order->status)->toBe('paid');
    $firstExpiresAt = $this->invitation->fresh()->expires_at;

    // Gửi lần 2 dồn dập (Replay cùng txId): Phải idempotent, trả về 200, không cộng thêm 180 ngày nữa
    $res2 = $this->call('POST', route('webhook.payment'), [], [], [], $this->transformHeadersToServerVars($headers), $rawContent);
    $res2->assertStatus(200);
    $res2->assertJsonPath('success', true);

    // Hạn sử dụng của thiệp phải giữ nguyên, không bị nhân đôi
    expect($this->invitation->fresh()->expires_at->timestamp)->toBe($firstExpiresAt->timestamp);
});

test('concurrent walk in guest registrations at reception door create token and checkin atomically', function () {
    $this->actingAs($this->user);

    $walkInResponse = $this->postJson(route('checkin.exception.walk_in', $this->invitation->slug), [
        'name' => 'Bác Trần Văn Quý (Phát sinh)',
        'salutation' => 'Bác',
        'group_name' => 'Khách Phát Sinh',
        'attendees' => 2,
        'table_name' => 'Bàn Hoàng Gia 01',
    ]);

    $walkInResponse->assertStatus(201)
        ->assertJsonPath('success', true);

    $guestId = $walkInResponse->json('guest.id');
    $walkInGuest = Guest::find($guestId);

    expect($walkInGuest)->not->toBeNull();
    expect($walkInGuest->guest_token)->not->toBeEmpty();
    expect($walkInGuest->checked_in_at)->not->toBeNull();
    expect($walkInGuest->checked_in_attendees)->toBe(2);
    expect($walkInGuest->table_id)->toBe($this->table->id);
    expect($walkInGuest->table_name)->toBe('Bàn Hoàng Gia 01');

    // Bàn tiệc ghi nhận thêm đúng 2 ghế
    expect($this->table->fresh()->assigned_attendees_count)->toBe(2);
});
