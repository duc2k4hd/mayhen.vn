<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the May Hen marketing landing page.
     */
    public function index(): View
    {
        $featuredTemplates = [
            [
                'id' => 1,
                'slug' => 'envelope-reveal-luxury',
                'name' => 'Hoàng Gia Cung Đình (Envelope Reveal)',
                'category' => 'luxury',
                'category_label' => 'Quiet Luxury',
                'description' => 'Hiệu ứng mở phong thư sáp niêm phong cao cấp, thiệp nâng nhẹ nhàng với cánh hoa rơi.',
                'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
                'badge' => 'Thịnh Hành Nhất',
            ],
            [
                'id' => 2,
                'slug' => 'editorial-scroll-vogue',
                'name' => 'Tạp Chí Vogue Cưới (Editorial)',
                'category' => 'editorial',
                'category_label' => 'Editorial Style',
                'description' => 'Bố cục tạp chí thời trang nghệ thuật, tôn vinh hình ảnh và câu chuyện tình yêu mộc mạc.',
                'image' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
                'badge' => 'Hiện Đại',
            ],
            [
                'id' => 3,
                'slug' => 'minimal-sand-beige',
                'name' => 'Ánh Dương Tối Giản (Minimal Luxury)',
                'category' => 'minimal',
                'category_label' => 'Tối Giản Tinh Tế',
                'description' => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào typography và cảm xúc.',
                'image' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
                'badge' => 'Thanh Lịch',
            ],
        ];

        return view('client.pages.home', [
            'featuredTemplates' => $featuredTemplates,
        ]);
    }
}
