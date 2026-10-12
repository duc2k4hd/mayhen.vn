<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function services(): View
    {
        return view('client.pages.services');
    }

    public function pricing(): View
    {
        $plans = [
            [
                'name' => 'Trải Nghiệm (Free)',
                'price' => '0đ',
                'description' => 'Tạo thiệp dùng thử và xem trước đầy đủ tính năng.',
                'features' => [
                    'Tạo 1 thiệp cưới cơ bản',
                    'Xem trước không giới hạn',
                    'Hiển thị thông tin cưới & bản đồ',
                    'Hạn lưu trữ 7 ngày',
                ],
                'is_featured' => false,
                'cta' => 'Tạo Thiệp Miễn Phí',
            ],
            [
                'name' => 'Hạnh Phúc (Premium)',
                'price' => '149.000đ',
                'period' => '/đám cưới',
                'description' => 'Gói phổ biến nhất, đáp ứng hoàn hảo cho mọi cặp đôi.',
                'features' => [
                    'Xuất bản link thiệp riêng chính thức',
                    'Mở khóa toàn bộ 3 mẫu thiệp cao cấp',
                    'Hiệu ứng phong thư & nhạc nền lãng mạn',
                    'RSVP xác nhận tham dự theo gia đình',
                    'QR mừng cưới chính chủ (không thu phí)',
                    'Lưu giữ kỷ niệm 6 tháng sau cưới',
                ],
                'is_featured' => true,
                'cta' => 'Chọn Gói Premium',
            ],
            [
                'name' => 'Trọn Vẹn (Wedding Pro)',
                'price' => '299.000đ',
                'period' => '/đám cưới',
                'description' => 'Dành cho tiệc cưới quy mô lớn, cần cá nhân hóa tối đa.',
                'features' => [
                    'Tất cả tính năng của gói Premium',
                    'Nhân bản thiệp Nhà Trai / Nhà Gái riêng biệt',
                    'Công cụ gửi thiệp cá nhân hóa qua Zalo',
                    'Quản lý danh sách khách mời & xuất Excel',
                    'Sổ lời chúc online & album ảnh kỷ niệm',
                    'Lưu giữ kỷ niệm vĩnh viễn 2 năm',
                ],
                'is_featured' => false,
                'cta' => 'Chọn Gói Pro',
            ],
        ];

        return view('client.pages.pricing', ['plans' => $plans]);
    }

    public function howItWorks(): View
    {
        return view('client.pages.how-it-works');
    }

    public function about(): View
    {
        return view('client.pages.about');
    }

    public function contact(): View
    {
        return view('client.pages.contact');
    }

    public function faq(): View
    {
        return view('client.pages.faq');
    }

    public function terms(): View
    {
        return view('client.pages.terms');
    }

    public function privacy(): View
    {
        return view('client.pages.privacy');
    }
}
