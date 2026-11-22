<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;

class PlanAccessService
{
    /**
     * Bậc phân quyền gói cước chuẩn hóa của Mây Hẹn.
     *
     * @var array<string, int>
     */
    public const TIER_RANK = [
        'free' => 0,
        'premium' => 1,
        'plus' => 2,
        'pro' => 3,
    ];

    /**
     * Các khóa hệ thống nhạy cảm trong custom_config tuyệt đối không cho client tự ghi đè.
     *
     * @var array<int, string>
     */
    public const PROTECTED_CUSTOM_CONFIG_KEYS = [
        'is_locked',
        'lock_reason',
        'locked_at',
        'entitlement_overrides',
        'entitlement_override_meta',
        'admin_notes',
        'presentation_token',
        'first_presented_at',
        'created_mail_sent_at',
    ];

    /**
     * Chuẩn hóa mã tier về một trong 4 mức chuẩn (free, premium, plus, pro).
     */
    public function normalizeTier(?string $tier): string
    {
        $normalized = strtolower(trim((string) $tier));

        return array_key_exists($normalized, self::TIER_RANK) ? $normalized : 'free';
    }

    /**
     * Lấy nhãn hiển thị tiếng Việt của gói cước / hạng mẫu.
     */
    public function getTierLabel(string $tier): string
    {
        return match ($this->normalizeTier($tier)) {
            'free' => 'Gói Miễn Phí',
            'premium' => 'Gói Thành Viên (Premium)',
            'plus' => 'Gói Plus (99.000đ)',
            'pro' => 'Gói Pro VIP (199.000đ)',
            default => 'Gói Miễn Phí',
        };
    }

    /**
     * Xác định bậc gói cao nhất đang có hiệu lực của người dùng (và/hoặc trên một thiệp cụ thể).
     */
    public function getEffectivePlanSlug(?User $user, ?Invitation $invitation = null): string
    {
        if ($user && $user->isAdmin()) {
            return 'pro';
        }

        $bestSlug = $user ? 'premium' : 'free';
        $bestRank = self::TIER_RANK[$bestSlug];

        // 1. Kiểm tra đơn hàng đã thanh toán gắn trực tiếp trên thiệp (nếu có)
        if ($invitation) {
            $state = EntitlementStateMachineService::getSubscriptionState($invitation);
            if ($state !== EntitlementStateMachineService::STATE_REFUNDED && $state !== EntitlementStateMachineService::STATE_EXPIRED) {
                $invitationPaidPlans = $invitation->orders()
                    ->where('status', 'paid')
                    ->with('plan')
                    ->get()
                    ->pluck('plan.slug')
                    ->filter();

                foreach ($invitationPaidPlans as $planSlug) {
                    $normalized = $this->normalizeTier((string) $planSlug);
                    if (self::TIER_RANK[$normalized] > $bestRank) {
                        $bestSlug = $normalized;
                        $bestRank = self::TIER_RANK[$normalized];
                    }
                }
            }
        }

        // 2. Kiểm tra đơn hàng đã thanh toán ở cấp tài khoản người dùng (hỗ trợ mua gói Plus/Pro trước khi tạo thiệp)
        if ($user && $bestRank < self::TIER_RANK['pro']) {
            $userPaidPlans = Order::query()
                ->where('user_id', $user->id)
                ->where('status', 'paid')
                ->with('plan')
                ->get()
                ->pluck('plan.slug')
                ->filter();

            foreach ($userPaidPlans as $planSlug) {
                $normalized = $this->normalizeTier((string) $planSlug);
                if (self::TIER_RANK[$normalized] > $bestRank) {
                    $bestSlug = $normalized;
                    $bestRank = self::TIER_RANK[$normalized];
                }
            }
        }

        return $bestSlug;
    }

    /**
     * Kiểm tra người dùng có đạt tối thiểu bậc gói yêu cầu hay không.
     */
    public function hasPlanAtLeast(?User $user, string $requiredTier, ?Invitation $invitation = null): bool
    {
        $required = $this->normalizeTier($requiredTier);
        $effective = $this->getEffectivePlanSlug($user, $invitation);

        return self::TIER_RANK[$effective] >= self::TIER_RANK[$required];
    }

    /**
     * Chức năng Tạo Thiệp Tự Động AI Curated yêu cầu đăng nhập và gói Plus trở lên (Admin miễn trừ).
     */
    public function canAccessCuratedStudio(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->hasPlanAtLeast($user, 'plus');
    }

    /**
     * Đặc quyền Xuất bản Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội (/thiep-su-kien -> /loi-moi/{slug})
     * yêu cầu người dùng đã đăng nhập và sở hữu Gói Pro VIP (199.000đ) trở lên (Admin miễn trừ).
     * Khách chưa có Gói Pro vẫn được xem thử, chỉnh sửa thử (Sandbox) và lưu nháp.
     */
    public function canPublishEventInvitation(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->hasPlanAtLeast($user, 'pro');
    }

    /**
     * Kiểm tra người dùng có đủ quyền sử dụng chính thức một hạng mẫu thiệp (free/premium/plus/pro) hay không.
     */
    public function canUseTemplateTier(?User $user, ?string $templateTier, ?Invitation $invitation = null): bool
    {
        $required = $this->normalizeTier($templateTier);

        if ($required === 'free') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $this->hasPlanAtLeast($user, $required, $invitation);
    }

    /**
     * Danh sách các hạng mẫu được phép sử dụng theo gói hiện tại của người dùng.
     *
     * @return array<int, string>
     */
    public function getAllowedTemplateTiers(?User $user, ?Invitation $invitation = null): array
    {
        $effectiveRank = self::TIER_RANK[$this->getEffectivePlanSlug($user, $invitation)];

        $allowed = [];
        foreach (self::TIER_RANK as $tier => $rank) {
            if ($rank <= $effectiveRank) {
                $allowed[] = $tier;
            }
        }

        return $allowed;
    }

    /**
     * Kiểm tra thiệp cưới có đủ điều kiện xuất bản công khai (/thiep/{slug}) cho khách ngoài xem hay không.
     * - Mẫu free & premium: luôn được phép xuất bản.
     * - Mẫu plus & pro: yêu cầu chủ sở hữu hoặc thiệp đã có gói thanh toán tương ứng (hoặc Admin).
     */
    public function canPublishInvitation(Invitation $invitation, ?User $actingUser = null): bool
    {
        $templateTier = $this->normalizeTier($invitation->template?->tier ?? 'free');

        if (in_array($templateTier, ['free', 'premium'], true)) {
            return true;
        }

        if ($actingUser && $actingUser->isAdmin()) {
            return true;
        }

        $owner = $actingUser ?? $invitation->wedding?->user;

        return $this->hasPlanAtLeast($owner, $templateTier, $invitation);
    }

    /**
     * Loại bỏ toàn bộ khóa quản trị/hệ thống nhạy cảm khỏi payload custom_config gửi từ client
     * và bảo toàn giá trị gốc phía server (chống Mass-Assignment Privilege Escalation).
     *
     * @param  array<string, mixed>  $incoming
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function sanitizeClientCustomConfig(array $incoming, array $existing = []): array
    {
        foreach (self::PROTECTED_CUSTOM_CONFIG_KEYS as $protectedKey) {
            unset($incoming[$protectedKey]);
        }

        foreach (self::PROTECTED_CUSTOM_CONFIG_KEYS as $protectedKey) {
            if (array_key_exists($protectedKey, $existing)) {
                $incoming[$protectedKey] = $existing[$protectedKey];
            }
        }

        return $incoming;
    }
}
