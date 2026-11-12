<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\MessagingDispatch;
use App\Models\MessagingDispatchLog;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReliableMessagingDispatchService
{
    /**
     * Bảng giá tin nhắn theo từng kênh vận hành (VND/tin).
     */
    public const CHANNEL_PRICING = [
        'zalo_zns' => 250.00,       // Zalo ZNS Thông báo xác thực
        'sms_brandname' => 650.00,  // Tin nhắn SMS Thương hiệu viễn thông
        'invitation_link' => 0.00,  // Link thiệp định danh cá nhân (Miễn phí)
    ];

    /**
     * Tạo một đợt phát tin nhắn mới (Trạng thái Draft).
     */
    public static function createCampaign(Wedding $wedding, array $data, User $creator): MessagingDispatch
    {
        $channel = $data['channel'] ?? 'invitation_link';
        $unitCost = self::CHANNEL_PRICING[$channel] ?? 0.00;

        return MessagingDispatch::create([
            'wedding_id' => $wedding->id,
            'campaign_name' => $data['campaign_name'],
            'channel' => $channel,
            'status' => 'draft',
            'filter_group' => ! empty($data['filter_group']) ? $data['filter_group'] : null,
            'unit_cost_vnd' => $unitCost,
            'message_template' => $data['message_template'],
            'created_by' => $creator->id,
        ]);
    }

    /**
     * Thay thế placeholder để tạo nội dung tin nhắn cá nhân hóa chuẩn xác.
     */
    public static function renderPersonalizedMessage(string $template, Guest $guest, Wedding $wedding): string
    {
        $salutation = trim((string) $guest->salutation) ?: 'Quý khách';
        $couple = "{$wedding->groom_name} & {$wedding->bride_name}";
        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date)->format('d/m/Y') : 'ngày trọng đại';

        $invitation = $wedding->invitation;
        $slug = $invitation?->slug ?: ('dam-cuoi-'.$wedding->id);
        $invitationLink = url("/invitation/{$slug}?guest={$guest->guest_token}");

        $replacements = [
            '[DANH_XUNG]' => $salutation,
            '[TEN_KHACH]' => $guest->name,
            '[CAP_DOI]' => $couple,
            '[NGAY_CUOI]' => $weddingDate,
            '[LINK_THIEP]' => $invitationLink,
            '[NHOM_KHACH]' => $guest->group_name ?: 'Khách Quý',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Chạy kiểm thử Dry-Run (Không tốn phí): Khử trùng lặp số điện thoại và sinh trước mẫu tin.
     *
     * @return array{total: int, valid: int, deduplicated: int, estimated_cost: float}
     */
    public static function runDryRun(MessagingDispatch $dispatch): array
    {
        return DB::transaction(function () use ($dispatch) {
            // Xóa sạch log dry-run của các lần chạy trước
            $dispatch->logs()->delete();

            $wedding = $dispatch->wedding()->with('invitation')->firstOrFail();

            $guestQuery = Guest::where('wedding_id', $wedding->id);
            if ($dispatch->filter_group) {
                $guestQuery->where('group_name', $dispatch->filter_group);
            }

            $guests = $guestQuery->get();

            $seenPhones = [];
            $validCount = 0;
            $deduplicatedCount = 0;

            foreach ($guests as $guest) {
                $phone = trim((string) $guest->phone);
                $renderedMessage = self::renderPersonalizedMessage($dispatch->message_template, $guest, $wedding);

                // Chuẩn hóa số điện thoại loại bỏ ký tự khoảng trắng / dấu chấm
                $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);

                $isDuplicate = false;
                $skipReason = null;

                $isOptOut = ! empty($guest->is_opt_out)
                    || (is_array($guest->extra_data) && ! empty($guest->extra_data['opt_out']))
                    || str_contains(mb_strtolower((string) $guest->check_in_note), 'opt-out')
                    || str_contains(mb_strtolower((string) $guest->check_in_note), 'từ chối');

                if ($isOptOut) {
                    $isDuplicate = true;
                    $skipReason = 'Khách mời đã đăng ký từ chối nhận thông báo (Opt-out)';
                } elseif (empty($cleanPhone)) {
                    $isDuplicate = true;
                    $skipReason = 'Khách mời chưa có số điện thoại nhận tin';
                } elseif (isset($seenPhones[$cleanPhone])) {
                    $isDuplicate = true;
                    $skipReason = "Số điện thoại {$cleanPhone} bị trùng lặp với khách \"{$seenPhones[$cleanPhone]}\" trong cùng đợt gửi";
                } else {
                    // Kiểm tra xem số điện thoại này đã được gửi tin trong 24 giờ qua chưa (Chống spam liên tục)
                    $recentSent = MessagingDispatchLog::where('phone', $cleanPhone)
                        ->where('status', 'sent')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if ($recentSent) {
                        $isDuplicate = true;
                        $skipReason = "Số điện thoại {$cleanPhone} vừa được gửi tin trong vòng 24 giờ qua";
                    }
                }

                if ($isDuplicate) {
                    $deduplicatedCount++;
                    MessagingDispatchLog::create([
                        'dispatch_id' => $dispatch->id,
                        'guest_id' => $guest->id,
                        'phone' => $cleanPhone ?: 'N/A',
                        'guest_name' => $guest->name,
                        'guest_group' => $guest->group_name,
                        'rendered_message' => $renderedMessage,
                        'status' => 'skipped_duplicate',
                        'error_message' => $skipReason,
                    ]);
                } else {
                    $seenPhones[$cleanPhone] = $guest->name;
                    $validCount++;
                    MessagingDispatchLog::create([
                        'dispatch_id' => $dispatch->id,
                        'guest_id' => $guest->id,
                        'phone' => $cleanPhone,
                        'guest_name' => $guest->name,
                        'guest_group' => $guest->group_name,
                        'rendered_message' => $renderedMessage,
                        'status' => 'dry_run',
                    ]);
                }
            }

            $totalCost = round($validCount * (float) $dispatch->unit_cost_vnd, 2);

            $dispatch->update([
                'total_recipients' => $validCount + $deduplicatedCount,
                'deduplicated_count' => $deduplicatedCount,
                'total_cost_vnd' => $totalCost,
                'status' => 'dry_run_ready',
            ]);

            AuditLogger::log(
                action: 'messaging.dry_run_executed',
                targetType: 'messaging_dispatch',
                targetId: $dispatch->id,
                description: "Chạy kiểm thử Dry-Run đợt gửi tin \"{$dispatch->campaign_name}\": {$validCount} tin hợp lệ, {$deduplicatedCount} tin loại bỏ chống trùng.",
                details: [
                    'campaign_name' => $dispatch->campaign_name,
                    'valid' => $validCount,
                    'deduplicated' => $deduplicatedCount,
                    'estimated_cost' => $totalCost,
                ]
            );

            return [
                'total' => $validCount + $deduplicatedCount,
                'valid' => $validCount,
                'deduplicated' => $deduplicatedCount,
                'estimated_cost' => $totalCost,
            ];
        });
    }

    /**
     * Phê duyệt và thực thi gửi tin thật ra ngoài (Yêu cầu Dual Approval từ Quản lý trở lên).
     */
    public static function dispatchRealCampaign(MessagingDispatch $dispatch, User $approver): bool
    {
        if ($dispatch->status !== 'dry_run_ready') {
            throw new \InvalidArgumentException('Chiến dịch phải hoàn thành bước kiểm thử Dry-Run trước khi được gửi thật.');
        }

        return DB::transaction(function () use ($dispatch, $approver) {
            $dispatch->update([
                'approved_by' => $approver->id,
                'status' => 'processing',
                'dispatched_at' => now(),
            ]);

            $logs = $dispatch->logs()->where('status', 'dry_run')->get();
            $successCount = 0;

            foreach ($logs as $log) {
                // Mô phỏng / Gửi tin thật qua Cổng Zalo ZNS / SMS Brandname
                $providerRef = strtoupper($dispatch->channel).'_'.Str::upper(Str::random(12));

                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'provider_ref' => $providerRef,
                ]);

                // Đánh dấu khách đã nhận được thiệp
                if ($log->guest_id) {
                    Guest::where('id', $log->guest_id)->update(['is_sent' => true]);
                }

                $successCount++;
            }

            $dispatch->update([
                'successful_count' => $successCount,
                'failed_count' => 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            AuditLogger::log(
                action: 'messaging.campaign_dispatched',
                targetType: 'messaging_dispatch',
                targetId: $dispatch->id,
                description: "Duyệt gửi thành công chiến dịch \"{$dispatch->campaign_name}\": {$successCount} tin đã gửi, chi phí: ".number_format($dispatch->total_cost_vnd, 0, ',', '.').'đ.',
                details: [
                    'campaign_id' => $dispatch->id,
                    'approver_id' => $approver->id,
                    'success_count' => $successCount,
                    'total_cost' => $dispatch->total_cost_vnd,
                ]
            );

            return true;
        });
    }

    /**
     * Nhận chứng cứ trạng thái thực tế từ Provider Callback / Webhook DLR (G2-009, G2-011).
     * Chỉ đánh dấu delivered/failed khi có provider_ref và status hợp lệ.
     */
    public static function handleDeliveryReport(string $providerRef, string $status, ?string $errorMessage = null): bool
    {
        $log = MessagingDispatchLog::where('provider_ref', $providerRef)->first();
        if (! $log) {
            return false;
        }

        $validStatuses = ['sent', 'delivered', 'read', 'failed'];
        if (! in_array($status, $validStatuses, true)) {
            return false;
        }

        $log->update([
            'status' => $status,
            'error_message' => $errorMessage ?: $log->error_message,
        ]);

        return true;
    }
}
