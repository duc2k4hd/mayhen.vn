<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;

class PostPaymentOnboardingService
{
    /**
     * Tạo thông tin hướng dẫn Onboarding khởi đầu thành công sau thanh toán (TASK-VAL-26).
     *
     * @return array{
     *     order: array<string, mixed>,
     *     plan: array<string, mixed>,
     *     invitation: ?array<string, mixed>,
     *     onboarding_steps: array<int, array{step: int, title: string, description: string, action_text: string, action_url: string, badge: string}>,
     *     support_contact: array{hotline: string, zalo_link: string, hours: string}
     * }
     */
    public function getOnboardingGuide(Order $order): array
    {
        $plan = $order->plan;
        $invitation = $order->invitation;
        $wedding = $invitation ? $invitation->wedding : null;

        $isPaid = $order->isPaid();

        $steps = [];

        // Bước 1: Khám phá thiệp cưới chính thức
        if ($invitation) {
            $steps[] = [
                'step' => 1,
                'title' => 'Xem & Chia Sẻ Link Thiệp Cưới Chính Thức',
                'description' => "Đường dẫn thiệp cưới của hai bạn đã được kích hoạt chính thức. Thời hạn sử dụng đến {$order->paid_at?->addDays($plan->duration_days)->format('d/m/Y')}.",
                'action_text' => 'Xem Thiệp Trực Tiếp',
                'action_url' => route('invitation.show', $invitation->slug),
                'badge' => 'Sẵn sàng gửi',
            ];
        } else {
            $steps[] = [
                'step' => 1,
                'title' => 'Bắt Đầu Tạo Mẫu Thiệp Mới',
                'description' => "Gói {$plan->name} cho phép hai bạn tạo tối đa {$plan->max_invitations} mẫu thiệp (Nhà Trai, Nhà Gái hoặc Báo Hỷ).",
                'action_text' => 'Vào Studio Tạo Thiệp',
                'action_url' => route('invitation.create'),
                'badge' => 'Bắt đầu',
            ];
        }

        // Bước 2: Vận hành ngày cưới & sơ đồ sảnh tiệc
        if ($invitation) {
            $steps[] = [
                'step' => 2,
                'title' => 'Bố Trí Sơ Đồ Bàn Tiệc & QR Điểm Danh Lễ Tân',
                'description' => 'Mở khóa Studio Xếp Bàn Tiệc, kiểm tra sức khỏe phân bàn và sẵn sàng công cụ quét QR đón tiếp.',
                'action_text' => 'Mở Sơ Đồ Bàn Tiệc',
                'action_url' => route('seating.index', $invitation->id),
                'badge' => 'Tính năng VIP',
            ];
        } else {
            $steps[] = [
                'step' => 2,
                'title' => 'Khám Phá Kho Mẫu Thiệp Cao Cấp',
                'description' => 'Mở khóa toàn bộ thư viện mẫu thiệp Quiet Luxury, hoa lá và hiệu ứng mở phong bì lụa.',
                'action_text' => 'Xem Kho Mẫu Thiệp',
                'action_url' => route('templates.index'),
                'badge' => 'Đã mở khóa',
            ];
        }

        // Bước 3: Bảo vệ quyền riêng tư số tài khoản mừng cưới
        if ($invitation) {
            $steps[] = [
                'step' => 3,
                'title' => 'Cấu Hình Bảo Vệ Số Tài Khoản Ngân Hàng',
                'description' => 'Tuân thủ Nghị định 13: Cài đặt che số tài khoản mừng cưới với người lạ, chỉ hiển thị đầy đủ cho khách có thiệp mời định danh.',
                'action_text' => 'Cài Đặt Quyền Riêng Tư',
                'action_url' => route('privacy.show', $invitation->id),
                'badge' => 'Khuyên dùng',
            ];
        } else {
            $steps[] = [
                'step' => 3,
                'title' => 'Xem Hướng Dẫn Vận Hành Ngày Cưới',
                'description' => 'Quy trình đón khách 3 bước, cách phân công bạn bè trực cổng tiệc và xử lý khách phát sinh.',
                'action_text' => 'Đến Trang Quản Trị',
                'action_url' => route('account.invitations'),
                'badge' => 'Tài liệu',
            ];
        }

        return [
            'order' => [
                'order_code' => $order->order_code,
                'amount' => (int) $order->amount,
                'status' => $order->status,
                'is_paid' => $isPaid,
                'paid_at' => $order->paid_at ? Carbon::parse($order->paid_at)->format('H:i - d/m/Y') : null,
                'created_at' => Carbon::parse($order->created_at)->format('H:i - d/m/Y'),
            ],
            'plan' => [
                'name' => $plan->name ?? 'Gói Dịch Vụ',
                'slug' => $plan->slug ?? 'pro',
                'duration_days' => (int) ($plan->duration_days ?? 730),
                'max_invitations' => (int) ($plan->max_invitations ?? 3),
            ],
            'invitation' => $invitation ? [
                'id' => $invitation->id,
                'slug' => $invitation->slug,
                'groom_name' => $wedding ? $wedding->groom_name : 'Chú Rể',
                'bride_name' => $wedding ? $wedding->bride_name : 'Cô Dâu',
            ] : null,
            'onboarding_steps' => $steps,
            'support_contact' => [
                'hotline' => '1900 xxxx (Hỗ trợ ngày cưới 24/7)',
                'zalo_link' => 'https://zalo.me/mayhenvn',
                'hours' => '8:00 - 22:00 hàng ngày (Kể cả Thứ 7 & Chủ Nhật)',
            ],
        ];
    }
}

