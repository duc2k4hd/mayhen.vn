<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\InvitationLifecycleService;
use Illuminate\Console\Command;

class CheckInvitationExpiryCommand extends Command
{
    /**
     * Tên và chữ ký dòng lệnh.
     *
     * @var string
     */
    protected $signature = 'invitation:check-expiry {--dry-run : Chạy mô phỏng không ghi log kiểm toán}';

    /**
     * Mô tả chức năng của lệnh.
     *
     * @var string
     */
    protected $description = 'Kiểm tra vòng đời hạn dùng thiệp cưới, cảnh báo sắp hết hạn và kích hoạt ân hạn (Mục 13.4 DOCX)';

    /**
     * Thực thi lệnh Artisan.
     */
    public function handle(InvitationLifecycleService $lifecycleService): int
    {
        $this->info('🔍 Đang bắt đầu quét vòng đời hạn dùng toàn bộ thiệp cưới trên hệ thống...');

        $isDryRun = (bool) $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('⚠️ Chế độ DRY-RUN đang bật. Không có dữ liệu nào bị thay đổi.');
        }

        $metrics = $lifecycleService->scanAndAuditLifecycle();

        $this->table(
            ['Phân Loại Vòng Đời', 'Số Lượng Thiệp', 'Quy Tắc Xử Lý Nghiệp Vụ'],
            [
                ['Sắp hết hạn (4 - 14 ngày)', $metrics['expiring_soon_count'], 'Gửi thông báo nhắc nhở chuẩn bị gia hạn'],
                ['Khẩn cấp (<= 3 ngày)', $metrics['critical_count'], 'Hiển thị cảnh báo màu đỏ trên Bảng điều khiển'],
                ['Trong thời gian ân hạn (Grace 7 ngày)', $metrics['grace_period_count'], 'Cho phép khách xem nhưng nhắc gia hạn khẩn'],
                ['Đã hết hạn hoàn toàn (> 7 ngày)', $metrics['expired_count'], 'Khóa truy cập công khai, bảo vệ an toàn media'],
            ]
        );

        if (! $isDryRun) {
            AuditLogger::log(
                'INVITATION_EXPIRY_SCAN',
                Invitation::class,
                'SYSTEM',
                "Quét tự động hạn dùng thiệp cưới: {$metrics['expiring_soon_count']} sắp hết hạn, {$metrics['critical_count']} khẩn cấp, {$metrics['grace_period_count']} ân hạn, {$metrics['expired_count']} hết hạn.",
                $metrics
            );

            $this->info('✓ Đã ghi nhận nhật ký kiểm toán hệ thống thành công.');
        }

        $this->info("✨ Hoàn tất quét vòng đời hạn dùng lúc {$metrics['processed_at']}.");

        return self::SUCCESS;
    }
}
