<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class MayHenInitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Initial Plans (4 gói: Free 0đ → Premium 0đ (đăng nhập) → Plus 99k → Pro 199k)
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Trải Nghiệm (Free)',
                'price' => 0,
                'duration_days' => 7,
                'max_invitations' => 1,
                'features' => [
                    'Tạo ngay 1 thiệp cưới cơ bản không cần đăng nhập',
                    'Xem trước không giới hạn trên điện thoại',
                    'Hiển thị thông tin cưới & bản đồ Google Maps',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'premium',
                'name' => 'Thành Viên (Premium)',
                'price' => 0,
                'duration_days' => 30,
                'max_invitations' => 1,
                'features' => [
                    'Miễn phí hoàn toàn khi đăng nhập tài khoản',
                    'Mở khóa kho mẫu thiệp Premium tinh tế',
                    'Lưu nháp & quản lý thiệp trực tiếp trên tài khoản',
                    'Xem trước, chỉnh sửa nhiều lần không mất dữ liệu',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'plus',
                'name' => 'Hạnh Phúc (Plus)',
                'price' => 99000,
                'duration_days' => 180,
                'max_invitations' => 1,
                'features' => [
                    'Xuất bản link thiệp riêng chính thức (yêu cầu đăng nhập)',
                    'Mở khóa toàn bộ mẫu thiệp hoa lá, hiện đại',
                    'Hiệu ứng âm thanh & nhạc nền lãng mạn',
                    'RSVP xác nhận tham dự theo gia đình',
                    'QR mừng cưới chính chủ không thu phí',
                    'Lưu giữ kỷ niệm 6 tháng sau cưới',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'pro',
                'name' => 'Trọn Vẹn (Wedding Pro)',
                'price' => 199000,
                'duration_days' => 730,
                'max_invitations' => 3,
                'features' => [
                    'Tất cả tính năng của gói Plus',
                    'Nhân bản thiệp Nhà Trai / Nhà Gái riêng biệt',
                    'Công cụ gửi thiệp cá nhân hóa qua Zalo',
                    'Quản lý danh sách khách mời & xuất Excel',
                    'Sổ lời chúc online & album ảnh kỷ niệm',
                    'Lưu giữ kỷ niệm bền vững 2 năm',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(['slug' => $planData['slug']], $planData);
        }

        $this->call(InvitationTemplateSeeder::class);
    }
}
