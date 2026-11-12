<?php

namespace App\Console\Commands;

use App\Services\WeddingAnniversaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DispatchAnniversaryRemindersCommand extends Command
{
    /**
     * Tên và chữ ký dòng lệnh Artisan.
     *
     * @var string
     */
    protected $signature = 'mayhen:dispatch-anniversary-reminders {--date= : Ngày mục tiêu định dạng Y-m-d (mặc định là hôm nay)}';

    /**
     * Mô tả lệnh Artisan.
     *
     * @var string
     */
    protected $description = 'Quét và gửi email chúc mừng Kỷ Niệm 1 Năm / Hàng Năm Ngày Cưới tới các cặp đôi (IDEA-D07)';

    /**
     * Thực thi lệnh Artisan.
     */
    public function handle(): int
    {
        $this->info('⚜️ Bắt đầu quét các đám cưới có kỷ niệm ngày cưới hôm nay...');

        $targetDate = null;
        if ($dateOpt = $this->option('date')) {
            try {
                $targetDate = Carbon::parse($dateOpt);
                $this->line("• Ngày mục tiêu: {$targetDate->format('d/m/Y')}");
            } catch (\Exception $e) {
                $this->error('Định dạng ngày không hợp lệ. Vui lòng dùng Y-m-d.');

                return self::FAILURE;
            }
        }

        $result = WeddingAnniversaryService::dispatchAnniversaryReminders($targetDate);

        $this->info("✓ Hoàn tất! Tìm thấy {$result['total_eligible']} đám cưới đủ điều kiện.");
        $this->info("✓ Đã gửi email chúc mừng tới {$result['total_dispatched']} cặp đôi.");

        return self::SUCCESS;
    }
}
