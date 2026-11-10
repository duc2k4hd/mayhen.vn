@extends('admin.layouts.master')

@section('title', 'Diễn Tập Trải Nghiệm Khách Mời — ' . ($wedding->groom_name ?? '') . ' & ' . ($wedding->bride_name ?? '') . ' — Mây Hẹn')
@section('page_title', '🎭 Guest Journey Synthetic Rehearsal (Diễn Tập Hành Trình Khách Mời)')

@section('breadcrumb_parent')
    <a href="{{ route('admin.invitations.index') }}">Thiệp Cưới</a> &bull;
    <a href="{{ route('admin.invitations.show', $invitation->id) }}">#{{ $invitation->id }}</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/invitations.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 20px;">
        <div>
            <div class="mayhen_admin_page_title">
                <span>{{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}</span>
                <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 0.75rem;">🎭 Chế Độ Diễn Tập Sandbox</span>
            </div>
            <div class="mayhen_admin_page_subtitle">
                Mã thiệp: <code>{{ $invitation->slug }}</code> &bull; Mẫu: <strong>{{ $template->name ?? 'Mặc định' }}</strong> &bull; Ngày cưới: <strong>{{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định' }}</strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.invitations.show', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Về Chi Tiết Thiệp
            </a>
            <a href="{{ url('/thiep/' . $invitation->slug . '?rehearsal=1') }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_primary">
                Mở Sandbox Tab Mới &nearr;
            </a>
        </div>
    </div>

    <!-- Sandbox Defense Guarantee Banner -->
    <div class="mayhen_admin_card" style="background: #F4FBF7; border-left: 4px solid #2E7D32; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 1.5rem;">🛡️</span>
            <div>
                <strong style="font-size: 0.9375rem; color: #1B5E20;">Môi Trường Giả Lập Cách Ly Hoàn Toàn (Zero DB Pollution Guarantee)</strong>
                <div style="font-size: 0.8125rem; color: #3D5A45; margin-top: 2px;">
                    Mọi hành động điền RSVP thử, gửi lời chúc hay quét mã điểm danh trong phiên diễn tập đều được gắn cờ sandbox và không làm thay đổi số liệu thực tế của cô dâu chú rể.
                </div>
            </div>
        </div>
        <div>
            <span class="mayhen_admin_badge mayhen_admin_badge_success">Sandbox Active</span>
        </div>
    </div>

    <!-- 2 Columns Layout: Left Live Mockup | Right 5 Rehearsal Stages -->
    <div class="mayhen_admin_detail_layout" style="display: grid; grid-template-columns: 360px 1fr; gap: 24px;">
        <!-- Left: Live Phone Mockup with Sandbox Mode -->
        <div class="mayhen_admin_phone_col">
            <div class="mayhen_admin_phone_wrapper">
                <div class="mayhen_admin_phone_island"></div>
                <div class="mayhen_admin_phone_screen">
                    <iframe 
                        src="{{ url('/thiep/' . $invitation->slug . '?embed=1&rehearsal=1') }}" 
                        title="Rehearsal Sandbox Preview {{ $invitation->slug }}"
                        loading="lazy"
                        style="width: 100%; height: 100%; border: none;"
                    ></iframe>
                </div>
            </div>

            <!-- Synthetic Guest Profile Box -->
            <div class="mayhen_admin_card" style="margin-top: 16px; padding: 14px; background: #FFFDF9;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                    Quan Khách Giả Lập Đang Diễn Tập
                </div>
                <div style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">
                    <strong>{{ $rehearsalGuest['name'] }}</strong><br>
                    <span style="font-size: 0.75rem; color: #7A756E;">
                        Nhóm: {{ $rehearsalGuest['group_name'] }} &bull; Bàn: {{ $rehearsalGuest['table_name'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: 5 Stages Rehearsal Guide -->
        <div>
            <!-- Stage 1 -->
            <div class="mayhen_admin_card" style="margin-bottom: 16px; padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        1
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 4px;">
                            Giai Đoạn 1: Mở Thiệp & Hiệu Ứng Khởi Đầu (Reveal & Motion)
                        </h4>
                        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Khách mời nhận link qua Zalo/Facebook, chạm mở phong bì lụa, cánh thiệp mở ra mượt mà và nhạc cưới du dương cất lên.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Phong bì mở êm ái</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Âm thanh tự phát sau chạm</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Khử chớp nháy Layout Shift</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="mayhen_admin_card" style="margin-bottom: 16px; padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        2
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 4px;">
                            Giai Đoạn 2: Duyệt 10 Section Tiêu Chuẩn & Đồng Bộ Live Sync
                        </h4>
                        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Khách cuộn dọc xem câu chuyện tình yêu, thông tin phụ mẫu hai bên, đồng hồ đếm ngược, bản đồ Google Maps và album 10 ảnh cưới.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">10/10 Section hiển thị đầy đủ</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Bản đồ chỉ đường mở đúng toạ độ</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Lịch cưới đồng bộ Apple/Google Calendar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="mayhen_admin_card" style="margin-bottom: 16px; padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        3
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 4px;">
                            Giai Đoạn 3: Xác Nhận Tham Dự RSVP Trực Quan
                        </h4>
                        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Quan khách lựa chọn "Có tham dự", chọn số người đi cùng (người lớn/trẻ em), ghi chú khẩu vị ăn chay hoặc dị ứng.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Form RSVP phản hồi tức thì</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Tự động tính sĩ số bàn tiệc</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Chống spam CSRF & Throttle</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="mayhen_admin_card" style="margin-bottom: 16px; padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        4
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 4px;">
                            Giai Đoạn 4: Sổ Ký Tên Điện Tử & Gửi Lời Chúc Mừng
                        </h4>
                        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Quan khách gửi tâm tình, lời chúc phúc vào lưu bút kỹ thuật số, xuất hiện rực rỡ với tem niêm phong sáp hoặc hiệu ứng stardust.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Hiệu ứng thẻ chúc bay bổng</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Lọc từ ngữ nhạy cảm tự động</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 5 -->
            <div class="mayhen_admin_card" style="padding: 20px;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #E8F5E9; color: #1B5E20; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        5
                    </div>
                    <div style="flex: 1;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: #1B5E20; margin: 0 0 4px;">
                            Giai Đoạn 5: Quét Mã QR & Tiếp Tân Đón Tiếp Tại Sảnh Tiệc
                        </h4>
                        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Ngày cưới đến, khách quét QR cá nhân tại bàn tiếp tân, hệ thống tra cứu số bàn ngay và điểm danh chỉ trong 1 giây.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Điểm danh 1 chạm</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Thông báo số bàn tiệc</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Đồng bộ Rescue Cockpit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

