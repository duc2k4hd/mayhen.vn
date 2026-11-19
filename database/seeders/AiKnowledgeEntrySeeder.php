<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiKnowledgeEntry;
use Illuminate\Database\Seeder;

class AiKnowledgeEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'category' => 'templates',
                'title' => 'Tư vấn chọn mẫu thiệp cưới theo phong cách và ngân sách',
                'keywords' => 'chọn mẫu thiệp, mẫu thiệp đẹp, gợi ý thiệp cưới, phong cách sang trọng, hiện đại, miễn phí, luxury',
                'answer' => 'Mây Hẹn có đầy đủ mẫu thiệp từ phong cách Quiet Luxury sang trọng, hiện đại tối giản đến hoa lá lãng mạn và truyền thống. Bạn có thể tạo thử hoàn toàn miễn phí, xem trực tiếp trên điện thoại và đổi sang mẫu khác chỉ với 1 chạm mà vẫn giữ nguyên 100% thông tin đã nhập.',
                'template_slug' => null,
                'doc_url' => '/docs',
                'priority' => 9,
            ],
            [
                'category' => 'docs',
                'title' => 'Hướng dẫn tạo thiệp cưới trực tuyến từng bước trên Studio Mây Hẹn',
                'keywords' => 'hướng dẫn tạo thiệp, cách tạo thiệp, không hiểu cách dùng, mới bắt đầu, studio tạo thiệp, sửa ảnh, sửa chữ',
                'answer' => 'Bạn chỉ cần chọn mẫu thiệp ưng ý rồi bấm Tạo thiệp: nhập thông tin cô dâu chú rể, ngày cưới (tự động tính Âm lịch), thay ảnh cưới với công cụ cắt ảnh thông minh, chọn nhạc nền và bấm Xuất bản. Trong Studio luôn có nút "Hướng dẫn" ở góc trên để chỉ dẫn trực tiếp từng bước.',
                'template_slug' => null,
                'doc_url' => '/docs/studio-editor/CLI-EDIT-001',
                'priority' => 9,
            ],
            [
                'category' => 'rsvp',
                'title' => 'Quản lý khách mời, xác nhận RSVP, xếp bàn tiệc và điểm danh QR',
                'keywords' => 'khách mời, rsvp, xếp bàn tiệc, sơ đồ bàn, điểm danh qr, quét mã qr, link mời riêng, gửi thiệp zalo',
                'answer' => 'Trong mục Tài khoản -> Quản lý khách mời, bạn có thể nhập danh sách từ Excel/CSV, tạo link thiệp cá nhân hóa kèm tên từng khách, theo dõi tự động số người lớn/trẻ em xác nhận tham dự (RSVP), xếp bàn tiệc trực quan và dùng cổng Điểm danh QR bằng mã PIN lễ tân 6 số ngay tại sảnh cưới.',
                'template_slug' => null,
                'doc_url' => '/docs',
                'priority' => 8,
            ],
            [
                'category' => 'pricing',
                'title' => 'Bảng giá các gói thiệp cưới Mây Hẹn và thanh toán VietQR',
                'keywords' => 'bảng giá, bao nhiêu tiền, gói miễn phí, gói plus, gói pro, 99k, thanh toán vietqr, nâng cấp gói',
                'answer' => 'Mây Hẹn có gói Miễn phí (0đ) để bạn trải nghiệm tạo thiệp ngay, gói Plus (99.000đ) mở khóa kho mẫu hoa lá, nhạc nền, album ảnh & quản lý RSVP đầy đủ, và gói Pro dành cho các mẫu cao cấp phong thư hoàng gia tem sáp 3D, tách link hai họ. Thanh toán tự động xác nhận qua VietQR trong vài giây.',
                'template_slug' => null,
                'doc_url' => '/docs',
                'priority' => 8,
            ],
            [
                'category' => 'docs',
                'title' => 'Đổi mẫu thiệp cưới khi đã nhập xong thông tin có bị mất dữ liệu không?',
                'keywords' => 'đổi mẫu thiệp, chuyển mẫu, có mất dữ liệu không, giữ nguyên ảnh, giữ nguyên thông tin',
                'answer' => 'Không hề mất dữ liệu! Toàn bộ mẫu thiệp trên Mây Hẹn dùng chung một chuẩn 10 phần thống nhất. Bạn có thể đổi sang bất kỳ mẫu nào khác ngay trong Studio và toàn bộ tên cặp đôi, ngày cưới, gia đình, địa điểm, ảnh cưới và nhạc nền sẽ tự động đồng bộ sang giao diện mới.',
                'template_slug' => null,
                'doc_url' => '/docs/studio-editor/CLI-EDIT-011',
                'priority' => 8,
            ],
        ];

        foreach ($entries as $item) {
            AiKnowledgeEntry::query()->firstOrCreate(
                ['title' => $item['title'], 'source' => AiKnowledgeEntry::SOURCE_ADMIN],
                array_merge($item, [
                    'source' => AiKnowledgeEntry::SOURCE_ADMIN,
                    'status' => AiKnowledgeEntry::STATUS_APPROVED,
                    'is_active' => true,
                    'approved_at' => now(),
                ])
            );
        }
    }
}
