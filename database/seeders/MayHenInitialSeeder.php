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
        // 1. Initial Plans
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Trải Nghiệm (Free)',
                'price' => 0,
                'duration_days' => 7,
                'max_invitations' => 1,
                'features' => [
                    'Tạo 1 thiệp cưới cơ bản',
                    'Xem trước không giới hạn trên mobile',
                    'Hiển thị thông tin cưới & bản đồ',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'plus',
                'name' => 'Hạnh Phúc (Plus)',
                'price' => 149000,
                'duration_days' => 180,
                'max_invitations' => 1,
                'features' => [
                    'Xuất bản link thiệp riêng chính thức',
                    'Mở khóa toàn bộ mẫu thiệp hoa lá, hiện đại',
                    'Hiệu ứng âm thanh & nhạc nền lãng mạn',
                    'RSVP xác nhận tham dự theo gia đình',
                    'QR mừng cưới chính chủ không thu phí',
                    'Lưu giữ kỷ niệm 6 tháng sau cưới',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'premium',
                'name' => 'Hạnh Phúc (Premium)',
                'price' => 149000,
                'duration_days' => 180,
                'max_invitations' => 1,
                'features' => [
                    'Xuất bản link thiệp riêng chính thức',
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
                'price' => 299000,
                'duration_days' => 730,
                'max_invitations' => 3,
                'features' => [
                    'Tất cả tính năng của gói Premium',
                    'Nhân bản thiệp Nhà Trai / Nhà Gái riêng biệt',
                    'Công cụ gửi thiệp cá nhân hóa qua Zalo',
                    'Quản lý danh sách khách mời & xuất Excel',
                    'Sổ lời chúc online & album ảnh kỷ niệm',
                    'Lưu giữ kỷ niệm vĩnh viễn 2 năm',
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
