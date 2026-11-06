<?php

use App\Models\InvitationTemplate;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('active template preview and embed preserve opening and responsive typography', function () {
    $response = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh');
    $response->assertStatus(200);

    $content = $response->getContent();
    expect($content)->toContain('mayhen_invitation_opening');
    expect($content)->toContain('Chạm Để Mở Thiệp');
    expect($content)->toContain('mayhen_invitation_ladi_wraper');

    $embedResponse = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh?embed=1');
    $embedResponse->assertStatus(200);
    $embedContent = $embedResponse->getContent();

    expect($embedContent)->toContain('mayhen_invitation_opening--opened');
    expect($embedContent)->toContain('display: none !important;');
    expect($embedContent)->toContain('mayhen_invitation_ladi_wraper');

    // 3. Kiểm tra file styles.css có các quy tắc auto-fit typography và chống đè chữ
    $cssResponse = $this->get('/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/styles.css');
    $cssResponse->assertStatus(200);
    $cssFile = file_get_contents($cssResponse->getFile()->getPathname());

    expect($cssFile)->toContain('clamp(');
    expect($cssFile)->toContain('.mayhen_invitation_opening');
    expect($cssFile)->toContain('@media');
});

test('editor validates human names max 4 words 30 chars no special characters and prevents xss', function () {
    $template = InvitationTemplate::where('slug', 'hanh-phuc-mang-ten-chung-minh')->firstOrFail();

    // 1. Hợp lệ: Tên 3-4 từ tiếng Việt có dấu, dưới 30 ký tự
    $validData = [
        'template_id' => $template->id,
        'wedding' => [
            'groom_name' => 'Nguyễn Minh Đức',
            'bride_name' => 'Trần Thị Mai Phương',
            'extra_data' => [
                'groom_father' => 'Nguyễn Văn Nam',
                'groom_mother' => 'Lê Thị Thu',
                'bride_father' => 'Trần Văn Hùng',
                'bride_mother' => 'Phạm Thị Lan',
            ],
        ],
    ];
    $response = $this->post('/tao-thiep', $validData);
    $response->assertStatus(302); // Redirect to invitation.show

    // 2. Không hợp lệ: Tên quá 4 từ (> 4 chữ)
    $tooManyWordsData = [
        'template_id' => $template->id,
        'wedding' => [
            'groom_name' => 'Nguyễn Trần Lê Hoàng Gia Bảo', // 6 từ
            'bride_name' => 'Lê Thị Mai',
        ],
    ];
    $responseInvalidWords = $this->post('/tao-thiep', $tooManyWordsData);
    $responseInvalidWords->assertSessionHasErrors(['Họ và tên Chú Rể']);

    // 3. Không hợp lệ: Tên quá 30 ký tự
    $tooLongData = [
        'template_id' => $template->id,
        'wedding' => [
            'groom_name' => 'Nguyễn Văn Thắng Đạt Hoàng Long', // > 30 ký tự
            'bride_name' => 'Lê Thị Mai',
        ],
    ];
    $responseTooLong = $this->post('/tao-thiep', $tooLongData);
    $responseTooLong->assertSessionHasErrors(['Họ và tên Chú Rể']);

    // 4. Không hợp lệ: Chứa số hoặc ký tự đặc biệt chống hack/phá
    $specialCharsData = [
        'template_id' => $template->id,
        'wedding' => [
            'groom_name' => 'Minh Đức 123', // Chứa số
            'bride_name' => 'Mai Anh <script>', // Chứa ký tự đặc biệt XSS
        ],
    ];
    $responseSpecialChars = $this->post('/tao-thiep', $specialCharsData);
    $responseSpecialChars->assertSessionHasErrors();
});
