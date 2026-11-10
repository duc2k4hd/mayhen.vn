<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\Wedding;
use Carbon\Carbon;

class RealFunnelAnalyticsService
{
    /**
     * Tính toán Phễu chuyển đổi toàn hệ thống (Real System-Wide Conversion Funnel).
     * Đo lường 100% dữ liệu thật, không có số liệu ước tính ảo.
     *
     * @return array<string, mixed>
     */
    public static function getGlobalFunnelMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $invitationQuery = Invitation::query();
        $orderQuery = Order::query()->where('status', 'paid');
        $guestQuery = Guest::query();
        $responseQuery = GuestResponse::query();

        if ($startDate && $endDate) {
            $invitationQuery->whereBetween('created_at', [$startDate, $endDate]);
            $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
            $guestQuery->whereBetween('created_at', [$startDate, $endDate]);
            $responseQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Chặng 1: Khám phá & Lựa chọn mẫu (Tổng lượt xem thiệp cưới thực tế)
        $totalViews = (int) (clone $invitationQuery)->sum('view_count');

        // Chặng 2: Khởi tạo thiệp cưới (Số thiệp cưới được tạo)
        $totalInvitations = (int) (clone $invitationQuery)->count();

        // Chặng 3: Chuyển đổi thanh toán đơn hàng (Đơn hàng đã thanh toán)
        $paidOrdersCount = (int) (clone $orderQuery)->count();

        // Chặng 4: Phát hành & Gửi link mời (Số khách mời đã được gửi link)
        $totalGuests = (int) (clone $guestQuery)->count();
        $totalSentGuests = (int) (clone $guestQuery)->where('is_sent', true)->count();

        // Chặng 5: Khách mời phản hồi tham dự RSVP
        $totalResponses = (int) (clone $responseQuery)->count();
        $attendingResponses = (int) (clone $responseQuery)->where('attending_status', 'attending')->count();

        // Tính tỷ lệ chuyển đổi từng chặng (Step-by-step conversion)
        $viewToCreateRate = $totalViews > 0 ? round(($totalInvitations / $totalViews) * 100, 1) : 0.0;
        $createToPaidRate = $totalInvitations > 0 ? round(($paidOrdersCount / $totalInvitations) * 100, 1) : 0.0;
        $sendRate = $totalGuests > 0 ? round(($totalSentGuests / $totalGuests) * 100, 1) : 0.0;
        $responseRateOnSent = $totalSentGuests > 0 ? round(($totalResponses / $totalSentGuests) * 100, 1) : 0.0;
        $attendingRate = $totalResponses > 0 ? round(($attendingResponses / $totalResponses) * 100, 1) : 0.0;

        return [
            'stages' => [
                'views' => [
                    'label' => '1. Lượt Xem Khám Phá Thiệp',
                    'count' => $totalViews,
                    'unit' => 'lượt xem',
                ],
                'invitations' => [
                    'label' => '2. Thiệp Cưới Khởi Tạo',
                    'count' => $totalInvitations,
                    'unit' => 'thiệp cưới',
                    'conversion_rate_from_prev' => $viewToCreateRate,
                ],
                'paid_orders' => [
                    'label' => '3. Đơn Hàng Thanh Toán',
                    'count' => $paidOrdersCount,
                    'unit' => 'đơn hàng',
                    'conversion_rate_from_prev' => $createToPaidRate,
                ],
                'sent_guests' => [
                    'label' => '4. Khách Mời Đã Gửi Link',
                    'count' => $totalSentGuests,
                    'total_guests' => $totalGuests,
                    'unit' => 'khách mời',
                    'conversion_rate_from_prev' => $sendRate,
                ],
                'rsvp_responses' => [
                    'label' => '5. Phản Hồi Tham Dự RSVP',
                    'count' => $totalResponses,
                    'attending_count' => $attendingResponses,
                    'unit' => 'phản hồi',
                    'conversion_rate_from_prev' => $responseRateOnSent,
                    'attending_rate' => $attendingRate,
                ],
            ],
            'summary' => [
                'total_views' => $totalViews,
                'total_invitations' => $totalInvitations,
                'total_paid_orders' => $paidOrdersCount,
                'total_sent_guests' => $totalSentGuests,
                'total_responses' => $totalResponses,
                'attending_responses' => $attendingResponses,
                'is_measured_data' => true,
            ],
        ];
    }

    /**
     * Đo lường hiệu suất chiến dịch gửi thiệp & phản hồi RSVP cho tiệc cưới cụ thể.
     * TUYỆT ĐỐI KHÔNG HARDCODE TỶ LỆ MỞ HAY TIẾT KIỆM CHI PHÍ.
     *
     * @return array<string, mixed>
     */
    public static function getWeddingCampaignDeliveryMetrics(?Wedding $wedding): array
    {
        if (! $wedding) {
            return [
                'total_guests' => 0,
                'sent_count' => 0,
                'unsent_count' => 0,
                'attending_count' => 0,
                'declined_count' => 0,
                'no_response_count' => 0,
                'send_rate' => 0.0,
                'response_rate_on_sent' => 0.0,
                'attending_rate_on_response' => 0.0,
                'is_measured_data' => true,
                'cost_notice' => '0đ phí duy trì nền tảng khi cặp đôi chia sẻ link qua Zalo hoặc mạng xã hội.',
            ];
        }

        $guests = $wedding->guests;
        $totalGuests = $guests->count();
        $sentCount = $guests->where('is_sent', true)->count();
        $unsentCount = max(0, $totalGuests - $sentCount);

        $responses = GuestResponse::where('wedding_id', $wedding->id)->get();
        $totalResponses = $responses->count();
        $attendingCount = $responses->where('attending_status', 'attending')->count();
        $declinedCount = $responses->where('attending_status', 'declined')->count();
        $noResponseCount = max(0, $totalGuests - $totalResponses);

        // Tỷ lệ gửi thiệp (%)
        $sendRate = $totalGuests > 0 ? round(($sentCount / $totalGuests) * 100, 1) : 0.0;

        // Tỷ lệ phản hồi trên số khách đã gửi (%)
        $responseRateOnSent = $sentCount > 0 ? round(($totalResponses / $sentCount) * 100, 1) : 0.0;

        // Tỷ lệ khách tham dự trên số phản hồi nhận được (%)
        $attendingRateOnResponse = $totalResponses > 0 ? round(($attendingCount / $totalResponses) * 100, 1) : 0.0;

        return [
            'total_guests' => $totalGuests,
            'sent_count' => $sentCount,
            'unsent_count' => $unsentCount,
            'response_count' => $totalResponses,
            'attending_count' => $attendingCount,
            'declined_count' => $declinedCount,
            'no_response_count' => $noResponseCount,
            'send_rate' => $sendRate,
            'response_rate_on_sent' => $responseRateOnSent,
            'attending_rate_on_response' => $attendingRateOnResponse,
            'is_measured_data' => true,
            'cost_notice' => 'Miễn phí 100% khi cặp đôi tự sao chép link chia sẻ qua Zalo, Messenger hoặc SMS cá nhân. (Nếu đăng ký dịch vụ SMS Brandname hoặc Zalo ZNS tự động từ tổng đài sẽ áp dụng theo biểu phí đối tác viễn thông).',
        ];
    }
}
