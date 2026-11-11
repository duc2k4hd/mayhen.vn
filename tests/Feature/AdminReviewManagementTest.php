<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Review;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Review Test',
        'slug' => 'mau-review-test-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Trần Quang Khải',
        'bride_name' => 'Nguyễn Thảo My',
        'wedding_date' => now()->subWeeks(2)->toDateString(),
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'thiep-review-test-'.uniqid(),
        'status' => 'published',
        'is_published' => true,
    ]);
});

test('customer can submit honest review with rating and content', function () {
    $response = $this->actingAs($this->user)->post(route('account.reviews.store', $this->invitation->id), [
        'rating' => 5,
        'title' => 'Trải nghiệm thiệp cưới tuyệt vời',
        'content' => 'Giao diện thiệp rất thanh lịch, bạn bè và người thân của chúng tôi ai cũng khen ngợi hết lời.',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reviews', [
        'user_id' => $this->user->id,
        'wedding_id' => $this->wedding->id,
        'couple_name' => 'Trần Quang Khải & Nguyễn Thảo My',
        'rating' => 5,
        'status' => 'pending', // Phải ở trạng thái pending chờ admin duyệt theo Mục 7.14 DOCX
        'verified_purchase' => true,
    ]);
});

test('customer review requires valid rating and minimum content length', function () {
    $response = $this->actingAs($this->user)->post(route('account.reviews.store', $this->invitation->id), [
        'rating' => 6, // Không hợp lệ
        'content' => 'Ngắn', // Dưới 10 ký tự
    ]);

    $response->assertSessionHasErrors(['rating', 'content']);
});

test('guest cannot submit review and unauthorized user cannot review another wedding', function () {
    $this->post(route('account.reviews.store', $this->invitation->id), [
        'rating' => 5,
        'content' => 'Khách vãng lai đánh giá.',
    ])->assertRedirect(route('login'));

    $otherUser = User::factory()->create();
    $this->actingAs($otherUser)->post(route('account.reviews.store', $this->invitation->id), [
        'rating' => 5,
        'content' => 'Đánh giá thiệp người khác trái phép.',
    ])->assertStatus(404);
});

test('admin can view reviews list and see operational kpis', function () {
    Review::create([
        'user_id' => $this->user->id,
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'template_id' => $this->template->id,
        'couple_name' => 'Trần Quang Khải & Nguyễn Thảo My',
        'rating' => 5,
        'content' => 'Mẫu thiệp rất xuất sắc và trang nhã.',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.reviews.index'));

    $response->assertStatus(200);
    $response->assertSee('Trần Quang Khải &amp; Nguyễn Thảo My', false);
    $response->assertSee('Mẫu thiệp rất xuất sắc và trang nhã.');
});

test('admin can approve customer review and toggle featured for homepage display', function () {
    $review = Review::create([
        'user_id' => $this->user->id,
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'template_id' => $this->template->id,
        'couple_name' => 'Trần Quang Khải & Nguyễn Thảo My',
        'rating' => 5,
        'title' => 'Rất hài lòng về chất lượng',
        'content' => 'Thiệp cưới tải rất nhanh, nhạc nền phát êm ái trên iPhone.',
        'status' => 'pending',
        'is_featured' => false,
    ]);

    // 1. Duyệt đánh giá
    $approveRes = $this->actingAs($this->admin)->post(route('admin.reviews.approve', $review));
    $approveRes->assertSessionHas('success');

    $review->refresh();
    expect($review->status)->toBe('approved');
    expect($review->reviewed_by)->toBe($this->admin->id);

    // 2. Ghim nổi bật lên trang chủ
    $featureRes = $this->actingAs($this->admin)->post(route('admin.reviews.toggle_featured', $review));
    $featureRes->assertSessionHas('success');

    $review->refresh();
    expect($review->is_featured)->toBeTrue();

    // 3. Kiểm tra hiển thị chân thật trên Trang Chủ
    $homeRes = $this->get(route('home'));
    $homeRes->assertStatus(200);
    $homeRes->assertSee('Trần Quang Khải &amp; Nguyễn Thảo My', false);
    $homeRes->assertSee('Thiệp cưới tải rất nhanh, nhạc nền phát êm ái trên iPhone.');
});

test('admin can reject or delete inappropriate review', function () {
    $review = Review::create([
        'user_id' => $this->user->id,
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'template_id' => $this->template->id,
        'couple_name' => 'Spam User',
        'rating' => 1,
        'content' => 'Nội dung quảng cáo rác không phù hợp.',
        'status' => 'pending',
    ]);

    // Từ chối / Ẩn
    $this->actingAs($this->admin)->post(route('admin.reviews.reject', $review))
        ->assertSessionHas('warning');

    $review->refresh();
    expect($review->status)->toBe('rejected');

    // Xóa vĩnh viễn
    $this->actingAs($this->admin)->delete(route('admin.reviews.destroy', $review))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('reviews', [
        'id' => $review->id,
    ]);
});
