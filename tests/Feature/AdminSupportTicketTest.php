<?php

use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->user = User::factory()->create();

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Chú Rể Test',
        'bride_name' => 'Cô Dâu Test',
        'wedding_date' => now()->addDay()->toDateString(),
    ]);

    $this->ticket = SupportTicket::create([
        'ticket_code' => 'TK-TEST-001',
        'user_id' => $this->user->id,
        'subject' => 'Hỗ trợ đổi nhạc nền tiệc cưới gấp',
        'body' => 'Nội dung chi tiết yêu cầu hỗ trợ đổi nhạc nền tiệc cưới.',
        'category' => 'template',
        'priority' => 'urgent',
        'status' => 'open',
    ]);
});

test('admin can access support index page and see kpis without error', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.support.index'));

    $response->assertStatus(200);
    $response->assertSee('TK-TEST-001');
    $response->assertSee('Hỗ trợ đổi nhạc nền tiệc cưới gấp');
});

test('admin can filter tickets by queue vip_48h', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.support.index', ['queue' => 'vip_48h']));

    $response->assertStatus(200);
    $response->assertSee('TK-TEST-001');
});

test('admin can view ticket detail and replies', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.support.show', $this->ticket));

    $response->assertStatus(200);
    $response->assertSee('TK-TEST-001');
    $response->assertSee('Hỗ trợ đổi nhạc nền tiệc cưới gấp');
});

test('admin can reply to ticket and update status', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.support.reply', $this->ticket), [
        'body' => 'Chào bạn, đội ngũ hỗ trợ đã nhận thông tin và xử lý giúp bạn.',
        'is_internal_note' => false,
    ]);

    $response->assertRedirect(route('admin.support.show', $this->ticket));
    $this->assertDatabaseHas('support_ticket_replies', [
        'ticket_id' => $this->ticket->id,
        'is_staff_reply' => true,
    ]);

    $this->ticket->refresh();
    expect($this->ticket->status)->toBe('in_progress');
});

test('admin can assign ticket to staff', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.support.assign', $this->ticket), [
        'assigned_to' => $this->admin->id,
    ]);

    $response->assertRedirect();
    $this->ticket->refresh();
    expect($this->ticket->assigned_to)->toBe($this->admin->id);
});
