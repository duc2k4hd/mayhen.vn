<?php

namespace App\Console\Commands;

use App\Jobs\PurgeExpiredInvitationJob;
use App\Models\Invitation;
use Illuminate\Console\Command;

class QueueExpiredInvitationsPurgeCommand extends Command
{
    protected $signature = 'mayhen:purge-expired-invitations
                            {--limit=100 : Số lượng thiệp tối đa đưa vào hàng đợi mỗi đợt chạy}
                            {--sync : Thực thi trực tiếp đồng bộ thay vì đẩy vào hàng đợi bất đồng bộ}';

    protected $description = 'Đưa các thiệp cưới đã hết hạn (3 tháng / 6 tháng / 1 năm) hoặc khách đã xóa vào hàng đợi tự động dọn media vĩnh viễn và lưu hồ sơ trọn đời cho Admin.';

    public function handle(): int
    {
        $limit = max(1, min(1000, (int) $this->option('limit')));
        $runSync = (bool) $this->option('sync');
        $now = now();
        $staleQueueThreshold = $now->copy()->subHours(2);

        $candidates = Invitation::withTrashed()
            ->whereNull('purged_at')
            ->where(function ($q) use ($staleQueueThreshold) {
                $q->whereNull('purge_queued_at')
                    ->orWhere('purge_queued_at', '<', $staleQueueThreshold);
            })
            ->where(function ($q) use ($now) {
                $q->where(function ($expiredQ) use ($now) {
                    $expiredQ->whereNotNull('expires_at')
                        ->where('expires_at', '<=', $now);
                })->orWhereNotNull('deleted_by_user_at');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('Không có thiệp nào đến hạn dọn dẹp.');

            return self::SUCCESS;
        }

        $queuedCount = 0;

        foreach ($candidates as $invitation) {
            $reason = $invitation->deleted_by_user_at !== null ? 'user_deleted' : 'expired';

            $invitation->forceFill(['purge_queued_at' => $now])->saveQuietly();

            if ($runSync) {
                PurgeExpiredInvitationJob::dispatchSync($invitation->id, $reason);
            } else {
                PurgeExpiredInvitationJob::dispatch($invitation->id, $reason);
            }

            $queuedCount++;
        }

        $modeLabel = $runSync ? 'đã dọn dẹp đồng bộ' : 'đã đưa vào hàng đợi dọn dẹp';
        $this->info("Hoàn tất: {$modeLabel} {$queuedCount} thiệp hết hạn / đã xóa (lưu hồ sơ trọn đời cho Admin).");

        return self::SUCCESS;
    }
}
