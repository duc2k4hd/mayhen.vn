<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Plan;
use Carbon\Carbon;

class InvitationLifecycleService
{
    /**
     * Thời gian ân hạn sau khi thiệp cưới hết hạn (Grace Period: 7 ngày).
     */
    public const GRACE_PERIOD_DAYS = 7;

    /**
     * Trạng thái vòng đời hạn dùng.
     */
    public const STATUS_PERMANENT = 'permanent';       // Không giới hạn thời gian (hoặc thời hạn đặc biệt)

    public const STATUS_HEALTHY = 'healthy';           // Còn dồi dào (> 14 ngày)

    public const STATUS_EXPIRING_SOON = 'expiring_soon'; // Sắp hết hạn (<= 14 ngày)

    public const STATUS_CRITICAL = 'critical';         // Khẩn cấp sắp hết hạn (<= 3 ngày)

    public const STATUS_GRACE_PERIOD = 'grace_period'; // Trong thời gian ân hạn 7 ngày sau khi hết hạn

    public const STATUS_EXPIRED = 'expired';           // Đã hết hạn hoàn toàn (> 7 ngày sau ngày hết hạn)

    /**
     * Tính toán chi tiết trạng thái vòng đời hạn dùng của thiệp cưới.
     *
     * @return array{
     *     status: string,
     *     status_label: string,
     *     badge_class: string,
     *     days_remaining: ?int,
     *     expires_at: ?Carbon,
     *     expires_at_formatted: string,
     *     is_published: bool,
     *     is_accessible: bool,
     *     grace_days_remaining: int,
     *     renewal_options: array
     * }
     */
    public function getLifecycleStatus(Invitation $invitation): array
    {
        $now = now();
        $expiresAt = $invitation->expires_at;

        if ($expiresAt === null) {
            return [
                'status' => self::STATUS_PERMANENT,
                'status_label' => 'Không giới hạn thời gian',
                'badge_class' => 'mayhen_badge--success',
                'days_remaining' => null,
                'expires_at' => null,
                'expires_at_formatted' => 'Không thời hạn',
                'is_published' => (bool) $invitation->is_published,
                'is_accessible' => (bool) $invitation->is_published,
                'grace_days_remaining' => 0,
                'renewal_options' => $this->getRenewalPlans($invitation),
            ];
        }

        $diffInSeconds = $now->diffInSeconds($expiresAt, false);
        $isPast = $expiresAt->isPast();

        if (! $isPast) {
            // Thiệp còn hạn (làm tròn lên ngày để bảo đảm trải nghiệm người dùng)
            $daysRemaining = (int) ceil($diffInSeconds / 86400);

            if ($daysRemaining <= 3) {
                $status = self::STATUS_CRITICAL;
                $statusLabel = "Khẩn cấp: Chỉ còn {$daysRemaining} ngày";
                $badgeClass = 'mayhen_badge--danger';
            } elseif ($daysRemaining <= 14) {
                $status = self::STATUS_EXPIRING_SOON;
                $statusLabel = "Sắp hết hạn: Còn {$daysRemaining} ngày";
                $badgeClass = 'mayhen_badge--warning';
            } else {
                $status = self::STATUS_HEALTHY;
                $statusLabel = "Còn {$daysRemaining} ngày";
                $badgeClass = 'mayhen_badge--success';
            }

            return [
                'status' => $status,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'days_remaining' => $daysRemaining,
                'expires_at' => $expiresAt,
                'expires_at_formatted' => $expiresAt->format('d/m/Y'),
                'is_published' => (bool) $invitation->is_published,
                'is_accessible' => (bool) $invitation->is_published,
                'grace_days_remaining' => 0,
                'renewal_options' => $this->getRenewalPlans($invitation),
            ];
        }

        // Thiệp đã quá ngày expires_at
        $daysPastExpiry = (int) floor(abs($diffInSeconds) / 86400);

        if ($daysPastExpiry < self::GRACE_PERIOD_DAYS) {
            $graceLeft = self::GRACE_PERIOD_DAYS - $daysPastExpiry;

            return [
                'status' => self::STATUS_GRACE_PERIOD,
                'status_label' => "Ân hạn gia hạn (còn {$graceLeft} ngày)",
                'badge_class' => 'mayhen_badge--warning',
                'days_remaining' => -$daysPastExpiry,
                'expires_at' => $expiresAt,
                'expires_at_formatted' => $expiresAt->format('d/m/Y'),
                'is_published' => (bool) $invitation->is_published,
                'is_accessible' => (bool) $invitation->is_published, // Vẫn cho phép xem trong 7 ngày ân hạn
                'grace_days_remaining' => $graceLeft,
                'renewal_options' => $this->getRenewalPlans($invitation),
            ];
        }

        return [
            'status' => self::STATUS_EXPIRED,
            'status_label' => 'Đã hết hạn lưu trữ',
            'badge_class' => 'mayhen_badge--secondary',
            'days_remaining' => -$daysPastExpiry,
            'expires_at' => $expiresAt,
            'expires_at_formatted' => $expiresAt->format('d/m/Y'),
            'is_published' => false,
            'is_accessible' => false,
            'grace_days_remaining' => 0,
            'renewal_options' => $this->getRenewalPlans($invitation),
        ];
    }

