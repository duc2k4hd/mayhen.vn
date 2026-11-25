<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Services\Payments\SepayGatewayService;
use App\Services\PlanAccessService;
use Illuminate\Console\Command;

class SyncPlanExpirationsCommand extends Command
{
    protected $signature = 'mayhen:sync-plan-expirations
                            {--limit=500 : Số lượng tài khoản tối đa xử lý mỗi lượt chạy}
                            {--pending-timeout=30 : Số phút tối đa giữ phiên đơn hàng pending chưa có hạn}
                            {--skip-sepay-sync : Bỏ qua bước tự động đối soát bù SePay API cho đơn pending}
                            {--dry-run : Chạy mô phỏng kiểm tra số liệu mà không ghi thay đổi vào CSDL}';

    protected $description = 'Tự động vớt đơn pending bị lỡ webhook qua SePay API, đóng phiên pending quá hạn 30 phút và gỡ gói Pro/Plus hết hạn về Premium (bỏ qua Admin).';

    public function handle(PlanAccessService $planAccess, SepayGatewayService $sepayGateway): int
    {
        $limit = max(1, min(5000, (int) $this->option('limit')));
        $pendingTimeout = max(5, (int) $this->option('pending-timeout'));
        $dryRun = (bool) $this->option('dry-run');
        $skipSepaySync = (bool) $this->option('skip-sepay-sync');
        $now = now();

        // 1. Tự động đối soát bù SePay API cho các đơn pending đã chờ >= 2 phút (TRƯỚC KHI đóng quá hạn 30 phút)
        $reconciledOrdersCount = 0;
        $mismatchOrdersCount = 0;
        $sepayApiRequests = 0;
        $pendingCheckCandidates = Order::query()
            ->where('status', 'pending')
            ->where('created_at', '<=', $now->copy()->subMinutes(2))
            ->orderBy('created_at')
            ->limit(100)
            ->get();

        if (! $skipSepaySync && $pendingCheckCandidates->isNotEmpty() && $sepayGateway->canQuerySepayApi()) {
            if ($dryRun) {
                $this->line("[DRY-RUN] Sẽ đối soát bù SePay API cho {$pendingCheckCandidates->count()} đơn pending đang chờ.");
            } else {
                $batchResult = $sepayGateway->reconcilePendingOrdersBatch($pendingCheckCandidates);
                $reconciledOrdersCount = $batchResult['settled_count'];
                $mismatchOrdersCount = $batchResult['mismatch_count'];
                $sepayApiRequests = $batchResult['api_requests'];

                if (! $batchResult['ok'] && ! empty($batchResult['error'])) {
                    $this->warn('Đối soát bù SePay API tạm gián đoạn: '.$batchResult['error']);
                }
            }
        }

        // 2. Quét tất cả tài khoản khách (không phải Admin) đang mang gói plus/pro đã quá hạn hoặc thiếu hạn
        $candidates = User::query()
            ->whereIn('current_plan_slug', ['plus', 'pro'])
            ->whereNotIn('role', ['admin', 'super_admin', 'manager', 'staff'])
            ->where('email', '!=', 'guest@mayhen.vn')
            ->where(function ($q) use ($now) {
                $q->whereNull('plan_expires_at')
                    ->orWhere('plan_expires_at', '<=', $now);
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $downgradedCount = 0;
        $keptActiveCount = 0;

        if ($dryRun) {
            $stalePendingCount = Order::query()
                ->where('status', 'pending')
                ->where(function ($q) use ($now, $pendingTimeout) {
                    $q->where(function ($expQ) use ($now) {
                        $expQ->whereNotNull('checkout_expires_at')
                            ->where('checkout_expires_at', '<=', $now);
                    })->orWhere(function ($legacyQ) use ($now, $pendingTimeout) {
                        $legacyQ->whereNull('checkout_expires_at')
                            ->where('created_at', '<=', $now->copy()->subMinutes($pendingTimeout));
                    });
                })
                ->count();

            $this->info(
                "[DRY-RUN] Phát hiện {$candidates->count()} tài khoản hết hạn cần rà soát, "
                ."{$pendingCheckCandidates->count()} đơn pending chờ đối soát SePay và "
                ."{$stalePendingCount} đơn pending quá hạn cần đóng."
            );

            return self::SUCCESS;
        }

        foreach ($candidates as $user) {
            $result = $planAccess->syncExpiredUserPlanIfNeeded($user, recordAudit: true);
            if ($result['changed']) {
                $downgradedCount++;
            } else {
                $keptActiveCount++;
            }
        }

        // 3. Đóng các phiên đơn hàng pending đã quá hạn để giải phóng khóa cho khách tạo phiên mới
        $expiredOrdersCount = $planAccess->expireStalePendingOrders(null, $pendingTimeout);

        $this->info(
            'Hoàn tất đồng bộ vòng đời gói & phiên thanh toán: '
            ."vớt tự động {$reconciledOrdersCount} đơn lỡ webhook ({$sepayApiRequests} req SePay, {$mismatchOrdersCount} lệch tiền), "
            ."gỡ/hạ {$downgradedCount} tài khoản hết hạn, "
            ."giữ nguyên {$keptActiveCount} tài khoản còn đơn hợp lệ, "
            ."đóng {$expiredOrdersCount} đơn pending quá hạn."
        );

        return self::SUCCESS;
    }
}
