<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\PrivacyErasureRequest;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrivacyRetentionService
{
    /**
     * Ngưỡng mặc định dọn dẹp và ẩn danh hóa dữ liệu khách mời sau ngày cưới (180 ngày).
     */
    public const DEFAULT_RETENTION_DAYS = 180;

    /**
     * Quét các đám cưới đã quá hạn lưu trữ cần ẩn danh hóa PII khách mời.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function scanRetentionEligibleWeddings(int $daysThreshold = self::DEFAULT_RETENTION_DAYS): Collection
    {
        $cutoffDate = Carbon::today()->subDays($daysThreshold);

        $weddings = Wedding::with('user')
            ->whereNotNull('wedding_date')
            ->where('wedding_date', '<=', $cutoffDate)
            ->latest('wedding_date')
            ->get();

        return $weddings->filter(function (Wedding $wedding) {
            $extra = $wedding->extra_data ?? [];

            return empty($extra['guest_pii_anonymized_at']);
        })->map(function (Wedding $wedding) {
            $guestsPhoneCount = Guest::where('wedding_id', $wedding->id)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->count();

            $responsesPiiCount = GuestResponse::where('wedding_id', $wedding->id)
                ->where(function ($q) {
                    $q->whereNotNull('phone')
                        ->orWhere('dietary_requirements', '!=', '[ANONYMIZED]')
                        ->orWhere('ip_address', '!=', '127.0.0.1');
                })
                ->count();

            $daysSinceWedding = (int) $wedding->wedding_date->diffInDays(now());

            return [
                'wedding_id' => $wedding->id,
                'wedding_name' => "{$wedding->groom_name} & {$wedding->bride_name}",
                'wedding_date' => $wedding->wedding_date->format('d/m/Y'),
                'days_since_wedding' => $daysSinceWedding,
                'user_name' => $wedding->user?->name ?? 'N/A',
                'user_email' => $wedding->user?->email ?? 'N/A',
                'guests_with_phone_count' => $guestsPhoneCount,
                'responses_pii_count' => $responsesPiiCount,
                'total_pii_records' => $guestsPhoneCount + $responsesPiiCount,
            ];
        })->values();
    }

    /**
     * Ẩn danh hóa dữ liệu khách mời của một đám cưới (Hỗ trợ Dry-Run an toàn).
     *
     * @return array<string, mixed>
     */
    public static function anonymizeWeddingGuestPii(Wedding $wedding, bool $dryRun = false): array
    {
        $guestsWithPhone = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        $responsesWithPii = GuestResponse::where('wedding_id', $wedding->id);

        $guestsPhoneCount = $guestsWithPhone->count();
        $responsesPiiCount = $responsesWithPii->count();

        if ($dryRun) {
            return [
                'dry_run' => true,
                'wedding_id' => $wedding->id,
                'guests_anonymized_count' => $guestsPhoneCount,
                'responses_anonymized_count' => $responsesPiiCount,
                'accounting_preserved' => true,
            ];
        }

        return DB::transaction(function () use ($wedding, $guestsPhoneCount, $responsesPiiCount) {
            // 1. Ẩn số điện thoại trong danh bạ khách
            Guest::where('wedding_id', $wedding->id)->update([
                'phone' => null,
            ]);

            // 2. Ẩn thông tin nhạy cảm trong phản hồi RSVP
            GuestResponse::where('wedding_id', $wedding->id)->update([
                'phone' => null,
                'dietary_requirements' => '[ANONYMIZED]',
                'ip_address' => '127.0.0.1',
            ]);

            // 3. Đánh dấu metadata hoàn tất vào wedding
            $extra = $wedding->extra_data ?? [];
            $extra['guest_pii_anonymized_at'] = now()->toIso8601String();
            $extra['guest_pii_anonymized_records'] = [
                'guests' => $guestsPhoneCount,
                'responses' => $responsesPiiCount,
            ];
            $wedding->extra_data = $extra;
            $wedding->save();

            AuditLogger::log(
                action: 'privacy.retention_guest_pii_anonymized',
                targetType: 'wedding',
                targetId: $wedding->id,
                description: "Ẩn danh hóa dữ liệu khách mời đám cưới #{$wedding->id} theo chính sách lưu trữ 180 ngày",
                details: [
                    'wedding_id' => $wedding->id,
                    'guests_anonymized_count' => $guestsPhoneCount,
                    'responses_anonymized_count' => $responsesPiiCount,
                    'accounting_preserved' => true,
                ]
            );

            return [
                'dry_run' => false,
                'wedding_id' => $wedding->id,
                'guests_anonymized_count' => $guestsPhoneCount,
                'responses_anonymized_count' => $responsesPiiCount,
                'accounting_preserved' => true,
                'anonymized_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Thực thi yêu cầu quên / ẩn danh hóa toàn bộ tài khoản người dùng theo DSAR Request.
     * TUYỆT ĐỐI BẢO TOÀN 100% CHỨNG TỪ KẾ TOÁN (ORDERS).
     *
     * @return array<string, mixed>
     */
    public static function anonymizeUserAccount(User $user, PrivacyErasureRequest $request): array
    {
        return DB::transaction(function () use ($user, $request) {
            $totalWeddings = $user->weddings()->count();
            $totalGuestsAnonymized = 0;
            $totalResponsesAnonymized = 0;

            // 1. Ẩn danh hóa toàn bộ thiệp cưới của người dùng
            foreach ($user->weddings as $wedding) {
                $res = self::anonymizeWeddingGuestPii($wedding, false);
                $totalGuestsAnonymized += $res['guests_anonymized_count'];
                $totalResponsesAnonymized += $res['responses_anonymized_count'];
            }

            // 2. Bảo toàn hồ sơ kế toán (Orders): kiểm tra số lượng
            $ordersPreservedCount = $user->orders()->count();

            // 3. Ẩn danh hóa hồ sơ cá nhân của user
            $anonymizedEmail = 'anonymized_'.$user->id.'_'.Str::lower(Str::random(6)).'@deleted.mayhen.vn';
            $user->update([
                'name' => '[Tài khoản đã ẩn danh]',
                'email' => $anonymizedEmail,
                'phone' => null,
                'is_locked' => true,
                'lock_reason' => 'Đã thực thi quyền lãng quên (DSAR Erasure Request #'.$request->request_code.')',
            ]);

            // 4. Cập nhật trạng thái yêu cầu
            $request->update([
                'status' => 'executed',
                'executed_at' => now(),
                'affected_records_count' => [
                    'weddings' => $totalWeddings,
                    'guests' => $totalGuestsAnonymized,
                    'responses' => $totalResponsesAnonymized,
                    'orders_preserved' => $ordersPreservedCount,
                ],
                'accounting_records_preserved' => true,
            ]);

            AuditLogger::log(
                action: 'privacy.dsar_user_account_anonymized',
                targetType: 'user',
                targetId: $user->id,
                description: "Thực thi ẩn danh hóa tài khoản người dùng #{$user->id} theo yêu cầu DSAR {$request->request_code}. Bảo toàn {$ordersPreservedCount} chứng từ kế toán.",
                details: [
                    'request_code' => $request->request_code,
                    'user_id' => $user->id,
                    'orders_preserved_count' => $ordersPreservedCount,
                    'weddings_count' => $totalWeddings,
                ]
            );

            return [
                'user_id' => $user->id,
                'request_code' => $request->request_code,
                'weddings_anonymized' => $totalWeddings,
                'guests_anonymized' => $totalGuestsAnonymized,
                'orders_preserved' => $ordersPreservedCount,
            ];
        });
    }

    /**
     * Tạo Secure Download Token cho file xuất dữ liệu DSAR có thời hạn và số lần tải giới hạn.
     *
     * @return array{token: string, expires_at: Carbon, download_url: string}
     */
    public static function generateSecureDownloadToken(User $user, int $ttlHours = 24): array
    {
        $token = 'dsar_tok_'.Str::random(40);
        $expiresAt = now()->addHours($ttlHours);

        $cacheData = [
            'user_id' => $user->id,
            'expires_at' => $expiresAt->toIso8601String(),
            'max_downloads' => 3,
            'downloads_count' => 0,
        ];

        Cache::put("dsar_download_token_{$token}", $cacheData, $expiresAt);

        return [
            'token' => $token,
            'expires_at' => $expiresAt,
            'download_url' => route('admin.privacy.download_dsar_export', ['token' => $token]),
        ];
    }

    /**
     * Xác minh và tiêu thụ một lần tải của Secure Download Token.
     */
    public static function verifyAndConsumeDownloadToken(string $token): ?int
    {
        $cacheKey = "dsar_download_token_{$token}";
        $data = Cache::get($cacheKey);

        if (! $data) {
            return null;
        }

        $expiresAt = Carbon::parse($data['expires_at']);
        if ($expiresAt->isPast()) {
            Cache::forget($cacheKey);

            return null;
        }

        if (($data['downloads_count'] ?? 0) >= ($data['max_downloads'] ?? 3)) {
            Cache::forget($cacheKey);

            return null;
        }

        $data['downloads_count'] = ($data['downloads_count'] ?? 0) + 1;
        Cache::put($cacheKey, $data, $expiresAt);

        return (int) $data['user_id'];
    }
}
