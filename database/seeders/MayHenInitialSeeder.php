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
        // 1. Initial Plans (4 gói chuẩn mới: Free 0đ (1 thiệp/3 tháng) → Premium 0đ (3 thiệp/3 tháng) → Plus 99k (10 thiệp/6 tháng) → Pro 199k (30 thiệp/1 năm))
        $plans = [
            [
                'slug' => 'free',
                'name' => 'Trải Nghiệm (Free)',
                'price' => 0,
                'duration_days' => 90,
                'max_invitations' => 1,
                'features' => [
                    'Tạo tối đa 1 thiệp cưới cơ bản không cần đăng nhập',
                    'Đường link thiệp tồn tại 3 tháng (tự động dọn khi hết hạn)',
                    'Xem trước không giới hạn trên điện thoại',
                    'Hiển thị thông tin cưới & bản đồ Google Maps',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'premium',
                'name' => 'Thành Viên (Premium)',
                'price' => 0,
                'duration_days' => 90,
                'max_invitations' => 3,
                'features' => [
                    'Tự động kích hoạt miễn phí ngay khi đăng ký / đăng nhập tài khoản',
                    'Tạo tối đa 3 thiệp cưới (tính cả thiệp đã xóa), mở khóa kho mẫu Premium',
                    'Mỗi link thiệp tồn tại 3 tháng (tự động dọn khi hết hạn)',
                    'Lưu nháp & quản lý thiệp trực tiếp trên tài khoản',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'plus',
                'name' => 'Hạnh Phúc (Plus)',
                'price' => 99000,
                'duration_days' => 180,
                'max_invitations' => 10,
                'features' => [
                    'Đăng ký & gia hạn chu kỳ 6 tháng (180 ngày) — tạo tối đa 10 thiệp',
                    'Mỗi thiệp tạo trong gói Plus luôn tồn tại trọn vẹn đủ 6 tháng từ ngày tạo',
                    'Mở khóa toàn bộ mẫu thiệp Plus, Tạo Thiệp Tự Động AI Curated',
                    'Hiệu ứng âm thanh, nhạc nền lãng mạn & album ảnh cưới sắc nét',
                    'RSVP xác nhận tham dự theo gia đình & QR mừng cưới 0% phí',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'pro',
                'name' => 'Trọn Vẹn (Wedding Pro)',
                'price' => 199000,
                'duration_days' => 365,
                'max_invitations' => 30,
                'features' => [
                    'Đăng ký & gia hạn chu kỳ 1 năm (365 ngày) — tạo tối đa 30 thiệp',
                    'Mỗi thiệp tạo trong gói Pro luôn tồn tại trọn vẹn đủ 1 năm từ ngày tạo',
                    'Tất cả tính năng của gói Plus & toàn bộ mẫu Hoàng Gia Pro cao cấp nhất',
                    'Nhân bản thiệp Nhà Trai / Nhà Gái riêng biệt & Sơ đồ bàn tiệc, Check-in QR',
                    'Mở khóa trọn bộ 138 mẫu Thiệp Sự Kiện & Mùa Lễ Hội (/thiep-su-kien)',
                    'Quản lý danh sách khách mời không giới hạn & xuất Excel chuẩn hóa',
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
