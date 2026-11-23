<?php

declare(strict_types=1);

namespace App\Services\Chatbot;

use App\Services\ClientDeviceProtectionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiChatbotAbuseGuardService
{
    /**
     * Số lần thất bại tối đa ở 6 bước đầu tiên trước khi kích hoạt lệnh cấm 24h.
     */
    public const MAX_FAILURES_BEFORE_BAN = 5;

    /**
     * Thời gian cấm trò chuyện (24 giờ = 86400 giây).
     */
    public const BAN_DURATION_SECONDS = 86400;

    /**
     * Thời gian tồn tại của bộ đếm số lần vi phạm (24 giờ).
     */
    public const STRIKES_TTL_SECONDS = 86400;

    public const CACHE_PREFIX_BAN = 'ai_chat_abuse_ban:';

    public const CACHE_PREFIX_STRIKES = 'ai_chat_abuse_strikes:';

    public const REGISTRY_KEY = 'ai_chat_banned_devices_registry';

    public function __construct(
        protected ClientDeviceProtectionService $deviceProtection
    ) {}

    /**
     * Phân giải định danh máy kết hợp 2 yếu tố: IP mạng + Machine ID (chống chặn nhầm máy khác cùng Wi-Fi).
     *
     * @return array{ip: string, machine_id: string, device_key: string}
     */
    public function resolveIdentifier(Request $request): array
    {
        $ip = (string) $request->ip();
        $machineId = $this->deviceProtection->resolveMachineId($request);
        $deviceKey = hash('sha256', "ai_abuse:ip:{$ip}|mid:{$machineId}");

        return [
            'ip' => $ip,
            'machine_id' => $machineId,
            'device_key' => $deviceKey,
        ];
    }

    /**
     * Kiểm tra xem thiết bị này có đang bị cấm 24h hay không.
     */
    public function isBanned(string $deviceKey): bool
    {
        return $this->getBanInfo($deviceKey) !== null;
    }

    /**
     * Lấy thông tin cấm của thiết bị. Nếu hết hạn, tự động dọn dẹp cache.
     *
     * @return array{device_key: string, ip: string, machine_id: string, banned_at: int, ban_until: int, strikes: int, last_reason: string, remaining_seconds: int}|null
     */
    public function getBanInfo(string $deviceKey): ?array
    {
        $cacheKey = self::CACHE_PREFIX_BAN.$deviceKey;
        $data = Cache::get($cacheKey);

        if (! is_array($data) || empty($data['ban_until'])) {
            return null;
        }

        $now = time();
        $banUntil = (int) $data['ban_until'];

        if ($now >= $banUntil) {
            Cache::forget($cacheKey);
            Cache::forget(self::CACHE_PREFIX_STRIKES.$deviceKey);
            $this->removeFromRegistry($deviceKey);

            return null;
        }

        $data['remaining_seconds'] = max(0, $banUntil - $now);

        return $data;
    }

    /**
     * Xác định xem một kết quả trả lời chat có bị FAIL ở 6 bước đầu tiên hay không.
     * 6 bước đầu:
     * - Bước 1: Tiếp nhận đầu vào
     * - Bước 2: Bảo mật & Phân tầng (blocked_security, prompt injection, fake authorization, external url, v.v.)
     * - Bước 2.5: Chủ đề cấm (forbidden_topics / out_of_scope)
     * - Bước 3: BM25 / Tri thức (retrieval_error)
     * - Bước 4: AI Tổng hợp JSON (provider_error, malformed output)
     * - Bước 5: Critic Reviewer (critic_rejected)
     * - Bước 6: Hậu kiểm & Allowlist (output_rejected_policy_gate)
     *
     * @param  array<string, mixed>  $result
     */
    public function isStepFailure(array $result): bool
    {
        $status = (string) ($result['status'] ?? '');
        $intent = (string) ($result['intent'] ?? '');
        $mode = (string) ($result['mode'] ?? '');
        $reviewStatus = (string) ($result['review_status'] ?? '');

        // 1. Chặn bảo mật ở Bước 2
        if ($intent === 'blocked_security' || $mode === 'blocked_security') {
            return true;
        }

        // 2. Chặn chủ đề cấm ở Bước 2.5 hoặc trượt vai bị hậu kiểm loại
        if ($mode === 'out_of_scope' || $intent === 'out_of_scope') {
            return true;
        }

        // 3. Chặn hậu kiểm cổng bảo mật Bước 6
        if ($mode === 'output_rejected_policy_gate' || $reviewStatus === 'blocked_input_gate') {
            return true;
        }

        // 4. Bị Critic loại ở Bước 5
        if ($mode === 'critic_rejected') {
            return true;
        }

        // 5. Lỗi kỹ thuật / Provider lỗi ở Bước 3, 4
        if ($status === 'error' || $status === 'invalid_input' || $status === 'retrieval_error' || $status === 'provider_error' || $mode === 'error') {
            return true;
        }

        // 6. Có cờ vi phạm chính sách bảo mật
        $flags = (array) ($result['security_flags'] ?? []);
        foreach ($flags as $flag) {
            if (is_string($flag) && str_starts_with($flag, 'policy:')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ghi nhận 1 lần vi phạm / thất bại ở 6 bước đầu tiên.
     * Tự động kích hoạt cấm 24h khi đạt mốc MAX_FAILURES_BEFORE_BAN.
     *
     * @return array{banned: bool, strikes: int, remaining_strikes: int, ban_until?: int, remaining_seconds?: int}
     */
    public function recordFailure(string $ip, string $machineId, string $deviceKey, string $reason = ''): array
    {
        $strikeKey = self::CACHE_PREFIX_STRIKES.$deviceKey;
        $strikes = (int) Cache::get($strikeKey, 0) + 1;
        Cache::put($strikeKey, $strikes, self::STRIKES_TTL_SECONDS);

        if ($strikes >= self::MAX_FAILURES_BEFORE_BAN) {
            $now = time();
            $banUntil = $now + self::BAN_DURATION_SECONDS;

            $banData = [
                'device_key' => $deviceKey,
                'ip' => $ip,
                'machine_id' => $machineId,
                'banned_at' => $now,
                'ban_until' => $banUntil,
                'strikes' => $strikes,
                'last_reason' => $reason !== '' ? $reason : 'Vượt quá 5 lần vi phạm ở các bước ban đầu',
            ];

            Cache::put(self::CACHE_PREFIX_BAN.$deviceKey, $banData, self::BAN_DURATION_SECONDS);
            $this->addToRegistry($deviceKey, $banData);

            return [
                'banned' => true,
                'strikes' => $strikes,
                'remaining_strikes' => 0,
                'ban_until' => $banUntil,
                'remaining_seconds' => self::BAN_DURATION_SECONDS,
            ];
        }

        return [
            'banned' => false,
            'strikes' => $strikes,
            'remaining_strikes' => max(0, self::MAX_FAILURES_BEFORE_BAN - $strikes),
        ];
    }

    /**
     * Lấy danh sách toàn bộ thiết bị đang bị cấm (tự động dọn dẹp các mục hết hạn).
     *
     * @return list<array{device_key: string, ip: string, machine_id: string, banned_at: int, ban_until: int, strikes: int, last_reason: string, remaining_seconds: int, banned_at_formatted: string, ban_until_formatted: string}>
     */
    public function getBannedDevices(): array
    {
        $registry = Cache::get(self::REGISTRY_KEY, []);
        if (! is_array($registry)) {
            $registry = [];
        }

        $now = time();
        $cleaned = [];
        $activeList = [];

        foreach ($registry as $deviceKey => $item) {
            if (! is_array($item) || empty($item['ban_until'])) {
                continue;
            }

            $banUntil = (int) $item['ban_until'];
            if ($now >= $banUntil) {
                // Đã hết hạn -> xóa cache
                Cache::forget(self::CACHE_PREFIX_BAN.$deviceKey);
                Cache::forget(self::CACHE_PREFIX_STRIKES.$deviceKey);

                continue;
            }

            $remainingSeconds = max(0, $banUntil - $now);
            $item['device_key'] = (string) $deviceKey;
            $item['remaining_seconds'] = $remainingSeconds;
            $item['banned_at_formatted'] = Carbon::createFromTimestamp((int) ($item['banned_at'] ?? $now))->format('H:i:s d/m/Y');
            $item['ban_until_formatted'] = Carbon::createFromTimestamp($banUntil)->format('H:i:s d/m/Y');

            $cleaned[$deviceKey] = $item;
            $activeList[] = $item;
        }

        if (count($cleaned) !== count($registry)) {
            Cache::put(self::REGISTRY_KEY, $cleaned, self::BAN_DURATION_SECONDS);
        }

        // Sắp xếp thời điểm cấm mới nhất lên đầu
        usort($activeList, fn ($a, $b) => ($b['banned_at'] ?? 0) <=> ($a['banned_at'] ?? 0));

        return array_values($activeList);
    }

    /**
     * Mở khóa cho 1 thiết bị cụ thể qua Device Key, Địa chỉ IP hoặc Machine ID.
     * Trả về số lượng thiết bị được mở khóa.
     */
    public function resetDevice(string $keyOrIpOrMid): int
    {
        $target = trim($keyOrIpOrMid);
        if ($target === '') {
            return 0;
        }

        $registry = Cache::get(self::REGISTRY_KEY, []);
        if (! is_array($registry)) {
            $registry = [];
        }

        $matchedKeys = [];

        // 1. Nếu trùng khớp trực tiếp key
        if (isset($registry[$target])) {
            $matchedKeys[] = $target;
        }

        // 2. Quét tìm theo IP hoặc Machine ID
        foreach ($registry as $dKey => $item) {
            if (! is_array($item)) {
                continue;
            }
            if (($item['ip'] ?? '') === $target || ($item['machine_id'] ?? '') === $target || $dKey === $target) {
                $matchedKeys[] = (string) $dKey;
            }
        }

        $matchedKeys = array_unique($matchedKeys);

        // Trường hợp người dùng nhập key chưa có trong registry nhưng có cache ban trực tiếp
        if (empty($matchedKeys)) {
            $matchedKeys[] = $target;
        }

        $clearedCount = 0;
        foreach ($matchedKeys as $dKey) {
            $hadBan = Cache::has(self::CACHE_PREFIX_BAN.$dKey);
            $hadStrikes = Cache::has(self::CACHE_PREFIX_STRIKES.$dKey);

            Cache::forget(self::CACHE_PREFIX_BAN.$dKey);
            Cache::forget(self::CACHE_PREFIX_STRIKES.$dKey);
            unset($registry[$dKey]);

            if ($hadBan || $hadStrikes) {
                $clearedCount++;
            }
        }

        Cache::put(self::REGISTRY_KEY, $registry, self::BAN_DURATION_SECONDS);

        return max($clearedCount, count($matchedKeys));
    }

    /**
     * Mở khóa toàn bộ thiết bị đang bị tạm dừng (Reset All).
     * Phục vụ quản trị viên kiểm thử hoặc khôi phục quyền truy cập diện rộng.
     */
    public function resetAll(): int
    {
        $registry = Cache::get(self::REGISTRY_KEY, []);
        if (! is_array($registry)) {
            $registry = [];
        }

        $count = count($registry);

        foreach (array_keys($registry) as $deviceKey) {
            Cache::forget(self::CACHE_PREFIX_BAN.$deviceKey);
            Cache::forget(self::CACHE_PREFIX_STRIKES.$deviceKey);
        }

        Cache::forget(self::REGISTRY_KEY);

        return $count;
    }

    /**
     * Sinh HTTP Response 429 thông báo khóa 24h cho máy client.
     *
     * @param  array<string, mixed>  $banInfo
     */
    public function makeBanResponse(array $banInfo): JsonResponse
    {
        $remaining = (int) ($banInfo['remaining_seconds'] ?? self::BAN_DURATION_SECONDS);
        $hours = ceil($remaining / 3600);

        return response()->json([
            'ok' => false,
            'banned' => true,
            'message' => "Bạn đã bị tạm khóa do vi phạm tiêu chuẩn cộng đồng của chúng tôi! Vui lòng chờ!. Thời gian còn lại: khoảng {$hours} giờ.",
            'remaining_seconds' => $remaining,
            'ban_until' => (int) ($banInfo['ban_until'] ?? (time() + $remaining)),
        ], 429);
    }

    /**
     * Thêm thông tin thiết bị bị cấm vào Registry tập trung.
     *
     * @param  array<string, mixed>  $banData
     */
    protected function addToRegistry(string $deviceKey, array $banData): void
    {
        $registry = Cache::get(self::REGISTRY_KEY, []);
        if (! is_array($registry)) {
            $registry = [];
        }

        $registry[$deviceKey] = $banData;
        Cache::put(self::REGISTRY_KEY, $registry, self::BAN_DURATION_SECONDS);
    }

    /**
     * Xóa 1 thiết bị khỏi Registry tập trung.
     */
    protected function removeFromRegistry(string $deviceKey): void
    {
        $registry = Cache::get(self::REGISTRY_KEY, []);
        if (is_array($registry) && isset($registry[$deviceKey])) {
            unset($registry[$deviceKey]);
            Cache::put(self::REGISTRY_KEY, $registry, self::BAN_DURATION_SECONDS);
        }
    }
}
