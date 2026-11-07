<?php

use App\Mail\TimeCapsuleAnniversaryMail;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingTimeCapsule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['email' => 'couple.anniversary@example.com']);

    $this->template = InvitationTemplate::create([
        'name' => 'Mẫu Thử Nghiệm Capsule',
        'slug' => 'mau-capsule-'.uniqid(),
        'description' => 'Test Template Capsule',
        'thumbnail_url' => 'thumb.jpg',
        'view_path' => 'client.invitations.tron-kiep-nay-ben-nhau.template',
        'category' => 'modern',
        'is_active' => true,
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Gia Huy',
        'bride_name' => 'Thanh Thủy',
        'wedding_date' => now()->subYear(),
        'venue_name' => 'Riverside Palace',
        'cover_photo_url' => '/assets/client/images/cover.jpg',
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'gia-huy-thanh-thuy-'.uniqid(),
        'is_published' => true,
    ]);
});

test('command sends anniversary notification email for due time capsules', function () {
    Mail::fake();

    // 1 capsule đến hạn hôm nay
    $dueCapsule = WeddingTimeCapsule::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'sender_type' => 'couple',
        'sender_name' => 'Gia Huy',
        'recipient_name' => 'Thanh Thủy',
        'title' => 'Tâm thư kỷ niệm 1 năm',
        'content' => 'Gửi người bạn đời yêu quý...',
        'milestone_years' => 1,
        'unlock_date' => now()->subDay(), // Đã đến hạn
        'is_sealed' => true,
        'seal_color' => 'ruby',
    ]);

    // 1 capsule chưa đến hạn
    $futureCapsule = WeddingTimeCapsule::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'sender_type' => 'couple',
        'sender_name' => 'Thanh Thủy',
        'recipient_name' => 'Gia Huy',
        'title' => 'Tâm thư kỷ niệm 3 năm',
        'content' => 'Hẹn gặp anh ở 3 năm nữa...',
        'milestone_years' => 3,
        'unlock_date' => now()->addYears(2), // Chưa đến hạn
        'is_sealed' => true,
        'seal_color' => 'gold',
    ]);

    // 1 capsule đã được gửi thông báo trước đó
    $alreadyNotifiedCapsule = WeddingTimeCapsule::create([
        'wedding_id' => $this->wedding->id,
        'invitation_id' => $this->invitation->id,
        'sender_type' => 'guest',
        'sender_name' => 'Bạn Thân',
        'recipient_name' => 'Hai Bạn',
        'title' => 'Lời chúc bí mật',
        'content' => '...',
        'milestone_years' => 1,
        'unlock_date' => now()->subDay(),
        'is_sealed' => true,
        'seal_color' => 'emerald',
        'notified_at' => now()->subDay(), // Đã gửi rồi
    ]);

    // Chạy command
    $this->artisan('mayhen:check-time-capsules')
        ->expectsOutputToContain('Bắt đầu quét Hộp Thời Gian đến hạn mở khóa ngày hôm nay')
        ->expectsOutputToContain('Hoàn tất! Đã gửi thành công 1 email')
        ->assertSuccessful();

    // Mail gửi đúng người và đúng nội dung
    Mail::assertSent(TimeCapsuleAnniversaryMail::class, function ($mail) {
        return $mail->hasTo('couple.anniversary@example.com')
            && $mail->capsule->title === 'Tâm thư kỷ niệm 1 năm';
    });

    // Chỉ gửi duy nhất 1 email cho due capsule
    Mail::assertSent(TimeCapsuleAnniversaryMail::class, 1);

    // Kiểm tra đã đánh dấu notified_at
    $dueCapsule->refresh();
    expect($dueCapsule->notified_at)->not->toBeNull();

    // Chạy lại lần 2 không gửi thêm email nào
    $this->artisan('mayhen:check-time-capsules')
        ->expectsOutputToContain('Không có Hộp Thời Gian nào cần thông báo hôm nay')
        ->assertSuccessful();

    Mail::assertSent(TimeCapsuleAnniversaryMail::class, 1);
});
