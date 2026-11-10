<?php

namespace App\Services;

use App\Models\CustomerFeedback;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;

class OperationsReplayService
{
    /**
     * Assemble an unified, chronological event timeline for an operational scope.
     * Supported scopes: 'wedding', 'order', 'user'.
     */
    public static function assembleTimeline(string $scopeType, string|int $scopeId, bool $maskPii = true): array
    {
        $events = collect();
        $targetWedding = null;
        $targetUser = null;

        if ($scopeType === 'wedding') {
            $targetWedding = Wedding::with(['user', 'invitations', 'tables'])->find($scopeId);
            $targetUser = $targetWedding?->user;
        } elseif ($scopeType === 'order') {
            $order = Order::with(['user', 'plan'])->where('order_code', $scopeId)->orWhere('id', $scopeId)->first();
            if ($order) {
                $targetUser = $order->user;
                $targetWedding = $targetUser?->weddings()->first();
            }
        } elseif ($scopeType === 'user') {
            $targetUser = User::with('weddings.invitations')->find($scopeId);
            $targetWedding = $targetUser?->weddings()->first();
        }

        // 1. Wedding Milestone Events
        if ($targetWedding) {
            $events->push([
                'timestamp' => $targetWedding->created_at,
                'source' => 'Wedding',
                'event_type' => 'wedding_profile_created',
                'title' => 'Khởi tạo hồ sơ đám cưới',
                'description' => "Cặp đôi {$targetWedding->groom_name} & {$targetWedding->bride_name} thiết lập hồ sơ ngày cưới ({$targetWedding->wedding_date?->format('d/m/Y')}).",
                'actor' => $targetUser ? self::maskEmail($targetUser->email, $maskPii) : 'Khách hàng',
                'severity' => 'info',
                'metadata' => [
                    'venue' => $targetWedding->venue_name,
                    'wedding_date' => $targetWedding->wedding_date?->format('d/m/Y'),
                ],
            ]);
        }

        // 2. Orders & Financial Events
        if ($targetUser) {
            $orders = Order::where('user_id', $targetUser->id)->with('plan')->get();
            foreach ($orders as $o) {
                $events->push([
                    'timestamp' => $o->created_at,
                    'source' => 'Finance',
                    'event_type' => 'order_created',
                    'title' => "Khởi tạo đơn hàng #{$o->order_code}",
                    'description' => 'Đăng ký gói '.($o->plan?->name ?? 'dịch vụ').' với tổng số tiền '.number_format($o->amount).'đ.',
                    'actor' => self::maskEmail($targetUser->email, $maskPii),
                    'severity' => 'info',
                    'metadata' => ['amount' => $o->amount, 'status' => $o->status],
                ]);

                if ($o->paid_at) {
                    $events->push([
                        'timestamp' => $o->paid_at,
                        'source' => 'Finance',
                        'event_type' => 'order_paid',
                        'title' => "Thanh toán thành công đơn #{$o->order_code}",
                        'description' => 'Ghi nhận thanh toán qua '.($o->payment_method ?? 'VietQR/SePay').' kèm mã đối soát ngân hàng.',
                        'actor' => 'Payment Gateway',
                        'severity' => 'success',
                        'metadata' => ['paid_amount' => $o->amount, 'ref' => $maskPii ? '****'.substr((string) $o->provider_transaction_id, -4) : $o->provider_transaction_id],
                    ]);
                }

                if ($o->refunded_at) {
                    $events->push([
                        'timestamp' => $o->refunded_at,
                        'source' => 'Finance',
                        'event_type' => 'order_refunded',
                        'title' => "Hoàn tiền đơn hàng #{$o->order_code}",
                        'description' => 'Xác nhận chuyển khoản hoàn tiền '.number_format($o->refund_amount ?? $o->amount).'đ cho khách hàng.',
                        'actor' => 'Kế toán Mây Hẹn',
                        'severity' => 'warning',
                        'metadata' => ['refund_amount' => $o->refund_amount],
                    ]);
                }
            }
        }

        // 3. Invitations & Studio Events
        if ($targetWedding) {
            $invitations = Invitation::where('wedding_id', $targetWedding->id)->with('template')->get();
            foreach ($invitations as $inv) {
                $events->push([
                    'timestamp' => $inv->created_at,
                    'source' => 'Studio',
                    'event_type' => 'invitation_created',
                    'title' => "Tạo bản thiệp '{$inv->slug}'",
                    'description' => 'Áp dụng mẫu thiết kế: '.($inv->template?->name ?? 'Mẫu thiệp'),
                    'actor' => $targetUser ? self::maskEmail($targetUser->email, $maskPii) : 'Studio Editor',
                    'severity' => 'info',
                    'metadata' => ['template' => $inv->template?->name, 'slug' => $inv->slug],
                ]);

                if ($inv->is_published) {
                    $events->push([
                        'timestamp' => $inv->updated_at,
                        'source' => 'Studio',
                        'event_type' => 'invitation_published',
                        'title' => 'Xuất bản thiệp cưới trực tuyến',
                        'description' => "Thiệp cưới chính thức mở công khai cho quan khách truy cập (Lượt xem hiện tại: {$inv->view_count}).",
                        'actor' => $targetUser ? self::maskEmail($targetUser->email, $maskPii) : 'Cặp đôi',
                        'severity' => 'success',
                        'metadata' => ['view_count' => $inv->view_count, 'is_locked' => $inv->is_locked],
                    ]);
                }
            }
        }

        // 4. Guest & Check-In Events
        if ($targetWedding) {
            $guests = Guest::where('wedding_id', $targetWedding->id)->with(['response', 'table'])->get();
            $checkedInCount = 0;
            $rsvpsCount = 0;

            foreach ($guests as $g) {
                if ($g->response) {
                    $rsvpsCount++;
                    $events->push([
                        'timestamp' => $g->response->created_at,
                        'source' => 'RSVP',
                        'event_type' => 'guest_rsvp_submitted',
                        'title' => 'Khách phản hồi RSVP: '.self::maskName($g->name, $maskPii),
                        'description' => 'Trạng thái: '.($g->response->attending_status === 'attending' ? 'Có tham dự' : 'Không tham dự')." (Sĩ số: {$g->actualAttendeesCount()}).",
                        'actor' => self::maskPhone($g->phone, $maskPii),
                        'severity' => $g->response->attending_status === 'attending' ? 'success' : 'neutral',
                        'metadata' => ['guest_id' => $g->id, 'status' => $g->response->attending_status],
                    ]);
                }

                if ($g->checked_in_at) {
                    $checkedInCount++;
                    $events->push([
                        'timestamp' => $g->checked_in_at,
                        'source' => 'CheckIn',
                        'event_type' => 'guest_checked_in',
                        'title' => 'Đón tiếp quan khách: '.self::maskName($g->name, $maskPii),
                        'description' => 'Xác nhận có mặt tại sảnh tiệc. Phân bổ bàn: '.($g->table?->name ?? $g->table_name ?? 'Chưa xếp bàn'),
                        'actor' => 'Lễ tân sảnh tiệc',
                        'severity' => 'success',
                        'metadata' => ['table' => $g->table?->name ?? $g->table_name],
                    ]);
                }
            }
        }

        // 5. Support & Incident Tickets
        if ($targetUser) {
            $tickets = SupportTicket::where('user_id', $targetUser->id)->get();
            foreach ($tickets as $t) {
                $events->push([
                    'timestamp' => $t->created_at,
                    'source' => 'Support',
                    'event_type' => 'ticket_opened',
                    'title' => "Khởi tạo yêu cầu hỗ trợ [{$t->ticket_code}]",
                    'description' => "Tiêu đề: {$t->subject} (Mức độ: {$t->priority}).",
                    'actor' => self::maskEmail($targetUser->email, $maskPii),
                    'severity' => $t->priority === 'urgent' ? 'danger' : 'warning',
                    'metadata' => ['ticket_code' => $t->ticket_code, 'category' => $t->category],
                ]);

                if ($t->status === 'resolved' && $t->resolved_at) {
                    $events->push([
                        'timestamp' => $t->resolved_at,
                        'source' => 'Support',
                        'event_type' => 'ticket_resolved',
                        'title' => "Giải quyết ticket [{$t->ticket_code}]",
                        'description' => 'Đội ngũ CSKH hoàn tất xử lý và khách xác nhận hài lòng.',
                        'actor' => 'CSKH Specialist',
                        'severity' => 'success',
                        'metadata' => ['ticket_code' => $t->ticket_code],
                    ]);
                }
            }
        }

        // 6. Customer Delight Feedback
        if ($targetUser) {
            $feedbacks = CustomerFeedback::where('user_id', $targetUser->id)->get();
            foreach ($feedbacks as $fb) {
                $events->push([
                    'timestamp' => $fb->created_at,
                    'source' => 'Feedback',
                    'event_type' => 'customer_feedback',
                    'title' => "Đánh giá CSAT: {$fb->rating} ⭐ ({$fb->milestone_label})",
                    'description' => $fb->comment ? "\"{$fb->comment}\"" : "Khách để lại đánh giá cảm xúc: {$fb->sentiment}.",
                    'actor' => self::maskEmail($targetUser->email, $maskPii),
                    'severity' => $fb->rating >= 4 ? 'success' : 'danger',
                    'metadata' => ['rating' => $fb->rating, 'sentiment' => $fb->sentiment],
                ]);
            }
        }

        // 7. Audit Logger Entries
        $auditLogs = AuditLogger::getRecentLogs(100);
        foreach ($auditLogs as $log) {
            $matches = false;
            $targetId = $log['target_id'] ?? null;
            $targetType = $log['target_type'] ?? '';

            if ($scopeType === 'wedding' && $targetWedding) {
                $matches = ($targetType === 'Wedding' && $targetId == $targetWedding->id)
                    || ($targetType === 'Invitation' && in_array($targetId, $targetWedding->invitations->pluck('id')->toArray()));
            } elseif ($scopeType === 'order') {
                $matches = ($targetType === 'Order' && $targetId == $scopeId);
            } elseif ($scopeType === 'user' && $targetUser) {
                $matches = ($targetType === 'User' && $targetId == $targetUser->id);
            }

            if ($matches && ! empty($log['timestamp'])) {
                $events->push([
                    'timestamp' => Carbon::parse($log['timestamp']),
                    'source' => 'AuditTrail',
                    'event_type' => $log['action'] ?? 'admin_action',
                    'title' => 'Kiểm toán: '.($log['action'] ?? 'Thao tác Admin'),
                    'description' => $log['message'] ?? 'Thao tác có kiểm soát bởi nhân viên',
                    'actor' => $log['user_email'] ?? 'Admin Panel',
                    'severity' => 'neutral',
                    'metadata' => $log['details'] ?? [],
                ]);
            }
        }

        // Sort events chronologically
        $sortedEvents = $events->sortBy(function ($item) {
            return $item['timestamp'] ? $item['timestamp']->timestamp : 0;
        })->values();

        // Calculate deltas and step indexes
        $timeline = [];
        $previousTime = null;

        foreach ($sortedEvents as $index => $item) {
            $currentTime = $item['timestamp'];
            $gapMinutes = 0;
            if ($previousTime && $currentTime) {
                $gapMinutes = max(0, $previousTime->diffInMinutes($currentTime));
            }

            $timeline[] = array_merge($item, [
                'step_index' => $index + 1,
                'time_formatted' => $currentTime ? $currentTime->format('d/m/Y H:i:s') : '—',
                'relative_time' => $currentTime ? $currentTime->diffForHumans() : '—',
                'gap_from_prev_minutes' => $gapMinutes,
            ]);

            $previousTime = $currentTime;
        }

        return [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'target_wedding' => $targetWedding,
            'target_user' => $targetUser,
            'is_masked' => $maskPii,
            'total_steps' => count($timeline),
            'timeline' => $timeline,
            'anomalies' => self::detectAnomalies($timeline),
        ];
    }

