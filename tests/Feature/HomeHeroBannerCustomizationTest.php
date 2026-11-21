<?php

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'email' => 'admin_banner_test@mayhen.vn',
        'role' => 'admin',
    ]);
});

test('client home page renders mayhen_home_hero with configured desktop and mobile 9:16 hero banner images', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertSee('mayhen_home_hero');
    $response->assertSee('--mayhen-home-hero-bg-image:', false);
    $response->assertSee('--mayhen-home-hero-bg-image-mobile:', false);
    $response->assertSee('/storage/images/client/banners/banner-home.webp', false);
    $response->assertSee('/storage/images/client/banners/banner-home-mobile.webp', false);
});

test('admin can view both desktop and mobile 9:16 home hero banner configurations in platform settings', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.settings.platform', ['group' => PlatformSetting::GROUP_BRAND_SEO]));

    $response->assertStatus(200);
    $response->assertSee('Ảnh Nền Hero Trang Chủ (Banner Home)');
    $response->assertSee('Ảnh Nền Hero Trang Chủ Mobile (Tỉ Lệ 9:16 TikTok)');
    $response->assertSee('field_home_hero_banner', false);
    $response->assertSee('home_hero_banner_file', false);
    $response->assertSee('field_home_hero_banner_mobile', false);
    $response->assertSee('home_hero_banner_mobile_file', false);
});

test('admin can update hero banner via url in platform settings', function () {
    $newBannerUrl = '/storage/images/client/banners/new-custom-banner.webp';

    $response = $this->actingAs($this->admin)->put(route('admin.settings.platform.update', ['group' => PlatformSetting::GROUP_BRAND_SEO]), [
        'brand_name' => 'Mây Hẹn Atelier',
        'tagline' => 'Nền tảng thiệp cưới cao cấp',
        'home_hero_banner' => $newBannerUrl,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $val = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner');
    expect($val)->toBe($newBannerUrl);

    // Verify home page displays new banner
    $homeRes = $this->get(route('home'));
    $homeRes->assertStatus(200);
    $homeRes->assertSee($newBannerUrl, false);
});

test('admin can upload new hero banner file and old uploaded file in storage is cleaned up', function () {
    Storage::fake('public');

    // 1. Initial uploaded file
    $initialFile = UploadedFile::fake()->image('old-banner.webp', 1920, 1080);
    $initialPath = $initialFile->storeAs('images/client/banners', 'old-banner.webp', 'public');
    $initialUrl = '/storage/'.$initialPath;

    PlatformSetting::setValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner', $initialUrl);
    Storage::disk('public')->assertExists($initialPath);

    // 2. Upload replacement banner file
    $replacementFile = UploadedFile::fake()->image('replacement-banner.webp', 1920, 1080);

    $response = $this->actingAs($this->admin)->put(route('admin.settings.platform.update', ['group' => PlatformSetting::GROUP_BRAND_SEO]), [
        'brand_name' => 'Mây Hẹn Atelier',
        'tagline' => 'Nền tảng thiệp cưới cao cấp',
        'home_hero_banner' => $initialUrl,
        'home_hero_banner_file' => $replacementFile,
    ]);

    $response->assertRedirect();

    // Old file in storage must be deleted
    Storage::disk('public')->assertMissing($initialPath);

    // New file must be saved and updated in setting
    $newVal = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner');
    expect($newVal)->toStartWith('/storage/images/client/banners/banner-home-');
    $newRelPath = substr($newVal, strlen('/storage/'));
    Storage::disk('public')->assertExists($newRelPath);
});

test('admin can upload new 9:16 mobile hero banner file and old mobile file in storage is cleaned up', function () {
    Storage::fake('public');

    $initialFile = UploadedFile::fake()->image('old-mobile-banner.webp', 1080, 1920);
    $initialPath = $initialFile->storeAs('images/client/banners', 'old-mobile-banner.webp', 'public');
    $initialUrl = '/storage/'.$initialPath;

    PlatformSetting::setValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner_mobile', $initialUrl);
    Storage::disk('public')->assertExists($initialPath);

    $replacementFile = UploadedFile::fake()->image('tiktok-hero-banner.webp', 1080, 1920);

    $response = $this->actingAs($this->admin)->put(route('admin.settings.platform.update', ['group' => PlatformSetting::GROUP_BRAND_SEO]), [
        'brand_name' => 'Mây Hẹn Atelier',
        'tagline' => 'Nền tảng thiệp cưới cao cấp',
        'home_hero_banner_mobile' => $initialUrl,
        'home_hero_banner_mobile_file' => $replacementFile,
    ]);

    $response->assertRedirect();
    Storage::disk('public')->assertMissing($initialPath);

    $newVal = PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'home_hero_banner_mobile');
    expect($newVal)->toStartWith('/storage/images/client/banners/banner-home-mobile-');
    $newRelPath = substr($newVal, strlen('/storage/'));
    Storage::disk('public')->assertExists($newRelPath);

    $homeRes = $this->get(route('home'));
    $homeRes->assertOk()->assertSee($newVal, false);
});
