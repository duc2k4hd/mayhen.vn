<?php

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow('2026-11-10 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('guest user is redirected to login when accessing support desk', function () {
    $response = $this->get(route('account.support.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated client can view their support tickets index page', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $user->id,
        'subject' => 'Hỗ trợ kiểm tra hiển thị thiệp cưới',
        'category' => 'technical',
        'priority' => 'medium',
        'status' => 'open',
        'body' => 'Thiệp của mình bị giật lag khi mở trên thiết bị iPhone cũ.',
        'sla_deadline_at' => now()->addHours(12),
    ]);

    $response = $this->actingAs($user)->get(route('account.support.index'));

    $response->assertOk();
    $response->assertSeeText('Trung Tâm Hỗ Trợ & Yêu Cầu');
    $response->assertSee($ticket->ticket_code);
    $response->assertSee('Hỗ trợ kiểm tra hiển thị thiệp cưới');
});

test('client can create a new support ticket successfully', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $payload = [
        'subject' => 'Cần hỗ trợ đổi ảnh bìa gấp cho tiệc cưới',
        'category' => 'invitation',
        'priority' => 'high',
        'body' => 'Mình đã tải ảnh mới nhưng chưa thấy cập nhật trên giao diện xem trước. Nhờ admin kiểm tra giúp.',
    ];

    $response = $this->actingAs($user)->post(route('account.support.store'), $payload);

    $ticket = SupportTicket::where('user_id', $user->id)->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->subject)->toBe('Cần hỗ trợ đổi ảnh bìa gấp cho tiệc cưới');
    expect($ticket->category)->toBe('invitation');
    expect($ticket->priority)->toBe('high');
    expect($ticket->status)->toBe('open');
    expect($ticket->ticket_code)->toStartWith('TK-');
    // High priority SLA is 4 hours
    expect($ticket->sla_deadline_at->toIso8601String())->toBe('2026-11-10T14:00:00+07:00');

    $response->assertRedirect(route('account.support.show', $ticket->ticket_code));
    $response->assertSessionHas('success');
});

test('client with wedding in next 48 hours gets VIP concierge SLA of 60 minutes', function () {
    $user = User::factory()->create(['role' => 'customer']);

    // Tạo tiệc cưới diễn ra vào ngày mai
    Wedding::create([
        'user_id' => $user->id,
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'wedding_date' => Carbon::parse('2026-11-11 18:00:00'),
    ]);

    $payload = [
        'subject' => 'Cần hỗ trợ khẩn cấp kiểm tra mã QR check-in ngày mai',
        'category' => 'technical',
        'priority' => 'low', // chọn low nhưng hệ thống tự động thăng hạng lên urgent VIP
        'body' => 'Mai tiệc cưới diễn ra rồi mà quét mã QR khách báo lỗi. Xin hỗ trợ gấp!',
    ];

    $response = $this->actingAs($user)->post(route('account.support.store'), $payload);

    $ticket = SupportTicket::where('user_id', $user->id)->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->priority)->toBe('urgent');
    // Urgent / VIP 48h SLA là 60 phút
    expect($ticket->sla_deadline_at->toIso8601String())->toBe('2026-11-10T11:00:00+07:00');
});

test('client can view ticket details and cannot view other users tickets', function () {
    $user1 = User::factory()->create(['role' => 'customer']);
    $user2 = User::factory()->create(['role' => 'customer']);

    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $user1->id,
        'subject' => 'Ticket bí mật của user 1',
        'category' => 'billing',
        'priority' => 'medium',
        'status' => 'open',
        'body' => 'Nội dung riêng tư của user 1',
        'sla_deadline_at' => now()->addHours(12),
    ]);

    // User 1 xem được ticket của mình
    $response1 = $this->actingAs($user1)->get(route('account.support.show', $ticket->ticket_code));
    $response1->assertOk();
    $response1->assertSee('Ticket bí mật của user 1');

    // User 2 không xem được ticket của user 1
    $response2 = $this->actingAs($user2)->get(route('account.support.show', $ticket->ticket_code));
    $response2->assertNotFound();
});

test('client can reply to their ticket and reopens resolved ticket', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $user->id,
        'subject' => 'Hỏi về tính năng bảng chỗ ngồi',
        'category' => 'general',
        'priority' => 'low',
        'status' => 'resolved',
        'body' => 'Làm sao để xuất sơ đồ bàn tiệc ra file in ấn?',
        'sla_deadline_at' => now()->addHours(24),
    ]);

    $response = $this->actingAs($user)->post(route('account.support.reply', $ticket->ticket_code), [
        'body' => 'Mình đã thử xuất nhưng bị lỗi hiển thị số ghế, nhờ chuyên viên xem lại giúp.',
    ]);

    $response->assertSessionHas('success');

    $reply = SupportTicketReply::where('ticket_id', $ticket->id)->first();
    expect($reply)->not->toBeNull();
    expect($reply->user_id)->toBe($user->id);
    expect($reply->is_staff_reply)->toBeFalse();
    expect($reply->is_internal_note)->toBeFalse();

    // Ticket chuyển từ resolved sang in_progress
    $ticket->refresh();
    expect($ticket->status)->toBe('in_progress');
});

test('client can view ticket details with staff and client replies without relation exception', function () {
    $user = User::factory()->create(['name' => 'Cô Dâu Minh Thư', 'role' => 'customer']);
    $staff = User::factory()->create(['name' => 'Chuyên Viên Hỗ Trợ Lan Anh', 'role' => 'staff']);

    $ticket = SupportTicket::create([
        'ticket_code' => SupportTicket::generateCode(),
        'user_id' => $user->id,
        'subject' => 'Hỗ trợ đổi nhạc nền thiệp cưới',
        'category' => 'invitation',
        'priority' => 'medium',
        'status' => 'open',
        'body' => 'Nhờ đổi nhạc nền bài Beautiful in White giúp mình.',
        'sla_deadline_at' => now()->addHours(12),
    ]);

    // Tạo reply của khách hàng
    SupportTicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $user->id,
        'body' => 'File mp3 mình gửi kèm qua email rồi ạ.',
        'is_staff_reply' => false,
        'is_internal_note' => false,
    ]);

    // Tạo reply của chuyên viên CSKH
    SupportTicketReply::create([
        'ticket_id' => $ticket->id,
        'user_id' => $staff->id,
        'body' => 'Chào bạn, Mây Hẹn đã nhận được file và cập nhật nhạc nền thành công!',
        'is_staff_reply' => true,
        'is_internal_note' => false,
    ]);

    // Khi truy cập /tai-khoan/ho-tro/{code}, không được ném RelationNotFoundException
    $response = $this->actingAs($user)->get(route('account.support.show', $ticket->ticket_code));

    $response->assertOk();
    $response->assertSee('File mp3 mình gửi kèm qua email');
    $response->assertSee('Mây Hẹn đã nhận được file');
    $response->assertSee('Chuyên Viên Mây Hẹn');
});
