@extends('client.pages.account.layout')

@section('page_title', 'Nhắc Hẹn Kỷ Niệm Ngày Cưới 1 Năm & Hàng Năm | ' . config('app.name'))
@section('account_breadcrumb', 'Kỷ Niệm Ngày Cưới')

@section('account_content')
<div class="mayhen_anniversary_studio" style="max-width: 1080px; margin: 0 auto;">
    {{-- Topbar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp cưới</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Nhắc Hẹn Kỷ Niệm Ngày Cưới (Anniversary Keepsake Suite)
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Cặp đôi <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • Tự động gửi thư chúc mừng trang trọng vào mỗi dịp tròn năm ngày cưới.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('anniversary.lounge', $invitation->slug) }}" target="_blank" 
               class="mayhen_account_btn" 
               style="background: #1F1D1A; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <span>Xem Trang Kỷ Niệm &rarr;</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 4 Thẻ KPI Dòng Thời Gian Hôn Nhân --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Thời Gian Chung Đôi</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ number_format($stats['days_married']) }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">ngày</span>
            </div>
            <div style="font-size: 12.5px; color: #9C7A4A; margin-top: 4px;">
                Ngày cưới: {{ $stats['wedding_date'] }}
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Thâm Niên Hôn Nhân</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ $stats['years_married'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">năm</span>
            </div>
            <div style="font-size: 12.5px; color: #6B7280; margin-top: 4px;">
                {{ $stats['current_milestone_name'] }}
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Đếm Ngược Kỷ Niệm Tới</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #C5A880; line-height: 1.1;">
                {{ $stats['days_until_next_anniversary'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">ngày nữa</span>
            </div>
            <div style="font-size: 12.5px; color: #555; margin-top: 4px;">
                Ngày: {{ $stats['next_anniversary_date'] }}
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Mốc Kỷ Niệm Tiếp Theo</div>
            <div style="font-size: 18px; font-weight: 600; color: #1F1D1A; margin-top: 4px; line-height: 1.3;">
                {{ $stats['next_milestone_name'] }}
            </div>
            <div style="font-size: 12.5px; color: #047857; margin-top: 6px; font-weight: 500;">
                Mốc tròn {{ $stats['next_anniversary_year'] }} năm
            </div>
        </div>
    </div>

    {{-- Hai Cột Cấu Hình & Trắc Nghiệm --}}
    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-bottom: 32px;">
        {{-- Cột Trái: Form Cấu Hình Nhắc Hẹn --}}
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 16px 0; font-weight: 600;">
                Cấu Hình Email Nhắc Hẹn Kỷ Niệm
            </h3>

            <form action="{{ route('account.anniversary.update', $invitation->id) }}" method="POST">
                @csrf

                <div style="margin-bottom: 20px; padding: 14px 18px; background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="enable_reminders" value="1" {{ $config['enable_reminders'] ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #1F1D1A;">
                        <span style="font-size: 14px; font-weight: 600; color: #1F1D1A;">
                            Tự động gửi email chúc mừng vào ngày kỷ niệm hàng năm
                        </span>
                    </label>
                    <p style="margin: 6px 0 0 28px; font-size: 12.5px; color: #8C827A; line-height: 1.5;">
                        Hệ thống sẽ tự động kích hoạt gửi thư vào 08:00 sáng ngày kỷ niệm ngày cưới kèm lời chúc và liên kết mở lại trang kỷ niệm.
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                            Email Nhận Thư 1 (Bắt buộc)
                        </label>
                        <input type="email" name="notification_email_1" value="{{ old('notification_email_1', $config['notification_email_1']) }}" required 
                               style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                            Email Nhận Thư 2 (Tùy chọn - Của bạn đời)
                        </label>
                        <input type="email" name="notification_email_2" value="{{ old('notification_email_2', $config['notification_email_2']) }}" 
                               style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Đường dẫn Ảnh Kỷ Niệm 1 Năm (URL)
                    </label>
                    <input type="text" name="anniversary_photo_url" value="{{ old('anniversary_photo_url', $config['anniversary_photo_url']) }}" placeholder="https://... ảnh kỷ niệm sau 1 năm chung đôi" 
                           style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                    <span style="font-size: 12px; color: #8C827A; margin-top: 4px; display: block;">
                        Bức ảnh chụp hai bạn sau 1 năm hôn nhân, hiển thị đối sánh cạnh ảnh cưới ngày xưa trên trang Kỷ Niệm.
                    </span>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Cảm Nhận Sau 1 Năm Hôn Nhân (Nhật Ký Kỷ Niệm)
                    </label>
                    <textarea name="anniversary_reflection_note" rows="4" 
                              style="width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none; line-height: 1.5;">{{ old('anniversary_reflection_note', $config['anniversary_reflection_note']) }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="submit" 
                            style="background: #1F1D1A; color: #FFFFFF; padding: 11px 24px; border-radius: 8px; font-size: 13.5px; border: none; cursor: pointer; font-weight: 600;">
                        Lưu Cấu Hình Nhắc Hẹn
                    </button>
                </div>
            </form>
        </div>

        {{-- Cột Phải: Thử Nghiệm & Bản Xem Trước --}}
        <div>
            <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin-bottom: 20px;">
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0 0 10px 0; font-weight: 600;">
                    Thử Nghiệm Gửi Email Kỷ Niệm
                </h4>
                <p style="font-size: 13px; color: #6B7280; line-height: 1.5; margin: 0 0 16px 0;">
                    Hệ thống sẽ gửi ngay một bức thư mẫu chúc mừng kỷ niệm ngày cưới tới email <strong>{{ $config['notification_email_1'] ?: 'của bạn' }}</strong> để kiểm tra hiển thị.
                </p>

                <form action="{{ route('account.anniversary.test_send', $invitation->id) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            style="width: 100%; background: #FAF8F5; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 11px 18px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span>Gửi Thử Email Chúc Mừng</span>
                    </button>
                </form>
            </div>

            {{-- Thẻ Xem Nhanh Trang Kỷ Niệm --}}
            <div style="background: linear-gradient(135deg, #1F1D1A 0%, #2C2824 100%); color: #FFFFFF; border-radius: 14px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 12px; letter-spacing: 0.12em; text-transform: uppercase; color: #C5A880; font-weight: 600; margin-bottom: 8px;">
                    Anniversary Keepsake Lounge
                </div>
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; margin: 0 0 10px 0; color: #FAF8F5;">
                    Trang Kỷ Niệm Thiêng Liêng
                </h4>
                <p style="font-size: 13px; color: #D8CEBD; line-height: 1.6; margin: 0 0 18px 0;">
                    Nơi cặp đôi mở lại chiếc thiệp cưới năm xưa, đọc lại hàng chục lời chúc tốt đẹp từ người thân, bạn bè và lưu lại những khoảnh khắc sau 1 năm về chung đôi.
                </p>
                <a href="{{ route('anniversary.lounge', $invitation->slug) }}" target="_blank" 
                   style="display: block; text-align: center; background: #C5A880; color: #1F1D1A; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; text-decoration: none; font-weight: 700;">
                    Mở Ngay Trong Tab Mới &rarr;
                </a>
            </div>
        </div>
    </div>

    {{-- Bảng Các Mốc Kỷ Niệm Ngày Cưới Truyền Thống & Ý Nghĩa --}}
    <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 16px 0; font-weight: 600;">
            Lộ Trình Các Cột Mốc Kỷ Niệm Hôn Nhân
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">1 Năm • Đám Cưới Giấy</strong>
                    <span style="background: #DEF7EC; color: #046C4E; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">Khởi đầu</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Tượng trưng cho sự mỏng manh nhưng thuần khiết, cùng nhau viết nên trang nhật ký đầu tiên.</p>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">2 Năm • Đám Cưới Bông</strong>
                    <span style="font-size: 12px; color: #8C827A;">Cotton</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Đan cài vào nhau như những sợi bông mềm mại, ấm áp và ngày càng khăng khít.</p>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">5 Năm • Đám Cưới Gỗ</strong>
                    <span style="background: #FEF3C7; color: #92400E; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">Vững chãi</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Tình yêu bén rễ sâu sắc như cây cổ thụ, vững vàng trước mọi sóng gió cuộc đời.</p>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">10 Năm • Đám Cưới Thiếc</strong>
                    <span style="font-size: 12px; color: #8C827A;">1 Thập kỷ</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Bền bỉ, dẻo dai và không bao giờ rỉ sét qua năm tháng thời gian.</p>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">25 Năm • Đám Cưới Bạc</strong>
                    <span style="background: #E0E7FF; color: #3730A3; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">Cao quý</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Ánh sáng bạc thanh cao, minh chứng cho một phần tư thế kỷ bên nhau son sắt.</p>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px; padding: 14px 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <strong style="color: #1F1D1A; font-size: 14px;">50 Năm • Đám Cưới Vàng</strong>
                    <span style="background: #FEF3C7; color: #B45309; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 700;">Hoàng kim</span>
                </div>
                <p style="font-size: 12.5px; color: #6B7280; margin: 0; line-height: 1.4;">Nửa thế kỷ đồng hành, báu vật tình yêu vô giá và nguồn cảm hứng bất diệt.</p>
            </div>
        </div>
    </div>
</div>
@endsection
