@extends('client.pages.account.layout')

@section('page_title', 'Trung Tâm Hỗ Trợ & Yêu Cầu | ' . config('app.name'))
@section('account_breadcrumb', 'Hỗ Trợ & Yêu Cầu')

@section('account_content')
<div class="mayhen_account_support_wrapper">

    {{-- Banner VIP Concierge 48h (Dành cho cặp đôi có đám cưới trong 48h tới) --}}
    @if($isVip48h)
        <div class="mayhen_account_support_vip_banner">
            <div class="mayhen_account_support_vip_icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div class="mayhen_account_support_vip_content">
                <h4 class="mayhen_account_support_vip_title">Hàng Đợi Ưu Tiên VIP Concierge — Tiệc Cưới Trong 48 Giờ</h4>
                <p class="mayhen_account_support_vip_desc">
                    Hệ thống nhận diện ngày cưới của bạn đang đến rất gần! Mọi yêu cầu hỗ trợ gửi từ tài khoản của bạn sẽ được tự động kích hoạt chế độ <strong>Khẩn cấp (SLA phản hồi dưới 60 phút)</strong> và chuyển thẳng tới nhóm kỹ thuật trực ngày cưới.
                </p>
            </div>
        </div>
    @endif

    {{-- Header & Hành Động --}}
    <div class="mayhen_account_section_header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 class="mayhen_account_section_title" style="margin: 0; font-size: 22px;">Trung Tâm Hỗ Trợ & Yêu Cầu</h2>
            <p class="mayhen_account_section_subtitle" style="margin: 4px 0 0; color: #8C827A; font-size: 14px;">
                Đội ngũ chuyên viên Mây Hẹn luôn đồng hành để đảm bảo thiệp cưới và ngày vui của bạn trọn vẹn nhất.
            </p>
        </div>
        <div>
            <a href="#form_gui_ticket" class="mayhen_account_btn mayhen_account_btn_gold" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tạo Yêu Cầu Hỗ Trợ Mới</span>
            </a>
        </div>
    </div>

    {{-- Thẻ Tóm Tắt Trạng Thái --}}
    <div class="mayhen_account_kpi_grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 28px;">
        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Tổng Số Yêu Cầu</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ $tickets->total() }}</div>
            <div class="mayhen_account_kpi_desc">Lịch sử trợ giúp từ Mây Hẹn</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Đang Xử Lý</span>
                <svg class="mayhen_account_kpi_icon" style="color: #9C7A4A;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #B8935D;">{{ $openTicketsCount }}</div>
            <div class="mayhen_account_kpi_desc">Đang được chuyên viên tiếp nhận</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Đã Hoàn Tất</span>
                <svg class="mayhen_account_kpi_icon" style="color: #2E7D32;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #2E7D32;">{{ $resolvedTicketsCount }}</div>
            <div class="mayhen_account_kpi_desc">Yêu cầu đã được giải quyết xong</div>
        </div>
    </div>

    {{-- Danh Sách Yêu Cầu --}}
    <div class="mayhen_account_card" style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 24px; margin-bottom: 32px;">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0 0 16px;">
            Lịch Sử Yêu Cầu Của Bạn
        </h3>

        @if($tickets->isEmpty())
            <div style="text-align: center; padding: 48px 20px;">
                <div style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; background: #FAF8F5; border: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: center; color: #C5A880;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <h4 style="font-size: 16px; font-weight: 600; color: #1F1D1A; margin: 0 0 8px;">Bạn chưa có yêu cầu hỗ trợ nào</h4>
                <p style="font-size: 14px; color: #8C827A; max-width: 440px; margin: 0 auto 20px;">
                    Nếu bạn gặp bất kỳ trở ngại nào trong quá trình chỉnh sửa thiệp, tải ảnh hay thiết lập bàn tiệc, hãy điền biểu mẫu bên dưới để được trợ giúp ngay.
                </p>
            </div>
        @else
            <div class="mayhen_account_table_responsive" style="overflow-x: auto;">
                <table class="mayhen_account_table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 1px solid #EAE3D9; color: #8C827A; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                            <th style="padding: 12px 16px;">Mã Ticket</th>
                            <th style="padding: 12px 16px;">Tiêu Đề</th>
                            <th style="padding: 12px 16px;">Phân Loại</th>
                            <th style="padding: 12px 16px;">Mức Độ</th>
                            <th style="padding: 12px 16px;">Trạng Thái</th>
                            <th style="padding: 12px 16px;">Ngày Gửi</th>
                            <th style="padding: 12px 16px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $t)
                            <tr style="border-bottom: 1px solid #F5EFEB;">
                                <td style="padding: 14px 16px; font-family: monospace; font-weight: 600; color: #9C7A4A;">
                                    {{ $t->ticket_code }}
                                </td>
                                <td style="padding: 14px 16px;">
                                    <a href="{{ route('account.support.show', $t->ticket_code) }}" style="color: #1F1D1A; text-decoration: none; font-weight: 500;">
                                        {{ Str::limit($t->subject, 45) }}
                                    </a>
                                </td>
                                <td style="padding: 14px 16px; color: #6B635B;">
                                    @switch($t->category)
                                        @case('technical') Kỹ thuật thiệp @break
                                        @case('billing') Gói cước & Đơn hàng @break
                                        @case('invitation') Nội dung thiệp @break
                                        @default Hỗ trợ chung
                                    @endswitch
                                </td>
                                <td style="padding: 14px 16px;">
                                    @if($t->priority === 'urgent')
                                        <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #FDE8E8; color: #9B1C1C;">Khẩn cấp</span>
                                    @elseif($t->priority === 'high')
                                        <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; background: #FEF08A; color: #854D0E;">Cao</span>
                                    @elseif($t->priority === 'medium')
                                        <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; background: #E0F2FE; color: #0369A1;">Trung bình</span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; background: #F3F4F6; color: #4B5563;">Thường</span>
                                    @endif
                                </td>
                                <td style="padding: 14px 16px;">
                                    @if($t->status === 'open')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #FEF3C7; color: #92400E;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #D97706;"></span> Mới
                                        </span>
                                    @elseif($t->status === 'in_progress')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #DBEAFE; color: #1E40AF;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #2563EB;"></span> Đang xử lý
                                        </span>
                                    @elseif($t->status === 'resolved')
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #0E9F6E;"></span> Đã giải quyết
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; background: #F3F4F6; color: #6B7280;">
                                            Đã đóng
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 14px 16px; color: #8C827A; font-size: 13px;">
                                    {{ $t->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <a href="{{ route('account.support.show', $t->ticket_code) }}" class="mayhen_account_btn" style="padding: 6px 14px; font-size: 13px; text-decoration: none;">
                                        Xem Chi Tiết &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div style="margin-top: 20px;">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Form Gửi Ticket Mới --}}
    <div id="form_gui_ticket" class="mayhen_account_card" style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 32px;">
        <div style="margin-bottom: 24px;">
            <span style="font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #9C7A4A; font-weight: 600;">Gửi Yêu Cầu Hỗ Trợ Mới</span>
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 4px 0 8px;">
                Bạn Cần Đội Ngũ Mây Hẹn Trợ Giúp Vấn Đề Gì?
            </h3>
            <p style="font-size: 14px; color: #6B635B; margin: 0;">
                Vui lòng điền thông tin chi tiết dưới đây. Hệ thống sẽ tự động chỉ định chuyên viên và gửi phản hồi trực tiếp vào tài khoản của bạn.
            </p>
        </div>

        @if($errors->any())
            <div class="mayhen_account_alert mayhen_account_alert_danger" style="margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('account.support.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 8px;">
                        Phân Loại Yêu Cầu <span style="color: #9B1C1C;">*</span>
                    </label>
                    <select name="category" required style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 10px; font-size: 14px; background: #FAF8F5; color: #1F1D1A;">
                        <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Sự cố kỹ thuật / Lỗi hiển thị thiệp cưới</option>
                        <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Thanh toán, Kích hoạt gói cước & Hoàn tiền</option>
                        <option value="invitation" {{ old('category') === 'invitation' ? 'selected' : '' }}>Hỗ trợ chỉnh sửa nội dung, ảnh & bố cục thiệp</option>
                        <option value="general" {{ old('category', 'general') === 'general' ? 'selected' : '' }}>Thắc mắc chung & Tư vấn tính năng</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 8px;">
                        Mức Độ Khẩn Cấp <span style="color: #9B1C1C;">*</span>
                    </label>
                    <select name="priority" required style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 10px; font-size: 14px; background: #FAF8F5; color: #1F1D1A;">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Bình thường (Phản hồi trong 24 giờ)</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Cần sớm (Phản hồi trong 12 giờ)</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Ưu tiên cao (Phản hồi trong 4 giờ)</option>
                        <option value="urgent" {{ old('priority') === 'urgent' || $isVip48h ? 'selected' : '' }}>
                            Khẩn cấp — Đám cưới diễn ra trong 48h (SLA dưới 60 phút)
                        </option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 8px;">
                    Tiêu Đề Yêu Cầu <span style="color: #9B1C1C;">*</span>
                </label>
                <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="Ví dụ: Cần kiểm tra lại ảnh bìa thiệp cưới không tải được trên Safari..." style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 10px; font-size: 14px; background: #FAF8F5; color: #1F1D1A; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 8px;">
                    Nội Dung Chi Tiết <span style="color: #9B1C1C;">*</span>
                </label>
                <textarea name="body" rows="5" required placeholder="Mô tả cụ thể sự cố hoặc nội dung bạn cần hỗ trợ. Nếu có đường link thiệp hoặc thiết bị gặp lỗi, xin vui lòng ghi rõ để chuyên viên hỗ trợ nhanh nhất..." style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 10px; font-size: 14px; background: #FAF8F5; color: #1F1D1A; line-height: 1.6; box-sizing: border-box;">{{ old('body') }}</textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="font-size: 13px; color: #8C827A; display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Yêu cầu của bạn được ghi nhận vào hệ thống giám sát thời gian thực của Mây Hẹn.</span>
                </div>

                <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="padding: 12px 28px; font-size: 14px; font-weight: 600; border: none; cursor: pointer;">
                    Gửi Yêu Cầu Trợ Giúp
                </button>
            </div>
        </form>
    </div>

</div>

<style>
.mayhen_account_support_vip_banner {
    background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%);
    border: 1px solid #FDE68A;
    border-radius: 16px;
    padding: 20px 24px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 28px;
    box-shadow: 0 4px 16px rgba(217, 119, 6, 0.08);
}
.mayhen_account_support_vip_icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #F59E0B;
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mayhen_account_support_vip_title {
    font-size: 16px;
    font-weight: 700;
    color: #92400E;
    margin: 0 0 4px;
}
.mayhen_account_support_vip_desc {
    font-size: 14px;
    color: #B45309;
    margin: 0;
    line-height: 1.5;
}
@media (max-width: 768px) {
    .mayhen_account_kpi_grid {
        grid-template-columns: 1fr !important;
    }
    #form_gui_ticket div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection

