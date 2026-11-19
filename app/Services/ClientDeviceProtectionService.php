<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ClientDeviceProtectionService
{
    /**
     * Tên Cookie định danh thiết bị độc lập (Machine ID).
     */
    public const COOKIE_NAME = 'mh_mid';

    /**
     * Số tài khoản tối đa được phép đăng ký trên cùng 1 máy / thiết bị.
     */
    public const MAX_ACCOUNTS_PER_DEVICE = 5;

    /**
     * Thời gian cấm đăng ký khi vượt quá số lượng tài khoản (7 ngày).
     */
    public const BAN_DURATION_DAYS = 7;

    public const BAN_DURATION_SECONDS = 7 * 86400;

    /**
     * Thời gian lưu trữ danh sách tài khoản đã đăng ký theo thiết bị (30 ngày).
     */
    public const ACCOUNTS_CACHE_TTL = 30 * 86400;

    /**
     * Phân giải hoặc cấp mới định danh thiết bị (Machine ID) duy nhất cho máy client.
     * Thứ tự ưu tiên:
     * 1. Cookie an toàn `mh_mid`
     * 2. Header `X-Device-Id` hoặc input form `_device_token` (đồng bộ từ localStorage client)
     * 3. Fallback: Hash fingerprint phần cứng & User-Agent kết hợp IP của máy
     */
    public function resolveMachineId(Request $request): string
    {
        // 1. Kiểm tra Cookie
        $cookieMid = $request->cookie(self::COOKIE_NAME);
        if ($this->isValidMachineId($cookieMid)) {
            return (string) $cookieMid;
        }

        // 2. Kiểm tra input hidden hoặc header từ client
        $inputMid = $request->input('_device_token') ?: $request->header('X-Device-Id');
        if ($this->isValidMachineId($inputMid)) {
            return (string) $inputMid;
        }

        // 3. Fallback: Sinh fingerprint kết hợp User-Agent, ngôn ngữ & IP của máy
        $ip = (string) $request->ip();
        $ua = (string) $request->userAgent();
        $lang = (string) $request->header('Accept-Language', '');

        return hash('sha256', "mh_device:{$ip}|{$ua}|{$lang}");
    }

    /**
     * Sinh Machine ID mới chuẩn UUID v4.
     */
    public function generateNewMachineId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Kiểm tra tính hợp lệ của chuỗi Machine ID (UUID v4 hoặc SHA-256 hash).
     */
    public function isValidMachineId(?string $id): bool
    {
        if (empty($id) || ! is_string($id)) {
            return false;
        }

        return (bool) preg_match('/\A[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\z/i', $id)
            || (bool) preg_match('/\A[a-f0-9]{64}\z/i', $id);
    }

    /**
     * Kiểm tra xem máy / thiết bị này có đang bị cấm đăng ký 7 ngày hay không.
     */
    public function isMachineBanned(string $machineId): bool
    {
        $cacheKey = $this->getBanCacheKey($machineId);
        $banUntil = Cache::get($cacheKey);

        if ($banUntil === null) {
            return false;
        }

        if (now()->timestamp >= (int) $banUntil) {
            Cache::forget($cacheKey);

            return false;
        }

        return true;
    }

    /**
     * Lấy thời điểm kết thúc lệnh cấm (nếu có).
     */
    public function getBanExpiresAt(string $machineId): ?Carbon
    {
        $banUntil = Cache::get($this->getBanCacheKey($machineId));
        if ($banUntil === null) {
            return null;
        }

        $carbon = Carbon::createFromTimestamp((int) $banUntil);

        return $carbon->isPast() ? null : $carbon;
    }

    /**
     * Lấy danh sách ID các tài khoản người dùng đã đăng ký thành công từ máy này.
     *
     * @return list<int>
     */
    public function getRegisteredAccountIds(string $machineId): array
    {
        $ids = Cache::get($this->getAccountsCacheKey($machineId), []);

        return is_array($ids) ? array_values(array_unique(array_filter($ids, 'is_numeric'))) : [];
    }

    /**
     * Lấy số lượng tài khoản đã đăng ký từ máy này.
     */
    public function getRegisteredCount(string $machineId): int
    {
        return count($this->getRegisteredAccountIds($machineId));
    }

    /**
     * Kiểm tra xem thiết bị này còn được phép đăng ký tài khoản mới hay không.
     */
    public function canRegister(string $machineId): bool
    {
        if ($this->isMachineBanned($machineId)) {
            return false;
        }

        if ($this->getRegisteredCount($machineId) >= self::MAX_ACCOUNTS_PER_DEVICE) {
            // Tự động kích hoạt lệnh cấm 7 ngày khi đạt ngưỡng
            $this->banMachine($machineId, self::BAN_DURATION_DAYS);

            return false;
        }

        return true;
    }

    /**
     * Ghi nhận 1 tài khoản đăng ký thành công từ thiết bị này.
     * Nếu số lượng tài khoản đạt >= 5, tự động cấm thiết bị trong vòng 7 ngày.
     */
    public function recordSuccessfulRegistration(string $machineId, int $userId): void
    {
        $accounts = $this->getRegisteredAccountIds($machineId);

        if (! in_array($userId, $accounts, true)) {
            $accounts[] = $userId;
            Cache::put($this->getAccountsCacheKey($machineId), $accounts, self::ACCOUNTS_CACHE_TTL);
        }

        if (count($accounts) >= self::MAX_ACCOUNTS_PER_DEVICE) {
            $this->banMachine($machineId, self::BAN_DURATION_DAYS);
        }
    }

    /**
     * Đặt lệnh cấm đăng ký cho thiết bị này trong N ngày (mặc định 7 ngày).
     */
    public function banMachine(string $machineId, int $days = self::BAN_DURATION_DAYS): void
    {
        $expiresAt = now()->addDays($days);
        Cache::put($this->getBanCacheKey($machineId), $expiresAt->timestamp, $days * 86400);
    }

    /**
     * Hủy lệnh cấm và reset giới hạn cho thiết bị (phục vụ test hoặc admin mở khóa).
     */
    public function unbanMachine(string $machineId): void
    {
        Cache::forget($this->getBanCacheKey($machineId));
        Cache::forget($this->getAccountsCacheKey($machineId));
    }

    /**
     * Tạo cookie an toàn để đính kèm vào HTTP Response.
     */
    public function makeCookie(string $machineId): Cookie
    {
        return cookie(
            name: self::COOKIE_NAME,
            value: $machineId,
            minutes: 525600, // 1 năm
            path: '/',
            domain: null,
            secure: request()->isSecure(),
            httpOnly: true,
            raw: false,
            sameSite: 'Lax'
        );
    }

    /**
     * Đính kèm Cookie Machine ID vào Response nếu chưa có.
     */
    public function attachCookieToResponse(Response $response, string $machineId): Response
    {
        $response->headers->setCookie($this->makeCookie($machineId));

        return $response;
    }

    protected function getBanCacheKey(string $machineId): string
    {
        return "mh_banned_machine_{$machineId}";
    }

    protected function getAccountsCacheKey(string $machineId): string
    {
        return "mh_registered_accounts_machine_{$machineId}";
    }
}
