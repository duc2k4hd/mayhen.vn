<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * All available wedding templates.
     */
    protected array $templates = [
        [
            'id' => 1,
            'slug' => 'envelope-reveal-luxury',
            'name' => 'Hoàng Gia Cung Đình (Envelope Reveal)',
            'category' => 'luxury',
            'category_label' => 'Quiet Luxury',
            'description' => 'Mở nắp phong thư sáp niêm phong cao cấp, thiệp nâng nhẹ nhàng với cánh hoa rơi.',
            'image' => 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
            'badge' => 'Bán Chạy Nhất',
            'accent_color' => '#C5A880',
            'features' => ['Hiệu ứng mở phong thư', 'Nhạc nền tự động', 'RSVP gia đình', 'Bản đồ Google Maps'],
        ],
        [
            'id' => 2,
            'slug' => 'editorial-scroll-vogue',
            'name' => 'Tạp Chí Vogue Cưới (Editorial)',
            'category' => 'editorial',
            'category_label' => 'Editorial Style',
            'description' => 'Bố cục tạp chí thời trang nghệ thuật, tôn vinh hình ảnh và câu chuyện tình yêu mộc mạc.',
            'image' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?auto=format&fit=crop&w=800&q=80',
            'badge' => 'Phong Cách Mới',
            'accent_color' => '#1C1917',
            'features' => ['Bố cục tạp chí thời trang', 'Cuộn thị giác parallax', 'Sổ lời chúc online', 'QR mừng cưới'],
        ],
        [
            'id' => 3,
            'slug' => 'minimal-sand-beige',
            'name' => 'Ánh Dương Tối Giản (Minimal Luxury)',
            'category' => 'minimal',
            'category_label' => 'Tối Giản Tinh Tế',
            'description' => 'Khoảng trắng phóng khoáng, phong cách kem cát ấm áp, tập trung vào typography và cảm xúc.',
            'image' => 'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?auto=format&fit=crop&w=800&q=80',
            'badge' => 'Được Yêu Thích',
            'accent_color' => '#9FAF90',
            'features' => ['Khoảng trắng thở thanh lịch', 'Typography sang trọng', 'Tối ưu siêu nhanh', 'Dễ xem cho người lớn tuổi'],
        ],
    ];

    /**
     * Display template gallery with filter.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $filtered = $this->templates;

        if ($category && in_array($category, ['luxury', 'editorial', 'minimal', 'envelope'])) {
            if ($category === 'envelope') {
                $category = 'luxury';
            }
            $filtered = array_filter($this->templates, fn ($item) => $item['category'] === $category);
        }

        return view('client.pages.templates', [
            'templates' => $filtered,
            'currentCategory' => $request->query('category'),
        ]);
    }

    /**
     * Display template detail page.
     */
    public function show(string $slug): View
    {
        $template = collect($this->templates)->firstWhere('slug', $slug);

        if (! $template) {
            abort(404, 'Mẫu thiệp không tồn tại');
        }

        $similar = collect($this->templates)->where('slug', '!=', $slug)->take(2);

        return view('client.pages.template-detail', [
            'template' => $template,
            'similarTemplates' => $similar,
        ]);
    }

    /**
     * Preview actual live invitation demo.
     */
    public function preview(string $slug): View
    {
        $template = collect($this->templates)->firstWhere('slug', $slug);

        if (! $template) {
            abort(404, 'Bản xem thử không tồn tại');
        }

        return view('client.pages.preview', [
            'template' => $template,
        ]);
    }
}
