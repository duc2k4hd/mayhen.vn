<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'customer']);

    $this->template = InvitationTemplate::where('slug', 'giua-muon-ngan-vi-sao')->first() ?? InvitationTemplate::create([
        'name' => 'Giữa Muôn Ngàn Vì Sao',
        'slug' => 'giua-muon-ngan-vi-sao',
        'description' => 'Mẫu thiệp thử nghiệm',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.giua-muon-ngan-vi-sao.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'wedding_date' => now()->addDays(60),
        'story' => 'Câu chuyện tình yêu ban đầu chưa chỉnh sửa.',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'hoang-nam-khanh-linh-test',
        'is_published' => true,
    ]);
});

test('khách hàng có thể tạo 3 gợi ý lời ngỏ Quiet Luxury với phong cách mặc định lãng mạn', function () {
    $response = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'keywords' => 'Đà Lạt, quán cafe mùa thu, tình yêu 5 năm',
        'how_we_met' => 'Gặp nhau lần đầu tại thư viện trường đại học',
        'tone' => 'romantic',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'couple' => 'Hoàng Nam & Khánh Linh',
        ]);

    $data = $response->json();
    expect($data['options'])->toHaveCount(3);

    // Mặc định 'romantic' được ưu tiên ở vị trí đầu
    expect($data['options'][0]['style_code'])->toBe('romantic');
    expect($data['options'][0]['content'])->toContain('Đà Lạt');
    expect($data['options'][0]['content'])->toContain('Gặp nhau lần đầu tại thư viện trường đại học');
});

test('khách hàng chọn tông tối giản (minimal) hoặc hóm hỉnh (cheerful) đưa phong cách tương ứng lên đầu', function () {
    // 1. Minimal
    $responseMinimal = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'tone' => 'minimal',
    ]);

    $responseMinimal->assertStatus(200);
    $dataMinimal = $responseMinimal->json();
    expect($dataMinimal['options'][0]['style_code'])->toBe('minimal');
    expect($dataMinimal['options'][0]['style_name'])->toBe('Tối Giản & Thanh Lịch');

    // 2. Cheerful
    $responseCheerful = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'tone' => 'cheerful',
    ]);

    $responseCheerful->assertStatus(200);
    $dataCheerful = $responseCheerful->json();
    expect($dataCheerful['options'][0]['style_code'])->toBe('cheerful');
    expect($dataCheerful['options'][0]['style_name'])->toBe('Trẻ Trung & Hóm Hỉnh');
});

test('chặn từ ngữ thô tục hoặc phản cảm (NoProfanity) ở các trường đầu vào', function () {
    // Từ thô tục trong keywords
    $response = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'keywords' => 'đụ má, chửi bậy',
        'tone' => 'romantic',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('keywords');

    // Từ thô tục trong how_we_met
    $response2 = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'how_we_met' => 'gặp nhau lúc đụ nhau',
    ]);

    $response2->assertStatus(422)
        ->assertJsonValidationErrors('how_we_met');
});

test('ngăn chặn triệt để xss và html injection trong nội dung sinh ra', function () {
    $xssShort = '<script>alert(1)</script>';

    $response = $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Nam '.$xssShort,
        'bride_name' => 'Linh',
        'keywords' => 'Cafe, '.$xssShort,
        'how_we_met' => 'Thư viện '.$xssShort,
        'tone' => 'romantic',
    ]);

    $response->assertStatus(200);
    $contentJson = json_encode($response->json());

    // Tuyệt đối không được chứa raw html tags
    expect($contentJson)->not->toContain('<script>');
    expect($contentJson)->not->toContain('</script>');
});

test('api sinh lời ngỏ không ghi đè tự động vào bản ghi thiệp cưới (idempotent)', function () {
    $initialStory = $this->wedding->story;

    $this->actingAs($this->user)->postJson(route('invitation.aiStory'), [
        'groom_name' => 'Hoàng Nam',
        'bride_name' => 'Khánh Linh',
        'keywords' => 'Kỷ niệm Paris lãng mạn',
        'tone' => 'romantic',
    ]);

    $this->wedding->refresh();
    expect($this->wedding->story)->toBe($initialStory);
});
