<?php

namespace App\Services;

use App\Mail\WeddingAnniversaryCongratulationMail;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class WeddingAnniversaryService
{
    /**
     * Bảng tên gọi các mốc kỷ niệm ngày cưới truyền thống & hiện đại.
     *
     * @var array<int, string>
     */
    public const MILESTONE_NAMES = [
        1 => 'Đám Cưới Giấy (Paper Wedding)',
        2 => 'Đám Cưới Bông (Cotton Wedding)',
        3 => 'Đám Cưới Da (Leather Wedding)',
        4 => 'Đám Cưới Hoa Quả & Sách (Fruit/Book Wedding)',
        5 => 'Đám Cưới Gỗ (Wood Wedding)',
        10 => 'Đám Cưới Thiếc (Tin Wedding)',
        15 => 'Đám Cưới Pha Lê (Crystal Wedding)',
        20 => 'Đám Cưới Sứ (China Wedding)',
        25 => 'Đám Cưới Bạc (Silver Wedding)',
        30 => 'Đám Cưới Ngọc Trai (Pearl Wedding)',
        40 => 'Đám Cưới Hồng Ngọc (Ruby Wedding)',
        50 => 'Đám Cưới Vàng (Golden Wedding)',
        60 => 'Đám Cưới Kim Cương (Diamond Wedding)',
    ];

    /**
     * Lấy tên gọi trang trọng của mốc kỷ niệm ngày cưới.
     */
    public static function getMilestoneName(int $years): string
    {
        return self::MILESTONE_NAMES[$years] ?? "Kỷ Niệm {$years} Năm Ngày Cưới";
    }

    /**
     * Lấy cấu hình Nhắc Hẹn Kỷ Niệm Ngày Cưới của một đám cưới.
     *
     * @return array{
     *     enable_reminders: bool,
     *     notification_email_1: string,
     *     notification_email_2: string,
     *     anniversary_photo_url: string,
     *     anniversary_reflection_note: string,
     *     one_year_moments: array<int, string>,
     *     last_reminded_year: int|null,
     *     last_reminded_at: string|null
     * }
     */
    public static function getConfig(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $invitation = $invitation ?: $wedding->invitations()->first();
        $customConfig = [];
        if ($invitation) {
            $customConfig = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);
        }

        $config = $customConfig['anniversary'] ?? [];

        $defaultEmail1 = $wedding->user?->email ?? '';

        return [
            'enable_reminders' => (bool) ($config['enable_reminders'] ?? true),
            'notification_email_1' => (string) ($config['notification_email_1'] ?? $defaultEmail1),
            'notification_email_2' => (string) ($config['notification_email_2'] ?? ''),
            'anniversary_photo_url' => (string) ($config['anniversary_photo_url'] ?? ''),
            'anniversary_reflection_note' => (string) ($config['anniversary_reflection_note'] ?? 'Tròn một năm về chung một mái nhà, cảm ơn vì đã luôn đồng hành, thấu hiểu và cùng nhau xây dựng tổ ấm bình yên.'),
            'one_year_moments' => ! empty($config['one_year_moments']) && is_array($config['one_year_moments']) ? $config['one_year_moments'] : [],
            'last_reminded_year' => isset($config['last_reminded_year']) ? (int) $config['last_reminded_year'] : null,
            'last_reminded_at' => $config['last_reminded_at'] ?? null,
        ];
    }

    /**
     * Cập nhật cấu hình Nhắc Hẹn Kỷ Niệm Ngày Cưới từ Studio.
     */
    public static function updateConfig(Wedding $wedding, array $data, ?Invitation $invitation = null, ?User $user = null): array
    {
        $invitation = $invitation ?: $wedding->invitations()->first();
        if (! $invitation) {
            return [];
        }

        $customConfig = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $current = self::getConfig($wedding, $invitation);

        $current['enable_reminders'] = isset($data['enable_reminders']) ? (bool) $data['enable_reminders'] : false;
        if (! empty($data['notification_email_1'])) {
            $current['notification_email_1'] = trim((string) $data['notification_email_1']);
        }
        if (isset($data['notification_email_2'])) {
            $current['notification_email_2'] = trim((string) $data['notification_email_2']);
        }
        if (isset($data['anniversary_photo_url'])) {
            $current['anniversary_photo_url'] = trim((string) $data['anniversary_photo_url']);
        }
        if (isset($data['anniversary_reflection_note'])) {
            $current['anniversary_reflection_note'] = trim((string) $data['anniversary_reflection_note']);
        }
        if (isset($data['one_year_moments']) && is_array($data['one_year_moments'])) {
            $current['one_year_moments'] = array_values(array_filter(array_map('trim', $data['one_year_moments'])));
        }

        $customConfig['anniversary'] = $current;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        AuditLogger::log(
            'ANNIVERSARY_CONFIG_UPDATED',
            'Wedding',
            $wedding->id,
            "Cập nhật cấu hình Nhắc Hẹn Kỷ Niệm Ngày Cưới thiệp #{$invitation->id}"
        );

        return $current;
    }

    /**
     * Tính toán thống kê dòng thời gian hôn nhân & mốc kỷ niệm tiếp theo.
     *
     * @return array{
     *     wedding_date: string,
     *     days_married: int,
     *     months_married: int,
     *     years_married: int,
     *     is_anniversary_today: bool,
     *     current_milestone_name: string,
     *     next_anniversary_year: int,
     *     next_anniversary_date: string,
     *     days_until_next_anniversary: int,
     *     next_milestone_name: string
     * }
     */
    public static function calculateTimelineStats(Wedding $wedding, ?Carbon $now = null): array
    {
        $now = $now ?: now();
        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : $now->copy();

        $daysMarried = max(0, $weddingDate->diffInDays($now));
        $monthsMarried = max(0, $weddingDate->diffInMonths($now));
        $yearsMarried = max(0, (int) $weddingDate->diffInYears($now));

        $isAnniversaryToday = ($weddingDate->month === $now->month && $weddingDate->day === $now->day && $daysMarried >= 300);

        $nextAnniversaryYear = $yearsMarried + 1;
        $nextAnniversaryDate = $weddingDate->copy()->year($now->year);
        if ($nextAnniversaryDate->isPast() && ! $isAnniversaryToday) {
            $nextAnniversaryDate->addYear();
        }

        $daysUntilNext = max(0, $now->diffInDays($nextAnniversaryDate, false));

        return [
            'wedding_date' => $weddingDate->format('d/m/Y'),
            'days_married' => (int) $daysMarried,
            'months_married' => (int) $monthsMarried,
            'years_married' => $yearsMarried,
            'is_anniversary_today' => $isAnniversaryToday,
            'current_milestone_name' => self::getMilestoneName(max(1, $yearsMarried)),
            'next_anniversary_year' => $nextAnniversaryYear,
            'next_anniversary_date' => $nextAnniversaryDate->format('d/m/Y'),
            'days_until_next_anniversary' => (int) $daysUntilNext,
            'next_milestone_name' => self::getMilestoneName($nextAnniversaryYear),
        ];
    }

    /**
     * Quét và gửi thư chúc mừng kỷ niệm ngày cưới tới các cặp đôi đủ điều kiện hôm nay.
     *
     * @return array{
     *     total_eligible: int,
     *     total_dispatched: int,
     *     wedding_ids: array<int, int>
     * }
     */
    public static function dispatchAnniversaryReminders(?Carbon $targetDate = null): array
    {
        $targetDate = $targetDate ?: now();
        $weddings = Wedding::with(['user', 'invitations'])
            ->whereNotNull('wedding_date')
            ->whereMonth('wedding_date', $targetDate->month)
            ->whereDay('wedding_date', $targetDate->day)
            ->get();

        $eligibleCount = 0;
        $dispatchedCount = 0;
        $dispatchedWeddingIds = [];

        foreach ($weddings as $wedding) {
            $invitation = $wedding->invitations()->first();
            if (! $invitation) {
                continue;
            }

            $weddingDate = Carbon::parse($wedding->wedding_date);
            $yearsMarried = (int) $weddingDate->diffInYears($targetDate);

            // Chỉ gửi khi đã tròn ít nhất 1 năm ngày cưới
            if ($yearsMarried < 1) {
                continue;
            }

            $config = self::getConfig($wedding, $invitation);
            if (! $config['enable_reminders']) {
                continue;
            }

            // Tránh gửi lặp lại trong cùng 1 năm
            if ($config['last_reminded_year'] === $yearsMarried) {
                continue;
            }

            $eligibleCount++;
            $milestoneName = self::getMilestoneName($yearsMarried);

            // Gửi email chúc mừng
            $emails = array_filter([$config['notification_email_1'], $config['notification_email_2']]);
            if (! empty($emails)) {
                foreach ($emails as $email) {
                    try {
                        Mail::to($email)->queue(
                            new WeddingAnniversaryCongratulationMail(
                                $wedding,
                                $invitation,
                                $yearsMarried,
                                $milestoneName
                            )
                        );
                    } catch (\Throwable $e) {
                        // Tiếp tục xử lý
                    }
                }
            }

            // Ghi nhận đã gửi nhắc hẹn năm nay
            $customConfig = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);

            $customConfig['anniversary']['last_reminded_year'] = $yearsMarried;
            $customConfig['anniversary']['last_reminded_at'] = $targetDate->toIso8601String();
            $invitation->custom_config = $customConfig;
            $invitation->save();

            AuditLogger::log(
                'ANNIVERSARY_REMINDER_DISPATCHED',
                'Wedding',
                $wedding->id,
                "Đã gửi email chúc mừng Kỷ Niệm {$yearsMarried} Năm Ngày Cưới ({$milestoneName}) cho cặp đôi '{$wedding->groom_name} & {$wedding->bride_name}'"
            );

            $dispatchedCount++;
            $dispatchedWeddingIds[] = $wedding->id;
        }

        return [
            'total_eligible' => $eligibleCount,
            'total_dispatched' => $dispatchedCount,
            'wedding_ids' => $dispatchedWeddingIds,
        ];
    }

    /**
     * Chuẩn bị toàn bộ dữ liệu phục vụ hiển thị Trang Kỷ Niệm Ngày Cưới (Anniversary Lounge).
     */
    public static function getAnniversaryLoungeData(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $stats = $wedding ? self::calculateTimelineStats($wedding) : [];
        $config = $wedding ? self::getConfig($wedding, $invitation) : [];

        $wishes = [];
        $totalWishes = 0;
        if ($wedding) {
            $wishesQuery = $wedding->wishes()->where('is_approved', true);
            $totalWishes = $wishesQuery->count();
            $wishes = $wishesQuery->latest()->limit(12)->get();
        }

        return [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'stats' => $stats,
            'config' => $config,
            'wishes' => $wishes,
            'total_wishes' => $totalWishes,
        ];
    }
}