    /**
     * Anomaly Detector: Identify unusual gaps, bottlenecks, or abandoned states.
     */
    public static function detectAnomalies(array $timeline): array
    {
        $anomalies = [];

        foreach ($timeline as $step) {
            // Gap > 48 hours between order creation and payment
            if ($step['event_type'] === 'order_created' && $step['gap_from_prev_minutes'] > 2880) {
                $anomalies[] = [
                    'step_index' => $step['step_index'],
                    'type' => 'prolonged_gap',
                    'severity' => 'warning',
                    'message' => "Khoảng thời gian chờ kéo dài hơn {$step['gap_from_prev_minutes']} phút trước bước này.",
                ];
            }

            // Urgent support ticket raised
            if ($step['event_type'] === 'ticket_opened' && ($step['severity'] ?? '') === 'danger') {
                $anomalies[] = [
                    'step_index' => $step['step_index'],
                    'type' => 'urgent_incident',
                    'severity' => 'danger',
                    'message' => "Phát sinh sự cố khẩn cấp: {$step['title']}.",
                ];
            }

            // Low CSAT rating
            if ($step['event_type'] === 'customer_feedback' && ($step['metadata']['rating'] ?? 5) <= 2) {
                $anomalies[] = [
                    'step_index' => $step['step_index'],
                    'type' => 'negative_csat',
                    'severity' => 'danger',
                    'message' => "Khách hàng phản ánh thất vọng ({$step['metadata']['rating']} sao).",
                ];
            }
        }

        return $anomalies;
    }

    private static function maskEmail(?string $email, bool $mask): string
    {
        if (! $email || ! $mask) {
            return $email ?? '—';
        }
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? 'mayhen.vn';
        $maskedName = substr($name, 0, 2).'***'.substr($name, -1);

        return "{$maskedName}@{$domain}";
    }

    private static function maskPhone(?string $phone, bool $mask): string
    {
        if (! $phone || ! $mask) {
            return $phone ?? '—';
        }
        if (strlen($phone) < 6) {
            return '09***';
        }

        return substr($phone, 0, 3).'****'.substr($phone, -3);
    }

    private static function maskName(?string $name, bool $mask): string
    {
        if (! $name || ! $mask) {
            return $name ?? '—';
        }
        $words = explode(' ', trim($name));
        if (count($words) <= 1) {
            return $name;
        }
        $lastWord = array_pop($words);

        return implode(' ', $words).' *';
    }
}
