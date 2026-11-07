@extends('client.pages.account.layout')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Màn Chiếu LED Sân Khấu: {$groom} & {$bride}";
@endphp

@section('account_breadcrumb')
    <a href="{{ route('account.invitations') }}" style="color: inherit; text-decoration: none;">Thiệp cưới</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Màn chiếu LED sân khấu</span>
@endsection

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Màn Chiếu LED Sân Khấu (Live Wedding Wall)
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Trình chiếu trực tiếp ảnh cưới, ảnh khách chụp tại bàn tiệc và lời chúc mừng thời gian thực trên màn hình LED sảnh cưới.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('live_wall.show', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <span>Mở Màn Chiếu Fullscreen</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #F2F8F5; border: 1px solid #D2E7DC; color: #1B5E3C; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div style="background: linear-gradient(135deg, #FAF8F5 0%, #F5EFEB 100%); border: 1px solid #EAE3D9; border-radius: 16px; padding: 22px 26px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #8C827A; font-weight: 600; margin-bottom: 6px;">
                Đường Link Trình Chiếu Cho Đội Kỹ Thuật Nhà Hàng
            </div>
            <div style="font-family: monospace; font-size: 16px; font-weight: 700; color: #9C7A4A;">
                {{ url('/trinh-chieu/' . $invitation->slug) }}
            </div>
            <div style="font-size: 12px; color: #7A726A; margin-top: 4px;">
                Gửi link này cho kỹ thuật viên âm thanh/ánh sáng của trung tâm tiệc cưới và nhấn F11 để bật chế độ Toàn Màn Hình.
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="navigator.clipboard.writeText('{{ url('/trinh-chieu/' . $invitation->slug) }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép Link', 2000);">
                Chép Link
            </button>
            <a href="{{ route('live_wall.show', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                Xem Thử Ngay
            </a>
        </div>
    </div>

    <form action="{{ route('live_wall.settings.update', $invitation->id) }}" method="POST">
        @csrf

        <div class="mayhen_account_section" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 16px; padding: 26px; margin-bottom: 28px;">
            <div class="mayhen_account_section_header" style="margin-bottom: 20px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #1F1D1A; margin: 0 0 4px 0;">
                    Tùy Chỉnh Giao Diện & Tốc Độ Trình Chiếu
                </h3>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Điều chỉnh giao diện phù hợp với loại màn hình LED hoặc máy chiếu của rạp tiệc.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Giao diện màu sắc -->
                <div>
                    <label class="mayhen_account_form_label" for="setting-theme">Tông màu chủ đạo</label>
                    <select name="theme" id="setting-theme" class="mayhen_account_form_input">
                        <option value="dark" {{ ($wallConfig['theme'] ?? 'dark') === 'dark' ? 'selected' : '' }}>
                            Nền Tối Sang Trọng (Velvet Espresso & Vàng Kim) — Khuyên Dùng Cho Màn LED
                        </option>
                        <option value="light" {{ ($wallConfig['theme'] ?? 'dark') === 'light' ? 'selected' : '' }}>
                            Nền Sáng Thanh Lịch (Ivory Cream & Champagne) — Phù Hợp Tiệc Ngoài Trời
                        </option>
                    </select>
                </div>

                <!-- Chế độ hiển thị -->
                <div>
                    <label class="mayhen_account_form_label" for="setting-mode">Chế độ hiển thị nội dung</label>
                    <select name="display_mode" id="setting-mode" class="mayhen_account_form_input">
                        <option value="mixed" {{ ($wallConfig['display_mode'] ?? 'mixed') === 'mixed' ? 'selected' : '' }}>
                            Kết Hợp: Ảnh Cưới / Khách Chụp (Bên Trái) + Lời Chúc Realtime (Bên Phải)
                        </option>
                        <option value="photos_only" {{ ($wallConfig['display_mode'] ?? 'mixed') === 'photos_only' ? 'selected' : '' }}>
                            Chỉ Trình Chiếu Ảnh Cưới & Ảnh Khách Chụp (Toàn Màn Hình)
                        </option>
                        <option value="wishes_only" {{ ($wallConfig['display_mode'] ?? 'mixed') === 'wishes_only' ? 'selected' : '' }}>
                            Chỉ Trình Chiếu Dòng Lời Chúc Mừng Quan Khách
                        </option>
                    </select>
                </div>

                <!-- Tốc độ chuyển cảnh -->
                <div>
                    <label class="mayhen_account_form_label" for="setting-speed">Tốc độ chuyển ảnh slideshow</label>
                    <select name="speed" id="setting-speed" class="mayhen_account_form_input">
                        <option value="4" {{ (int)($wallConfig['speed'] ?? 6) === 4 ? 'selected' : '' }}>4 giây (Nhanh)</option>
                        <option value="6" {{ (int)($wallConfig['speed'] ?? 6) === 6 ? 'selected' : '' }}>6 giây (Tiêu chuẩn - Khuyên dùng)</option>
                        <option value="8" {{ (int)($wallConfig['speed'] ?? 6) === 8 ? 'selected' : '' }}>8 giây (Chậm rãi, nhẹ nhàng)</option>
                        <option value="12" {{ (int)($wallConfig['speed'] ?? 6) === 12 ? 'selected' : '' }}>12 giây (Thong thả)</option>
                    </select>
                </div>

                <!-- Mã PIN bảo vệ -->
                <div>
                    <label class="mayhen_account_form_label" for="setting-pin">Mã PIN bảo vệ màn chiếu (Tùy chọn)</label>
                    <input type="text" 
                        name="pin" 
                        id="setting-pin" 
                        value="{{ old('pin', $wallConfig['pin'] ?? '') }}" 
                        maxlength="10"
                        placeholder="Để trống nếu không khóa (vd: 1234)" 
                        class="mayhen_account_form_input">
                    <span style="font-size: 12px; color: #8C827A; margin-top: 4px; display: block;">
                        Nếu đặt PIN, người mở đường link màn chiếu phải nhập đúng mã để bắt đầu.
                    </span>
                </div>
            </div>

            <!-- Mã QR góc màn hình -->
            <div style="background: #FAF8F5; border: 1px dashed #D8CEBD; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <strong style="font-size: 14px; color: #1F1D1A; display: block;">Hiển thị mã QR tương tác ở góc màn hình</strong>
                    <span style="font-size: 12px; color: #7A726A;">
                        Cho phép khách mời tại bàn tiệc quét mã QR để gửi lời chúc hoặc đăng ảnh trực tiếp lên màn hình LED.
                    </span>
                </div>
                <input type="hidden" name="show_qr" value="0">
                <input type="checkbox" name="show_qr" value="1" {{ !isset($wallConfig['show_qr']) || $wallConfig['show_qr'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #C5A880; cursor: pointer;">
            </div>

            <div style="margin-top: 24px;">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Lưu Cấu Hình Màn Chiếu</span>
                </button>
            </div>
        </div>
    </form>
@endsection

