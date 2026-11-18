<?php

declare(strict_types=1);

use App\Http\Controllers\Client\InvitationEditorController;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Database\Seeders\InvitationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    InvitationEditorController::clearMemoization();
    Cache::forget('mayhen_active_templates_collection');
    $this->seed(InvitationTemplateSeeder::class);
});

test('trang /tao-thiep nạp đầy đủ tài nguyên Hướng dẫn tương tác và nút Hướng dẫn cố định với định danh tài khoản an toàn', function (): void {
    $template = InvitationTemplate::query()->where('slug', 'vom-troi-may-ao-cuoi')->firstOrFail();
    InvitationEditorController::clearMemoization();
    Cache::flush();

    // 1. Khách vãng lai (Guest)
    $guestResp = $this->get(route('invitation.create', ['template' => $template->slug]));
    $guestResp->assertOk()
        ->assertSee('editor-tour.css', false)
        ->assertSee('id="mayhen_editor_tour_trigger_btn"', false)
        ->assertSee('aria-label="Xem hướng dẫn sử dụng Studio"', false)
        ->assertSee('Hướng dẫn')
        ->assertSee('window.__MAYHEN_AUTH_USER_ID = null;', false);

    // 2. Khách hàng đã đăng nhập nhận định danh số riêng biệt (không lộ email/SĐT vào biến tour)
    $user = User::factory()->create([
        'email' => 'private-couple@example.com',
    ]);
    $authResp = $this->actingAs($user)->get(route('invitation.create', ['template' => $template->slug]));
    $authResp->assertOk()
        ->assertSee("window.__MAYHEN_AUTH_USER_ID = {$user->id};", false)
        ->assertDontSee('private-couple@example.com', false);
});

test('Hướng dẫn tương tác Studio tuyệt đối chỉ xuất hiện trên /tao-thiep, không nạp trên trang công khai, kho mẫu, tài liệu hay quản trị', function (): void {
    $template = InvitationTemplate::query()->where('slug', 'vom-troi-may-ao-cuoi')->first()
        ?? InvitationTemplate::query()->where('is_active', true)->where('tier', 'free')->firstOrFail();

    $owner = User::factory()->create();
    $wedding = Wedding::query()->create([
        'user_id' => $owner->id,
        'groom_name' => 'Minh Tiến',
        'bride_name' => 'Thu Nguyệt',
        'wedding_date' => '2027-03-14',
    ]);
    $invitation = Invitation::query()->create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'kiem-tra-pham-vi-tour-thiep-'.uniqid(),
        'revision_id' => 0,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $nonEditorUrls = [
        route('home'),
        route('templates.index'),
        route('templates.preview', ['slug' => $template->slug]),
        route('invitation.show', ['slug' => $invitation->slug]),
        route('docs.index'),
        route('docs.show', ['category' => 'studio-editor', 'slug' => 'CLI-EDIT-001']),
        route('login'),
        route('admin.login'),
    ];

    foreach ($nonEditorUrls as $url) {
        $resp = $this->get($url);
        $resp->assertOk();
        $html = $resp->getContent();
        expect($html)->not->toContain('editor-tour.css');
        expect($html)->not->toContain('mayhen_editor_tour_trigger_btn');
        expect($html)->not->toContain('product-tour.js');
    }
});

test('tài liệu Cẩm nang Studio CLI-EDIT-001 cập nhật hướng dẫn sử dụng nút Hướng dẫn tương tác', function (): void {
    $resp = $this->get(route('docs.show', [
        'category' => 'studio-editor',
        'slug' => 'CLI-EDIT-001',
    ]));

    $resp->assertOk()
        ->assertSee('Nút Hướng dẫn (?)')
        ->assertSee('không ảnh hưởng đến nội dung đã nhập');
});
