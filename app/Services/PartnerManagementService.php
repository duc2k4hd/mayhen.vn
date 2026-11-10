<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\PartnerCommission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PartnerManagementService
{
    /**
     * Thống kê KPI tổng quan mạng lưới đối tác Studio & Wedding Planner.
     */
    public function getPartnerNetworkMetrics(): array
    {
        $totalPartners = Partner::count();
        $activePartners = Partner::where('status', 'active')->count();
        $pendingPartners = Partner::where('status', 'pending')->count();
        $suspendedPartners = Partner::where('status', 'suspended')->count();

        $totalWeddings = DB::table('weddings')->whereNotNull('partner_id')->count();

        $totalCommissionsAccrued = (float) PartnerCommission::where('type', 'commission')
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        $totalCommissionsPaid = (float) PartnerCommission::where('type', 'payout')
            ->orWhere('status', 'paid')
            ->sum('amount');

        $totalWalletLiability = (float) Partner::sum('wallet_balance');

        return [
            'total_partners' => $totalPartners,
            'active_partners' => $activePartners,
            'pending_partners' => $pendingPartners,
            'suspended_partners' => $suspendedPartners,
            'total_weddings' => $totalWeddings,
            'total_commissions_accrued' => $totalCommissionsAccrued,
            'total_commissions_paid' => $totalCommissionsPaid,
            'total_wallet_liability' => $totalWalletLiability,
        ];
    }

    /**
     * Lọc và phân trang danh sách đối tác.
     */
    public function getPartnersList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Partner::with(['user'])->withCount(['weddings', 'commissions']);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('partner_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['tier']) && $filters['tier'] !== 'all') {
            $query->where('tier', $filters['tier']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Phê duyệt hồ sơ đối tác Studio.
     */
    public function approvePartner(Partner $partner, int $adminId): Partner
    {
        return DB::transaction(function () use ($partner, $adminId) {
            $partner->update([
                'status' => 'active',
            ]);

            if ($partner->user && $partner->user->role !== 'admin') {
                $partner->user->update(['role' => 'partner']);
            }

            AuditLogger::log(
                'PARTNER_APPROVED',
                'partner',
                $partner->id,
                "Phê duyệt đối tác Studio {$partner->business_name}",
                [
                    'admin_id' => $adminId,
                    'business_name' => $partner->business_name,
                    'partner_code' => $partner->partner_code,
                ]
            );

            return $partner->fresh();
        });
    }

    /**
     * Đình chỉ hoặc tạm khóa đối tác Studio.
     */
    public function suspendPartner(Partner $partner, string $reason, int $adminId): Partner
    {
        return DB::transaction(function () use ($partner, $reason, $adminId) {
            $customConfig = $partner->custom_config ?? [];
            $customConfig['suspension_reason'] = $reason;
            $customConfig['suspended_at'] = now()->toIso8601String();
            $customConfig['suspended_by'] = $adminId;

            $partner->update([
                'status' => 'suspended',
                'custom_config' => $customConfig,
            ]);

            AuditLogger::log(
                'PARTNER_SUSPENDED',
                'partner',
                $partner->id,
                "Đình chỉ đối tác Studio {$partner->business_name}: {$reason}",
                [
                    'admin_id' => $adminId,
                    'reason' => $reason,
                ]
            );

            return $partner->fresh();
        });
    }

    /**
     * Cập nhật chính sách chiết khấu & hạng đối tác.
     */
    public function updateCommissionRate(Partner $partner, float $rate, string $tier, int $adminId): Partner
    {
        if ($rate < 0 || $rate > 100) {
            throw new InvalidArgumentException('Tỷ lệ hoa hồng phải nằm trong khoảng từ 0% đến 100%.');
        }

        $validTiers = ['silver', 'gold', 'platinum'];
        if (! in_array($tier, $validTiers, true)) {
            throw new InvalidArgumentException('Hạng đối tác không hợp lệ. Chỉ chấp nhận silver, gold, platinum.');
        }

        $oldRate = $partner->commission_rate;
        $oldTier = $partner->tier;

        $partner->update([
            'commission_rate' => $rate,
            'tier' => $tier,
        ]);

        AuditLogger::log(
            'PARTNER_RATE_UPDATED',
            'partner',
            $partner->id,
            "Cập nhật chính sách hoa hồng cho {$partner->business_name}: {$rate}% (Hạng {$tier})",
            [
                'admin_id' => $adminId,
                'old_rate' => $oldRate,
                'new_rate' => $rate,
                'old_tier' => $oldTier,
                'new_tier' => $tier,
            ]
        );

        return $partner->fresh();
    }

    /**
     * Quyết toán và chi trả hoa hồng đối tác có chứng từ ngân hàng bắt buộc (Chuẩn P0).
     */
    public function settleCommission(
        PartnerCommission $commission,
        string $bankReferenceCode,
        string $reconciliationNote,
        int $adminId
    ): PartnerCommission {
        $bankRef = trim($bankReferenceCode);
        if (empty($bankRef) || strlen($bankRef) < 4) {
            throw new InvalidArgumentException('Mã tham chiếu ngân hàng (Bank Reference Code) bắt buộc phải có thật và tối thiểu 4 ký tự.');
        }

        return DB::transaction(function () use ($commission, $bankRef, $reconciliationNote, $adminId) {
            /** @var PartnerCommission $lockedCommission */
            $lockedCommission = PartnerCommission::where('id', $commission->id)->lockForUpdate()->firstOrFail();

            if ($lockedCommission->status === 'paid') {
                throw new InvalidArgumentException('Giao dịch hoa hồng này đã được quyết toán và chi trả trước đó.');
            }

            $lockedPartner = Partner::where('id', $lockedCommission->partner_id)->lockForUpdate()->firstOrFail();

            // Nếu là yêu cầu chi trả (payout), khấu trừ ví an toàn
            if ($lockedCommission->type === 'payout' && $lockedCommission->status !== 'paid') {
                $payoutAmount = (float) $lockedCommission->amount;
                if ($lockedPartner->wallet_balance < $payoutAmount) {
                    throw new InvalidArgumentException('Số dư ví của đối tác không đủ để thực hiện lệnh chi trả này.');
                }
                $lockedPartner->decrement('wallet_balance', $payoutAmount);
            }

            $lockedCommission->update([
                'status' => 'paid',
                'payout_reference_code' => $bankRef,
                'reconciliation_note' => $reconciliationNote,
                'reconciled_at' => now(),
                'reconciled_by' => $adminId,
            ]);

            AuditLogger::log(
                'PARTNER_COMMISSION_SETTLED',
                'partner_commission',
                $lockedCommission->id,
                "Quyết toán hoa hồng #{$lockedCommission->id} với mã ngân hàng {$bankRef}",
                [
                    'admin_id' => $adminId,
                    'commission_id' => $lockedCommission->id,
                    'partner_id' => $lockedPartner->id,
                    'amount' => $lockedCommission->amount,
                    'bank_reference_code' => $bankRef,
                    'reconciliation_note' => $reconciliationNote,
                ]
            );

            return $lockedCommission->fresh();
        });
    }

    /**
     * Xuất dữ liệu đối soát hoa hồng mạng lưới.
     */
    public function getCommissionsForExport(array $filters = []): array
    {
        $query = PartnerCommission::with(['partner', 'order', 'wedding', 'reconciledBy'])->latest();

        if (! empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        return $query->get()->map(function (PartnerCommission $c) {
            return [
                'id' => $c->id,
                'partner_code' => $c->partner ? $c->partner->partner_code : 'N/A',
                'business_name' => $c->partner ? $c->partner->business_name : 'N/A',
                'amount' => $c->amount,
                'type' => $c->type,
                'status' => $c->status,
                'description' => $c->description,
                'order_code' => $c->order ? $c->order->order_code : 'N/A',
                'payout_reference_code' => $c->payout_reference_code ?? 'Chưa đối soát',
                'reconciled_at' => $c->reconciled_at ? $c->reconciled_at->format('Y-m-d H:i:s') : 'N/A',
                'reconciled_by' => $c->reconciledBy ? $c->reconciledBy->name : 'N/A',
                'created_at' => $c->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }
}
