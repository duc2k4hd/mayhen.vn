<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Order;
use App\Models\Plan;

class EntitlementStateMachineService
{
    /**
     * Danh mục các trạng thái vòng đời gói cước bất biến.
     */
    public const STATE_TRIAL = 'trial';

    public const STATE_ACTIVE = 'active';

    public const STATE_GRACE_PERIOD = 'grace_period';

    public const STATE_EXPIRED = 'expired';

    public const STATE_REFUNDED = 'refunded';

    /**
     * Ma trận quyền lợi theo gói dịch vụ (Entitlement Matrix).
     */
    public const ENTITLEMENT_CATALOG = [
        'remove_branding' => [
            'label' => 'Bỏ Nhãn Hiệu Mây Hẹn (Zero Branding)',
            'description' => 'Ẩn hoàn toàn logo và chân trang bản quyền mayhen.vn.',
            'plans' => ['pro'],
        ],
        'custom_domain' => [
            'label' => 'Tên Miền Riêng Độc Bản (Custom Domain)',
            'description' => 'Gắn kết tên miền website cưới riêng (ví dụ: minhtuanthuhawedding.vn).',
            'plans' => ['pro'],
        ],
        'multi_family_invitations' => [
            'label' => 'Nhân Bản Thiệp Nhà Trai / Nhà Gái',
            'description' => 'Tách riêng thiệp mời Lễ Vu Quy và Lễ Thành Hôn độc lập.',
            'plans' => ['pro'],
        ],
        'live_guest_photo_drop' => [
            'label' => 'Khách Tải Ảnh Bàn Tiệc (Live Photo Drop)',
            'description' => 'Quan khách quét QR tại bàn tiệc tải ảnh tự nhiên lên album trực tiếp.',
            'plans' => ['plus', 'premium', 'pro'],
        ],
        'background_music' => [
            'label' => 'Nhạc Nền MP3 Du Dương',
            'description' => 'Tùy chọn tải nhạc nền tự động phát sau khi khách chạm mở thiệp.',
            'plans' => ['plus', 'premium', 'pro'],
        ],
        'unlimited_rsvps' => [
            'label' => 'Xác Nhận Tham Dự RSVP Không Giới Hạn',
            'description' => 'Thu thập phản hồi quan khách không giới hạn số lượng.',
            'plans' => ['plus', 'premium', 'pro'],
        ],
        'export_excel_crm' => [
            'label' => 'Xuất Danh Bạ & Sổ Kế Toán Ra Excel',
            'description' => 'Tải file CSV/Excel UTF-8 BOM danh bạ và tiền mừng cưới.',
            'plans' => ['plus', 'premium', 'pro'],
        ],
        'extended_storage' => [
            'label' => 'Lưu Trữ Kỷ Niệm Dài Hạn (6 tháng - 2 năm)',
            'description' => 'Bảo toàn vĩnh viễn hình ảnh và lời chúc của đôi uyên ương.',
            'plans' => ['plus', 'premium', 'pro'],
        ],
    ];

    /**
     * Xác định trạng thái vòng đời đăng ký hiện tại của thiệp cưới (State Machine).
     */
    public static function getSubscriptionState(Invitation $invitation): string
    {
        // 1. Kiểm tra đơn hàng hoàn tiền gần nhất
        $latestOrder = $invitation->orders()->latest('id')->first();
        if ($latestOrder && $latestOrder->status === 'refunded') {
            return self::STATE_REFUNDED;
        }

        // 2. Nếu chưa từng thanh toán đơn hàng nào (hoặc chỉ có đơn pending)
        $paidOrderExists = $invitation->orders()->where('status', 'paid')->exists();
        if (! $paidOrderExists) {
            // Nếu thiệp có ngày hết hạn và đã qua ngày hết hạn dùng thử
            if ($invitation->expires_at && $invitation->expires_at->isPast()) {
                return self::STATE_EXPIRED;
            }

            return self::STATE_TRIAL;
        }

        // 3. Đã có đơn hàng trả phí: kiểm tra ngày hết hạn
        if ($invitation->expires_at === null || $invitation->expires_at->isFuture()) {
            return self::STATE_ACTIVE;
        }

        // 4. Nếu đã quá hạn expires_at: kiểm tra thời gian ân hạn (Grace Period 7 ngày)
        $daysPastExpiry = $invitation->expires_at->diffInDays(now(), false);
        if ($daysPastExpiry <= 7) {
            return self::STATE_GRACE_PERIOD;
        }

        return self::STATE_EXPIRED;
    }

    /**
     * Lấy gói cước có hiệu lực hiện tại của thiệp cưới.
     */
    public static function getActivePlan(Invitation $invitation): Plan
    {
        $paidOrder = $invitation->orders()
            ->where('status', 'paid')
            ->with('plan')
            ->latest('paid_at')
            ->first();

        if ($paidOrder && $paidOrder->plan) {
            return $paidOrder->plan;
        }

        // Fallback về gói Free mặc định
        return Plan::where('slug', 'free')->first() ?? new Plan([
            'slug' => 'free',
            'name' => 'Trải Nghiệm (Free)',
            'price' => 0,
            'duration_days' => 7,
            'max_invitations' => 1,
            'features' => ['Tạo 1 thiệp cưới cơ bản'],
            'is_active' => true,
        ]);
    }

    /**
     * Kiểm tra một quyền lợi tính năng cụ thể có được cấp hay không.
     */
    public static function canAccessFeature(Invitation $invitation, string $featureKey): bool
    {
        $state = self::getSubscriptionState($invitation);

        // Khi thiệp đã hoàn tiền hoặc hết hạn quá 7 ngày -> Khóa toàn bộ quyền lợi cao cấp
        if ($state === self::STATE_REFUNDED || $state === self::STATE_EXPIRED) {
            return false;
        }

        $activePlan = self::getActivePlan($invitation);
        $planSlug = $activePlan->slug;

        // Chuẩn hóa alias gói plus/premium
        if ($planSlug === 'premium') {
            $planSlug = 'plus';
        }

        $entitlement = self::ENTITLEMENT_CATALOG[$featureKey] ?? null;
        if (! $entitlement) {
            return false;
        }

        return in_array($planSlug, $entitlement['plans'], true);
    }

    /**
     * Lấy danh mục toàn bộ quyền lợi kèm cờ trạng thái cấp phát thực tế.
     *
     * @return array<string, array{label: string, description: string, is_granted: bool}>
     */
    public static function getGrantedEntitlements(Invitation $invitation): array
    {
        $result = [];
        foreach (self::ENTITLEMENT_CATALOG as $key => $meta) {
            $result[$key] = [
                'label' => $meta['label'],
                'description' => $meta['description'],
                'is_granted' => self::canAccessFeature($invitation, $key),
            ];
        }

        return $result;
    }

    /**
     * Áp dụng chuyển trạng thái khi đơn hàng thanh toán thành công (State Transition: -> Active).
     */
    public static function applyPaidOrder(Invitation $invitation, Order $order): void
    {
        $plan = $order->plan;
        $durationDays = $plan ? (int) $plan->duration_days : 180;

        // Nếu thiệp đang active và chưa hết hạn: Cộng dồn nối tiếp
        if ($invitation->expires_at && $invitation->expires_at->isFuture()) {
            $newExpiry = $invitation->expires_at->addDays($durationDays);
        } else {
            $newExpiry = now()->addDays($durationDays);
        }

        $invitation->update([
            'expires_at' => $newExpiry,
        ]);

        Invitation::clearCache($invitation->slug);
    }

    /**
     * Áp dụng chuyển trạng thái khi đơn hàng bị hoàn tiền (State Transition: -> Refunded).
     */
    public static function applyRefundedOrder(Invitation $invitation, Order $order): void
    {
        $invitation->update([
            'expires_at' => now(),
        ]);

        Invitation::clearCache($invitation->slug);
    }

    /**
     * Nhãn hiển thị trạng thái tiếng Việt chuẩn Quiet Luxury.
     */
    public static function getStateLabel(string $state): string
    {
        return match ($state) {
            self::STATE_ACTIVE => '● Đang Hoạt Động (Active)',
            self::STATE_TRIAL => '⚙ Dùng Thử Trải Nghiệm (Trial)',
            self::STATE_GRACE_PERIOD => '⚠ Ân Hạn Gia Hạn (Grace Period)',
            self::STATE_EXPIRED => '✕ Đã Hết Hạn (Expired)',
            self::STATE_REFUNDED => '↺ Đã Hoàn Tiền (Refunded)',
            default => 'Không Xác Định',
        };
    }
}
