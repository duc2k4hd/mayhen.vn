<?php

namespace App\Console\Commands;

use App\Mail\TimeCapsuleAnniversaryMail;
use App\Models\WeddingTimeCapsule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckTimeCapsuleAnniversaries extends Command
{
    /**
     * Tên và cú pháp của lệnh console.
     *
     * @var string
     */
    protected $signature = 'mayhen:check-time-capsules';

    /**
     * Mô tả của lệnh console.
     *
     * @var string
     */
    protected $description = 'Quét và gửi email thông báo mở niêm phong Hộp Thời Gian đến hạn cho các cặp đôi';

    /**
     * Thực thi lệnh console.
     */
    public function handle(): int
    {
        $today = Carbon::now()->toDateString();
        $this->info("Bắt đầu quét Hộp Thời Gian đến hạn mở khóa ngày hôm nay: {$today}...");

        $capsules = WeddingTimeCapsule::query()
            ->whereDate('unlock_date', '<=', $today)
            ->whereNull('notified_at')
            ->with(['wedding.user', 'invitation'])
            ->get();

        if ($capsules->isEmpty()) {
            $this->info('Không có Hộp Thời Gian nào cần thông báo hôm nay.');

            return self::SUCCESS;
        }

        $sentCount = 0;
        foreach ($capsules as $capsule) {
            // Xác định email gửi tới: ưu tiên email tài khoản cặp đôi, fallback sang sender_email
            $recipientEmail = $capsule->wedding?->user?->email ?: $capsule->sender_email;

            if (! $recipientEmail || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Bỏ qua tâm thư #{$capsule->id}: Không tìm thấy địa chỉ email hợp lệ.");
                $capsule->update(['notified_at' => now()]);

                continue;
            }

            try {
                Mail::to($recipientEmail)->send(new TimeCapsuleAnniversaryMail($capsule));
                $capsule->update(['notified_at' => now()]);
                $sentCount++;
                $this->line("✓ Đã gửi email kỷ niệm tới {$recipientEmail} cho tâm thư #{$capsule->id} ('{$capsule->title}')");
            } catch (\Throwable $e) {
                $this->error("Lỗi khi gửi email cho tâm thư #{$capsule->id}: {$e->getMessage()}");
            }
        }

        $this->info("Hoàn tất! Đã gửi thành công {$sentCount} email thông báo Hộp Thời Gian.");

        return self::SUCCESS;
    }
}

