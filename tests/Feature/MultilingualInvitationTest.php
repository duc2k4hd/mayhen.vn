<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\MultilingualInvitationService;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Đa Ngôn Ngữ',
        'slug' => 'mau-test-multilingual-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Michael Smith',
        'bride_name' => 'Nguyễn Mai Lan',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Park Hyatt Saigon',
        'venue_address' => '2 Lam Son Square, District 1, Ho Chi Minh City',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'michael-mai-lan-'.uniqid(),
        'title' => 'Thiệp Cưới Michael & Mai Lan',
        'is_published' => true,
        'custom_config' => [
            'theme_color' => '#C5A880',
            'i18n' => [
                'enabled_locales' => ['vi', 'en'],
                'default_locale' => 'vi',
            ],
        ],
    ]);
});

test('khách chưa đăng nhập không thể truy cập trang cài đặt đa ngôn ngữ', function () {
    $this->get(route('account.multilingual.index', $this->invitation->id))
        ->assertRedirect(route('login'));

    $this->post(route('account.multilingual.update', $this->invitation->id), [
        'enabled_locales' => ['vi', 'en'],
        'default_locale' => 'vi',
    ])->assertRedirect(route('login'));
});

test('người dùng khác bị từ chối 403 khi truy cập hoặc cập nhật cấu hình đa ngôn ngữ', function () {
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)
        ->get(route('account.multilingual.index', $this->invitation->id))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('account.multilingual.update', $this->invitation->id), [
            'enabled_locales' => ['vi', 'en'],
            'default_locale' => 'vi',
        ])->assertForbidden();
});

test('chủ sở hữu truy cập thành công trang cài đặt đa ngôn ngữ', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.multilingual.index', $this->invitation->id));

    $response->assertOk()
        ->assertSee('Đa Ngôn Ngữ Thiệp Cưới')
        ->assertSee('Tiếng Việt')
        ->assertSee('English')
        ->assertSee('한국어')
        ->assertSee('中文')
        ->assertSee($this->invitation->slug.'?lang=en');
});

test('chủ sở hữu cập nhật thành công cấu hình đa ngôn ngữ và tùy biến bản dịch', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.multilingual.update', $this->invitation->id), [
            'enabled_locales' => ['vi', 'en', 'ko'],
            'default_locale' => 'en',
            'overrides' => [
                'en' => [
                    'hero.cordially_invite' => 'Warmly invite you to celebrate our union',
                ],
            ],
        ]);

    $response->assertRedirect(route('account.multilingual.index', $this->invitation->id))
        ->assertSessionHas('success');

    $this->invitation->refresh();
    $i18n = $this->invitation->custom_config['i18n'] ?? [];

    expect($i18n['enabled_locales'])->toContain('vi', 'en', 'ko')
        ->and($i18n['default_locale'])->toBe('en')
        ->and($i18n['overrides']['en']['hero.cordially_invite'])->toBe('Warmly invite you to celebrate our union');
});

test('MultilingualInvitationService phân giải ngôn ngữ chính xác theo thứ tự ưu tiên', function () {
    // 1. Theo query param
    expect(MultilingualInvitationService::resolveLocale('en', $this->invitation))->toBe('en')
        ->and(MultilingualInvitationService::resolveLocale('ko', $this->invitation))->toBe('ko')
        ->and(MultilingualInvitationService::resolveLocale('zh', $this->invitation))->toBe('zh');

    // 2. Không truyền param -> dùng default của invitation
    $this->invitation->update([
        'custom_config' => [
            'i18n' => [
                'enabled_locales' => ['vi', 'ko'],
                'default_locale' => 'ko',
            ],
        ],
    ]);
    expect(MultilingualInvitationService::resolveLocale(null, $this->invitation))->toBe('ko');

    // 3. Fallback tiếng Việt
    expect(MultilingualInvitationService::resolveLocale('unsupported_locale', null))->toBe('vi');
});

test('API public translations trả về đầy đủ từ điển theo ngôn ngữ cho thiệp cưới', function () {
    $response = $this->getJson(route('api.invitation.translations', [
        'slug' => $this->invitation->slug,
        'locale' => 'en',
    ]));

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'locale' => 'en',
            'slug' => $this->invitation->slug,
        ]);

    $dictionary = $response->json('dictionary');
    expect($dictionary)->toBeArray()
        ->and($dictionary['opening.wedding_invitation'])->toBe('WEDDING INVITATION')
        ->and($dictionary['hero.wedding_of'])->toBe('The Wedding Of')
        ->and($dictionary['rsvp.title'])->toBe('RSVP Confirmation')
        ->and($dictionary['countdown.title'])->toBe('Countdown to the Big Day');
});

test('từ điển dịch thuật hỗ trợ đầy đủ 4 ngôn ngữ cốt lõi theo Mục 2.10 DOCX', function () {
    $lexVi = MultilingualInvitationService::getDictionary('vi');
    $lexEn = MultilingualInvitationService::getDictionary('en');
    $lexKo = MultilingualInvitationService::getDictionary('ko');
    $lexZh = MultilingualInvitationService::getDictionary('zh');

    expect($lexVi['hero.wedding_of'])->toBe('Hôn Lễ Của')
        ->and($lexEn['hero.wedding_of'])->toBe('The Wedding Of')
        ->and($lexKo['hero.wedding_of'])->toBe('결혼합니다')
        ->and($lexZh['hero.wedding_of'])->toBe('我们要结婚了');

    // Kiểm tra các key RSVP
    expect($lexVi['rsvp.attending'])->toBe('Tôi sẽ tham dự')
        ->and($lexEn['rsvp.attending'])->toBe('Joyfully Accept')
        ->and($lexKo['rsvp.attending'])->toBe('참석합니다')
        ->and($lexZh['rsvp.attending'])->toBe('确认出席');
});
