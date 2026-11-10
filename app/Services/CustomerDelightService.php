<?php

namespace App\Services;

use App\Models\CustomerFeedback;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerDelightService
{
    /**
     * Record a customer delight feedback entry.
     * Automatically triggers a high-priority Support Ticket if rating is low (<= 2 stars).
     */
    public static function recordFeedback(array $data, ?User $user = null): CustomerFeedback
    {
        return DB::transaction(function () use ($data, $user) {
            $rating = (int) ($data['rating'] ?? 5);
            $rating = max(1, min(5, $rating));

            $sentiment = match (true) {
                $rating >= 5 => 'delighted',
                $rating === 4 => 'satisfied',
                $rating === 3 => 'neutral',
                default => 'frustrated',
            };

            $userId = $user?->id ?? ($data['user_id'] ?? null);
            $milestone = $data['milestone'] ?? 'general';
            $category = $data['category'] ?? 'general';
            $comment = trim($data['comment'] ?? '');

            $linkedTicketId = null;

            // Trigger proactive recovery workflow for low ratings (Delight Recovery Action Plan)
            if ($rating <= 2 && $userId) {
                $ticketCode = 'TK-CSAT-'.strtoupper(Str::random(6));
                $milestoneLabel = match ($milestone) {
                    'after_publish' => 'Sau Xuất Bản Thiệp',
                    'after_payment' => 'Sau Thanh Toán',
                    'post_wedding' => 'Sau Ngày Cưới',
                    default => 'Trải Nghiệm Chung',
                };

                $ticket = SupportTicket::create([
                    'user_id' => $userId,
                    'ticket_code' => $ticketCode,
                    'subject' => "[CSAT Cảnh Báo] Khách Hàng #{$userId} Đánh Giá {$rating}⭐ ({$milestoneLabel})",
                    'body' => "Phản hồi khách hàng: \"{$comment}\"\nDanh mục phản ánh: {$category}\nCột mốc: {$milestoneLabel}.\nCSKH cần chủ động liên hệ hỗ trợ giải tỏa khúc mắc kịp thời.",
                    'category' => 'general',
                    'priority' => 'urgent',
                    'status' => 'open',
                ]);

                $linkedTicketId = $ticket->id;
            }

            $feedback = CustomerFeedback::create([
                'user_id' => $userId,
                'wedding_id' => $data['wedding_id'] ?? null,
                'milestone' => $milestone,
                'rating' => $rating,
                'sentiment' => $sentiment,
                'category' => $category,
                'comment' => $comment ?: null,
                'linked_ticket_id' => $linkedTicketId,
                'action_status' => $rating <= 2 ? 'pending_review' : 'resolved',
                'metadata' => $data['metadata'] ?? null,
            ]);

            AuditLogger::log(
                'customer_feedback_received',
                'CustomerFeedback',
                $feedback->id,
                "Khách hàng #{$userId} gửi phản hồi {$rating} sao ({$sentiment}) tại mốc {$milestone}",
                [
                    'rating' => $rating,
                    'milestone' => $milestone,
                    'has_ticket' => (bool) $linkedTicketId,
                ]
            );

            return $feedback;
        });
    }

    /**
     * Compute comprehensive Delight & CSAT / NPS metrics.
     */
    public static function getDelightMetrics(): array
    {
        $feedbacks = CustomerFeedback::all();
        $total = $feedbacks->count();

        if ($total === 0) {
            return [
                'total_feedbacks' => 0,
                'average_score' => 5.0,
                'satisfaction_rate' => 100,
                'nps_score' => 100,
                'alerts_count' => 0,
                'breakdown_by_milestone' => [],
                'breakdown_by_category' => [],
            ];
        }

        $avgScore = round($feedbacks->avg('rating'), 1);
        $promoters = $feedbacks->where('rating', '>=', 4)->count();
        $detractors = $feedbacks->where('rating', '<=', 2)->count();
        $passives = $feedbacks->where('rating', 3)->count();

        $satisfactionRate = (int) round(($promoters / $total) * 100);
        $npsScore = (int) round((($promoters - $detractors) / $total) * 100);

        $alertsCount = $feedbacks->where('rating', '<=', 2)->where('action_status', 'pending_review')->count();

        $byMilestone = [
            'after_publish' => $feedbacks->where('milestone', 'after_publish')->count(),
            'after_payment' => $feedbacks->where('milestone', 'after_payment')->count(),
            'post_wedding' => $feedbacks->where('milestone', 'post_wedding')->count(),
            'general' => $feedbacks->where('milestone', 'general')->count(),
        ];

        $byCategory = [
            'studio_editor' => $feedbacks->where('category', 'studio_editor')->count(),
            'pricing' => $feedbacks->where('category', 'pricing')->count(),
            'customer_service' => $feedbacks->where('category', 'customer_service')->count(),
            'guest_rsvp' => $feedbacks->where('category', 'guest_rsvp')->count(),
            'template_design' => $feedbacks->where('category', 'template_design')->count(),
        ];

        return [
            'total_feedbacks' => $total,
            'average_score' => $avgScore,
            'satisfaction_rate' => $satisfactionRate,
            'nps_score' => $npsScore,
            'alerts_count' => $alertsCount,
            'breakdown_by_milestone' => $byMilestone,
            'breakdown_by_category' => $byCategory,
        ];
    }

    /**
     * Resolve a feedback entry with an Action Plan note by Staff/Manager.
     */
    public static function resolveActionPlan(CustomerFeedback $feedback, User $staff, string $actionNote, string $status = 'resolved'): void
    {
        $feedback->update([
            'action_status' => $status,
            'action_plan_note' => $actionNote,
            'handled_by' => $staff->id,
            'handled_at' => now(),
        ]);

        AuditLogger::log(
            'resolve_feedback_action_plan',
            'CustomerFeedback',
            $feedback->id,
            "Nhân viên {$staff->name} hoàn tất can thiệp phản hồi #{$feedback->id} (Trạng thái: {$status})",
            [
                'status' => $status,
                'staff_id' => $staff->id,
            ]
        );
    }
}
