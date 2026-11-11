<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingMusicService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->otherUser = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->template = InvitationTemplate::first() ?? InvitationTemplate::create([
        'name' => 'Mẫu Test Wedding Music',
        'slug' => 'mau-test-wedding-music-'.uniqid(),
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Đức Thịnh',
        'bride_name' => 'Thanh Thảo',
        'wedding_date' => now()->addMonths(2)->toDateString(),
        'venue_name' => 'Park Hyatt Saigon',
        'venue_address' => '2 Lam Son Square, District 1, TP.HCM',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'user_id' => $this->user->id,
        'slug' => 'thinh-thao-'.uniqid(),
        'title' => 'Thiệp Cưới Đức Thịnh & Thanh Thảo',
        'is_published' => true,
    ]);
});

test('cặp đôi có thể truy cập màn hình quản trị âm nhạc hôn lễ và xem 8 bản nhạc curated', function () {
    $response = $this->actingAs($this->user)
        ->get(route('account.music.index', $this->invitation->id));

    $response->assertStatus(200);
    $response->assertSee('Giai Điệu Hôn Lễ Quiet Luxury');
    $response->assertSee('Canon in D');
    $response->assertSee('River Flows in You');
    $response->assertSee('A Thousand Years');
    $response->assertSee('Thư Viện Tuyển Chọn Quiet Luxury');
});

test('người dùng không thể truy cập hoặc can thiệp cài đặt âm nhạc của thiệp cưới người khác', function () {
    $response = $this->actingAs($this->otherUser)
        ->get(route('account.music.index', $this->invitation->id));

    $response->assertStatus(403);
});

test('cặp đôi có thể cập nhật bài hát tuyển chọn, âm lượng và cấu hình autoplay', function () {
    $payload = [
        'track_id' => 'a_thousand_years_cello',
        'volume' => 0.85,
        'enabled' => '1',
        'autoplay' => '1',
        'loop' => '1',
        'fade_in' => '1',
        'pause_on_video' => '1',
        'floating_dock' => '1',
    ];

    $response = $this->actingAs($this->user)
        ->post(route('account.music.update', $this->invitation->id), $payload);

    $response->assertRedirect(route('account.music.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $musicConfig = WeddingMusicService::resolveMusicConfig($this->invitation);

    expect($musicConfig['track_id'])->toBe('a_thousand_years_cello');
    expect($musicConfig['title'])->toBe('A Thousand Years (Solo Cello & Harp)');
    expect($musicConfig['volume'])->toBe(0.85);
    expect($musicConfig['enabled'])->toBeTrue();
    expect($musicConfig['floating_dock'])->toBeTrue();
});

test('cặp đôi có thể tải lên tệp âm thanh riêng và kích hoạt làm nhạc nền', function () {
    $file = UploadedFile::fake()->create('our_song.mp3', 3000, 'audio/mpeg');

    $response = $this->actingAs($this->user)
        ->post(route('account.music.upload', $this->invitation->id), [
            'audio_file' => $file,
            'custom_title' => 'Ánh Nắng Của Anh (Bản Acoustic Riêng)',
            'custom_artist' => 'Đức Thịnh ft. Acoustic Band',
        ]);

    $response->assertRedirect(route('account.music.index', $this->invitation->id));
    $response->assertSessionHas('success');

    $this->invitation->refresh();
    $musicConfig = WeddingMusicService::resolveMusicConfig($this->invitation);

    expect($musicConfig['title'])->toBe('Ánh Nắng Của Anh (Bản Acoustic Riêng)');
    expect($musicConfig['artist'])->toBe('Đức Thịnh ft. Acoustic Band');
    expect($musicConfig['is_custom_upload'])->toBeTrue();
    expect($musicConfig['url'])->toContain('audio/wedding_audio_');
});

test('hệ thống chặn tải lên các tệp tin không hợp lệ hoặc không phải âm thanh', function () {
    $badFile = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

    $response = $this->actingAs($this->user)
        ->post(route('account.music.upload', $this->invitation->id), [
            'audio_file' => $badFile,
            'custom_title' => 'File Độc Hại',
        ]);

    $response->assertSessionHasErrors(['audio_file']);
});

test('component nút đĩa than nổi floating vinyl render chuẩn xác trên giao diện thiệp', function () {
    // Đảm bảo thiệp đã có cấu hình nhạc
    WeddingMusicService::updateMusicSettings($this->invitation, [
        'track_id' => 'golden_hour_harp',
        'volume' => 0.75,
        'enabled' => true,
        'floating_dock' => true,
    ]);

    $this->invitation->refresh();

    $view = $this->view('client.components.music-dock', [
        'invitation' => $this->invitation,
        'isEmbed' => false,
    ]);

    $rendered = (string) $view;
    expect($rendered)->toContain('mayhen_invitation_music_dock');
    expect($rendered)->toContain('mayhen_invitation_vinyl_disc');
    expect($rendered)->toContain('mayhen_invitation_sound_waves');
    expect($rendered)->toContain(e('Golden Hour (Harp & Ambient Strings)'));
});

test('service phân giải cấu hình fallback an toàn khi thiệp chưa có dữ liệu âm nhạc', function () {
    $emptyInvitation = new Invitation;
    $config = WeddingMusicService::resolveMusicConfig($emptyInvitation);

    expect($config['enabled'])->toBeTrue();
    expect($config['track_id'])->toBe('canon_in_d_acoustic');
    expect($config['volume'])->toBe(0.7);
    expect($config['autoplay'])->toBeTrue();
    expect($config['fade_in'])->toBeTrue();
});
