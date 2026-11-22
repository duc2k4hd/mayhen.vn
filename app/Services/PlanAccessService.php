<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

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
     * Quy chuẩn bất biến cho 4 gói dịch vụ (Single Source of Truth):
     * - Free (khách vãng lai): tối đa 1 thiệp, link tồn tại 3 tháng (90 ngày)
     * - Premium (mặc định khi đăng ký/đăng nhập): tối đa 3 thiệp, link tồn tại 3 tháng (90 ngày)
     * - Plus (trả phí): đăng ký & gia hạn 6 tháng/lần, tối đa 10 thiệp, mỗi thiệp tồn tại đủ 6 tháng từ lúc tạo
     * - Pro (trả phí): đăng ký & gia hạn 1 năm/lần, tối đa 30 thiệp, mỗi thiệp tồn tại đủ 1 năm từ lúc tạo
     *
     * @var array<string, array{max_invitations: int, duration_months: int, duration_days: int, is_paid: bool}>
     */
    public const PLAN_RULES = [
        'free' => [
            'max_invitations' => 1,
            'duration_months' => 3,
            'duration_days' => 90,
            'is_paid' => false,
        ],
        'premium' => [
            'max_invitations' => 3,
            'duration_months' => 3,
            'duration_days' => 90,
            'is_paid' => false,
        ],
        'plus' => [
            'max_invitations' => 10,
            'duration_months' => 6,
            'duration_days' => 180,
            'is_paid' => true,
        ],
        'pro' => [
            'max_invitations' => 30,
            'duration_months' => 12,
            'duration_days' => 365,
            'is_paid' => true,
        ],
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
        '_archived_for_admin',
        'archive_reason',
        'purged_at',
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
     * Lấy cấu hình quy chuẩn của một gói dịch vụ.
     *
     * @return array{max_invitations: int, duration_months: int, duration_days: int, is_paid: bool}
     */
    public function getPlanRule(string $tier): array
    {
        $normalized = $this->normalizeTier($tier);

        return self::PLAN_RULES[$normalized];
    }

    /**
     * Kiểm tra tài khoản có phải khách vãng lai (chưa đăng nhập hoặc guest@mayhen.vn) hay không.
     */
    public function isGuestUser(?User $user): bool
    {
        return $user === null || strtolower((string) $user->email) === 'guest@mayhen.vn';
    }

    /**
     * Tính mốc hết hạn của 1 link thiệp khi tạo mới theo đúng thời hạn trọn vẹn của gói:
     * - Free / Premium: +3 tháng
     * - Plus: +6 tháng (kể cả khi gói Plus của tài khoản sắp hết hạn, thiệp vẫn tồn tại đủ 6 tháng)
     * - Pro: +1 năm (kể cả khi gói Pro của tài khoản sắp hết hạn, thiệp vẫn tồn tại đủ 1 năm)
     */
    public function calculateInvitationExpiresAt(string $planSlug, ?Carbon $from = null): Carbon
    {
        $rule = $this->getPlanRule($planSlug);
        $base = $from ? $from->copy() : now();

        return $base->addMonths($rule['duration_months']);
    }

    /**
     * Lấy nhãn hiển thị tiếng Việt của gói cước / hạng mẫu.
     */
    public function getTierLabel(string $tier): string
    {
        return match ($this->normalizeTier($tier)) {
            'free' => 'Gói Miễn Phí (Free)',
            'premium' => 'Gói Thành Viên (Premium)',
            'plus' => 'Gói Plus (6 Tháng)',
            'pro' => 'Gói Pro VIP (1 Năm)',
            default => 'Gói Miễn Phí (Free)',
        };
    }

    /**
     * Xác định thời điểm hết hạn hiệu lực của một đơn hàng đã thanh toán.
     */
    public function getOrderEffectiveExpiry(Order $order): ?Carbon
    {
        if ($order->expires_at !== null) {
            return $order->expires_at->copy();
        }

        $planSlug = $this->normalizeTier($order->plan?->slug ?? 'free');
        $rule = $this->getPlanRule($planSlug);
        $months = $order->period_months ?: $rule['duration_months'];
        $anchor = $order->starts_at ?? $order->paid_at ?? $order->created_at;

        if (! $anchor) {
            return null;
        }

        return $anchor->copy()->addMonths($months);
    }

    /**
     * Lấy danh sách các đơn hàng trả phí (Plus/Pro) đang còn hiệu lực của người dùng.
     *
     * @return Collection<int, Order>
     */
    public function getActivePaidOrders(User $user, ?string $planSlug = null): Collection
    {
        if ($this->isGuestUser($user)) {
            return new Collection;
        }

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->with('plan')
            ->orderBy('id')
            ->get();

        return $orders->filter(function (Order $order) use ($planSlug): bool {
            $slug = $this->normalizeTier($order->plan?->slug ?? 'free');
            if (! in_array($slug, ['plus', 'pro'], true)) {
                return false;
            }
            if ($planSlug !== null && $slug !== $this->normalizeTier($planSlug)) {
                return false;
            }

            $expiry = $this->getOrderEffectiveExpiry($order);

            return $expiry !== null && $expiry->isFuture();
        })->values();
    }

    /**
     * Xác định gói đang còn hiệu lực ở CẤP TÀI KHOẢN (dùng để xét quyền tạo thiệp mới & gia hạn).
     * - Chưa đăng nhập / guest@mayhen.vn -> free
     * - Đã đăng nhập mặc định -> premium
     * - Có gói Plus (6 tháng) hoặc Pro (1 năm) đang còn hạn -> plus / pro
     * - Khi Plus / Pro hết hạn -> tự động quay về premium (không ảnh hưởng các thiệp cũ còn hạn).
     */
    public function getAccountActivePlanSlug(?User $user): string
    {
        if ($user && $user->isAdmin()) {
            return 'pro';
        }

        if ($this->isGuestUser($user)) {
            return 'free';
        }

        $bestSlug = 'premium';
        $bestRank = self::TIER_RANK[$bestSlug];

        // 1. Kiểm tra trạng thái gói lưu trực tiếp trên tài khoản User
        $userPlanSlug = $this->normalizeTier($user->current_plan_slug ?? 'premium');
        if (in_array($userPlanSlug, ['plus', 'pro'], true)) {
            if ($user->plan_expires_at !== null && $user->plan_expires_at->isFuture()) {
                $bestSlug = $userPlanSlug;
                $bestRank = self::TIER_RANK[$userPlanSlug];
            }
        }

        // 2. Đối chiếu với các đơn hàng đã thanh toán còn hiệu lực (tự động hỗ trợ cả đơn hàng đối soát / test)
        foreach ($this->getActivePaidOrders($user) as $order) {
            $slug = $this->normalizeTier($order->plan?->slug ?? 'free');
            if (self::TIER_RANK[$slug] > $bestRank) {
                $bestSlug = $slug;
                $bestRank = self::TIER_RANK[$slug];
            }
        }

        return $bestSlug;
    }

    /**
     * Lấy mốc hết hạn của gói trả phí đang hoạt động trên tài khoản (null nếu đang ở Free/Premium).
     */
    public function getAccountPlanExpiresAt(?User $user): ?Carbon
    {
        if (! $user || $user->isAdmin() || $this->isGuestUser($user)) {
            return null;
        }

        $accountPlan = $this->getAccountActivePlanSlug($user);
        if (! in_array($accountPlan, ['plus', 'pro'], true)) {
            return null;
        }

        $candidates = [];
        if ($user->current_plan_slug === $accountPlan && $user->plan_expires_at?->isFuture()) {
            $candidates[] = $user->plan_expires_at->copy();
        }

        foreach ($this->getActivePaidOrders($user, $accountPlan) as $order) {
            $expiry = $this->getOrderEffectiveExpiry($order);
            if ($expiry && $expiry->isFuture()) {
                $candidates[] = $expiry;
            }
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, fn (Carbon $a, Carbon $b) => $b->getTimestamp() <=> $a->getTimestamp());

        return $candidates[0];
    }

    /**
     * Xác định bậc gói cao nhất đang có hiệu lực của người dùng (và/hoặc trên một thiệp cụ thể).
     * Lưu ý quan trọng: Nếu một thiệp được tạo trong lúc tài khoản còn gói Plus/Pro, thiệp đó
     * vẫn giữ quyền lợi và tồn tại đúng đủ 6 tháng / 1 năm của gói ngay cả khi gói tài khoản đã hết hạn.
     */
    public function getEffectivePlanSlug(?User $user, ?Invitation $invitation = null): string
    {
        if ($user && $user->isAdmin()) {
            return 'pro';
        }

        $bestSlug = $this->getAccountActivePlanSlug($user);
        $bestRank = self::TIER_RANK[$bestSlug];

        // Kiểm tra quyền lợi gắn trực tiếp trên bản thân thiệp cưới (bảo toàn đủ 6 tháng / 1 năm của thiệp đã tạo)
        if ($invitation && $invitation->purged_at === null) {
            $state = EntitlementStateMachineService::getSubscriptionState($invitation);
            $isInvitationStillValid = $state !== EntitlementStateMachineService::STATE_REFUNDED
                && $state !== EntitlementStateMachineService::STATE_EXPIRED
                && ! $invitation->isExpired();

            if ($isInvitationStillValid) {
                $stampedSlug = $this->normalizeTier($invitation->plan_slug ?? 'free');
                if (in_array($stampedSlug, ['plus', 'pro'], true) && self::TIER_RANK[$stampedSlug] > $bestRank) {
                    $bestSlug = $stampedSlug;
                    $bestRank = self::TIER_RANK[$stampedSlug];
                }

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

        return $bestSlug;
    }

    /**
     * Kích hoạt hoặc gia hạn gói Plus (6 tháng) / Pro (1 năm) cho tài khoản người dùng từ đơn hàng đã thanh toán.
     * Bắt buộc chạy trong DB Transaction:
     * - Gói Plus: luôn đăng ký / gia hạn đúng 6 tháng (180 ngày) mỗi lần.
     * - Gói Pro: luôn đăng ký / gia hạn đúng 1 năm (12 tháng / 365 ngày) mỗi lần.
     * - Nếu gia hạn khi cùng gói vẫn đang còn hạn: cộng dồn nối tiếp vào plan_expires_at.
     */
    public function activateUserPlanFromOrder(Order $order): void
    {
        $order->loadMissing(['plan']);
        $plan = $order->plan;
        if (! $plan) {
            return;
        }

        $planSlug = $this->normalizeTier($plan->slug);
        if (! in_array($planSlug, ['plus', 'pro'], true)) {
            return;
        }

        $rule = self::PLAN_RULES[$planSlug];
        $periodMonths = $rule['duration_months']; // Plus = 6, Pro = 12

        $user = User::whereKey($order->user_id)->lockForUpdate()->first();
        if (! $user || $this->isGuestUser($user)) {
            return;
        }

        $now = now();
        $isSameActivePlan = $user->current_plan_slug === $planSlug
            && $user->plan_expires_at !== null
            && $user->plan_expires_at->isFuture();

        $hasPreviousSamePaidOrder = Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->where('id', '!=', $order->id)
            ->whereHas('plan', fn ($q) => $q->where('slug', $planSlug))
            ->exists();

        $startsAt = $isSameActivePlan ? $user->plan_expires_at->copy() : $now->copy();
        $expiresAt = $startsAt->copy()->addMonths($periodMonths);
        $isRenewal = $isSameActivePlan || $hasPreviousSamePaidOrder;

        $order->forceFill([
            'period_months' => $periodMonths,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_renewal' => $isRenewal,
        ])->save();

        // Chỉ cập nhật current_plan_slug trên user nếu gói mới cao hơn hoặc bằng gói đang hiệu lực hiện tại
        $currentActiveSlug = $this->getAccountActivePlanSlug($user);
        $shouldUpdateUserPlan = self::TIER_RANK[$planSlug] >= self::TIER_RANK[$currentActiveSlug]
            || $user->plan_expires_at === null
            || $user->plan_expires_at->isPast();

        if ($shouldUpdateUserPlan) {
            $user->forceFill([
                'current_plan_slug' => $planSlug,
                'plan_started_at' => ($isSameActivePlan && $user->plan_started_at) ? $user->plan_started_at : $startsAt,
                'plan_expires_at' => $expiresAt,
                'active_plan_order_id' => $order->id,
            ])->save();
        }
    }

    /**
     * Tính toán chính xác hạn mức tạo thiệp còn lại (CHỐNG LÁCH XÓA ĐI TẠO LẠI).
     * Mọi thiệp đã tạo (bao gồm cả thiệp người dùng đã xóa `SoftDeletes` hoặc đã auto-purge) đều được tính vào hạn mức!
     *
     * @param  array<int, string>  $sessionSlugs
     * @return array{
     *     plan_slug: string,
     *     plan_label: string,
     *     limit: int,
     *     used: int,
     *     remaining: int,
     *     can_create: bool,
     *     duration_months: int,
     *     plan_expires_at: ?Carbon,
     *     active_cycle_order_id: ?int,
     *     is_unlimited: bool,
     *     blocked_message: ?string
     * }
     */
    public function getCreationQuotaSummary(?User $user, ?string $guestDeviceId = null, array $sessionSlugs = []): array
    {
        if ($user && $user->isAdmin()) {
            return [
                'plan_slug' => 'pro',
                'plan_label' => 'Quản Trị Viên (Không Giới Hạn)',
                'limit' => PHP_INT_MAX,
                'used' => 0,
                'remaining' => PHP_INT_MAX,
                'can_create' => true,
                'duration_months' => 12,
                'plan_expires_at' => null,
                'active_cycle_order_id' => null,
                'is_unlimited' => true,
                'blocked_message' => null,
            ];
        }

        // 1. Khách vãng lai (Gói Free — tối đa 1 thiệp / thiết bị & phiên, lưu trữ 3 tháng)
        if ($this->isGuestUser($user)) {
            $cleanDeviceId = trim((string) $guestDeviceId);
            $cleanSlugs = array_values(array_filter($sessionSlugs, fn ($s) => is_string($s) && trim($s) !== ''));

            $used = 0;
            if ($cleanDeviceId !== '' || $cleanSlugs !== []) {
                $used = Invitation::withTrashed()
                    ->where('plan_slug', 'free')
                    ->where(function ($q) use ($cleanDeviceId, $cleanSlugs) {
                        if ($cleanDeviceId !== '') {
                            $q->where('guest_device_id', $cleanDeviceId);
                        }
                        if ($cleanSlugs !== []) {
                            if ($cleanDeviceId !== '') {
                                $q->orWhereIn('slug', $cleanSlugs);
                            } else {
                                $q->whereIn('slug', $cleanSlugs);
                            }
                        }
                    })
                    ->count();
            }

            $limit = self::PLAN_RULES['free']['max_invitations']; // 1
            $remaining = max(0, $limit - $used);
            $canCreate = $used < $limit;

            return [
                'plan_slug' => 'free',
                'plan_label' => $this->getTierLabel('free'),
                'limit' => $limit,
                'used' => $used,
                'remaining' => $remaining,
                'can_create' => $canCreate,
                'duration_months' => self::PLAN_RULES['free']['duration_months'],
                'plan_expires_at' => null,
                'active_cycle_order_id' => null,
                'is_unlimited' => false,
                'blocked_message' => $canCreate ? null : 'Gói Miễn Phí (không đăng nhập) chỉ cho phép tạo tối đa 1 thiệp (tính cả thiệp đã xóa). Vui lòng đăng ký/đăng nhập tài khoản để tự động nhận Gói Thành Viên (Premium — tối đa 3 thiệp) hoặc nâng cấp gói Plus/Pro.',
            ];
        }

        $accountPlan = $this->getAccountActivePlanSlug($user);
        $rule = self::PLAN_RULES[$accountPlan];
        $planExpiresAt = $this->getAccountPlanExpiresAt($user);

        // 2. Tài khoản Premium mặc định (tối đa 3 thiệp, tính cả thiệp Free đã đồng bộ & thiệp đã xóa)
        if ($accountPlan === 'premium') {
            $used = Invitation::withTrashed()
                ->where(function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                        ->orWhereHas('wedding', fn ($wq) => $wq->where('user_id', $user->id));
                })
                ->whereIn('plan_slug', ['free', 'premium'])
                ->whereNull('plan_cycle_order_id')
                ->count();

            $limit = $rule['max_invitations']; // 3
            $remaining = max(0, $limit - $used);
            $canCreate = $used < $limit;

            return [
                'plan_slug' => 'premium',
                'plan_label' => $this->getTierLabel('premium'),
                'limit' => $limit,
                'used' => $used,
                'remaining' => $remaining,
                'can_create' => $canCreate,
                'duration_months' => $rule['duration_months'],
                'plan_expires_at' => null,
                'active_cycle_order_id' => null,
                'is_unlimited' => false,
                'blocked_message' => $canCreate ? null : "Tài khoản Gói Thành Viên (Premium) đã dùng hết {$used}/{$limit} lượt tạo thiệp (đã bao gồm cả thiệp bạn đã xóa). Vui lòng nâng cấp lên Gói Plus (10 thiệp / 6 tháng) hoặc Gói Pro (30 thiệp / 1 năm) để tạo thêm.",
            ];
        }

        // 3. Tài khoản Plus (10 thiệp / chu kỳ 6 tháng) hoặc Pro (30 thiệp / chu kỳ 1 năm)
        $activeOrders = $this->getActivePaidOrders($user, $accountPlan);
        $perOrderLimit = $rule['max_invitations'];

        if ($activeOrders->isNotEmpty()) {
            $activeOrderIds = $activeOrders->pluck('id')->map(fn ($id) => (int) $id)->all();
            $limit = count($activeOrderIds) * $perOrderLimit;
            $activeCycleOrderId = end($activeOrderIds) ?: null;

            $earliestAnchor = $activeOrders
                ->map(fn (Order $o) => $o->paid_at ?? $o->created_at ?? now())
                ->sortBy(fn (Carbon $c) => $c->getTimestamp())
                ->first();

            $used = Invitation::withTrashed()
                ->where(function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                        ->orWhereHas('wedding', fn ($wq) => $wq->where('user_id', $user->id));
                })
                ->where(function ($q) use ($activeOrderIds, $accountPlan, $earliestAnchor) {
                    $q->whereIn('plan_cycle_order_id', $activeOrderIds)
                        ->orWhere(function ($legacyQ) use ($accountPlan, $earliestAnchor) {
                            $legacyQ->whereNull('plan_cycle_order_id')
                                ->where('plan_slug', $accountPlan);
                            if ($earliestAnchor) {
                                $legacyQ->where('created_at', '>=', $earliestAnchor);
                            }
                        });
                })
                ->count();
        } else {
            // Trường hợp Admin cấp gói trực tiếp trên User (plan_expires_at còn hạn nhưng không qua Order)
            $limit = $perOrderLimit;
            $activeCycleOrderId = $user->active_plan_order_id;
            $cycleStart = $user->plan_started_at ?? now()->subMonths($rule['duration_months']);

            $used = Invitation::withTrashed()
                ->where(function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                        ->orWhereHas('wedding', fn ($wq) => $wq->where('user_id', $user->id));
                })
                ->where('plan_slug', $accountPlan)
                ->where('created_at', '>=', $cycleStart)
                ->count();
        }

        $remaining = max(0, $limit - $used);
        $canCreate = $used < $limit;

        $upgradeHint = $accountPlan === 'plus'
            ? 'Vui lòng gia hạn thêm chu kỳ Gói Plus (6 tháng / +10 thiệp) hoặc nâng cấp lên Gói Pro (1 năm / 30 thiệp).'
            : 'Vui lòng gia hạn thêm chu kỳ Gói Pro (1 năm / +30 thiệp) để mở rộng hạn mức.';

        return [
            'plan_slug' => $accountPlan,
            'plan_label' => $this->getTierLabel($accountPlan),
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
            'can_create' => $canCreate,
            'duration_months' => $rule['duration_months'],
            'plan_expires_at' => $planExpiresAt,
            'active_cycle_order_id' => $activeCycleOrderId,
            'is_unlimited' => false,
            'blocked_message' => $canCreate ? null : "Bạn đã sử dụng hết {$used}/{$limit} lượt tạo thiệp của {$this->getTierLabel($accountPlan)} (tính cả các thiệp đã xóa). {$upgradeHint}",
        ];
    }

    /**
     * Xác nhận người dùng/khách còn hạn mức tạo thiệp mới; nếu hết sẽ ném ValidationException ngay lập tức.
     *
     * @param  array<int, string>  $sessionSlugs
     * @return array<string, mixed>
     */
    public function assertCanCreateInvitation(?User $user, ?string $guestDeviceId = null, array $sessionSlugs = []): array
    {
        $summary = $this->getCreationQuotaSummary($user, $guestDeviceId, $sessionSlugs);

        if (! $summary['can_create']) {
            throw ValidationException::withMessages([
                'quota' => $summary['blocked_message'] ?? 'Bạn đã đạt giới hạn tạo thiệp tối đa của gói hiện tại.',
            ]);
        }

        return $summary;
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
        if (! $user || $this->isGuestUser($user)) {
            return false;
        }

        return $this->hasPlanAtLeast($user, 'plus');
    }

    /**
     * Đặc quyền Xuất bản Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội (/thiep-su-kien -> /loi-moi/{slug})
     * yêu cầu người dùng đã đăng nhập và sở hữu Gói Pro VIP (1 năm) đang còn hạn (Admin miễn trừ).
     */
    public function canPublishEventInvitation(?User $user): bool
    {
        if (! $user || $this->isGuestUser($user)) {
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

        if (! $user || $this->isGuestUser($user)) {
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
     * - Mẫu plus & pro: yêu cầu chủ sở hữu hoặc bản thân thiệp đã có gói thanh toán tương ứng còn hạn (hoặc Admin).
     */
    public function canPublishInvitation(Invitation $invitation, ?User $actingUser = null): bool
    {
        if ($invitation->purged_at !== null || $invitation->deleted_by_user_at !== null) {
            return false;
        }

        $templateTier = $this->normalizeTier($invitation->template?->tier ?? 'free');

        if (in_array($templateTier, ['free', 'premium'], true)) {
            return true;
        }

        if ($actingUser && $actingUser->isAdmin()) {
            return true;
        }

        $owner = $actingUser ?? $invitation->owner ?? $invitation->wedding?->user;

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