    /**
     * Lấy danh sách các gói gia hạn & nâng cấp khả dụng cho thiệp cưới.
     */
    public function getRenewalPlans(Invitation $invitation): array
    {
        $plans = Plan::where('is_active', true)
            ->where('price', '>', 0)
            ->orderBy('price')
            ->get();

        $activePlan = EntitlementStateMachineService::getActivePlan($invitation);

        return $plans->map(function (Plan $plan) use ($activePlan) {
            $isUpgrade = $plan->price > $activePlan->price;
            $typeLabel = $isUpgrade ? 'Nâng Cấp Gói' : 'Gia Hạn Duy Trì';

            return [
                'plan_id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'price' => $plan->price,
                'formatted_price' => number_format($plan->price, 0, ',', '.').'đ',
                'duration_days' => $plan->duration_days,
                'duration_label' => $this->formatDurationLabel($plan->duration_days),
                'is_upgrade' => $isUpgrade,
                'type_label' => $typeLabel,
            ];
        })->toArray();
    }

    /**
     * Gia hạn thiệp cưới trực tiếp (theo số ngày hoặc theo gói cước).
     */
    public function extendDuration(Invitation $invitation, int $days, string $reason = 'Tự động gia hạn / Thanh toán'): Invitation
    {
        $currentExpiresAt = $invitation->expires_at;
        $baseDate = ($currentExpiresAt && $currentExpiresAt->isFuture()) ? $currentExpiresAt : now();
        $newExpiresAt = $baseDate->copy()->addDays($days);

        $oldDateStr = $currentExpiresAt ? $currentExpiresAt->format('d/m/Y') : 'Không thời hạn';
        $newDateStr = $newExpiresAt->format('d/m/Y');

        $invitation->update([
            'expires_at' => $newExpiresAt,
            'is_published' => true,
        ]);

        // Ghi log kiểm toán
        AuditLogger::log(
            'INVITATION_RENEWED',
            Invitation::class,
            $invitation->id,
            "Gia hạn thiệp cưới #{$invitation->slug} thêm {$days} ngày ({$oldDateStr} -> {$newDateStr}). Lý do: {$reason}",
            [
                'user_id' => auth()->id() ?? $invitation->wedding?->user_id,
                'old_expires_at' => $currentExpiresAt?->toIso8601String(),
                'new_expires_at' => $newExpiresAt->toIso8601String(),
                'added_days' => $days,
            ]
        );

        return $invitation;
    }

    /**
     * Quét và phân loại thiệp cưới theo trạng thái vòng đời.
     *
     * @return array{
     *     expiring_soon_count: int,
     *     critical_count: int,
     *     grace_period_count: int,
     *     expired_count: int,
     *     processed_at: string
     * }
     */
    public function scanAndAuditLifecycle(): array
    {
        $now = now();

        // 1. Sắp hết hạn (còn từ 4 đến 14 ngày)
        $expiringSoon = Invitation::whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now->copy()->addDays(4), $now->copy()->addDays(14)])
            ->count();

        // 2. Khẩn cấp sắp hết hạn (còn <= 3 ngày và chưa qua ngày hết hạn)
        $critical = Invitation::whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now, $now->copy()->addDays(3)])
            ->count();

        // 3. Trong thời gian ân hạn Grace Period (quá hạn trong vòng 7 ngày)
        $gracePeriod = Invitation::whereNotNull('expires_at')
            ->whereBetween('expires_at', [$now->copy()->subDays(self::GRACE_PERIOD_DAYS), $now])
            ->count();

        // 4. Đã quá hạn hơn 7 ngày
        $expired = Invitation::whereNotNull('expires_at')
            ->where('expires_at', '<', $now->copy()->subDays(self::GRACE_PERIOD_DAYS))
            ->count();

        return [
            'expiring_soon_count' => $expiringSoon,
            'critical_count' => $critical,
            'grace_period_count' => $gracePeriod,
            'expired_count' => $expired,
            'processed_at' => $now->toIso8601String(),
        ];
    }

    protected function formatDurationLabel(int $days): string
    {
        if ($days >= 730) {
            return '2 Năm (730 ngày)';
        }
        if ($days >= 365) {
            return '1 Năm (365 ngày)';
        }
        if ($days >= 180) {
            return '6 Tháng (180 ngày)';
        }
        if ($days >= 30) {
            return '1 Tháng (30 ngày)';
        }

        return "{$days} ngày";
    }
}
