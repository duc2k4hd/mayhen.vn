@extends('partner.layouts.master')

@section('title', 'Đăng Ký Đối Tác Studio & Wedding Planner | ' . config('app.name'))

@section('content')
<div style="max-width: 780px; margin: 0 auto;">
    {{-- Header Banner --}}
    <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="mayhen_partner_portal_pill">MẠNG LƯỚI ĐỐI TÁC MÂY HẸN</span>
        <h1 style="font-family: var(--font-heading, 'Cormorant Garamond', Georgia, serif); font-size: 2.5rem; font-weight: 600; color: #1F1D1A; margin: 0.5rem 0;">
            Gia Nhập Cổng Đối Tác Studio & Wedding Planner
        </h1>
        <p style="font-size: 1rem; color: #6E685F; max-width: 580px; margin: 0 auto; line-height: 1.6;">
            Nâng tầm dịch vụ cưới, khởi tạo thiệp cưới không giới hạn cho khách hàng, gắn logo thương hiệu độc quyền và nhận chiết khấu hoa hồng hấp dẫn.
        </p>
    </div>

    @if(session('warning'))
        <div class="mayhen_partner_alert mayhen_partner_alert--error">
            {{ session('warning') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mayhen_partner_alert mayhen_partner_alert--error">
            {{ session('error') }}
        </div>
    @endif

    {{-- 3 Key Benefits Bar --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 2.5rem;">
        <div style="background: #FFFFFF; border: 1px solid #EBE5DC; border-radius: 12px; padding: 1.25rem; text-align: center;">
            <div style="font-size: 1.75rem; margin-bottom: 0.35rem;">⚜️</div>
            <strong style="display: block; font-size: 0.92rem; color: #1F1D1A; margin-bottom: 0.25rem;">Co-Branding Độc Quyền</strong>
            <span style="font-size: 0.78rem; color: #8C8479;">Khắc tên & logo Studio ở chân thiệp của khách</span>
        </div>
        <div style="background: #FFFFFF; border: 1px solid #EBE5DC; border-radius: 12px; padding: 1.25rem; text-align: center;">
            <div style="font-size: 1.75rem; margin-bottom: 0.35rem;">💰</div>
            <strong style="display: block; font-size: 0.92rem; color: #1F1D1A; margin-bottom: 0.25rem;">Chiết Khấu 20% - 30%</strong>
            <span style="font-size: 0.78rem; color: #8C8479;">Hoa hồng tích lũy tự động theo từng đơn hàng</span>
        </div>
        <div style="background: #FFFFFF; border: 1px solid #EBE5DC; border-radius: 12px; padding: 1.25rem; text-align: center;">
            <div style="font-size: 1.75rem; margin-bottom: 0.35rem;">🔑</div>
            <strong style="display: block; font-size: 0.92rem; color: #1F1D1A; margin-bottom: 0.25rem;">Bàn Giao 1-Chạm</strong>
            <span style="font-size: 0.78rem; color: #8C8479;">Chuyển giao quyền chỉnh sửa cho cặp đôi bảo mật</span>
        </div>
    </div>

    {{-- Registration Form Card --}}
    <div class="mayhen_partner_card">
        <form action="{{ route('partner.register.store') }}" method="POST">
            @csrf

            <div class="form_group">
                <label class="form_label" for="business_name">Tên Studio / Ảnh Viện / Wedding Planner *</label>
                <input 
                    type="text" 
                    name="business_name" 
                    id="business_name" 
                    class="form_input" 
                    required 
                    placeholder="Ví dụ: Élysee Wedding Studio, The Planners Vietnam..." 
                    value="{{ old('business_name') }}"
                >
                @error('business_name')
                    <span style="color: #C62828; font-size: 0.8rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form_group">
                    <label class="form_label" for="phone">Số điện thoại liên hệ *</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        class="form_input" 
                        required 
                        placeholder="0912 345 678" 
                        value="{{ old('phone', auth()->user()?->phone) }}"
                    >
                    @error('phone')
                        <span style="color: #C62828; font-size: 0.8rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form_group">
                    <label class="form_label" for="email">Email doanh nghiệp *</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form_input" 
                        required 
                        placeholder="contact@studio.vn" 
                        value="{{ old('email', auth()->user()?->email) }}"
                    >
                    @error('email')
                        <span style="color: #C62828; font-size: 0.8rem; margin-top: 0.3rem; display: block;">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form_group">
                    <label class="form_label" for="partner_type">Lĩnh vực hoạt động chính</label>
                    <select name="partner_type" id="partner_type" class="form_select">
                        <option value="wedding_studio">Studio Áo Cưới & Chụp Ảnh Cưới</option>
                        <option value="wedding_planner">Wedding Planner & Điều Phối Tiệc Cưới</option>
                        <option value="venue_hotel">Trung Tâm Tiệc Cưới & Khách Sạn</option>
                        <option value="makeup_stylist">Chuyên Viên Trang Điểm / Stylist Cưới</option>
                        <option value="other">Dịch Vụ Cưới Khác</option>
                    </select>
                </div>

                <div class="form_group">
                    <label class="form_label" for="website">Website / Fanpage / TikTok (tùy chọn)</label>
                    <input 
                        type="url" 
                        name="website" 
                        id="website" 
                        class="form_input" 
                        placeholder="https://facebook.com/studio..." 
                        value="{{ old('website') }}"
                    >
                </div>
            </div>

            <div class="form_group">
                <label class="form_label" for="address">Địa chỉ cơ sở Studio</label>
                <input 
                    type="text" 
                    name="address" 
                    id="address" 
                    class="form_input" 
                    placeholder="Ví dụ: 128 Nguyễn Trãi, Quận 1, TP. Hồ Chí Minh" 
                    value="{{ old('address') }}"
                >
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--gold" style="width: 100%; padding: 0.95rem; font-size: 1rem;">
                    ✨ Hoàn Tất Đăng Ký & Kích Hoạt Cổng Đối Tác
                </button>
            </div>

            <p style="text-align: center; font-size: 0.8rem; color: #8C8479; margin-top: 1rem; line-height: 1.5;">
                Bằng việc đăng ký, bạn đồng ý với Điều khoản Đối tác & Chính sách bảo mật dữ liệu khách hàng của Mây Hẹn.
            </p>
        </form>
    </div>
</div>
@endsection

