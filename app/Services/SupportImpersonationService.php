<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SupportImpersonationService
{
    /**
     * Thời lượng tối đa của một phiên hỗ trợ an toàn (30 phút).
     */
    public const DEFAULT_TTL_MINUTES = 30;

    public const SESSION_ORIGINAL_ADMIN_ID = 'mayhen_impersonation_original_admin_id';

    public const SESSION_ORIGINAL_ADMIN_NAME = 'mayhen_impersonation_original_admin_name';

    public const SESSION_ORIGINAL_ADMIN_EMAIL = 'mayhen_impersonation_original_admin_email';

    public const SESSION_TARGET_USER_ID = 'mayhen_impersonation_target_user_id';

    public const SESSION_EXPIRES_AT = 'mayhen_impersonation_expires_at';

    public const SESSION_REASON = 'mayhen_impersonation_reason';

    public const SESSION_TICKET = 'mayhen_impersonation_ticket';

    /**
     * Bắt đầu phiên xem dưới góc nhìn khách hàng (Safe Support Impersonation).
     *
     * @return array{
     *     success: bool,
     *     expires_at: int,
     *     target_user: User
     * }
     */
    public function startImpersonation(User $admin, User $targetCustomer, string $reason, ?string $ticketCode = null, int $ttlMinutes = self::DEFAULT_TTL_MINUTES): array
    {
        if (! $admin->is_admin && ! $admin->canAccessAdminPanel()) {
            throw new \InvalidArgumentException('Chỉ Quản trị viên Mây Hẹn mới có quyền kích hoạt chế độ hỗ trợ khách hàng.');
        }

        if ($admin->id === $targetCustomer->id) {
            throw new \InvalidArgumentException('Không thể kích hoạt chế độ hỗ trợ trên chính tài khoản của bạn.');
        }

        $expiresAt = now()->addMinutes($ttlMinutes)->timestamp;

        // Lưu thông tin phiên kiểm toán vào Session
        session()->put(self::SESSION_ORIGINAL_ADMIN_ID, $admin->id);
        session()->put(self::SESSION_ORIGINAL_ADMIN_NAME, $admin->name);
        session()->put(self::SESSION_ORIGINAL_ADMIN_EMAIL, $admin->email);
        session()->put(self::SESSION_TARGET_USER_ID, $targetCustomer->id);
        session()->put(self::SESSION_EXPIRES_AT, $expiresAt);
        session()->put(self::SESSION_REASON, $reason);
        session()->put(self::SESSION_TICKET, $ticketCode);
        session()->save();

        // Chuyển Auth sang khách hàng
        Auth::login($targetCustomer);

        // Ghi nhật ký kiểm toán hệ thống
        AuditLogger::log(
            'SUPPORT_IMPERSONATION_STARTED',
            User::class,
            $targetCustomer->id,
            "Quản trị viên {$admin->email} bắt đầu phiên xem dưới góc nhìn khách hàng {$targetCustomer->email}. Lý do: {$reason}",
            [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'customer_id' => $targetCustomer->id,
                'customer_email' => $targetCustomer->email,
                'ticket_code' => $ticketCode,
                'reason' => $reason,
                'ttl_minutes' => $ttlMinutes,
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]
        );

        return [
            'success' => true,
            'expires_at' => $expiresAt,
            'target_user' => $targetCustomer,
        ];
    }

    /**
     * Kết thúc phiên hỗ trợ an toàn và đưa Quản trị viên trở lại tài khoản gốc.
     */
    public function stopImpersonation(): ?User
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        $adminId = Session::get(self::SESSION_ORIGINAL_ADMIN_ID);
        $adminEmail = Session::get(self::SESSION_ORIGINAL_ADMIN_EMAIL);
        $customerId = Session::get(self::SESSION_TARGET_USER_ID);
        $reason = Session::get(self::SESSION_REASON);

        $admin = User::find($adminId);

        // Xóa sạch session hỗ trợ
        session()->forget([
            self::SESSION_ORIGINAL_ADMIN_ID,
            self::SESSION_ORIGINAL_ADMIN_NAME,
            self::SESSION_ORIGINAL_ADMIN_EMAIL,
            self::SESSION_TARGET_USER_ID,
            self::SESSION_EXPIRES_AT,
            self::SESSION_REASON,
            self::SESSION_TICKET,
        ]);
        session()->save();

        if ($admin) {
            Auth::login($admin);
        } else {
            Auth::logout();
        }

        // Ghi nhật ký kiểm toán kết thúc phiên
        AuditLogger::log(
            'SUPPORT_IMPERSONATION_ENDED',
            User::class,
            $customerId ?: 'SYSTEM',
            "Quản trị viên {$adminEmail} đã kết thúc phiên hỗ trợ khách hàng #{$customerId}.",
            [
                'admin_id' => $adminId,
                'customer_id' => $customerId,
                'reason' => $reason,
                'ended_at' => now()->toIso8601String(),
            ]
        );

        return $admin;
    }

    /**
     * Kiểm tra xem phiên làm việc hiện tại có đang trong chế độ hỗ trợ an toàn không.
     */
    public function isImpersonating(): bool
    {
        if (! Session::has(self::SESSION_ORIGINAL_ADMIN_ID) || ! Session::has(self::SESSION_EXPIRES_AT)) {
            return false;
        }

        $expiresAt = (int) Session::get(self::SESSION_EXPIRES_AT);
        if (now()->timestamp > $expiresAt) {
            return false;
        }

        return true;
    }

    /**
     * Lấy thông tin chi tiết về phiên hỗ trợ hiện tại.
     *
     * @return array{
     *     admin_id: int,
     *     admin_name: string,
     *     admin_email: string,
     *     target_user_id: int,
     *     expires_at: int,
     *     remaining_seconds: int,
     *     reason: string,
     *     ticket: ?string
     * }|null
     */
    public function getCurrentSession(): ?array
    {
        if (! $this->isImpersonating()) {
            return null;
        }

        $expiresAt = (int) Session::get(self::SESSION_EXPIRES_AT);
        $remainingSeconds = max(0, $expiresAt - now()->timestamp);

        return [
            'admin_id' => (int) Session::get(self::SESSION_ORIGINAL_ADMIN_ID),
            'admin_name' => (string) Session::get(self::SESSION_ORIGINAL_ADMIN_NAME),
            'admin_email' => (string) Session::get(self::SESSION_ORIGINAL_ADMIN_EMAIL),
            'target_user_id' => (int) Session::get(self::SESSION_TARGET_USER_ID),
            'expires_at' => $expiresAt,
            'remaining_seconds' => $remainingSeconds,
            'reason' => (string) Session::get(self::SESSION_REASON),
            'ticket' => Session::get(self::SESSION_TICKET),
        ];
    }
}
