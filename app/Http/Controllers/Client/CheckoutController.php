<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Coupon;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\OrderSettlementService;
use App\Services\Payments\SepayGatewayService;
use App\Services\PlanAccessService;
use App\Services\PostPaymentOnboardingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    private const CHECKOUT_SESSION_MINUTES = 30;

    private const CLIENT_VERIFY_COOLDOWN_SECONDS = 10;

    public function __construct(
        protected SepayGatewayService $sepayGateway,
        protected PlanAccessService $planAccess
    ) {}

    /**
     * Hiển thị trang khởi tạo thanh toán cho gói dịch vụ (Bắt buộc đã đăng nhập tài khoản chính chủ).
     */
    public function show(Request $request, string $planSlug): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user || $this->planAccess->isGuestUser($user)) {
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()
                ->route('login', ['redirect' => $request->fullUrl()])
                ->with('info', 'Vui lòng đăng nhập tài khoản Mây Hẹn để tiến hành thanh toán và tự động kích hoạt gói dịch vụ vào tài khoản của bạn.');
        }

        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();
        $normalizedPlanSlug = strtolower(trim((string) $plan->slug));
        if (! in_array($normalizedPlanSlug, ['plus', 'pro'], true) && (int) $plan->price <= 0) {
            return redirect()
                ->route('pricing')
                ->with('warning', 'Gói miễn phí không cần thanh toán. Vui lòng chọn Gói Plus hoặc Gói Pro để nâng cấp.');
        }

        $invitation = null;
        $invParam = $request->query('invitation_id') ?: $request->query('invitation');
        if ($invParam) {
            $inv = Invitation::with(['wedding', 'template'])
                ->where(is_numeric($invParam) ? 'id' : 'slug', $invParam)
                ->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
            }
        }

        // Nếu mở với ?order=MH... thuộc chính chủ -> chuyển thẳng sang URL phiên thanh toán bền vững
        $resumeCode = strtoupper(trim((string) $request->query('order', '')));
        if ($resumeCode !== '') {
            $existingOrder = Order::where('order_code', $resumeCode)
                ->where('user_id', $user->id)
                ->where('plan_id', $plan->id)
                ->where('status', 'pending')
                ->first();

            if ($existingOrder) {
                if (! $existingOrder->checkout_token) {
                    $existingOrder->forceFill(['checkout_token' => bin2hex(random_bytes(32))])->save();
                }

                return redirect()->route('checkout.session', [
                    'orderCode' => $existingOrder->order_code,
                    'token' => $existingOrder->checkout_token,
                ]);
            }
        }

        // Đánh giá chặt chẽ điều kiện đăng ký gói (Admin luôn Pro, Pro còn hạn không mua thêm, Plus chỉ nâng lên Pro, tối đa 1 đơn pending)
        $eligibility = $this->planAccess->evaluatePurchaseEligibility($user, $plan, checkPendingOrder: true);
        $pendingOrder = $eligibility['pending_order'];
        if ($pendingOrder && ! $pendingOrder->checkout_token) {
            $pendingOrder->forceFill(['checkout_token' => bin2hex(random_bytes(32))])->save();
        }

        $accountPlanSlug = $eligibility['current_plan'];
        $accountPlanExpiry = $eligibility['current_plan_expires_at'];
        $planRule = $this->resolvePlanRule($plan, $normalizedPlanSlug);

        return view('client.pages.checkout', [
            'plan' => $plan,
            'planRule' => $planRule,
            'user' => $user,
            'beneficiaryName' => $user->name ?: 'Thành viên',
            'beneficiaryEmail' => $user->email,
            'isSharedPayerView' => false,
            'isSessionMode' => false,
            'sessionOrder' => null,
            'isSessionActivePending' => false,
            'isSessionTerminated' => false,
            'terminatedTitle' => '',
            'terminatedMessage' => '',
            'eligibility' => $eligibility,
            'activePendingOrder' => $pendingOrder,
            'invitation' => $invitation,
            'accountPlanSlug' => $accountPlanSlug,
            'accountPlanLabel' => $this->planAccess->getTierLabel($accountPlanSlug),
            'accountPlanExpiry' => $accountPlanExpiry,
            'isUpgradeFromPlus' => $eligibility['is_upgrade'],
            'canManualVerify' => $this->sepayGateway->canQuerySepayApi(),
            'resumedOrderPayload' => null,
            'supportPhone' => PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'support_phone', '0969.888.999'),
            'supportZaloUrl' => PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'zalo_url', ''),
        ]);
    }

    /**
     * Link phiên thanh toán bền vững & có thể gửi cho người khác thanh toán hộ.
     * URL: /thanh-toan/phien/{orderCode}/{token}
     * - F5 / tải lại trang hoặc chuyển app ngân hàng quay lại luôn giữ nguyên màn hình QR & đếm ngược khi phiên còn hiệu lực.
     * - Ngay khi chính chủ hủy phiên hoặc phiên quá hạn 30 phút, link tự động vô hiệu hóa (ẩn toàn bộ QR/STK, báo đỏ rõ ràng).
     */
    public function showSession(Request $request, string $orderCode, string $token): View
    {
        $order = Order::with(['plan', 'user', 'invitation.wedding', 'invitation.template'])
            ->where('order_code', strtoupper(trim($orderCode)))
            ->firstOrFail();

        abort_unless($order->hasValidCheckoutToken($token), 403, 'Liên kết phiên thanh toán không hợp lệ hoặc đã hết hạn.');

        $plan = $order->plan;
        abort_if(! $plan, 404);

        // Tự động đóng phiên nếu đơn pending đã quá hạn 30 phút (không cần chờ tới nhịp Cron)
        if ($order->status === 'pending' && $order->checkout_expires_at !== null && $order->checkout_expires_at->isPast()) {
            $this->planAccess->expireStalePendingOrders($order->user);
            $order->refresh();
        }

        $beneficiary = $order->user;
        $viewer = $request->user();
        $isOwnerOrAdmin = $viewer !== null && ($viewer->id === $order->user_id || $viewer->isAdmin());
        $isSharedPayerView = ! $isOwnerOrAdmin;

        $normalizedPlanSlug = strtolower(trim((string) $plan->slug));
        $planRule = $this->resolvePlanRule($plan, $normalizedPlanSlug);

        $accountPlanSlug = $beneficiary ? $this->planAccess->getAccountActivePlanSlug($beneficiary) : 'premium';
        $accountPlanExpiry = $beneficiary ? $this->planAccess->getAccountPlanExpiresAt($beneficiary) : null;

        $isSessionPaid = $order->isPaid();
        $isSessionActivePending = $order->status === 'pending'
            && ($order->checkout_expires_at === null || $order->checkout_expires_at->isFuture());
        $isSessionTerminated = ! $isSessionPaid && ! $isSessionActivePending;
        [$terminatedTitle, $terminatedMessage] = $this->resolveTerminatedSessionCopy($order, $isSharedPayerView);

        $cfg = $this->sepayGateway->getConfig();
        $sessionPayload = null;
        if ($isSessionActivePending && $this->sepayGateway->hasValidBankAccount($cfg)) {
            $discount = max(0, (int) $plan->price - (int) $order->amount);
            $sessionPayload = $this->formatCheckoutResponse($order, (int) $plan->price, $discount, null, $cfg, $isOwnerOrAdmin);
        }

        $beneficiaryEmail = $beneficiary?->email ?? '';
        $displayEmail = $isSharedPayerView ? $this->maskEmail($beneficiaryEmail) : $beneficiaryEmail;

        return view('client.pages.checkout', [
            'plan' => $plan,
            'planRule' => $planRule,
            'user' => $beneficiary,
            'beneficiaryName' => $beneficiary?->name ?: 'Thành viên Mây Hẹn',
            'beneficiaryEmail' => $displayEmail,
            'isSharedPayerView' => $isSharedPayerView,
            'isSessionMode' => true,
            'sessionOrder' => $order,
            'isSessionActivePending' => $isSessionActivePending,
            'isSessionTerminated' => $isSessionTerminated,
            'terminatedTitle' => $terminatedTitle,
            'terminatedMessage' => $terminatedMessage,
            'eligibility' => [
                'allowed' => true,
                'code' => 'SESSION_VIEW',
                'message' => null,
                'pending_order' => null,
            ],
            'activePendingOrder' => null,
            'invitation' => $isOwnerOrAdmin ? $order->invitation : null,
            'accountPlanSlug' => $accountPlanSlug,
            'accountPlanLabel' => $this->planAccess->getTierLabel($accountPlanSlug),
            'accountPlanExpiry' => $isOwnerOrAdmin ? $accountPlanExpiry : null,
            'isUpgradeFromPlus' => $accountPlanSlug === 'plus' && $normalizedPlanSlug === 'pro',
            'canManualVerify' => $this->sepayGateway->canQuerySepayApi($cfg),
            'resumedOrderPayload' => $sessionPayload,
            'supportPhone' => PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'support_phone', '0969.888.999'),
            'supportZaloUrl' => PlatformSetting::getValue(PlatformSetting::GROUP_BRAND_SEO, 'zalo_url', ''),
        ]);
    }

    /**
     * Khởi tạo đơn hàng gắn chính chủ tài khoản đăng nhập + sinh checkout_token riêng + VietQR / SePay QR.
     */
    public function process(CreateOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || $this->planAccess->isGuestUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập tài khoản Mây Hẹn trước khi thanh toán.',
            ], 401);
        }

        $plan = Plan::where('is_active', true)->findOrFail($request->validated('plan_id'));
        $normalizedPlanSlug = strtolower(trim((string) $plan->slug));
        if (! in_array($normalizedPlanSlug, ['plus', 'pro'], true) && (int) $plan->price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ hỗ trợ thanh toán cho Gói Plus (chu kỳ 6 tháng) và Gói Pro (chu kỳ 1 năm).',
            ], 422);
        }
        $periodMonths = $normalizedPlanSlug === 'pro' ? 12 : ($normalizedPlanSlug === 'plus' ? 6 : null);

        $invitationId = $request->validated('invitation_id');
        if ($invitationId !== null) {
            $invitation = Invitation::with('wedding')->findOrFail($invitationId);
            abort_unless($invitation->isOwner(), 404);
        }

        $cfg = $this->sepayGateway->getConfig();
        if (! $this->sepayGateway->hasValidBankAccount($cfg)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình tài khoản nhận thanh toán. Vui lòng liên hệ quản trị viên.',
            ], 503);
        }

        // Bổ sung SĐT/Họ tên vào hồ sơ tài khoản nếu trước đó còn trống (không bao giờ ghi đè email tài khoản)
        $submittedPhone = trim((string) $request->validated('customer_phone'));
        $submittedName = trim((string) ($request->validated('customer_name') ?? ''));
        $profileUpdates = [];
        if (empty($user->phone) && $submittedPhone !== '') {
            $profileUpdates['phone'] = $submittedPhone;
        }
        if (empty($user->name) && $submittedName !== '') {
            $profileUpdates['name'] = $submittedName;
        }
        if ($profileUpdates !== []) {
            $user->forceFill($profileUpdates)->save();
        }

        $orderCode = 'MH'.strtoupper(Str::random(10));
        $checkoutToken = bin2hex(random_bytes(32));
        $couponCode = strtoupper(trim((string) $request->input('coupon_code', '')));
        $appliedCouponCode = null;
        $discountAmount = 0;

        $creationOutcome = DB::transaction(function () use (
            $user,
            $plan,
            $couponCode,
            $orderCode,
            $checkoutToken,
            $invitationId,
            $request,
            $periodMonths,
            &$appliedCouponCode,
            &$discountAmount
        ): array {
            // Khóa hàng User trong Transaction để chống 100% race condition tạo 2 đơn pending cùng lúc
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $eligibility = $this->planAccess->evaluatePurchaseEligibility($lockedUser, $plan, checkPendingOrder: true);

            if (! $eligibility['allowed']) {
                $pendingOrder = $eligibility['pending_order'];
                if ($pendingOrder && ! $pendingOrder->checkout_token) {
                    $pendingOrder->forceFill(['checkout_token' => bin2hex(random_bytes(32))])->save();
                }

                return [
                    'blocked' => true,
                    'http_status' => $eligibility['http_status'],
                    'payload' => [
                        'success' => false,
                        'code' => $eligibility['code'],
                        'message' => $eligibility['message'],
                        'pending_order_code' => $pendingOrder?->order_code,
                        'pending_session_url' => $pendingOrder ? route('checkout.session', [
                            'orderCode' => $pendingOrder->order_code,
                            'token' => $pendingOrder->checkout_token,
                        ]) : null,
                    ],
                ];
            }

            $orderAmount = (int) $plan->price;

            if ($couponCode !== '') {
                $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();
                if ($coupon && $coupon->isValid($plan->price)) {
                    $discountAmount = (int) $coupon->calculateDiscount($plan->price);
                    $orderAmount = max(0, (int) $plan->price - $discountAmount);
                    $appliedCouponCode = $coupon->code;
                    $coupon->incrementUsage();
                }
            }

            $createdOrder = Order::create([
                'order_code' => $orderCode,
                'checkout_token' => $checkoutToken,
                'user_id' => $lockedUser->id,
                'plan_id' => $plan->id,
                'invitation_id' => $invitationId,
                'amount' => $orderAmount,
                'status' => 'pending',
                'payment_method' => $request->validated('payment_method'),
                'period_months' => $periodMonths,
                'checkout_expires_at' => now()->addMinutes(self::CHECKOUT_SESSION_MINUTES),
            ]);

            return [
                'blocked' => false,
                'order' => $createdOrder,
            ];
        });

        if ($creationOutcome['blocked']) {
            return response()->json($creationOutcome['payload'], $creationOutcome['http_status']);
        }

        /** @var Order $order */
        $order = $creationOutcome['order'];

        return response()->json($this->formatCheckoutResponse($order, (int) $plan->price, $discountAmount, $appliedCouponCode, $cfg, true));
    }

    /**
     * Chính chủ hủy phiên thanh toán đang chờ (pending) để giải phóng khóa và chọn gói khác.
     * Nếu khách lỡ chuyển tiền sau khi hủy, Webhook SePay / Admin Check SePay vẫn tự phục hồi an toàn.
     */
    public function cancelPendingOrder(Request $request, string $orderCode): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $outcome = DB::transaction(function () use ($user, $orderCode): array {
            $order = Order::where('order_code', strtoupper(trim($orderCode)))
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->user_id !== $user->id && ! $user->isAdmin()) {
                return [
                    'ok' => false,
                    'http_status' => 403,
                    'flash_type' => 'error',
                    'message' => 'Bạn không có quyền hủy đơn hàng này.',
                ];
            }

            if ($order->isPaid()) {
                return [
                    'ok' => false,
                    'http_status' => 409,
                    'flash_type' => 'error',
                    'message' => "Đơn hàng #{$order->order_code} đã được thanh toán thành công, không thể hủy.",
                ];
            }

            if ($order->status !== 'pending') {
                return [
                    'ok' => true,
                    'http_status' => 200,
                    'flash_type' => 'info',
                    'message' => "Phiên thanh toán #{$order->order_code} đã được đóng trước đó.",
                ];
            }

            $order->forceFill([
                'status' => 'cancelled',
                'checkout_expires_at' => now(),
                'last_sepay_check_message' => 'Khách hàng tự hủy phiên chờ thanh toán để chọn gói khác.',
            ])->save();

            return [
                'ok' => true,
                'http_status' => 200,
                'flash_type' => 'success',
                'message' => "Đã hủy phiên chờ thanh toán #{$order->order_code}. Bạn có thể tạo đơn thanh toán mới ngay bây giờ.",
            ];
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $outcome['ok'],
                'message' => $outcome['message'],
            ], $outcome['http_status']);
        }

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            return redirect($redirectTo)->with($outcome['flash_type'], $outcome['message']);
        }

        return back()->with($outcome['flash_type'], $outcome['message']);
    }

    /**
     * Polling kiểm tra trạng thái thanh toán của đơn hàng.
     * Bảo vệ chặt bằng checkout_token 256-bit (hoặc Admin) — hỗ trợ cả chủ đơn lẫn người nhận link thanh toán hộ.
     */
    public function checkStatus(Request $request, string $orderCode): JsonResponse
    {
        $user = $request->user();
        $order = Order::with('user')->where('order_code', strtoupper(trim($orderCode)))->firstOrFail();

        if (! $this->canAccessOrderCheckout($order, $user, $request)) {
            return response()->json([
                'success' => false,
                'message' => 'Token xác thực phiên thanh toán không hợp lệ hoặc bạn không có quyền kiểm tra đơn hàng này.',
            ], 403);
        }

        // Tự động đóng đơn pending đã quá hạn 30 phút để vô hiệu hóa đồng bộ trên mọi tab đang mở
        if ($order->status === 'pending' && $order->checkout_expires_at !== null && $order->checkout_expires_at->isPast()) {
            $this->planAccess->expireStalePendingOrders($order->user);
            $order->refresh();
        }

        $isOwnerOrAdmin = $user !== null && ($order->user_id === $user->id || $user->isAdmin());
        $isSharedPayerView = ! $isOwnerOrAdmin;
        $isPaid = $order->isPaid();
        $isExpired = ! $isPaid && $order->checkout_expires_at !== null && $order->checkout_expires_at->isPast();
        $isCancelled = $order->status === 'cancelled';
        $isTerminated = ! $isPaid && ($order->status !== 'pending' || $isExpired);
        [$terminatedTitle, $terminatedMessage] = $isTerminated
            ? $this->resolveTerminatedSessionCopy($order, $isSharedPayerView)
            : [null, null];

        return response()->json([
            'success' => true,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'is_paid' => $isPaid,
            'is_cancelled' => $isCancelled,
            'is_expired' => $isExpired,
            'is_terminated' => $isTerminated,
            'terminated_title' => $terminatedTitle,
            'terminated_message' => $terminatedMessage,
            'expires_at' => $order->checkout_expires_at?->toIso8601String(),
            'last_sepay_check_at' => $order->last_sepay_check_at?->toIso8601String(),
            'last_sepay_check_message' => $order->last_sepay_check_message,
            'success_url' => $isOwnerOrAdmin ? route('checkout.first_success', $order->order_code) : null,
        ]);
    }

    /**
     * Chủ động bấm "Tôi đã chuyển khoản — Kiểm tra với SePay".
     * Bảo vệ chặt bằng checkout_token 256-bit (hỗ trợ cả chủ đơn lẫn người trả hộ mở link chia sẻ) + cooldown 10s.
     */
    public function verifyWithSepay(Request $request, string $orderCode): JsonResponse
    {
        $user = $request->user();
        $order = Order::with(['plan', 'invitation'])
            ->where('order_code', strtoupper(trim($orderCode)))
            ->firstOrFail();

        if (! $this->canAccessOrderCheckout($order, $user, $request)) {
            return response()->json([
                'success' => false,
                'is_paid' => false,
                'message' => 'Token xác thực phiên thanh toán không hợp lệ hoặc đã hết hạn.',
            ], 403);
        }

        $isOwnerOrAdmin = $user !== null && ($order->user_id === $user->id || $user->isAdmin());
        $successUrl = $isOwnerOrAdmin ? route('checkout.first_success', $order->order_code) : null;

        if ($order->isPaid()) {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'paid',
                'message' => "Đơn hàng #{$order->order_code} đã được xác nhận thanh toán thành công!",
                'success_url' => $successUrl,
            ]);
        }

        if ($order->status === 'refunded') {
            return response()->json([
                'success' => false,
                'is_paid' => false,
                'is_terminated' => true,
                'status' => 'refunded',
                'message' => "Đơn hàng #{$order->order_code} đã được hoàn tiền và không thể kích hoạt lại.",
            ], 409);
        }

        if ($order->last_sepay_check_at !== null) {
            $elapsed = (int) $order->last_sepay_check_at->diffInSeconds(now(), true);
            if ($elapsed < self::CLIENT_VERIFY_COOLDOWN_SECONDS) {
                $wait = self::CLIENT_VERIFY_COOLDOWN_SECONDS - $elapsed;

                return response()->json([
                    'success' => false,
                    'is_paid' => false,
                    'status' => 'cooldown',
                    'retry_after' => $wait,
                    'message' => "Hệ thống vừa kiểm tra SePay xong. Vui lòng chờ {$wait} giây rồi thử lại.",
                ], 429);
            }
        }

        $trigger = $isOwnerOrAdmin ? 'client' : 'shared_payer';
        $result = $this->sepayGateway->reconcileOrderWithSepay($order, $user, $trigger);
        $order->refresh();

        $isTerminated = ! $result['is_paid'] && $order->status !== 'pending';
        [$terminatedTitle, $terminatedMessage] = $isTerminated
            ? $this->resolveTerminatedSessionCopy($order, ! $isOwnerOrAdmin)
            : [null, null];

        $responseStatus = $result['is_paid']
            ? 200
            : (in_array($result['http_status'], [429, 503], true) ? $result['http_status'] : 200);

        return response()->json([
            'success' => $result['ok'],
            'is_paid' => $result['is_paid'],
            'is_terminated' => $isTerminated,
            'terminated_title' => $terminatedTitle,
            'terminated_message' => $terminatedMessage,
            'status' => $result['status'],
            'message' => $result['message'],
            'cooldown_seconds' => self::CLIENT_VERIFY_COOLDOWN_SECONDS,
            'success_url' => $successUrl,
        ], $responseStatus);
    }

    /**
     * Webhook receiver từ SePay (URL: https://mayhen.vn/api/payments/sepay/webhook).
     * Hỗ trợ đồng thời chuẩn SePay chính thức (`Authorization: Apikey <KEY>`) và chuẩn HMAC SHA-256 legacy.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $auth = $this->sepayGateway->verifyWebhookRequest($request);
        if (! $auth['ok']) {
            if ($auth['http_status'] === 401) {
                $this->sepayGateway->recordAuditLog(
                    status: 'error',
                    dbStatus: 'unverified',
                    message: "Từ chối webhook không hợp lệ ({$auth['method']})",
                    orderCode: null,
                    amount: 0,
                    transactionId: $request->input('id') ? (string) $request->input('id') : null,
                    ip: $request->ip(),
                    content: is_string($request->input('content')) ? $request->input('content') : '',
                    rawPayload: ['auth_method' => $auth['method']]
                );
            }

            return response()->json(['error' => $auth['error']], $auth['http_status']);
        }

        $transferType = $request->input('transferType');
        if ($transferType === 'out') {
            return response()->json(['success' => true]);
        }
        if ($transferType !== 'in') {
            return response()->json(['success' => false, 'message' => 'Invalid transfer type'], 422);
        }

        $cfg = $this->sepayGateway->getConfig();
        $bankAccount = $cfg['bank_account'];
        if ($bankAccount === '') {
            return response()->json(['error' => 'Payment account is not configured'], 503);
        }

        $receivedAccount = $request->input('accountNumber');
        $receivedSubAccount = $request->input('subAccount');
        $accountMatched = (is_string($receivedAccount) && trim($receivedAccount) === $bankAccount)
            || (is_string($receivedSubAccount) && trim($receivedSubAccount) !== '' && trim($receivedSubAccount) === $bankAccount);

        if (! $accountMatched) {
            $this->sepayGateway->recordAuditLog(
                status: 'error',
                dbStatus: 'unverified',
                message: 'Tài khoản nhận tiền trong webhook không khớp cấu hình Mây Hẹn',
                orderCode: null,
                amount: is_numeric($request->input('transferAmount')) ? (int) $request->input('transferAmount') : 0,
                transactionId: $request->input('id') ? (string) $request->input('id') : null,
                ip: $request->ip(),
                content: is_string($request->input('content')) ? $request->input('content') : '',
                rawPayload: $request->all()
            );

            return response()->json(['success' => false, 'message' => 'Invalid payment account'], 422);
        }

        $content = $request->input('content') ?? $request->input('description') ?? '';
        $rawAmount = $request->input('transferAmount') ?? $request->input('amount');
        if (! is_string($content) || (! is_int($rawAmount) && ! is_string($rawAmount))) {
            return response()->json(['success' => false, 'message' => 'Invalid payment data'], 422);
        }

        $amount = filter_var($rawAmount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($amount === false) {
            return response()->json(['success' => false, 'message' => 'Invalid payment data'], 422);
        }

        $paymentCode = $request->input('code');
        $orderCode = $this->sepayGateway->extractOrderCode(is_string($paymentCode) ? $paymentCode : null, $content);
        $providerTransId = $request->input('id')
            ? (string) $request->input('id')
            : ($request->input('referenceCode') ? (string) $request->input('referenceCode') : null);

        if (! $orderCode) {
            $this->sepayGateway->recordAuditLog(
                status: 'warning',
                dbStatus: 'pending_review',
                message: 'Không tìm thấy mã đơn hàng trong nội dung chuyển khoản',
                orderCode: null,
                amount: (int) $amount,
                transactionId: $providerTransId,
                ip: $request->ip(),
                content: $content,
                rawPayload: $request->all()
            );

            return response()->json(['success' => false, 'message' => 'No order code detected in description'], 200);
        }

        $result = app(OrderSettlementService::class)->settleFromWebhook(
            $orderCode,
            (int) $amount,
            $providerTransId
        );

        $dbStatus = match (true) {
            $result['log_level'] === 'success' => 'success',
            $result['log_level'] === 'info' => 'duplicate',
            $result['http_status'] === 400 => 'mismatch',
            $result['http_status'] === 409 => 'duplicate',
            default => 'pending_review',
        };

        $this->sepayGateway->recordAuditLog(
            status: $result['log_level'],
            dbStatus: $dbStatus,
            message: $result['log_message'],
            orderCode: $orderCode,
            amount: (int) $amount,
            transactionId: $providerTransId,
            ip: $request->ip(),
            content: $content,
            rawPayload: $request->all()
        );

        return response()->json($result['payload'], $result['http_status']);
    }

    /**
     * Màn hình Onboarding Khởi Đầu Thành Công Sau Thanh Toán (Bắt buộc chính chủ hoặc Admin).
     */
    public function firstSuccess(Request $request, string $orderCode, PostPaymentOnboardingService $service): View
    {
        $order = Order::with(['plan', 'invitation.wedding'])
            ->where('order_code', strtoupper($orderCode))
            ->firstOrFail();

        $user = $request->user();
        abort_unless($user && ($order->user_id === $user->id || $user->isAdmin()), 403);

        if (! $order->checkout_token) {
            $order->forceFill(['checkout_token' => bin2hex(random_bytes(32))])->save();
        }

        $guide = $service->getOnboardingGuide($order);

        return view('client.pages.checkout-first-success', [
            'order' => $order,
            'guide' => $guide,
        ]);
    }

    /**
     * Kiểm tra tính hợp lệ và tính mức giảm của mã ưu đãi (Wave 06 - ADM-A11).
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $code = strtoupper(trim((string) $request->input('code', '')));
        $planId = $request->input('plan_id');

        if ($code === '' || ! $planId) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng cung cấp mã ưu đãi và gói dịch vụ hợp lệ.',
            ], 422);
        }

        $plan = Plan::where('is_active', true)->find($planId);
        if (! $plan) {
            return response()->json([
                'success' => false,
                'message' => 'Gói dịch vụ không tồn tại hoặc đã tạm dừng.',
            ], 404);
        }

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon || ! $coupon->isValid($plan->price)) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không hợp lệ, đã hết hạn hoặc chưa đạt điều kiện đơn hàng.',
            ], 422);
        }

        $discount = (int) $coupon->calculateDiscount($plan->price);
        $finalAmount = max(0, (int) $plan->price - $discount);

        return response()->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
                'formatted_discount' => number_format($discount, 0, ',', '.').'đ',
                'formatted_final_amount' => number_format($finalAmount, 0, ',', '.').'đ',
            ],
            'message' => "Áp dụng mã ưu đãi \"{$coupon->code}\" thành công! Giảm ".number_format($discount, 0, ',', '.').'đ',
        ]);
    }

    /**
     * Xác minh quyền truy cập API trạng thái / đối soát phiên thanh toán:
     * - Admin luôn có quyền.
     * - Mọi yêu cầu từ khách hàng hoặc người thanh toán hộ BẮT BUỘC mang đúng checkout_token 256-bit của đơn hàng.
     */
    protected function canAccessOrderCheckout(Order $order, ?User $user, Request $request): bool
    {
        if ($user && $user->isAdmin()) {
            return true;
        }

        $providedToken = $request->header('X-Checkout-Token')
            ?? $request->input('checkout_token')
            ?? $request->query('token');

        return $order->hasValidCheckoutToken(is_string($providedToken) ? $providedToken : null);
    }

    /**
     * @return array{duration_months: int, duration_days: int, max_invitations: int}
     */
    protected function resolvePlanRule(Plan $plan, string $normalizedPlanSlug): array
    {
        if (in_array($normalizedPlanSlug, ['plus', 'pro'], true)) {
            $rule = $this->planAccess->getPlanRule($normalizedPlanSlug);

            return [
                'duration_months' => $rule['duration_months'],
                'duration_days' => $rule['duration_days'],
                'max_invitations' => $rule['max_invitations'],
            ];
        }

        return [
            'duration_months' => max(1, (int) round(($plan->duration_days ?: 180) / 30)),
            'duration_days' => (int) ($plan->duration_days ?: 180),
            'max_invitations' => (int) ($plan->max_invitations ?: 1),
        ];
    }

    /**
     * Thông điệp tiêu chuẩn khi phiên thanh toán đã bị hủy, hết hạn hoặc hoàn tiền.
     *
     * @return array{0: string, 1: string}
     */
    protected function resolveTerminatedSessionCopy(Order $order, bool $isSharedPayerView): array
    {
        $sharedHint = $isSharedPayerView
            ? ' Vui lòng KHÔNG chuyển khoản cho mã đơn này; hãy liên hệ chủ tài khoản để nhận đường dẫn thanh toán mới nếu cần.'
            : ' Vui lòng KHÔNG chuyển khoản cho mã đơn này; bạn có thể quay lại bảng giá để khởi tạo phiên thanh toán mới bất cứ lúc nào.';

        if ($order->status === 'refunded') {
            return [
                'Đơn Hàng Đã Được Hoàn Tiền',
                "Đơn hàng #{$order->order_code} đã được hoàn tiền và chấm dứt hiệu lực.{$sharedHint}",
            ];
        }

        if ($order->status === 'failed') {
            return [
                'Phiên Thanh Toán Không Còn Hiệu Lực',
                "Đơn hàng #{$order->order_code} đã chuyển sang trạng thái thất bại và bị đóng.{$sharedHint}",
            ];
        }

        return [
            'Phiên Thanh Toán Đã Bị Hủy',
            "Phiên thanh toán #{$order->order_code} đã bị chủ tài khoản hủy hoặc đã quá thời hạn 30 phút.{$sharedHint}",
        ];
    }

    /**
     * Che bớt địa chỉ email chủ tài khoản khi hiển thị trên link thanh toán chia sẻ cho người trả hộ.
     */
    protected function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, min(2, mb_strlen($local, 'UTF-8')), 'UTF-8');

        return $visible.'***@'.$domain;
    }

    /**
     * Chuẩn hóa payload trả về sau khi khởi tạo hoặc khôi phục phiên thanh toán.
     *
     * @param  array<string, string>  $cfg
     * @return array<string, mixed>
     */
    protected function formatCheckoutResponse(
        Order $order,
        int $originalAmount,
        int $discountAmount,
        ?string $appliedCouponCode,
        array $cfg,
        bool $includeOwnerSuccessUrl = true
    ): array {
        $qr = $this->sepayGateway->buildQrPayload($order, $cfg);
        $expiresAt = $order->checkout_expires_at ?? now()->addMinutes(self::CHECKOUT_SESSION_MINUTES);
        $remainingSeconds = max(0, (int) now()->diffInSeconds($expiresAt, false));

        return [
            'success' => true,
            'order_code' => $order->order_code,
            'checkout_token' => $order->checkout_token,
            'session_url' => route('checkout.session', [
                'orderCode' => $order->order_code,
                'token' => $order->checkout_token,
            ]),
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => $remainingSeconds,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $appliedCouponCode,
            'amount' => $order->amount,
            'formatted_amount' => number_format($order->amount, 0, ',', '.').'đ',
            'vietqr_url' => $qr['vietqr_url'],
            'sepay_qr_url' => $qr['sepay_qr_url'],
            'bank_info' => $qr['bank_info'],
            'status_url' => route('checkout.status', $order->order_code),
            'verify_url' => route('checkout.verify', $order->order_code),
            'success_url' => $includeOwnerSuccessUrl ? route('checkout.first_success', $order->order_code) : null,
            'can_manual_verify' => $this->sepayGateway->canQuerySepayApi($cfg),
        ];
    }
}
