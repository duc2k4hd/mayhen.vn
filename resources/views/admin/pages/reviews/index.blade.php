@extends('admin.layouts.master')

@section('title', 'Quản Lý Đánh Giá & Cảm Nhận Khách Hàng')
@section('page_title', 'Đánh Giá Khách Hàng (Customer Reviews)')

@section('content')
<div class="mayhen_admin_page_wrapper">
    {{-- Header & Breadcrumbs --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9C7A4A; font-weight: 700; margin-bottom: 4px;">
                Mục 7.14 DOCX • Quản Trị Cảm Nhận Khách Hàng Thực Tế
            </div>
            <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 1.85rem; font-weight: 600; color: #1F1D1A; margin: 0;">
                Kiểm Duyệt & Quản Trị Đánh Giá (CSAT)
            </h1>
            <p style="font-size: 0.88rem; color: #7A726A; margin: 4px 0 0;">
                Toàn bộ đánh giá đều xuất phát từ cặp đôi thực tế đã tạo thiệp trên hệ thống. Tuyệt đối không sinh đánh giá giả mạo.
            </p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div style="background: #FFF8E1; border-left: 4px solid #F57F17; color: #E65100; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #FDEDEC; border-left: 4px solid #E74C3C; color: #922B21; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="padding: 16px 20px; border-left: 4px solid #9C7A4A;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Tổng Số Đánh Giá</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #1F1D1A;">{{ number_format($kpis['total']) }}</div>
            <div style="font-size: 0.78rem; color: #7A726A; margin-top: 2px;">Cặp đôi đã phản hồi</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px 20px; border-left: 4px solid #F57F17;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Chờ Kiểm Duyệt</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #F57F17;">{{ number_format($kpis['pending']) }}</div>
            <div style="font-size: 0.78rem; color: #7A726A; margin-top: 2px;">Cần xem xét xuất bản</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px 20px; border-left: 4px solid #2E7D32;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Đã Xuất Bản</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #2E7D32;">{{ number_format($kpis['approved']) }}</div>
            <div style="font-size: 0.78rem; color: #7A726A; margin-top: 2px;">{{ $kpis['featured'] }} đánh giá ghim trang chủ</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px 20px; border-left: 4px solid #B8935D;">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Điểm Hài Lòng (CSAT)</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #9C7A4A;">
                {{ $kpis['avg_rating'] }} <span style="font-size: 1.1rem; color: #D4AF37;">★</span>
            </div>
            <div style="font-size: 0.78rem; color: #7A726A; margin-top: 2px;">Thang điểm 5.0 tối đa</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mayhen_admin_card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form action="{{ route('admin.reviews.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 220px;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tìm tên cặp đôi, tiêu đề hoặc nội dung..." 
                    class="mayhen_admin_input"
                    style="width: 100%;"
                >
            </div>

            <div style="min-width: 160px;">
                <select name="status" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ kiểm duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Đã ẩn / Từ chối</option>
                </select>
            </div>

            <div style="min-width: 130px;">
                <select name="rating" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Số sao</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Sao (Tuyệt vời)</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Sao (Hài lòng)</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Sao (Bình thường)</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Sao (Chưa hài lòng)</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Sao (Kém)</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <select name="featured" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Ghim trang chủ</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Đang ghim trang chủ</option>
                </select>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 8px 18px;">
                Lọc Đánh Giá
            </button>

            @if(request()->hasAny(['search', 'status', 'rating', 'featured']))
                <a href="{{ route('admin.reviews.index') }}" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="padding: 8px 14px;">
                    Đặt Lại
                </a>
            @endif
        </form>
    </div>

    {{-- Reviews Table --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <table class="mayhen_admin_table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 180px;">Cặp Đôi & Ngày Cưới</th>
                    <th style="width: 150px;">Mẫu Thiệp</th>
                    <th style="width: 110px;">Số Sao</th>
                    <th>Nội Dung Cảm Nhận Thực Tế</th>
                    <th style="width: 130px; text-align: center;">Trạng Thái</th>
                    <th style="width: 90px; text-align: center;">Trang Chủ</th>
                    <th style="width: 150px; text-align: right;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    @php
                        $badgeStyle = $review->getStatusBadgeStyle();
                    @endphp
                    <tr>
                        <td style="font-family: monospace; font-size: 0.85rem; color: #7A726A;">
                            #{{ $review->id }}
                        </td>
                        <td>
                            <strong style="color: #1F1D1A; display: block; font-size: 0.92rem;">
                                {{ $review->couple_name }}
                            </strong>
                            <span style="font-size: 0.78rem; color: #7A726A;">
                                @if($review->wedding_date)
                                    Cưới: {{ $review->wedding_date->format('d/m/Y') }}
                                @else
                                    Khách hàng #{{ $review->user_id }}
                                @endif
                            </span>
                            @if($review->verified_purchase)
                                <span style="display: inline-block; font-size: 0.68rem; background: #E8F5E9; color: #2E7D32; padding: 1px 6px; border-radius: 4px; margin-top: 3px;">
                                    ✓ Đã Xác Thực
                                </span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 0.84rem; color: #4A453E;">
                                {{ $review->template_name }}
                            </span>
                        </td>
                        <td>
                            <div style="color: #D4AF37; font-size: 1rem; letter-spacing: 2px;" title="{{ $review->rating }} sao">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $review->rating ? '★' : '☆' }}
                                @endfor
                            </div>
                        </td>
                        <td>
                            @if($review->title)
                                <strong style="color: #1F1D1A; font-size: 0.88rem; display: block; margin-bottom: 2px;">
                                    {{ $review->title }}
                                </strong>
                            @endif
                            <div style="font-size: 0.84rem; color: #4A453E; line-height: 1.5;">
                                {{ $review->content }}
                            </div>
                            <span style="font-size: 0.72rem; color: #9E9E9E; margin-top: 4px; display: block;">
                                Gửi lúc: {{ $review->created_at->format('H:i d/m/Y') }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; background: {{ $badgeStyle['bg'] }}; color: {{ $badgeStyle['color'] }}; border: 1px solid {{ $badgeStyle['border'] }};">
                                {{ $review->status_label }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <form action="{{ route('admin.reviews.toggle_featured', $review) }}" method="POST" style="display: inline;">
                                @csrf
                                <button 
                                    type="submit" 
                                    style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: {{ $review->is_featured ? '#D4AF37' : '#D0C8BE' }};"
                                    title="{{ $review->is_featured ? 'Bấm để gỡ ghim khỏi trang chủ' : 'Bấm để ghim lên trang chủ' }}"
                                >
                                    {{ $review->is_featured ? '★' : '☆' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                @if($review->status !== 'approved')
                                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="mayhen_admin_btn mayhen_admin_btn_sm"
                                            style="background: #2E7D32; color: #FFFFFF;"
                                            title="Phê duyệt hiển thị đánh giá"
                                        >
                                            ✓ Duyệt
                                        </button>
                                    </form>
                                @endif

                                @if($review->status !== 'rejected')
                                    <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="mayhen_admin_btn mayhen_admin_btn_sm"
                                            style="background: #FFF8E1; color: #E65100; border: 1px solid #FFE082;"
                                            title="Ẩn hoặc từ chối đánh giá"
                                        >
                                            Ẩn
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn đánh giá này không?');">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="mayhen_admin_btn mayhen_admin_btn_sm"
                                        style="background: #FDEDEC; color: #922B21; border: 1px solid #F5B7B1;"
                                        title="Xóa đánh giá"
                                    >
                                        🗑
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px 16px; color: #7A726A;">
                            <div style="font-size: 2rem; margin-bottom: 8px;">💌</div>
                            <strong style="color: #1F1D1A; display: block; margin-bottom: 4px;">Chưa có đánh giá nào phù hợp bộ lọc</strong>
                            <span style="font-size: 0.85rem;">Các đánh giá do cặp đôi gửi lên sẽ xuất hiện tại đây để quản trị viên kiểm duyệt.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($reviews->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #EFE8DD;">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
