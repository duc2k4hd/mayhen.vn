<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Publish Diff',
        'slug' => 'mau-test-publish-diff-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Thành Nam',
        'bride_name' => 'Bích Ngọc',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'JW Marriott Hotel Hanoi',
        'venue_address' => 'No 8 Do Duc Duc Road, Me Tri, Nam Tu Liem, Hanoi',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'nam-ngoc-'.uniqid(),
        'title' => 'Thiệp Cưới Thành Nam & Bích Ngọc',
        'is_published' => true,
        'revision_id' => 1,
        'published_at' => now(),
        'draft_config' => [
            'groom_name' => 'Thành Nam (Cập nhật)',
            'venue_name' => 'JW Marriott Grand Ballroom',
        ],
    ]);
});

test('chủ thiệp có thể truy cập Studio Thanh Tra & So Sánh Bản Nháp', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.publish_diff.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Publish Diff Inspector');
});

test('người dùng khác bị 403 khi truy cập Publish Diff Studio của chủ thiệp', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.publish_diff.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cam kết xuất bản bản nháp thành bản live chính thức', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.publish_diff.publish', $this->invitation->id), [
            'change_note' => 'Cập nhật lại tên sảnh tiệc chính xác',
        ]);

    $response->assertRedirect(route('account.publish_diff.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $freshInvitation = $this->invitation->fresh();
    expect($freshInvitation->revision_id)->toBeGreaterThanOrEqual(1);
});

test('cặp đôi có thể hủy bỏ toàn bộ thay đổi nháp để hoàn tác về bản live', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.publish_diff.discard', $this->invitation->id));

    $response->assertRedirect(route('account.publish_diff.index', $this->invitation->id));
    $response->assertSessionHas('info');

    $freshInvitation = $this->invitation->fresh();
    expect($freshInvitation->draft_config)->toBeNull();
});

test('API Diff cung cấp cấu trúc so sánh thay đổi phục vụ nhúng Studio Editor', function () {
    $response = $this->actingAs($this->user)
        ->getJson(route('account.publish_diff.api_diff', $this->invitation->id));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'diff',
            'revision_id',
            'published_at',
        ]);
});
