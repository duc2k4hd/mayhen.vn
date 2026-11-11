<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\PlatformSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvitationRenewalService
{
    public const STATUS_LIFETIME = 'lifetime';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRING_SOON = 'expiring_soon';

    public const STATUS_IN_GRACE_PERIOD = 'in_grace_period';

    public const STATUS_ARCHIVED = 'archived';

    public function __construct(
        protected EmailTemplateService $emailTemplateService
    ) {}

    /**
     * Lấy số ngày ân hạn cấu hình từ hệ thống (Mục 13.4 DOCX & Dòng 89 PRD V3).
     */
    public function getGraceDays(): int
    {
        $setting = PlatformSetting::where('group', PlatformSetting::GROUP_SECURITY)
            ->where('key', 'grace_period_days')
            ->first();

        return (int) ($setting?->value ?? 7);
    }

    /**
     * Xác định trạng thái vòng đời hạn dùng của thiệp cưới.
     *
     * @return array{status: string, label: string, color: string, days_diff: int, is_grace: bool}
     */
    public function getLifecycleStatus(Invitation $invitation): array
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        // 1. Kiểm tra nếu bị chủ động đánh dấu lưu trữ
        if (! empty($config['is_archived'])) {
            return [
                'status' => self::STATUS_ARCHIVED,
                'label' => 'Đã Lưu Trữ Kỷ Niệm',
                'color' => '#5C5446',
                'days_diff' => 0,
                'is_grace' => false,
            ];
        }

        // 2. Lưu trữ trọn đời (Vĩnh cửu)
        if ($invitation->expires_at === null) {
            return [
                'status' => self::STATUS_LIFETIME,
                'label' => 'Lưu Trữ Trọn Đời (VIP)',
                'color' => '#8C6B37',
                'days_diff' => 99999,
                'is_grace' => false,
            ];
        }

        $now = Carbon::now();
        $expiresAt = Carbon::parse($invitation->expires_at);
        $graceDays = $this->getGraceDays();
        $graceEnd = (clone $expiresAt)->addDays($graceDays);

        // 3. Đã quá thời gian ân hạn -> Tự động chuyển sang Archived
        if ($now->greaterThan($graceEnd)) {
            return [
                'status' => self::STATUS_ARCHIVED,
                'label' => 'Hết Hạn & Lưu Trữ',
                'color' => '#B71C1C',
                'days_diff' => (int) $expiresAt->diffInDays($now, false),
                'is_grace' => false,
            ];
        }

        // 4. Đang trong thời gian ân hạn (Grace Period)
        if ($now->greaterThanOrEqualTo($expiresAt) && $now->lessThanOrEqualTo($graceEnd)) {
            $remainingGrace = (int) $now->diffInDays($graceEnd, false);

            return [
                'status' => self::STATUS_IN_GRACE_PERIOD,
                'label' => "Đang Ân Hạn (Còn {$remainingGrace} ngày)",
                'color' => '#E65100',
                'days_diff' => -$remainingGrace,
                'is_grace' => true,
            ];
        }

        // 5. Sắp hết hạn trong vòng 14 ngày
        $daysUntilExpire = (int) $now->diffInDays($expiresAt, false);
        if ($daysUntilExpire <= 14) {
            return [
                'status' => self::STATUS_EXPIRING_SOON,
                'label' => "Sắp Hết Hạn ({$daysUntilExpire} ngày)",
                'color' => '#F57F17',
                'days_diff' => $daysUntilExpire,
                'is_grace' => false,
            ];
        }

        // 6. Đang hoạt động bình thường
        return [
            'status' => self::STATUS_ACTIVE,
            'label' => 'Đang Hoạt Động',
            'color' => '#2E7D32',
            'days_diff' => $daysUntilExpire,
            'is_grace' => false,
        ];
    }

    /**
     * Thống kê 4 chỉ số KPI về hạn dùng thiệp cưới.
     *
     * @return array{total: int, expiring_soon: int, in_grace: int, archived: int, lifetime: int}
     */
    public function getRenewalKpis(): array
    {
        $all = Invitation::all();
        $graceDays = $this->getGraceDays();

        $kpis = [
            'total' => $all->count(),
            'expiring_soon' => 0,
            'in_grace' => 0,
            'archived' => 0,
            'lifetime' => 0,
        ];

        foreach ($all as $inv) {
            $lifecycle = $this->getLifecycleStatus($inv);
            match ($lifecycle['status']) {
                self::STATUS_EXPIRING_SOON => $kpis['expiring_soon']++,
                self::STATUS_IN_GRACE_PERIOD => $kpis['in_grace']++,
                self::STATUS_ARCHIVED => $kpis['archived']++,
                self::STATUS_LIFETIME => $kpis['lifetime']++,
                default => null,
            };
        }

        return $kpis;
    }

    /**
     * Lấy danh sách thiệp cưới có phân trang và lọc theo trạng thái hạn dùng.
     */
    public function getFilteredInvitations(Request $request): LengthAwarePaginator
    {
        $query = Invitation::with(['wedding.user', 'template'])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $keyword = trim((string) $request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('slug', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lấy tất cả và phân loại theo trạng thái nếu có filter
        $statusFilter = $request->input('status', 'all');

        $paginator = $query->paginate(15);

        // Gắn kèm thông tin lifecycle vào từng bản ghi
        $paginator->getCollection()->transform(function ($invitation) {
            $invitation->lifecycle = $this->getLifecycleStatus($invitation);

            return $invitation;
        });

        // Nếu có lọc status cụ thể
        if ($statusFilter !== 'all') {
            $filtered = $paginator->getCollection()->filter(function ($inv) use ($statusFilter) {
                return $inv->lifecycle['status'] === $statusFilter;
            });
            $paginator->setCollection($filtered);
        }

        return $paginator;
    }

    /**
     * Gia hạn thời hạn lưu trữ thiệp cưới (Mục 13.4 DOCX).
     */
    public function extendDuration(Invitation $invitation, string $duration, string $reason = '', ?User $admin = null): Invitation
    {
        $currentExpires = $invitation->expires_at ? Carbon::parse($invitation->expires_at) : Carbon::now();
        $baseTime = $currentExpires->isPast() ? Carbon::now() : $currentExpires;

        $newExpires = match ($duration) {
            '1_month' => (clone $baseTime)->addDays(30),
            '3_months' => (clone $baseTime)->addDays(90),
            '6_months' => (clone $baseTime)->addDays(180),
            '1_year' => (clone $baseTime)->addYear(),
            'lifetime' => null,
            default => (clone $baseTime)->addDays(30),
        };

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        unset($config['is_archived']);
        $config['last_extended_at'] = now()->toIso8601String();
        $config['extension_reason'] = $reason ?: 'Gia hạn dịch vụ bởi Quản trị viên';

        $invitation->expires_at = $newExpires;
        $invitation->custom_config = $config;
        $invitation->is_published = true;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_EXTENDED',
            Invitation::class,
            $invitation->id,
            "Gia hạn thiệp cưới [{$invitation->slug}] thêm [{$duration}] (Hạn mới: ".($newExpires ? $newExpires->format('d/m/Y') : 'Trọn đời').')',
            [
                'duration' => $duration,
                'old_expires_at' => $currentExpires->toIso8601String(),
                'new_expires_at' => $newExpires?->toIso8601String(),
                'reason' => $reason,
                'admin_id' => $admin?->id ?? auth()->id(),
            ]
        );

        return $invitation;
    }

    /**
     * Gửi email nhắc nhở gia hạn thiệp cưới tới cặp đôi.
     *
     * @return array{success: bool, message: string}
     */
    public function sendRenewalReminder(Invitation $invitation, ?User $admin = null): array
    {
        $owner = $invitation->wedding?->user;
        if (! $owner || empty($owner->email)) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy địa chỉ email của chủ sở hữu thiệp cưới.',
            ];
        }

        $groomName = $invitation->wedding->groom_name ?? 'Chú Rể';
        $brideName = $invitation->wedding->bride_name ?? 'Cô Dâu';
        $expiresStr = $invitation->expires_at ? Carbon::parse($invitation->expires_at)->format('d/m/Y') : 'Sắp tới';

        $renewalUrl = url("/studio/{$invitation->id}/nang-cap");

        // Sử dụng EmailTemplateService để dispatch test email an toàn
        $template = $this->emailTemplateService->getTemplateByCode('renewal_reminder');

        if ($template) {
            $tokens = [
                'groom_name' => $groomName,
                'bride_name' => $brideName,
                'expires_at' => $expiresStr,
                'renewal_url' => $renewalUrl,
                'invitation_title' => $invitation->title,
            ];
            $this->emailTemplateService->sendTestEmail($template, $owner->email, $tokens);
        }

        AuditLogger::log(
            'RENEWAL_REMINDER_DISPATCHED',
            Invitation::class,
            $invitation->id,
            "Gửi email nhắc hạn thiệp cưới [{$invitation->slug}] tới [{$owner->email}]",
            ['owner_email' => $owner->email, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return [
            'success' => true,
            'message' => "Đã gửi thông báo nhắc gia hạn tới email [{$owner->email}] thành công.",
        ];
    }

    /**
     * Chuyển thiệp cưới sang chế độ lưu trữ kỷ niệm (Archive).
     */
    public function archiveInvitation(Invitation $invitation, string $reason = '', ?User $admin = null): bool
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $config['is_archived'] = true;
        $config['archived_at'] = now()->toIso8601String();
        $config['archive_reason'] = $reason ?: 'Hết hạn lưu trữ & chuyển sang kho kỷ niệm';

        $invitation->custom_config = $config;
        $invitation->is_published = false;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_ARCHIVED',
            Invitation::class,
            $invitation->id,
            "Chuyển thiệp cưới [{$invitation->slug}] sang trạng thái Lưu Trữ Kỷ Niệm (Archived)",
            ['reason' => $reason, 'admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Khôi phục thiệp cưới từ trạng thái lưu trữ (Unarchive).
     */
    public function unarchiveInvitation(Invitation $invitation, ?User $admin = null): bool
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        unset($config['is_archived']);
        $config['unarchived_at'] = now()->toIso8601String();

        // Nếu đã hết hạn thì cộng thêm 30 ngày để khách có thể sử dụng
        if ($invitation->expires_at && Carbon::parse($invitation->expires_at)->isPast()) {
            $invitation->expires_at = Carbon::now()->addDays(30);
        }

        $invitation->custom_config = $config;
        $invitation->is_published = true;
        $invitation->save();

        AuditLogger::log(
            'INVITATION_UNARCHIVED',
            Invitation::class,
            $invitation->id,
            "Khôi phục thiệp cưới [{$invitation->slug}] khỏi trạng thái Lưu Trữ",
            ['admin_id' => $admin?->id ?? auth()->id()]
        );

        return true;
    }

    /**
     * Xuất danh sách theo dõi gia hạn thiệp cưới ra tệp CSV (UTF-8 BOM).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $invitations = Invitation::with(['wedding.user', 'template'])->get();
        $filename = 'mayhen_invitation_renewals_'.date('Ymd_His').'.csv';

        AuditLogger::log(
            'RENEWALS_EXPORTED',
            Invitation::class,
            0,
            "Xuất danh sách theo dõi gia hạn ({$invitations->count()} thiệp) ra tệp CSV",
            ['count' => $invitations->count(), 'admin_id' => auth()->id()]
        );

        return response()->streamDownload(function () use ($invitations) {
            $handle = fopen('php://output', 'w');
            // Thêm UTF-8 BOM để Excel hiển thị đúng dấu tiếng Việt
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Slug',
                'Tiêu Đề',
                'Cô Dâu',
                'Chú Rể',
                'Email Chủ Thiệp',
                'Mẫu Thiệp',
                'Ngày Xuất Bản',
                'Ngày Hết Hạn',
                'Trạng Thái Hạn Dùng',
                'Số Ngày Còn Lại',
            ]);

            foreach ($invitations as $inv) {
                $lifecycle = $this->getLifecycleStatus($inv);
                fputcsv($handle, [
                    $inv->id,
                    $inv->slug,
                    $inv->title,
                    $inv->wedding->bride_name ?? '',
                    $inv->wedding->groom_name ?? '',
                    $inv->wedding->user->email ?? '',
                    $inv->template->name ?? '',
                    $inv->published_at ? Carbon::parse($inv->published_at)->format('d/m/Y H:i') : '',
                    $inv->expires_at ? Carbon::parse($inv->expires_at)->format('d/m/Y') : 'Trọn đời',
                    $lifecycle['label'],
                    $lifecycle['days_diff'] === 99999 ? 'Vĩnh viễn' : $lifecycle['days_diff'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
